<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerNewsManWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 *
 */

class DeliveryServerNewsManWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'newsman-web-api';

    /**
     * @var string 
     */
    protected $_providerUrl = 'https://www.newsmanapp.com/';

    /**
     * @var string
     */
    protected $_initStatus;
    
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
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'username'  => Yii::t('servers', 'Account ID'),
            'password'  => Yii::t('servers', 'Api key'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'username'  => Yii::t('servers', 'Account ID'),
            'password'  => Yii::t('servers', 'Api key'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'username'  => Yii::t('servers', 'Account ID'),
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
        
        $headerPrefix = Yii::app()->params['email.custom.header.prefix'];
        $headers = array();
        if (!empty($params['headers'])) {
            $headers = $this->parseHeadersIntoKeyValue($params['headers']);
        }
        $headers['X-Sender']      = $fromEmail;
        $headers['X-Receiver']    = $toEmail;
        $headers['X-NZ-Metadata'] = sha1($toEmail . $toName);
        $headers[$headerPrefix . 'Mailer'] = 'NewsMan Web API';
        
        $metaData   = array();
        if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
            $metaData['campaign_uid'] = $campaignId = $headers[$headerPrefix . 'Campaign-Uid'];
        }
        if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
            $metaData['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
        }

        $sent = false;

        try {
     
            $sendParams = array(
                'key'        => $this->password,
                'account_id' => $this->username,
                'message'    => array(
                    'from_name'  => $fromName,
                    'from_email' => $fromEmail,
                    'html'       => $params['body'],
                    'text'       => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
                    'headers'    => array(),
                    'subject'    => $params['subject'],
                    'template_engine' => 'handlebars',
                ),
                'recipients' => array(
                    array(
                        'email' => $toEmail,
                        'name'  => $toName,
                    )
                ),
            );
            
            // 1.3.7
            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                $sendParams['message']['attachments'] = array();
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $sendParams['message']['attachments'][] = array(
                            'name'          => basename($attachment),
                            'content_type'  => 'application/octet-stream',
                            'data'          => base64_encode(file_get_contents($attachment))
                        );
                    }
                }
            }
            //
            
            if ($onlyPlainText) {
                unset($sendParams['message']['html']);
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://cluster.newsmanapp.com/api/1.0/message.send");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, CJSON::encode($sendParams));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-Type: application/json"
            ));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception($error);
            }
            curl_close($ch);

            $response = CJSON::decode($response, true);
            
            if (!empty($response['err'])) {
                throw new Exception($response['err']);
            }
            
            if (empty($response[0]) || $response[0]['status'] != 'queued') {
                throw new Exception(print_r($response, true));
            }
            
            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => $response[0]['send_id']);
            
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
        $params['transport'] = self::TRANSPORT_NEWSMAN_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->_initStatus = $this->status;
        $this->hostname    = 'web-api.newsmansmtp.ro';
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

    /**
     * @return string
     */
    public function getDswhUrl()
    {
        $url = Yii::app()->options->get('system.urls.frontend_absolute_url') . 'dswh/newsman';
        if (MW_IS_CLI) {
            return $url;
        }
        if (Yii::app()->request->isSecureConnection && parse_url($url, PHP_URL_SCHEME) == 'http') {
            $url = substr_replace($url, 'https', 0, 4);
        }
        return $url;
    }
}
