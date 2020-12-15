<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerMailgunWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 *
 */

class DeliveryServerMailgunWebApi extends DeliveryServer
{
	/**
	 * US region
	 */
	const REGION_US = 'us';

	/**
	 * EU region
	 */
	const REGION_EU = 'eu';

	/**
	 * @var string
	 */
	protected $serverType = 'mailgun-web-api';

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
	protected $_providerUrl = 'https://www.mailgun.com/';

	/**
	 * @var array
	 */
	public $webhooks = array();

	/**
	 * @var string
	 */
	public $region = 'us';

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('password', 'required'),
			array('password', 'length', 'max' => 255),
			array('region', 'in', 'range' => array_keys($this->getRegionsList())),
		);
		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$labels = array(
			'hostname' => Yii::t('servers', 'Domain name'),
			'password' => Yii::t('servers', 'Api key'),
			'region'   => Yii::t('servers', 'Api region'),
		);
		return CMap::mergeArray(parent::attributeLabels(), $labels);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHelpTexts()
	{
		$texts = array(
			'hostname'  => Yii::t('servers', 'Mailgun verified domain name.'),
			'password'  => Yii::t('servers', 'Mailgun api key.'),
			'region'    => Yii::t('servers', 'Mailgun api geo region.'),
		);

		return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
	}

	/**
	 * @inheritdoc
	 */
	public function attributePlaceholders()
	{
		$placeholders = array(
			'hostname'  => Yii::t('servers', 'Domain name'),
			'password'  => Yii::t('servers', 'Api key'),
		);

		return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
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

		$replyToEmail = null;
		if (!empty($params['replyTo'])) {
			list($replyToEmail) = $this->getMailer()->findEmailAndName($params['replyTo']);
		}

		$headerPrefix = Yii::app()->params['email.custom.header.prefix'];
		$metaData     = array();

		$headers = array();
		if (!empty($params['headers'])) {
			$headers = $this->parseHeadersIntoKeyValue($params['headers']);
		}

		if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
			$metaData['campaign_uid'] = $headers[$headerPrefix . 'Campaign-Uid'];
		}
		if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
			$metaData['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
		}

		$sent = false;

		try {

			if (!$this->preCheckWebHook()) {
				throw new Exception($this->_preCheckError);
			}

			$message = array(
				'from'       => sprintf('=?%s?B?%s?= <%s>', strtolower(Yii::app()->charset), base64_encode($fromName), $fromEmail),
				'to'         => sprintf('=?%s?B?%s?= <%s>', strtolower(Yii::app()->charset), base64_encode($toName), $toEmail),
				'subject'    => $params['subject'],
				'text'       => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
				'html'       => $params['body'],
				'o:tag'      => array('bulk-mail'),
				'v:metadata' => CJSON::encode($metaData),
			);

			// since 1.5.2
			foreach ($headers as $headerName => $headerValue) {
				$message['h:' . $headerName] = $headerValue;
			}

			if (!empty($replyToEmail)) {
				$message['h:Reply-To'] = $replyToEmail;
			}

			$onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
			if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
				$attachments = array_filter(array_unique($params['attachments']));
				$message['attachment'] = array();
				foreach ($attachments as $attachment) {
					if (is_file($attachment)) {
						$message['attachment'][] = array(
							'filePath' => $attachment,
							'fileName' => basename($attachment),
						);
					}
				}
			}

			if ($onlyPlainText) {
				unset($message['html']);
			}

			$result = $this->getClient()->messages()->send($this->hostname, $message);
			if (is_object($result) && $result->getId()) {
				$this->getMailer()->addLog('OK');
				$sent = array('message_id' => str_replace(array('<', '>'), '', $result->getId()));
			} else {
				throw new Exception(Yii::t('servers', 'Unable to make the delivery!') . print_r($result, true));
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
		$params['transport'] = self::TRANSPORT_MAILGUN_WEB_API;
		return parent::getParamsArray($params);
	}

	/**
	 * @return bool|string
	 */
	public function requirementsFailed()
	{
		if (!version_compare(PHP_VERSION, '7.0', '>=')) {
			return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
				'{type}'    => $this->serverType,
				'{version}' => '7.0',
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
		$className = '\Mailgun\Mailgun';

		// since 1.6.3
		$params = array($this->password);
		if ($this->region === self::REGION_EU) {
			$params[] = 'https://api.eu.mailgun.net';
		}
		//

		return $clients[$id] = call_user_func_array(array($className, 'create'), $params);
	}

	/**
	 * @inheritdoc
	 */
	protected function afterConstruct()
	{
		parent::afterConstruct();
		$this->_initStatus = $this->status;
		$this->webhooks    = (array)$this->getModelMetaData()->itemAt('webhooks');
		$this->region      = (string)$this->getModelMetaData()->itemAt('region');
	}

	/**
	 * @inheritdoc
	 */
	protected function afterFind()
	{
		$this->_initStatus = $this->status;
		$this->webhooks    = (array)$this->getModelMetaData()->itemAt('webhooks');
		$this->region      = (string)$this->getModelMetaData()->itemAt('region');
		parent::afterFind();
	}

	/**
	 * @inheritdoc
	 */
	protected function beforeSave()
	{
		$this->getModelMetaData()->add('webhooks', (array)$this->webhooks);
		$this->getModelMetaData()->add('region', (string)$this->region);
		return parent::beforeSave();
	}

	/**
	 * @inheritdoc
	 */
	protected function afterDelete()
	{
		if (!empty($this->webhooks)) {
			foreach ($this->webhooks as $name => $url) {
				try {
					$this->getClient()->webhooks()->delete($this->hostname, $name);
				} catch (Exception $e) {

				}
			}
		}
		parent::afterDelete();
	}

	/**
	 * @return bool
	 */
	protected function preCheckWebHook()
	{
		if (MW_IS_CLI || $this->isNewRecord || $this->_initStatus !== self::STATUS_INACTIVE) {
			return true;
		}

		if (!is_array($this->webhooks)) {
			$this->webhooks = array();
		}

		foreach (array('bounce', 'drop', 'spam') as $webhook) {

			try {
				$this->getClient()->webhooks()->delete($this->hostname, $webhook);
			} catch (Exception $e) {

			}

			try {
				$result = $this->getClient()->webhooks()->create($this->hostname, $webhook, $this->getDswhUrl());
			} catch (Exception $e) {
				$this->_preCheckError = $e->getMessage();
			}

			if ($this->_preCheckError) {
				break;
			}

			if ($result->getWebhookUrl()) {
				$this->webhooks[$webhook] = $result->getWebhookUrl();
				$this->_preCheckError = null;
			} else {
				$this->_preCheckError = Yii::t('servers', 'Cannot create the {name} webhook!', array('{name}' => $webhook));
			}

			if ($this->_preCheckError) {
				break;
			}
		}

		if ($this->_preCheckError) {
			return false;
		}

		return $this->save(false);
	}

	/**
	 * @return bool
	 */
	public function getCanEmbedImages()
	{
		return false;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function getFormFieldsDefinition(array $params = array())
	{
		$form = new CActiveForm();
		return parent::getFormFieldsDefinition(CMap::mergeArray(array(
			'username'                => null,
			'port'                    => null,
			'protocol'                => null,
			'timeout'                 => null,
			'signing_enabled'         => null,
			'max_connection_messages' => null,
			'bounce_server_id'        => null,
			'force_sender'            => null,
			'region'                  => array(
				'visible'   => true,
				'fieldHtml' => $form->dropDownList($this, 'region', $this->getRegionsList(), $this->getHtmlOptions('region')),
			)
		), $params));
	}

	/**
	 * @return array
	 */
	public function getRegionsList()
	{
		return array(
			self::REGION_US => 'US',
			self::REGION_EU => 'EU',
		);
	}
}