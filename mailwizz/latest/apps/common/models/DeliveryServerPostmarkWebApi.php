<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerPostmarkWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.2
 *
 */

class DeliveryServerPostmarkWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'postmark-web-api';

    /**
     * @var string 
     */
    protected $_providerUrl = 'https://postmarkapp.com/';
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('password', 'required'),
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
            'password' => Yii::t('servers', 'Server api token'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'password' => Yii::t('servers', 'The server api token from your postmark account'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'password' => Yii::t('servers', 'Server api token'),
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
        $headers[$headerPrefix . 'Mailer'] = 'Postmark Web API';
        
        $sent = false;
        $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
        
        try {

            $sendParams = array(
                'To'            => $toEmail,
                'From'          => $fromEmail,
                'ReplyTo'       => $replyToEmail,
                'Headers'       => $headers,
                'Subject'       => $params['subject'],
                'TextBody'      => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
            );
            
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                $sendParams['Attachments'] = array();
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $sendParams['Attachments'][] = array(
                            'Name'          => basename($attachment),
                            'Content'       => base64_encode(file_get_contents($attachment)),
                            'ContentType'   => 'application/octet-stream',
                        );
                    }
                }
            }
            
            if (!$onlyPlainText) {
                $sendParams['HtmlBody'] = $params['body'];
            }

            $response = $this->getClient()->sendEmailBatch(array($sendParams));
            if (empty($response) || empty($response[0]) || empty($response[0]['MessageID'])) {
                throw new Exception(json_encode((array)$response));
            }

            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => $response[0]['MessageID']);
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
        $params['transport'] = self::TRANSPORT_POSTMARK_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return bool|string
     */
    public function requirementsFailed()
    {
        if (!version_compare(PHP_VERSION, '5.5', '>=')) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => '5.5',
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
        $className = '\Postmark\PostmarkClient';
        return $clients[$id] = new $className($this->password);
    }


    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = 'web-api.postmark.com';
    }
    
    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
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
        ), $params));
    }
}
