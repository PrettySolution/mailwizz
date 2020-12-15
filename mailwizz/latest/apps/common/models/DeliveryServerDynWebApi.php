<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerDynWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.3
 *
 */

class DeliveryServerDynWebApi extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'dyn-web-api';

    /**
     * @var string 
     */
    protected $_providerUrl = 'https://dyn.com/email/';

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
            'password' => Yii::t('servers', 'One of your dyn.com api keys.'),
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

            $className = '\Dyn\MessageManagement';
            $mm = new $className($this->password);

            $className = '\Dyn\MessageManagement\Mail';
            $mail = new $className();

            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            $fromEmail     = (!empty($fromEmail) ? $fromEmail : $this->from_email);
            $fromName      = (!empty($fromName) ? $fromName : $this->from_name);
            $replyToEmail  = (!empty($replyToEmail) ? $replyToEmail : $this->from_email);
            $replyToName   = (!empty($replyToName) ? $replyToName : $this->from_name);
            $senderEmail   = (!empty($fromEmail) ? $fromEmail : $this->from_email);
            $senderName    = (!empty($fromName) ? $fromName : $this->from_name);

            $mail
                ->setEncoding(strtoupper(Yii::app()->charset))
                ->setFrom($fromEmail, $fromName)
                ->setTo($toEmail, $toName)
                ->setSubject($params['subject'])
                ->setSender($senderEmail, $senderName)
                ->addReplyTo($replyToEmail, $replyToName);

            if (!$onlyPlainText) {
                $mail->setHtmlBody(!empty($params['body']) ? $params['body'] : '');
            }

            $mail->setTextBody(!empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']));

            if (!empty($params['headers'])) {
                $headers = $this->parseHeadersIntoKeyValue($params['headers']);
                foreach ($headers as $name => $value) {
                    if (substr($name, 0, 2) !== 'X-') {
                        continue;
                    }
                    $mail->setXHeader($name, $value);
                }
            }
            
            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $_attachments = array_unique($params['attachments']);
                foreach ($_attachments as $attachment) {
                    if (is_file($attachment)) {
                        $className = '\Zend\Mime\Part';
                        $mimePart = new $className(fopen($attachment, 'r'));
                        $mimePart->type = "application/octet-stream";
                        $mail->getBody()->addPart($mimePart);
                    }
                }
            }

            // send it
            if ($sent = $mm->send($mail)) {
                $this->getMailer()->addLog('OK');
                $sent = array('message_id' => StringHelper::random(60));
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
        $params['transport'] = self::TRANSPORT_DYN_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return bool|string
     */
    public function requirementsFailed()
    {
        if (!version_compare(PHP_VERSION, '5.3.3', '>=')) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => '5.3.23',
            ));
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname = 'web-api.email.dynect.net';
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
