<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerAmazonSesWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 *
 */

class DeliveryServerAmazonSesWebApi extends DeliveryServerSmtpAmazon
{
    /**
     * @var string
     */
    protected $serverType = 'amazon-ses-web-api';

    /**
     * @var string
     */
    protected $_initStatus;

    /**
     * @var string 
     */
    protected $_preCheckSesSnsError;

    /**
     * @var string
     */
    protected $_providerUrl = 'https://aws.amazon.com/ses/';

    /**
     * @var array
     */
    protected $notificationTypes = array('Bounce', 'Complaint');

    /**
     * @var string 
     */
    public $topic_arn;

    /**
     * @var string 
     */
    public $subscription_arn;

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

        if (empty($fromName)) {
            $fromName = $params['fromName'];
        }
        
        $sent = false;
        try {
            
            if (!$this->preCheckSesSns()) {
                throw new Exception($this->_preCheckSesSnsError);
            }
            
            $message = array(
                'Source'       => sprintf('=?%s?B?%s?= <%s>', strtolower(Yii::app()->charset), base64_encode($fromName), $fromEmail),
                'Destinations' => array(sprintf('=?%s?B?%s?= <%s>', strtolower(Yii::app()->charset), base64_encode($toName), $toEmail)),
                'RawMessage' => array(
                    'Data' => $this->getMailer()->getEmailMessage($params),
                ),
            );

            $response = $this->getSesClient()->sendRawEmail($message);
            
            if ($response['MessageId']) {
                $sent = array('message_id' => $response['MessageId']);
                $this->getMailer()->addLog('OK');
            } else {
                throw new Exception(Yii::t('servers', 'Unable to make the delivery!'));
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
        $params['transport'] = self::TRANSPORT_AMAZON_SES_WEB_API;
        return parent::getParamsArray($params);
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = array(
            'username'  => Yii::t('servers', 'Access Key ID'),
            'password'  => Yii::t('servers', 'Secret Access Key'),
        );

        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'username'   => Yii::t('servers', 'Your Amazon SES SMTP username, something like: i.e: AKIAIYYYYYYYYYYUBBFQ. Please make sure this user has enough rights for SES but also for SNS'),
            'force_from' => Yii::t('servers', 'When to force the FROM address. Please note that if you set this option to Never and you send from a unverified domain, all your emails will fail delivery. It is best to leave this option as is unless you really know what you are doing.'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
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
    public function getRegionFromHostname()
    {
        $parts = explode('.', str_replace('.amazonaws.com', '', $this->hostname));
        return array_pop($parts);
    }

    /**
     * @return mixed
     */
    public function getSesClient()
    {
        static $clients = array();
        $id = (int)$this->server_id;
        if (!empty($clients[$id])) {
            return $clients[$id];
        }
        $className = '\Aws\Ses\SesClient';
        return $clients[$id] =  new $className(array(
            'region'  => $this->getRegionFromHostname(),
            'version' => '2010-12-01',
            'credentials' => array(
                'key'     => trim($this->username),
                'secret'  => trim($this->password),
            ),
        ));
    }

    /**
     * @return mixed
     */
    public function getSnsClient()
    {
        static $clients = array();
        $id = (int)$this->server_id;
        if (!empty($clients[$id])) {
            return $clients[$id];
        }
        $className = '\Aws\Sns\SnsClient';
        return $clients[$id] = new $className(array(
            'region'      => $this->getRegionFromHostname(),
            'version'     => '2010-03-31',
            'credentials' => array(
                'key'    => trim($this->username),
                'secret' => trim($this->password),
            )
        ));
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->_initStatus      = $this->status;
        $this->topic_arn        = $this->getModelMetaData()->itemAt('topic_arn');
        $this->subscription_arn = $this->getModelMetaData()->itemAt('subscription_arn');
        $this->force_from       = self::FORCE_FROM_ALWAYS;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->_initStatus      = $this->status;
        $this->topic_arn        = $this->getModelMetaData()->itemAt('topic_arn');
        $this->subscription_arn = $this->getModelMetaData()->itemAt('subscription_arn');
        parent::afterFind();
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('topic_arn', $this->topic_arn);
        $this->getModelMetaData()->add('subscription_arn', $this->subscription_arn);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterDelete()
    {
        try {
            $this->getSesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity'          => $this->from_email,
                'ForwardingEnabled' => true,
            ));
            foreach($this->notificationTypes as $type) {
                $this->getSesClient()->setIdentityNotificationTopic(array(
                    'Identity'          => $this->from_email,
                    'NotificationType'  => $type,
                ));
            }
            if (!empty($this->subscription_arn)) {
                $this->getSnsClient()->unsubscribe(array('SubscriptionArn' => $this->subscription_arn));
            }
        } catch (Exception $e) {

        }
        parent::afterDelete();
    }

    /**
     * @return bool
     */
    protected function preCheckSesSns()
    {
        if (MW_IS_CLI || $this->isNewRecord || $this->_initStatus !== self::STATUS_INACTIVE) {
            return true;
        }

        try {

            $this->getSesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity'          => $this->from_email,
                'ForwardingEnabled' => true,
            ));
            foreach($this->notificationTypes as $type) {
                $this->getSesClient()->setIdentityNotificationTopic(array(
                    'Identity'          => $this->from_email,
                    'NotificationType'  => $type,
                ));
            }

            if (!empty($this->subscription_arn)) {
                try {
                    $this->getSnsClient()->unsubscribe(array('SubscriptionArn' => $this->subscription_arn));
                } catch (Exception $e) {}
            }

            $result          = $this->getSnsClient()->createTopic(array('Name' => 'MWZSESHANDLER' . (int)$this->server_id));
            $this->topic_arn = $result->get('TopicArn');
            $subscribeUrl    = $this->getDswhUrl();

            $result = $this->getSnsClient()->subscribe(array(
                'TopicArn' => $this->topic_arn,
                'Protocol' => stripos($subscribeUrl, 'https') === 0 ? 'https' : 'http',
                'Endpoint' => $subscribeUrl,
            ));
            if (stripos($result->get('SubscriptionArn'), 'pending') === false) {
                $this->subscription_arn = $result->get('SubscriptionArn');
            }

            foreach($this->notificationTypes as $type) {
                $this->getSesClient()->setIdentityNotificationTopic(array(
                    'Identity'          => $this->from_email,
                    'NotificationType'  => $type,
                    'SnsTopic'          => $this->topic_arn,
                ));
            }

            $this->getSesClient()->setIdentityFeedbackForwardingEnabled(array(
                'Identity'          => $this->from_email,
                'ForwardingEnabled' => false,
            ));

        } catch (Exception $e) {
            $this->_preCheckSesSnsError = $e->getMessage();
            return false;
        }

        return $this->save(false);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
        return parent::getFormFieldsDefinition(CMap::mergeArray(array(
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'max_connection_messages' => null,
            'bounce_server_id'        => null,
        ), $params));
    }
}
