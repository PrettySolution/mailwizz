<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerTipimailWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 *
 */

class DeliveryServerTipimailWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'tipimail-web-api';

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
    protected $_providerUrl = 'https://www.tipimail.com/';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('username, password', 'required'),
            array('password', 'length', 'max' => 255),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'username'   => Yii::t('servers', 'SMTP username'),
            'password'   => Yii::t('servers', 'Api key'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'username' => Yii::t('servers', 'Your smtp username'),
            'password' => Yii::t('servers', 'Your api key'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'username'   => 'dd623d60cc62d890cabb00c4cb716333',
            'password'   => '123a15725f4b676fd79d746c7d9d0b21',
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
        $replyToName  = null;
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
        $headers['Reply-To']   = $replyToEmail;
        $headers[$headerPrefix . 'Mailer'] = 'Mailjet Web API';

        $metaData   = array();
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
            
            $messageClass = '\Tipimail\Messages\Message';
            $message      = new $messageClass();
            
            $subject = sprintf('=?%s?B?%s?=', strtolower(Yii::app()->charset), base64_encode($params['subject']));
            
            $message->addTo($toEmail, sprintf('=?%s?B?%s?=', strtolower(Yii::app()->charset), base64_encode($toName)));
            $message->setFrom($fromEmail, sprintf('=?%s?B?%s?=', strtolower(Yii::app()->charset), base64_encode($fromName)));
            $message->setSubject($subject);
            
            if ($replyToEmail) {
                $message->setReplyTo($replyToEmail, $replyToName);
            }
            
            $message->setText(!empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']));
            $message->setApiKey($this->password);

            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $_attachments = array_unique($params['attachments']);
                foreach ($_attachments as $attachment) {
                    if (is_file($attachment)) {
                        $fileName = basename($attachment);
                        $message->addAttachmentFromFile($attachment, $fileName);
                    }
                }
            }
            
            if (!$onlyPlainText) {
                $message->setHtml($params['body']);
            }

            $message->setMeta($metaData);
            
            $this->getClient()->getMessagesService()->send($message);

            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => StringHelper::random(40));

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
     * @return mixed
     */
    public function getClient()
    {
        static $clients = array();
        $id = (int)$this->server_id;
        if (!empty($clients[$id])) {
            return $clients[$id];
        }
        $className = '\Tipimail\Tipimail';
        return $clients[$id] = new $className($this->username, $this->password);
    }

    /**
     * @return bool|string
     */
    public function requirementsFailed()
    {
        if (!version_compare(PHP_VERSION, '5.3', '>=')) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => 5.3,
            ));
        }
        return false;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParamsArray(array $params = array())
    {
        $params['transport'] = self::TRANSPORT_TIPIMAIL_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->_initStatus = $this->status;
        $this->hostname    = 'web-api.tipimail.com';
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
        return true;
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
