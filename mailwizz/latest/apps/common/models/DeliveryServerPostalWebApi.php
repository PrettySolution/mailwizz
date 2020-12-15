<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerPostalWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 *
 */

class DeliveryServerPostalWebApi extends DeliveryServer
{
    /**
     * Flag for http scheme
     */
    const HOSTNAME_SCHEME_HTTP = 'http';

    /**
     * Flag for https scheme
     */
    const HOSTNAME_SCHEME_HTTPS = 'https';

    /**
     * @var string
     */
    protected $serverType = 'postal-web-api';

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
    protected $_providerUrl = 'https://postal.atech.media';

    /**
     * @var string
     */
    public $hostname_scheme = self::HOSTNAME_SCHEME_HTTP;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('password, hostname_scheme', 'required'),
            array('password', 'length', 'max' => 255),
            array('hostname_scheme', 'in', 'range' => array_keys($this->getHostnameSchemes()))
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'password'        => Yii::t('servers', 'Api key'),
            'hostname_scheme' => Yii::t('servers', 'Hostname scheme'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'hostname'        => Yii::t('servers', 'Your Postal server host'),
            'password'        => Yii::t('servers', 'One of your Postal api keys'),
            'hostname_scheme' => Yii::t('servers', 'If you are accessing your Postal dashboard using HTTPS, then select HTTPS, otherwise use HTTP'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'hostname' => 'postal.example.com',
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
        $headers[$headerPrefix . 'Mailer'] = 'Postal Web API';

        $sent = false;

        try {
            $onlyPlainText = !empty($params['onlyPlainText']) && $params['onlyPlainText'] === true;
            $messageClass  = '\Postal\SendMessage';
            $message       = new $messageClass($this->getClient());

            $message->to($toEmail);
            $message->replyTo($replyToEmail);
            $message->from($fromEmail);
            $message->subject($params['subject']);

            $message->plainBody(!empty($params['plainText']) ? $params['plainText'] : CampaignHelper::htmlToText($params['body']));
            if (!$onlyPlainText) {
                $message->htmlBody($params['body']);
            }

            foreach ($headers as $name => $value) {
                $message->header($name, $value);
            }

            if (!$onlyPlainText && !empty($params['attachments']) && is_array($params['attachments'])) {
                $attachments = array_unique($params['attachments']);
                $sendParams['content']['attachments'] = array();
                foreach ($attachments as $attachment) {
                    if (is_file($attachment)) {
                        $message->attach(basename($attachment), 'application/octet-stream', file_get_contents($attachment));
                    }
                }
            }

            $result = $message->send();

            foreach ($result->recipients() as $email => $message) {
                if ($message->id()) {
                    $this->getMailer()->addLog('OK');
                    $sent = array('message_id' => $message->id());
                    break;
                }
                throw new Exception(json_encode($message));
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
        $params['transport'] = self::TRANSPORT_POSTAL_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return bool|string
     */
    public function requirementsFailed()
    {
        if (!version_compare(PHP_VERSION, '5.4', '>=')) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => '5.4',
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
        $className = '\Postal\Client';
        return $clients[$id] = new $className($this->getApiUrl(), $this->password);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('hostname_scheme', (string)$this->hostname_scheme);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->hostname_scheme = (string)$this->getModelMetaData()->itemAt('hostname_scheme');
        $this->_initStatus     = $this->status;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->hostname_scheme = (string)$this->getModelMetaData()->itemAt('hostname_scheme');
        $this->_initStatus     = $this->status;
        parent::afterFind();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
        $form = new CActiveForm();
        $fields = parent::getFormFieldsDefinition(CMap::mergeArray(array(
            'username'                => null,
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'signing_enabled'         => null,
            'max_connection_messages' => null,
            'bounce_server_id'        => null,
            'force_sender'            => null,
        ), $params));

        $newFields = array();
        foreach ($fields as $id => $definition) {
            if ($id === 'hostname') {
                $newFields['hostname_scheme'] = array(
                    'visible'   => true,
                    'fieldHtml' => $form->dropDownList($this, 'hostname_scheme', $this->getHostnameSchemes(), $this->getHtmlOptions('hostname_scheme')),
                );
            }
            $newFields[$id] = $definition;
        }
        return $newFields;
    }

    /**
     * @return array
     */
    public function getHostnameSchemes()
    {
        return array(
            self::HOSTNAME_SCHEME_HTTP  => Yii::t('servers', 'HTTP'),
            self::HOSTNAME_SCHEME_HTTPS => Yii::t('servers', 'HTTPS'),
        );
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return sprintf('%s://%s', $this->hostname_scheme, $this->hostname);
    }

    /**
     * @inheritdoc
     */
    public function getDswhUrl()
    {
        $url = Yii::app()->options->get('system.urls.frontend_absolute_url') . 'dswh/postal';
        if (MW_IS_CLI) {
            return $url;
        }
        if (Yii::app()->request->isSecureConnection && parse_url($url, PHP_URL_SCHEME) == 'http') {
            $url = substr_replace($url, 'https', 0, 4);
        }
        return $url;
    }
}
