<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerMandrillWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 *
 */

class DeliveryServerMandrillWebApi extends DeliveryServer
{
    protected $serverType = 'mandrill-web-api';

    protected $_initStatus;

    protected $_preCheckError;

    public $subaccount;

    public $webhook = array();

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('username, password', 'required'),
            array('password, subaccount', 'length', 'max' => 255),
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
            'subaccount' => Yii::t('servers', 'Subaccount'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'username'    => Yii::t('servers', 'Your mandrill account username.'),
            'password'    => Yii::t('servers', 'One of your mandrill api keys.'),
            'subaccount'  => Yii::t('servers', 'The subaccount name, optional.'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

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
            list($replyToEmail) = $this->getMailer()->findEmailAndName($params['replyTo']);
        }

        $headerPrefix = Yii::app()->params['email.custom.header.prefix'];

        $headers = array();
        if (!empty($params['headers'])) {
            $headers = $this->parseHeadersIntoKeyValue($params['headers']);
        }

        $headers['Reply-To']    = !empty($replyToEmail) ? $replyToEmail : $fromEmail;
        $headers['X-Sender']    = $fromEmail;
        $headers['X-Receiver']  = $toEmail;
        $headers[$headerPrefix . 'Mailer'] = 'Mandrill Web API';

        if (!isset($headers['Return-Path']) && !empty($params['returnPath'])) {
            list($returnPathEmail) = $this->getMailer()->findEmailAndName($params['returnPath']);
            $headers['Return-Path'] = $returnPathEmail;
        }

        $recipientMetaData = array('rcpt' => $toEmail, 'values' => array());
        if (isset($headers[$headerPrefix . 'Campaign-Uid'])) {
            $recipientMetaData['values']['campaign_uid'] = $headers[$headerPrefix . 'Campaign-Uid'];
        }
        if (isset($headers[$headerPrefix . 'Subscriber-Uid'])) {
            $recipientMetaData['values']['subscriber_uid'] = $headers[$headerPrefix . 'Subscriber-Uid'];
        }

        $sent = false;

        try {
            if (!$this->preCheckWebHook()) {
                throw new Exception($this->_preCheckError);
            }

            $message = array(
                'html'       => $params['body'],
                'text'       => !empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']),
                'subject'    => $params['subject'],
                'from_email' => $fromEmail,
                'from_name'  => $fromName,
                'to'         => array(
                    array(
                        'email' => $toEmail,
                        'name'  => $toName,
                        'type'  => 'to'
                    )
                ),
                'headers'               => $headers,
                'important'             => false,
                'auto_text'             => false,
                'auto_html'             => false,
                'inline_css'            => false,
                'url_strip_qs'          => false,
                'preserve_recipients'   => true,
                'view_content_link'     => false,
                'bcc_address'           => null,
                'tracking_domain'       => null,
                'signing_domain'        => null,
                'return_path_domain'    => null,
                'merge'                 => false,
                'tags'                  => array('mailing'),
                'recipient_metadata'    => array($recipientMetaData),
                'attachments'           => array(),
                'images'                => array(),
            );

            if (!empty($this->subaccount)) {
                $message['subaccount'] = $this->subaccount;
            }

            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $message['attachments'][] = array(
                            'type'    => 'application/octet-stream',
                            'name'    => basename($attachment),
                            'content' => base64_encode(file_get_contents($attachment)),
                        );
                    }
                }
            }

            if (!$onlyPlainText && !empty($params['embedImages']) && is_array($params['embedImages'])) {
                $cids = array();
                foreach ($params['embedImages'] as $imageData) {
                    if (!isset($imageData['path'], $imageData['cid'])) {
                        continue;
                    }
                    if (is_file($imageData['path'])) {
                        $cids['cid:' . $imageData['cid']] = base64_encode(file_get_contents($imageData['path']));
                        $imageData['mime'] = empty($imageData['mime']) ? 'image/jpg' : $imageData['mime'];
                        $message['images'][] = array(
                            'type'    => $imageData['mime'],
                            'name'    => $imageData['cid'],
                            'content' => $cids['cid:' . $imageData['cid']],
                        );
                    }
                }
                $message['html'] = str_replace(array_keys($cids), array_values($cids), $message['html']);
                unset($cids);
            }
            
            if ($onlyPlainText) {
                unset($message['html']);
            }

            $async  = true; // this returns queued status
            $ipPool = 'Main Pool';
            $sendAt = '';
            $result = $this->getClient()->messages->send($message, $async, $ipPool, $sendAt);

            if (!empty($result[0]) && !empty($result[0]['status']) && in_array($result[0]['status'], array('sent', 'queued'))) {
                if (!empty($result[0]['reject_reason']) && stripos($result[0]['reject_reason'], 'bounce') !== false) {
                    if (stripos($result[0]['reject_reason'], 'hard-bounce') !== false) {
                        $this->getMailer()->addLog($result[0]['reject_reason']);
                        EmailBlacklist::addToBlacklist($toEmail, $result[0]['reject_reason']);
                    }
                } else {
                    $this->getMailer()->addLog('OK');
                    $sent = array('message_id' => $result[0]['_id']);
                }
            }  else {
                throw new Exception(Yii::t('servers', 'Unable to make the delivery!'));
            }
        } catch (Exception $e) {
            $this->getMailer()->addLog(get_class($e) . ' - ' . $e->getMessage());
        }

        if ($sent) {
            $this->logUsage();
        }

        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return $sent;
    }

    public function getParamsArray(array $params = array())
    {
        $params['transport'] = self::TRANSPORT_MANDRILL_WEB_API;
        return parent::getParamsArray($params);
    }

    public function requirementsFailed()
    {
        if (!MW_COMPOSER_SUPPORT) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => 5.3,
            ));
        }
        return false;
    }

    public function getClient()
    {
        static $clients = array();
        $id = (int)$this->server_id;
        if (!empty($clients[$id])) {
            return $clients[$id];
        }
        return $clients[$id] = new Mandrill($this->password);
    }

    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->_initStatus = $this->status;
        $this->hostname    = 'web-api.mandrill.com';
        $this->subaccount  = $this->getModelMetaData()->itemAt('subaccount');
        $this->webhook     = (array)$this->getModelMetaData()->itemAt('webhook');
    }

    protected function afterFind()
    {
        $this->_initStatus = $this->status;
        $this->subaccount  = $this->getModelMetaData()->itemAt('subaccount');
        $this->webhook     = (array)$this->getModelMetaData()->itemAt('webhook');
        parent::afterFind();
    }

    protected function beforeSave()
    {
        $this->getModelMetaData()->add('subaccount', $this->subaccount);
        $this->getModelMetaData()->add('webhook', (array)$this->webhook);
        return parent::beforeSave();
    }

    protected function afterDelete()
    {
        if (!empty($this->webhook['id'])) {
            try {
                $this->getClient()->webhooks->delete($this->webhook['id']);
            } catch(Mandrill_Error $e) {}
        }
        parent::afterDelete();
    }

    protected function preCheckWebHook()
    {
        if (MW_IS_CLI || $this->isNewRecord || $this->_initStatus !== self::STATUS_INACTIVE) {
            return true;
        }
        
        $url         = $this->getDswhUrl();
        $events      = array('hard_bounce', 'soft_bounce', 'spam', 'unsub', 'reject', 'blacklist');
        $description = 'Notifications Webhook - DO NOT ALTER THIS IN ANY WAY!';
        
        if (!is_array($this->webhook)) {
            $this->webhook = array();
        }

        if (!empty($this->webhook['id'])) {
            try {
                $info = $this->getClient()->webhooks->info($this->webhook['id']);
                if ($info['url'] != $url || $info['auth_key'] != $this->webhook['auth_key']) {
                    $this->webhook = $this->getClient()->webhooks->update($this->webhook['id'], $url, $description, $events);
                }
            } catch(Mandrill_Error $e) {
                try {
                    $this->getClient()->webhooks->delete($this->webhook['id']);
                } catch(Mandrill_Error $exception) {}
                $this->webhook = array();
                $this->webhook['error'] = get_class($e) . ' - ' . $e->getMessage();
                $this->_preCheckError = $this->webhook['error'];
                return false;
            }
        }

        if (empty($this->webhook)) {
            try {
                $this->webhook = $this->getClient()->webhooks->add($url, $description, $events);
            } catch(Mandrill_Error $e) {
                $this->webhook['error'] = get_class($e) . ' - ' . $e->getMessage();
                $this->_preCheckError = $this->webhook['error'];
                return false;
            }
        }

        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function getCanEmbedImages()
    {
        return true;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
        $form = new CActiveForm();
        return parent::getFormFieldsDefinition(CMap::mergeArray(array(
            'hostname'                => null,
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'bounce_server_id'        => null,
            'max_connection_messages' => null,
            'signing_enabled'         => null,
            'force_sender'            => null,
            'subaccount'              => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'subaccount', $this->getHtmlOptions('subaccount')),
            )
        ), $params));
    }
}
