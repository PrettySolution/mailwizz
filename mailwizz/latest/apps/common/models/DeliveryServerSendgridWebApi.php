<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSendgridWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 *
 */

class DeliveryServerSendgridWebApi extends DeliveryServer
{
	/**
	 * @var string
	 */
	protected $serverType = 'sendgrid-web-api';

	/**
	 * @var string
	 */
	protected $_initStatus;

	/**
	 * @var string
	 */
	protected $_preCheckError;

	/**
	 * @var string
	 */
	protected $_providerUrl = 'https://sendgrid.com/';

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('username, password', 'required'),
			array('username, password', 'length', 'max' => 255),
		);
		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array
	 */
	public function attributeLabels()
	{
		$texts = array(
			'password'  => Yii::t('servers', 'Api key'),
		);

		return CMap::mergeArray(parent::attributeLabels(), $texts);
	}

	/**
	 * @return array
	 */
	public function attributeHelpTexts()
	{
		$texts = array(
			'username'  => Yii::t('servers', 'Your sendgrid username.'),
			'password'  => Yii::t('servers', 'One of your sendgrid api key.'),
		);

		return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DeliveryServer the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @param array $params
	 * @return array|bool
	 */
	public function sendEmail(array $params = array())
	{
		$params = (array)Yii::app()->hooks->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);

		if (!ArrayHelper::hasKeys($params, array('from', 'to', 'subject', 'body'))) {
			return false;
		}

		list($toEmail, $toName)     = $this->getMailer()->findEmailAndName($params['to']);
		list($fromEmail, $fromName) = $this->getMailer()->findEmailAndName($params['from']);

		if (!empty($params['fromName'])) {
			$fromName = $params['fromName'];
		}

		$replyToEmail = $replyToName = null;
		if (!empty($params['replyTo'])) {
			list($replyToEmail, $replyToName) = $this->getMailer()->findEmailAndName($params['replyTo']);
		}

		$headerPrefix = Yii::app()->params['email.custom.header.prefix'];
		$headers = array();
		if (!empty($params['headers'])) {
			$headers = $this->parseHeadersIntoKeyValue($params['headers']);
		}

		$headers['X-Sender']    = $fromEmail;
		$headers['X-Receiver']  = $toEmail;
		$headers[$headerPrefix . 'Mailer'] = 'Sendgrid Web API';

		$customArgs = array(
			'date' => date('Y-m-d H:i:s'),
		);

		if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
			$customArgs['campaign_uid'] = $headers[$headerPrefix . 'Campaign-Uid'];
		}
		if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
			$customArgs['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
		}

		$sent = false;

		try {

			if (!$this->preCheckWebHook()) {
				throw new Exception($this->_preCheckError);
			}

			$data = array(
				'personalizations' => array(
					array(
						'subject' => $params['subject'],
						'to' => array(
							array(
								'email' => $toEmail,
								'name'  => sprintf('=?%s?B?%s?=', strtolower(Yii::app()->charset), base64_encode($toName))
							),
						),
						'custom_args' => $customArgs,
						'headers'     => $headers,
					)
				),
				'from' => array(
					'email' => $fromEmail,
					'name'  => $fromName,
				),
				'reply_to' => array(
					'email' => $replyToEmail,
					'name'  => $replyToName,
				),
				'content' => array(),
			);

			$onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
			if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
				$attachments = array_unique($params['attachments']);
				$data['attachments'] = array();
				foreach ($attachments as $attachment) {
					if (is_file($attachment)) {
						$data['attachments'][] = array(
							'content'    => base64_encode(file_get_contents($attachment)),
							'type'       => pathinfo($attachment, PATHINFO_EXTENSION),
							'filename'   => basename($attachment),
							'content_id' => StringHelper::random(20),
						);
					}
				}
			}

			$data['content'][] = array(
				'type'  => 'text/plain',
				'value' => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
			);

			if (!$onlyPlainText) {
				$data['content'][] = array(
					'type'  => 'text/html',
					'value' => $params['body'],
				);
			}

			$result = $this->getClient()->client->mail()->send()->post($data);

			if ($result->statusCode() >= 200 && $result->statusCode() < 300) {
				$this->getMailer()->addLog('OK');
				$sent = array('message_id' => StringHelper::random(60));
			} elseif ($result->body()) {
				throw new Exception($result->body());
			} else {
				throw new Exception(Yii::t('servers', 'Unable to make the delivery!'));
			}

		} catch (Exception $e) {
			$this->getMailer()->addLog($e->getMessage());
		}

		if ($sent) {
			$this->logUsage();
		}

		Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

		return $sent;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getParamsArray(array $params = array())
	{
		$params['transport'] = self::TRANSPORT_SENDGRID_WEB_API;
		return parent::getParamsArray($params);
	}

	/**
	 * @return bool|string
	 */
	public function requirementsFailed()
	{
		if (!version_compare(PHP_VERSION, '5.6', '>=')) {
			return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
				'{type}'    => $this->serverType,
				'{version}' => '5.6',
			));
		}
		return false;
	}

	/**
	 * @return mixed
	 */
	public function getClient()
	{
		static $clients = array();
		$id = (int)$this->server_id;
		if (!empty($clients[$id])) {
			return $clients[$id];
		}
		$className = '\SendGrid';
		return $clients[$id] = new $className($this->password, array(
			'turn_off_ssl_verification' => true,
		));
	}

	/**
	 * @inheritdoc
	 */
	protected function afterConstruct()
	{
		parent::afterConstruct();
		$this->_initStatus = $this->status;
		$this->hostname    = 'web-api.sendgrid.com';
	}

	/**
	 * @inheritdoc
	 */
	protected function afterFind()
	{
		$this->_initStatus = $this->status;
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	protected function preCheckWebHook()
	{
		if (MW_IS_CLI || $this->isNewRecord || $this->_initStatus !== self::STATUS_INACTIVE) {
			return true;
		}

		$postValues = array(
			'api_user'  => $this->username,
			'api_key'   => $this->password,
			'name'      => 'eventnotify',
			'processed' => 0,
			'dropped'   => 1,
			'deferred'  => 1,
			'delivered' => 0,
			'bounce'    => 1,
			'click'     => 0,
			'open'      => 0,
			'unsubscribe' => 0,
			'spamreport'=> 1,
			'url'       => $this->getDswhUrl(),
			'version'   => 3,
		);

		try {

			/** @var SendGrid\Client $client */
			$client = $this->getClient()->client->user();

			/** @var SendGrid\Client $client */
			$webhook = $client->webhooks()->event()->settings();

			/** @var Sendgrid\Response $result */
			$result = $webhook->patch(array(
				'enabled'           => true,
				'url'               => $this->getDswhUrl(),
				'group_resubscribe' => false,
				'delivered'         => false,
				'spam_report'       => true,
				'bounce'            => true,
				'deferred'          => true,
				'unsubscribe'       => false,
				'processed'         => false,
				'open'              => false,
				'click'             => false,
				'dropped'           => true,
			));

			if ((int)$result->statusCode() !== 200) {
				throw new Exception((string)$result->body());
			}

			$resp = json_decode((string)$result->body());
			if (empty($resp) || empty($resp->url) || $resp->url != $this->getDswhUrl()) {
				throw new Exception((string)$result->body());
			}

		} catch (Exception $e) {
			$this->_preCheckError = $e->getMessage();

			if (empty($this->_preCheckError)) {
				$this->_preCheckError = Yii::t('servers', 'Unknown error!');
			}
		}

		if ($this->_preCheckError) {
			return false;
		}

		return $this->save(false);
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getFormFieldsDefinition(array $params = array())
	{
		return parent::getFormFieldsDefinition(CMap::mergeArray(array(
			'hostname'                => null,
			'port'                    => null,
			'protocol'                => null,
			'timeout'                 => null,
			'signing_enabled'         => null,
			'max_connection_messages' => null,
			'bounce_server_id'        => null,
			'force_sender'            => null,
		), $params));
	}
}
