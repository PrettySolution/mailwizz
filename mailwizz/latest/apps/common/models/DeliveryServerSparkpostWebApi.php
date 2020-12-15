<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSparkpostWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.6
 *
 */

class DeliveryServerSparkpostWebApi extends DeliveryServer
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
    protected $serverType = 'sparkpost-web-api';

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
    protected $_providerUrl = 'https://www.sparkpost.com/';

    /**
     * @var string
     */
    public $ip_pool = '';

	/**
	 * @var string
	 */
	public $region = 'us';
	
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('password', 'required'),
            array('password', 'length', 'max' => 255),
            
            array('ip_pool', 'length', 'max' => 255),
	        array('region', 'in', 'range' => array_keys($this->getRegionsList())),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'password' => Yii::t('servers', 'Api key'),
            'ip_pool'  => Yii::t('servers', 'Ip pool'),
            'region'   => Yii::t('servers', 'Api region'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'password' => Yii::t('servers', 'One of your sparkpost api keys.'),
            'ip_pool'  => Yii::t('servers', 'Your dedicated IP Pool, only if you have any.'),
            'region'   => Yii::t('servers', 'Sparkpost api geo region.'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'password' => Yii::t('servers', 'Api key'),
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

        $replyToEmail = $replyToName = null;
        if (!empty($params['replyTo'])) {
            list($replyToEmail, $replyToName) = $this->getMailer()->findEmailAndName($params['replyTo']);
        }

        $headerPrefix = Yii::app()->params['email.custom.header.prefix'];
        $headers = array();
        if (!empty($params['headers'])) {
            $headers = $this->parseHeadersIntoKeyValue($params['headers']);
        }
        $headers['X-Sender']   = $fromEmail;
        $headers['X-Receiver'] = $toEmail;
        $headers[$headerPrefix . 'Mailer'] = 'Sparkpost Web API';

        $campaignId = StringHelper::random(40);
        $metaData   = array();
        if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
            $metaData['campaign_uid'] = $campaignId = $headers[$headerPrefix . 'Campaign-Uid'];
        }
        if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
            $metaData['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
        }

        $sent = false;

        try {
            if (!$this->preCheckWebHook()) {
                throw new Exception($this->_preCheckError);
            }

            $sendParams = array(
                'campaign_id' => $campaignId,
                'metadata'    => (object)$metaData,
                'recipients'  => array(
                    array(
                        'address' => array(
                            'email' => $toEmail,
                            'name'  => $toName,
                        ),
                        'metadata'  => (object)$metaData,
                    ),
                ),
                'content'   => array(
                    'from' => array(
                        'email' => $fromEmail,
                        'name'  => $fromName,
                    ),
                    'subject'  => $params['subject'],
                    'reply_to' => !empty($replyToEmail) ? $replyToEmail : $fromEmail,
                    'headers'  => $headers,
                    'text'     => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
                    'html'     => $params['body'],
                ),
            );
            
            // 1.3.7
            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                $sendParams['content']['attachments'] = array();
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $sendParams['content']['attachments'][] = array(
                            'type' => 'application/octet-stream',
                            'name' => basename($attachment),
                            'data' => base64_encode(file_get_contents($attachment))
                        );
                    }
                }
            }
            //

            // 1.4.5
            if (!empty($this->ip_pool)) {
                $sendParams['options']['ip_pool'] = $this->ip_pool;
            }

            // unset the html content
            if ($onlyPlainText) {
                unset($sendParams['content']['html']);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->getApiUrl('transmissions'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, CJSON::encode($sendParams));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                sprintf("Authorization: %s", $this->password),
                "Accept: application/json"
            ));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception($error);
            }
            curl_close($ch);

            $response = CJSON::decode($response, false);
            if (!empty($response->errors)) {
                $errors = array();
                foreach ($response->errors as $error) {
                    $errors[] = $error->message . (!empty($error->description) ? ' - ' . $error->description : '');
                }
                throw new Exception(implode("<br />", $errors));
            }
            
            if (empty($response->results)) {
                throw new Exception(print_r($response, true));
            }

            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => $response->results->id);
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
        $params['transport'] = self::TRANSPORT_SPARKPOST_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('ip_pool', (string)$this->ip_pool);
	    $this->getModelMetaData()->add('region', (string)$this->region);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->ip_pool     = (string)$this->getModelMetaData()->itemAt('ip_pool');
	    $this->region      = (string)$this->getModelMetaData()->itemAt('region');
        $this->_initStatus = $this->status;
        $this->hostname    = 'web-api.sparkpost.com';
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->ip_pool     = (string)$this->getModelMetaData()->itemAt('ip_pool');
	    $this->region      = (string)$this->getModelMetaData()->itemAt('region');
        $this->_initStatus = $this->status;
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    protected function afterDelete()
    {
        try {
            $this->deleteWebhook();
        } catch (Exception $e) {

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
        
        try {
            $this->deleteWebhook();
        } catch (Exception $e) {}
        
        try {
            $this->createWebhook();
        } catch (Exception $e) {
            $this->_preCheckError = $e->getMessage();
        }

        if ($this->_preCheckError) {
            return false;
        }

        return $this->save(false);
    }

    /**
     * @throws Exception
     */
    public function createWebhook()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrl('webhooks'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, CJSON::encode(array(
            'name'       => 'MWZ-#' . $this->server_id,
            'target'     => $this->getDswhUrl(),
            'auth_token' => $this->password,
            'events'     => array('bounce', 'spam_complaint', 'list_unsubscribe', 'link_unsubscribe')
        )));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            sprintf("Authorization: %s", $this->password),
        ));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error);
        }
        curl_close($ch);

        $response = CJSON::decode($response, false);
        if (!empty($response->errors)) {
            $errors = array();
            foreach ($response->errors as $error) {
                $errors[] = $error->message . (!empty($error->description) ? ' - ' . $error->description : '');
            }
            $error = Yii::t('servers', 'When creating the webhooks, we got following errors: {error}', array(
                '{error}' => implode("<br />", $errors)
            ));
            throw new Exception($error);
        }
    }

    /**
     * @throws Exception
     */
    public function deleteWebhook()
    {
        $url = $this->getDswhUrl();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrl('webhooks'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            sprintf("Authorization: %s", $this->password),
            "Accept: application/json"
        ));

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception($error);
        }
        curl_close($ch);

        $ids = array();
        $response = CJSON::decode($response, false);
        if (!empty($response->results)) {
            foreach ($response->results as $result) {
                if ($result->target == $url) {
                    $ids[] = $result->id;
                }
            }
        }

        foreach ($ids as $id) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->getApiUrl('webhooks/' . $id));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json",
                sprintf("Authorization: %s", $this->password),
            ));

            curl_exec($ch);
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception($error);
            }
            curl_close($ch);
        }
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
            'hostname'                => null,
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'signing_enabled'         => null,
            'max_connection_messages' => null,
            'bounce_server_id'        => null,
            'force_sender'            => null,
            'ip_pool'                 => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'ip_pool', $this->getHtmlOptions('ip_pool')),
            ),
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

	/**
	 * @return string
	 */
	public function getApiUrl($append = '')
	{
		$apiUrl = 'https://api.sparkpost.com/api/v1';
		if ($this->region === self::REGION_EU) {
			$apiUrl = 'https://api.eu.sparkpost.com/api/v1';
		}
		
		return rtrim($apiUrl, '/') . ($append ? '/' . trim($append, '/') : '');
	}
}
