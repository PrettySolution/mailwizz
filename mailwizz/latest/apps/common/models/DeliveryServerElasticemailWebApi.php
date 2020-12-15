<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerElasticemailWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 *
 */

class DeliveryServerElasticemailWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'elasticemail-web-api';

    /**
     * @var string 
     */
    protected $_providerUrl = 'https://elasticemail.com/';

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
            'username' => Yii::t('servers', 'Your elastic email account username/email.'),
            'password' => Yii::t('servers', 'One of your elastic email api keys.'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'username'  => Yii::t('servers', 'Username'),
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

        list($fromEmail, $fromName) = $this->getMailer()->findEmailAndName($params['from']);
        list($toEmail, $toName)     = $this->getMailer()->findEmailAndName($params['to']);

        if (!empty($params['fromName'])) {
            $fromName = $params['fromName'];
        }

        $replyToEmail = $replyToName = null;
        if (!empty($params['replyTo'])) {
            list($replyToEmail, $replyToName) = $this->getMailer()->findEmailAndName($params['replyTo']);
        }

        $sent = false;

        try {
            
            $postData = array(
                'username'      => $this->username,
                'api_key'       => $this->password,
                'from'          => !empty($fromEmail) ? $fromEmail : $this->from_email,
                'from_name'     => !empty($fromName) ? $fromName : $this->from_name,
                'sender'        => !empty($fromEmail) ? $fromEmail : $this->from_email,
                'sender_name'   => !empty($fromName) ? $fromName : $this->from_name,
                'reply_to'      => !empty($replyToEmail) ? $replyToEmail : $this->from_email,
                'reply_to_name' => !empty($replyToName) ? $replyToName : $this->from_name,
                'to'            => sprintf('"%s" <%s>', $toName, $toEmail),
                'subject'       => $params['subject'],
                'body_html'     => !empty($params['body']) ? $params['body'] : '',
                'body_text'     => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
                'encodingtype'  => 4, // was 3
            );
            
            if (!empty($params['headers'])) {
                $headers = $this->parseHeadersIntoKeyValue($params['headers']);
                $i = 0;
                foreach ($headers as $name => $value) {
                    $i++;
                    $postData['header' . $i] = sprintf('%s: %s', $name, $value);
                }
            }

            // attachments
            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments']) && class_exists('CURLFile', false)) {
                $attachments = array_unique($params['attachments']);
                $i = 0;
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $i++;
                        $postData['file_' . $i] = new CURLFile($attachment, 'application/octet-stream', basename($attachment));
                    }
                }
            }
            
            if ($onlyPlainText) {
                unset($postData['body_html']);
            }
            
            $response = AppInitHelper::simpleCurlPost('https://api.elasticemail.com/v2/email/send', $postData, (int)$this->timeout);
            
            if ($response['status'] != 'success') {
                throw new Exception(Yii::app()->ioFilter->stripClean($response['message']));
            }
            
            $rsp = @json_decode($response['message']);
            if (empty($rsp) || empty($rsp->success) || !$rsp->success || empty($rsp->data) || empty($rsp->data->messageid)) {
                throw new Exception(!empty($rsp->error) ? $rsp->error : $response['message']);
            }
            
            $this->getMailer()->addLog('OK');
            $sent = array('message_id' => trim($rsp->data->messageid));
            
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
        $params['transport'] = self::TRANSPORT_ELASTICEMAIL_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return bool
     */
    public function requirementsFailed()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = 'web-api.elasticemail.com';
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
