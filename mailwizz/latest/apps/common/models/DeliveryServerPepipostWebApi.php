<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerPepipostWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.1
 *
 */

class DeliveryServerPepipostWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'pepipost-web-api';

    /**
     * @var string 
     */
    protected $_providerUrl = 'https://www.pepipost.com/';
    
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
            'password' => Yii::t('servers', 'Api key'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'password' => Yii::t('servers', 'One of your pepipost api keys.'),
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
        $headers[$headerPrefix . 'Mailer'] = 'PepiPost Web API';

        $messageID = StringHelper::random(40);
        $metaData  = array(
            'message_id' => $messageID,
        );
        if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
            $metaData['campaign_uid'] = $headers[$headerPrefix . 'Campaign-Uid'];
        }
        if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
            $metaData['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
        }

        $sent = false;

        try {
            
            $sendParams = array(
                'api_key'       => $this->password,
                'email_details' => array(
                    'fromname' => $fromName,
                    'from'     => $fromEmail,
                    'subject'  => $params['subject'],
                    'replytoid'=> !empty($replyToEmail) ? $replyToEmail : $fromEmail,
                    'content'  => $params['body'],
                ),
                'X-APIHEADER' => array(CJSON::encode($metaData)),
                'settings'    => array(
                    'footer'     => 0,
                    'unsubscribe'=> 0,
                ),
                'recipients' => array($toEmail),
            );
            
            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                $sendParams['files'] = array();
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $sendParams['files'][basename($attachment)] = base64_encode(file_get_contents($attachment));
                    }
                }
            }
            
            if ($onlyPlainText) {
                $sendParams['email_details']['content'] = !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']);
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.pepipost.com/api/web.send.json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sendParams));
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $error = curl_error($ch);
                curl_close($ch);
                throw new Exception($error);
            }
            curl_close($ch);

            $response = CJSON::decode($response, false);
            if (!empty($response->errorcode)) {
                throw new Exception(json_encode($response));
            }

            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => $messageID);
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
        $params['transport'] = self::TRANSPORT_PEPIPOST_WEB_API;
        return parent::getParamsArray($params);
    }
    
    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = 'web-api.pepipost.com';
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
