<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "delivery_server".
 *
 * The followings are the available columns in table 'delivery_server':
 * @property integer $server_id
 * @property integer $customer_id
 * @property integer $bounce_server_id
 * @property string $type
 * @property string $name
 * @property string $hostname
 * @property string $username
 * @property string $password
 * @property integer $port
 * @property string $protocol
 * @property integer $timeout
 * @property string $from_email
 * @property string $from_name
 * @property string $reply_to_email
 * @property integer $probability
 * @property integer $hourly_quota
 * @property integer $daily_quota
 * @property integer $monthly_quota
 * @property integer $pause_after_send
 * @property string $meta_data
 * @property string $confirmation_key
 * @property string $locked
 * @property string $use_for
 * @property string $signing_enabled
 * @property string $force_from
 * @property string $force_reply_to
 * @property string $force_sender
 * @property string $must_confirm_delivery
 * @property integer $max_connection_messages
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign[] $campaigns
 * @property BounceServer $bounceServer
 * @property TrackingDomain $trackingDomain
 * @property Customer $customer
 * @property DeliveryServerUsageLog[] $usageLogs
 * @property DeliveryServerDomainPolicy[] $domainPolicies
 * @property CustomerGroup[] $customerGroups
 */
class DeliveryServer extends ActiveRecord
{
    const TRANSPORT_SMTP = 'smtp';

    const TRANSPORT_SMTP_AMAZON = 'smtp-amazon';

    const TRANSPORT_SMTP_POSTMASTERY = 'smtp-postmastery';

	const TRANSPORT_SMTP_POSTAL = 'smtp-postal';

	const TRANSPORT_SMTP_MYSMTPCOM = 'smtp-mysmtpcom';

    const TRANSPORT_SENDMAIL = 'sendmail';

    const TRANSPORT_PHP_MAIL = 'php-mail';

    const TRANSPORT_PICKUP_DIRECTORY = 'pickup-directory';
    
    const TRANSPORT_MANDRILL_WEB_API = 'mandrill-web-api';

    const TRANSPORT_AMAZON_SES_WEB_API = 'amazon-ses-web-api';

    const TRANSPORT_MAILGUN_WEB_API = 'mailgun-web-api';

    const TRANSPORT_SENDGRID_WEB_API = 'sendgrid-web-api';

    const TRANSPORT_LEADERSEND_WEB_API = 'leadersend-web-api';

    const TRANSPORT_ELASTICEMAIL_WEB_API = 'elasticemail-web-api';

    const TRANSPORT_DYN_WEB_API = 'dyn-web-api';

    const TRANSPORT_SPARKPOST_WEB_API = 'sparkpost-web-api';
    
    const TRANSPORT_PEPIPOST_WEB_API = 'pepipost-web-api';
    
    const TRANSPORT_POSTMARK_WEB_API = 'postmark-web-api';

    const TRANSPORT_MAILJET_WEB_API = 'mailjet-web-api';

    const TRANSPORT_MAILERQ_WEB_API = 'mailerq-web-api';
    
    const TRANSPORT_SENDINBLUE_WEB_API = 'sendinblue-web-api';

    const TRANSPORT_TIPIMAIL_WEB_API = 'tipimail-web-api';
    
    const TRANSPORT_NEWSMAN_WEB_API = 'newsman-web-api';

    const TRANSPORT_POSTAL_WEB_API = 'postal-web-api';

    const DELIVERY_FOR_SYSTEM = 'system';

    const DELIVERY_FOR_CAMPAIGN_TEST = 'campaign-test';

    const DELIVERY_FOR_TEMPLATE_TEST = 'template-test';

    const DELIVERY_FOR_CAMPAIGN = 'campaign';

    const DELIVERY_FOR_LIST = 'list';

    const DELIVERY_FOR_TRANSACTIONAL = 'transactional';

    const USE_FOR_ALL = 'all';

    const USE_FOR_TRANSACTIONAL = 'transactional';

    const USE_FOR_CAMPAIGNS = 'campaigns';
    
    const USE_FOR_EMAIL_TESTS = 'email-tests';
    
    const USE_FOR_REPORTS = 'reports';
    
    const USE_FOR_LIST_EMAILS = 'list-emails';
    
    const USE_FOR_INVOICES = 'invoices';

    const STATUS_IN_USE = 'in-use';

    const STATUS_HIDDEN = 'hidden';

    const STATUS_DISABLED = 'disabled';

	const STATUS_PENDING_DELETE = 'pending-delete';

    const TEXT_NO = 'no';

    const TEXT_YES = 'yes';
    
    const FORCE_FROM_WHEN_NO_SIGNING_DOMAIN = 'when no valid signing domain';

    const FORCE_FROM_ALWAYS = 'always';

    const FORCE_FROM_NEVER = 'never';

    const FORCE_REPLY_TO_ALWAYS = 'always';

    const FORCE_REPLY_TO_NEVER = 'never';
    
    const QUOTA_CACHE_SECONDS = 300;

    protected $serverType = 'smtp';

    // flag to mark what kind of delivery we are making
    protected $_deliveryFor = 'system';

    // what do we deliver
    protected $_deliveryObject;

    // mailer object
    protected $_mailer;

    // list of additional headers to send for this server
    public $additional_headers = array();

    // since 1.3.4.9
    protected $_hourlySendingsLeft;
    
    // since 1.3.6.2
    protected $_monthlySendingsLeft;

    // since 1.3.5 - flag to determine if logging usage
    protected $_logUsage = true;

    // since 1.3.5, store campaign emails in queue and flush at __destruct
    protected $_campaignQueueEmails = array();
    
    // since 1.3.9.3
    protected $_hourlyQuotaAccessKey = 'DeliveryServerGetHourlyQuotaLeft::%d';

    // since 1.4.4
    protected $_dailyQuotaAccessKey = 'DeliveryServerGetDailyQuotaLeft::%d';

    // since 1.3.9.3
    protected $_monthlyQuotaAccessKey = 'DeliveryServerGetMonthlyQuotaLeft::%d';
    
    // since 1.3.6.1
    public $canConfirmDelivery = false;
    
    // since 1.5.0
    protected $_initHourlyQuota  = 0;
    protected $_initDailyQuota   = 0;
    protected $_initMonthlyQuota = 0;
    
    // since 1.5.2
    protected $_providerUrl = '';

    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return '{{delivery_server}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return CMap::mergeArray(array(
            'passwordHandler' => array(
                'class' => 'common.components.db.behaviors.RemoteServerPasswordHandlerBehavior'
            ),
        ), parent::behaviors());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('hostname, from_email', 'required'),

            array('type', 'length', 'min' => 2, 'max' => 20),
	        array('name, hostname, username, from_email, from_name, reply_to_email', 'length', 'min' => 2, 'max'=>255),
            array('password', 'length', 'min' => 2, 'max'=>150),
            array('port, probability, timeout', 'numerical', 'integerOnly'=>true),
            array('port', 'length', 'min'=> 2, 'max' => 5),
            array('probability', 'length', 'min'=> 1, 'max' => 3),
            array('probability', 'in', 'range' => array_keys($this->getProbabilityArray())),
            array('timeout', 'numerical', 'min' => 5, 'max' => 120),
            array('from_email, reply_to_email', 'email', 'validateIDN' => true),
            array('protocol', 'in', 'range' => array_keys($this->getProtocolsArray())),
            array('hourly_quota, daily_quota, monthly_quota, pause_after_send', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('hourly_quota, daily_quota, monthly_quota, pause_after_send', 'length', 'max' => 11),
            array('bounce_server_id', 'exist', 'className' => 'BounceServer', 'attributeName' => 'server_id', 'allowEmpty' => true),
            array('tracking_domain_id', 'exist', 'className' => 'TrackingDomain', 'attributeName' => 'domain_id', 'allowEmpty' => true),
            array('hostname, username, from_email, type, status, customer_id', 'safe', 'on' => 'search'),
            array('additional_headers', '_validateAdditionalHeaders'),
            array('customer_id', 'exist', 'className' => 'Customer', 'attributeName' => 'customer_id', 'allowEmpty' => true),
            array('locked, signing_enabled, force_sender', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('use_for', 'in', 'range' => array_keys($this->getUseForOptions())),
            array('force_from', 'in', 'range' => array_keys($this->getForceFromOptions())),
            array('force_reply_to', 'in', 'range' => array_keys($this->getForceReplyToOptions())),
            array('must_confirm_delivery', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('max_connection_messages', 'numerical', 'integerOnly' => true, 'min' => 1),
            array('max_connection_messages', 'length', 'max' => 11),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'campaigns'         => array(self::MANY_MANY, 'Campaign', '{{campaign_to_delivery_server}}(server_id, campaign_id)'),
            'bounceServer'      => array(self::BELONGS_TO, 'BounceServer', 'bounce_server_id'),
            'trackingDomain'    => array(self::BELONGS_TO, 'TrackingDomain', 'tracking_domain_id'),
            'customer'          => array(self::BELONGS_TO, 'Customer', 'customer_id'),
            'usageLogs'         => array(self::HAS_MANY, 'DeliveryServerUsageLog', 'server_id'),
            'domainPolicies'    => array(self::HAS_MANY, 'DeliveryServerDomainPolicy', 'server_id'),
            'customerGroups'    => array(self::MANY_MANY, 'CustomerGroup', 'delivery_server_to_customer_group(server_id, group_id)'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'server_id'                     => Yii::t('servers', 'Server'),
            'customer_id'                   => Yii::t('servers', 'Customer'),
            'bounce_server_id'              => Yii::t('servers', 'Bounce server'),
            'tracking_domain_id'            => Yii::t('servers', 'Tracking domain'),
            'type'                          => Yii::t('servers', 'Type'),
            'name'                          => Yii::t('servers', 'Name'),
            'hostname'                      => Yii::t('servers', 'Hostname'),
            'username'                      => Yii::t('servers', 'Username'),
            'password'                      => Yii::t('servers', 'Password'),
            'port'                          => Yii::t('servers', 'Port'),
            'protocol'                      => Yii::t('servers', 'Protocol'),
            'timeout'                       => Yii::t('servers', 'Timeout'),
            'from_email'                    => Yii::t('servers', 'From email'),
            'from_name'                     => Yii::t('servers', 'From name'),
            'reply_to_email'                => Yii::t('servers', 'Reply-To email'),
            'probability'                   => Yii::t('servers', 'Probability'),
            'hourly_quota'                  => Yii::t('servers', 'Hourly quota'),
            'daily_quota'                   => Yii::t('servers', 'Daily quota'),
            'monthly_quota'                 => Yii::t('servers', 'Monthly quota'),
            'meta_data'                     => Yii::t('servers', 'Meta data'),
            'additional_headers'            => Yii::t('servers', 'Additional headers'),
            'locked'                        => Yii::t('servers', 'Locked'),
            'use_for'                       => Yii::t('servers', 'Use for'),
            'signing_enabled'               => Yii::t('servers', 'Signing enabled'),
            'force_from'                    => Yii::t('servers', 'Force FROM'),
            'force_reply_to'                => Yii::t('servers', 'Force Reply-To'),
            'force_sender'                  => Yii::t('servers', 'Force Sender'),
            'must_confirm_delivery'         => Yii::t('servers', 'Must confirm delivery'),
            'max_connection_messages'       => Yii::t('servers', 'Max. connection messages'),
            'pause_after_send'              => Yii::t('servers', 'Pause after send'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
    * Retrieves a list of models based on the current search/filter conditions.
    * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
    */
    public function search()
    {
        $criteria=new CDbCriteria;

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with = array(
                    'customer' => array(
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => array(
                            ':name'    => '%' . $this->customer_id . '%',
                        ),
                    )
                );
            }
        }
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.hostname', $this->hostname, true);
        $criteria->compare('t.username', $this->username, true);
        $criteria->compare('t.from_email', $this->from_email, true);
        $criteria->compare('t.type', $this->type);

	    if (empty($this->status)) {
		    $criteria->addNotInCondition('t.status', [self::STATUS_HIDDEN, self::STATUS_PENDING_DELETE]);
	    } else {
		    $criteria->compare('t.status', $this->status);
	    }

	    $criteria->order = 't.name ASC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder'  => array(
                    't.server_id' => CSort::SORT_DESC,
                ),
            ),
        ));
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
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        $this->additional_headers = $this->parseHeadersFormat((array)$this->getModelMetaData()->itemAt('additional_headers'));
        $this->_deliveryFor       = self::DELIVERY_FOR_SYSTEM;
        $this->type               = $this->serverType;
        
        // since 1.3.6.3 default always
        $this->force_from = self::FORCE_FROM_ALWAYS;
        
        parent::afterConstruct();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->additional_headers = $this->parseHeadersFormat((array)$this->getModelMetaData()->itemAt('additional_headers'));
        $this->_deliveryFor       = self::DELIVERY_FOR_SYSTEM;
        
        // since 1.5.0
        $this->_initHourlyQuota  = $this->hourly_quota;
        $this->_initDailyQuota   = $this->daily_quota;
        $this->_initMonthlyQuota = $this->monthly_quota;
        
        parent::afterFind();
    }

    /**
     * @since 1.3.5.9
     * @param array $headers
     * @return array
     */
    public function parseHeadersFormat($headers = array())
    {
        if (!is_array($headers) || empty($headers)) {
            return array();
        }
        $_headers = array();

        foreach ($headers as $k => $v) {
            // pre 1.3.5.9 format
            if (is_string($k) && is_string($v)) {
                $_headers[] = array('name' => $k, 'value' => $v);
                continue;
            }
            // post 1.3.5.9 format
            if (is_numeric($k) && is_array($v) && array_key_exists('name', $v) && array_key_exists('value', $v)) {
                $_headers[] = array('name' => $v['name'], 'value' => $v['value']);
            }
        }

        return $_headers;
    }

    /**
     * @since 1.3.5.9
     * @param array $headers
     * @return array
     */
    public function parseHeadersIntoKeyValue($headers = array())
    {
        $_headers = array();

        foreach ($headers as $k => $v) {
            if (is_string($k) && is_string($v)) {
                $_headers[$k] = $v;
                continue;
            }
            if (is_numeric($k) && is_array($v) && array_key_exists('name', $v) && array_key_exists('value', $v)) {
                $_headers[$v['name']] = $v['value'];
            }
        }

        return $_headers;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function sendEmail(array $params = array())
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getMailer()
    {
        if ($this->_mailer === null) {
            $this->_mailer = clone Yii::app()->mailer;
        }
        return $this->_mailer;
    }

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        if (!$this->isNewRecord && !MW_IS_CLI) {
            if (empty($this->customer_id)) {
                $this->locked = self::TEXT_NO;
            }

            $model = self::model()->findByPk((int)$this->server_id);
            $keys = array('hostname', 'username', 'password', 'port', 'protocol', 'from_email');
            if (!empty($this->bounce_server_id)) {
                array_push($keys, 'bounce_server_id');
            }
            foreach ($keys as $key) {
                if ($model->$key !== $this->$key) {
                    $this->status = self::STATUS_INACTIVE;
                    break;
                }
            }
        }
        return parent::afterValidate();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('additional_headers', (array)$this->additional_headers);
        if (empty($this->type)) {
            $this->type = $this->serverType;
        }
        
        if (empty($this->use_for)) {
            $this->use_for = self::USE_FOR_ALL;
        }
        
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        // since 1.5.0
        if ((int)$this->hourly_quota != (int)$this->_initHourlyQuota) {
            Yii::app()->cache->delete(sha1(sprintf($this->_hourlyQuotaAccessKey, (int)$this->server_id)));
        }
        if ((int)$this->daily_quota != (int)$this->_initDailyQuota) {
            Yii::app()->cache->delete(sha1(sprintf($this->_dailyQuotaAccessKey, (int)$this->server_id)));
        }
        if ((int)$this->monthly_quota != (int)$this->_initMonthlyQuota) {
            Yii::app()->cache->delete(sha1(sprintf($this->_monthlyQuotaAccessKey, (int)$this->server_id)));
        }
        $this->_initHourlyQuota  = $this->hourly_quota;
        $this->_initDailyQuota   = $this->daily_quota;
        $this->_initMonthlyQuota = $this->monthly_quota;
        //
        
        parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    protected function beforeDelete()
    {
	    if (!$this->getCanBeDeleted()) {
		    return false;
	    }

	    if (!$this->getIsPendingDelete()) {
		    $this->saveStatus(self::STATUS_PENDING_DELETE);

		    return false;
	    }

	    return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'bounce_server_id'          => Yii::t('servers', 'The server that will handle bounce emails for this SMTP server.'),
            'tracking_domain_id'        => Yii::t('servers', 'The domain that will be used for tracking purposes, must be a DNS CNAME of the master domain.'),
            'name'                      => Yii::t('servers', 'The name of this server to make a distinction if having multiple servers with same hostname.'),
            'hostname'                  => Yii::t('servers', 'The hostname of your SMTP server, usually something like smtp.domain.com.'),
            'username'                  => Yii::t('servers', 'The username of your SMTP server, usually something like you@domain.com.'),
            'password'                  => Yii::t('servers', 'The password of your SMTP server, used in combination with your username to authenticate your request.'),
            'port'                      => Yii::t('servers', 'The port of your SMTP server, usually this is 25, but 465 and 587 are also valid choices for some of the servers depending on the security protocol they are using. If unsure leave it to 25.'),
            'protocol'                  => Yii::t('servers', 'The security protocol used to access this server. If unsure, leave it blank or select TLS if blank does not work for you.'),
            'timeout'                   => Yii::t('servers', 'The maximum number of seconds we should wait for the server to respond to our request. 30 seconds is a proper value.'),
            'from_email'                => Yii::t('servers', 'The default email address used in the FROM header when nothing is specified'),
            'from_name'                 => Yii::t('servers', 'The default name used in the FROM header, together with the FROM email when nothing is specified'),
            'reply_to_email'            => Yii::t('servers', 'The default email address used in the Reply-To header when nothing is specified'),
            'probability'               => Yii::t('servers', 'When having multiple servers from where you send, the probability helps to choose one server more than another. This is useful if you are using servers with various quota limits. A lower probability means a lower sending rate using this server.'),
            'hourly_quota'              => Yii::t('servers', 'In case there are limits that apply for sending with this server, you can set a hourly quota for it and it will only send in one hour as many emails as you set here. Set it to 0 in order to not apply any hourly limit.'),
            'daily_quota'               => Yii::t('servers', 'In case there are limits that apply for sending with this server, you can set a daily quota for it and it will only send in one day as many emails as you set here. Set it to 0 in order to not apply any daily limit.'),
            'monthly_quota'             => Yii::t('servers', 'In case there are limits that apply for sending with this server, you can set a monthly quota for it and it will only send in one monthly as many emails as you set here. Set it to 0 in order to not apply any monthly limit.'),
            'locked'                    => Yii::t('servers', 'Whether this server is locked and assigned customer cannot change or delete it'),
            'use_for'                   => Yii::t('servers', 'For which type of sending can this server be used for'),
            'signing_enabled'           => Yii::t('servers', 'Whether signing is enabled when sending emails through this delivery server'),
            'force_from'                => Yii::t('servers', 'When to force the FROM email address'),
            'force_reply_to'            => Yii::t('servers', 'When to force the Reply-To email address'),
            'force_sender'              => Yii::t('servers', 'Whether to force the Sender header, if unsure, leave this disabled'),
            'must_confirm_delivery'     => Yii::t('servers', 'Whether the server can and must confirm the actual delivery. Leave as is if not sure.'),
            'max_connection_messages'   => Yii::t('servers', 'The maximum number of messages to send through a single smtp connection'),
            'pause_after_send'          => Yii::t('servers', 'The number of microseconds to pause after an email is sent. A microsecond is one millionth of a second, so to pause for two seconds you would enter: 2000000'),
        );
        
        // since 1.3.6.3
        if (stripos($this->type, 'web-api') !== false || in_array($this->type, array('smtp-amazon'))) {
            $texts['force_from'] = Yii::t('servers', 'When to force the FROM address. Please note that if you set this option to Never and you send from a unverified domain, all your emails will fail delivery. It is best to leave this option as is unless you really know what you are doing.');
        }

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritdoc
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'hostname'          => Yii::t('servers', 'smtp.your-server.com'),
            'username'          => Yii::t('servers', 'you@domain.com'),
            'password'          => Yii::t('servers', 'your smtp account password'),
            'from_email'        => Yii::t('servers', 'you@domain.com'),
            'reply_to_email'    => Yii::t('servers', 'you@domain.com'),
        );
        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
    }

    /**
     * @return array
     */
    public function getBounceServersArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'server_id, hostname, username, service';
        
        if (Yii::app()->apps->isAppName('backend')) {
            $criteria->addCondition('customer_id = :cid OR customer_id IS NULL');
        } else {
            $criteria->addCondition('customer_id = :cid OR server_id = :sid');
            $criteria->params[':sid'] = (int)$this->bounce_server_id;
        }
        $criteria->params[':cid'] = (int)$this->customer_id;

        $criteria->addInCondition('status', array(BounceServer::STATUS_ACTIVE, BounceServer::STATUS_CRON_RUNNING));
        $criteria->order = 'server_id DESC';
        $models = BounceServer::model()->findAll($criteria);

        $_options[''] = Yii::t('app', 'Choose');
        foreach ($models as $model) {
            $_options[$model->server_id] = sprintf('%s - %s(%s)', strtoupper($model->service), $model->hostname, $model->username);
        }

        return $_options;
    }

    /**
     * @return string
     */
    public function getDisplayBounceServer()
    {
        if (empty($this->bounceServer)) {
            return '';
        }

        $model = $this->bounceServer;

        return sprintf('%s - %s(%s)', strtoupper($model->service), $model->hostname, $model->username);
    }

    /**
     * @return array
     */
    public function getBounceServerNotSupportedTypes()
    {
        $types = array(
            self::TRANSPORT_AMAZON_SES_WEB_API,
            self::TRANSPORT_MANDRILL_WEB_API,
            self::TRANSPORT_MAILGUN_WEB_API,
            self::TRANSPORT_SENDGRID_WEB_API,
            self::TRANSPORT_LEADERSEND_WEB_API,
            self::TRANSPORT_ELASTICEMAIL_WEB_API,
            self::TRANSPORT_DYN_WEB_API,
            self::TRANSPORT_SPARKPOST_WEB_API,
            self::TRANSPORT_PEPIPOST_WEB_API,
            self::TRANSPORT_POSTMARK_WEB_API,
            self::TRANSPORT_NEWSMAN_WEB_API,
            self::TRANSPORT_MAILJET_WEB_API,
            self::TRANSPORT_SENDINBLUE_WEB_API,
            self::TRANSPORT_TIPIMAIL_WEB_API,
            self::TRANSPORT_POSTAL_WEB_API
        );
        return (array)Yii::app()->hooks->applyFilters('delivery_servers_get_bounce_server_not_supported_types', $types);
    }

    /**
     * @return bool
     */
    public function getBounceServerNotSupported()
    {
        return in_array($this->type, $this->getBounceServerNotSupportedTypes());
    }

    /**
     * @return array
     */
    public function getSigningSupportedTypes()
    {
        $types = array(
            self::TRANSPORT_PHP_MAIL,
            self::TRANSPORT_PICKUP_DIRECTORY,
            self::TRANSPORT_SENDMAIL,
            self::TRANSPORT_SMTP,
            self::TRANSPORT_MAILERQ_WEB_API,
        );
        return (array)Yii::app()->hooks->applyFilters('delivery_servers_get_signing_supported_types', $types);
    }

    /**
     * @return array
     */
    public function getTrackingDomainsArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'domain_id, name';

        if (Yii::app()->apps->isAppName('backend')) {
            $criteria->addCondition('customer_id = :cid OR customer_id IS NULL');
        } else {
            $criteria->addCondition('customer_id = :cid OR domain_id = :did');
            $criteria->params[':did'] = (int)$this->tracking_domain_id;
        }
        $criteria->params[':cid'] = (int)$this->customer_id;

        $criteria->order = 'domain_id DESC';
        $models = TrackingDomain::model()->findAll($criteria);

        $_options[''] = Yii::t('app', 'Choose');
        foreach ($models as $model) {
            $_options[$model->domain_id] = $model->name;
        }

        return $_options;
    }

    /**
     * @return array
     */
    public function getProtocolsArray()
    {
        return array(
            ''          => Yii::t('app', 'Choose'),
            'tls'       => 'TLS',
            'ssl'       => 'SSL',
            'starttls'  => 'STARTTLS',
        );
    }

    /**
     * @return mixed|string
     */
    public function getProtocolName()
    {
        $protocols = $this->getProtocolsArray();
        return !empty($this->protocol) && !empty($protocols[$this->protocol]) ? $protocols[$this->protocol] : Yii::t('app', 'Default');
    }

    /**
     * @return array
     */
    public function getProbabilityArray()
    {
        $options = array('' => Yii::t('app', 'Choose'));
        for ($i = 5; $i <= 100; ++$i) {
            if ($i % 5 == 0) {
                $options[$i] = $i . ' %';
            }
        }
        return $options;
    }

    // this will be removed
    public function getDefaultParamsArray()
    {
        return array();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParamsArray(array $params = array())
    {
        $deliveryObject = null;
        $customer       = isset($params['customer']) && is_object($params['customer']) ? $params['customer'] : null;
        
        if ($deliveryObject = $this->getDeliveryObject()) {
            if (!$customer && is_object($deliveryObject) && $deliveryObject instanceof Campaign) {
                $customer = $deliveryObject->customer;
            }
            if (!$customer && is_object($deliveryObject) && $deliveryObject instanceof Lists && !empty($deliveryObject->default)) {
                $customer = $deliveryObject->customer;
            }
        }
        
        if ($customer) {
            $hlines = $customer->getGroupOption('servers.custom_headers', '');
        } else {
            $hlines = Yii::app()->options->get('system.customer_servers.custom_headers', '');
        }
        $defaultHeaders = DeliveryServerHelper::getOptionCustomerCustomHeadersArrayFromString($hlines);
        
        foreach ((array)$this->additional_headers as $header) {
            if (!isset($header['name'], $header['value'])) {
                continue;
            }
            foreach ($defaultHeaders as $index => $dheader) {
                if ($dheader['name'] == $header['name']) {
                    unset($defaultHeaders[$index]);
                    continue;
                }
            }
        }
        
        foreach ((array)$this->additional_headers as $header) {
            if (!isset($header['name'], $header['value'])) {
                continue;
            }
            $defaultHeaders[] = $header;
        }
        
        // reindex
        $defaultHeaders = array_values($defaultHeaders);
        
        // default params
        $defaultParams = CMap::mergeArray(array(
            'server_id'             => (int)$this->server_id,
            'transport'             => self::TRANSPORT_SMTP,
            'hostname'              => null,
            'username'              => null,
            'password'              => null,
            'port'                  => 25,
            'timeout'               => 30,
            'protocol'              => null,
            'probability'           => 100,
            'headers'               => $defaultHeaders,
            'from'                  => $this->from_email,
            'fromName'              => $this->from_name,
            'sender'                => $this->from_email,
            'returnPath'            => $this->from_email,
            'replyTo'               => !empty($this->reply_to_email) ? $this->reply_to_email : $this->from_email,
            'to'                    => null,
            'subject'               => null,
            'body'                  => null,
            'plainText'             => null,
            'trackingEnabled'       => $this->getTrackingEnabled(), // changed from 1.3.5.3
            'signingEnabled'        => $this->getSigningEnabled(),
            'forceFrom'             => $this->force_from,
            'forcedFromEmail'       => null,
            'forceReplyTo'          => $this->force_reply_to,
            'forceSender'           => $this->force_sender == self::TEXT_YES, // 1.3.7.1
            'sendingDomain'         => null, // 1.3.7.1
            'dkimPrivateKey'        => null,
            'dkimDomain'            => null,
            'dkimSelector'          => SendingDomain::getDkimSelector(),
            'maxConnectionMessages' => !empty($this->max_connection_messages) ? $this->max_connection_messages : 1,
        ), $this->attributes);

        // avoid merging arrays recursive ending up with multiple arrays when we expect only one.
        $uniqueKeys = array('from', 'sender', 'returnPath', 'replyTo', 'to');
        foreach ($uniqueKeys as $key) {
            if (array_key_exists($key, $params) && array_key_exists($key, $defaultParams)) {
                unset($defaultParams[$key]);
            }
        }

        //
        if (!empty($params['headers'])) {
            foreach ($params['headers'] as $index => $header) {
                if (!isset($header['name'], $header['value'])) {
                    continue;
                }
                foreach ($defaultParams['headers'] as $idx => $h) {
                    if (!isset($h['name'], $h['value'])) {
                        continue;
                    }
                    if (strtolower($h['name']) == strtolower($header['name'])) {
                        unset($defaultParams['headers'][$idx]);
                    }
                }
            }
        }

        // merge them all now
        $params      = CMap::mergeArray($defaultParams, $params);
        $customer_id = null;
        $fromEmail   = $this->from_email;
        
        if (is_object($deliveryObject) && $deliveryObject instanceof Campaign) {
            $_fromName   = !empty($params['fromNameCustom']) ? $params['fromNameCustom'] : $deliveryObject->from_name;
            $_fromEmail  = !empty($params['fromEmailCustom']) ? $params['fromEmailCustom'] : $deliveryObject->from_email;
            $_replyEmail = !empty($params['replyToCustom']) ? $params['replyToCustom'] : $deliveryObject->reply_to;

            $params['fromName'] = $_fromName;
            $params['from']     = array($_fromEmail => $_fromName);
            $params['sender']   = array($_fromEmail => $_fromName);
            $params['replyTo']  = array($_replyEmail => $_fromName);

            $customer_id = $deliveryObject->customer_id;
            $fromEmail   = $_fromEmail;
        }
        
        if (is_object($deliveryObject) && $deliveryObject instanceof Lists && !empty($deliveryObject->default)) {
            $_fromName   = !empty($params['fromNameCustom']) ? $params['fromNameCustom'] : $deliveryObject->default->from_name;
            $_fromEmail  = !empty($params['fromEmailCustom']) ? $params['fromEmailCustom'] : $deliveryObject->default->from_email;
            $_replyEmail = !empty($params['replyToCustom']) ? $params['replyToCustom'] : $deliveryObject->default->reply_to;

            $params['fromName'] = $_fromName;
            $params['from']     = array($_fromEmail => $_fromName);
            $params['sender']   = array($_fromEmail => $_fromName);
            $params['replyTo']  = array($_replyEmail => $_fromName);

            $customer_id = $deliveryObject->customer_id;
            $fromEmail   = $_fromEmail;
        }

        if ($params['forceReplyTo'] == self::FORCE_REPLY_TO_ALWAYS) {
            $params['replyTo'] = !empty($this->reply_to_email) ? $this->reply_to_email : $this->from_email;
        }

        if ($params['forceFrom'] == self::FORCE_FROM_ALWAYS) {
            $fromEmail = $this->from_email;
        }

        if (!empty($params['signingEnabled'])) {
            $sendingDomain = null;
            if (!empty($this->bounce_server_id) && !empty($this->bounceServer)) {
                $returnPathEmail = !empty($this->bounceServer->email) ? $this->bounceServer->email : $this->bounceServer->username;
                $sendingDomain   = SendingDomain::model()->signingEnabled()->findVerifiedByEmail($returnPathEmail, $customer_id);
            }
            if (empty($sendingDomain)) {
                $sendingDomain = SendingDomain::model()->signingEnabled()->findVerifiedByEmail($fromEmail, $customer_id);
            }
            if (!empty($sendingDomain)) {
                $params['dkimPrivateKey'] = $sendingDomain->dkim_private_key;
                $params['dkimDomain']     = $sendingDomain->name;
            }
        }
        
        if ($params['forceFrom'] == self::FORCE_FROM_ALWAYS || ($params['forceFrom'] == self::FORCE_FROM_WHEN_NO_SIGNING_DOMAIN && empty($params['dkimDomain']))) {
            $fromEmail = $this->from_email;
            if (!empty($params['from'])) {
                if (is_array($params['from'])) {
                    foreach ($params['from'] as $key => $value) {
                        break;
                    }
                    $params['from']   = array($fromEmail => $value);
                    $params['sender'] = array($fromEmail => $value);
                } else {
                    $params['from']   = $fromEmail;
                    $params['sender'] = $fromEmail;
                }
            }
        }

        $hasBounceServer = false;
        if (!empty($this->bounce_server_id) && !empty($this->bounceServer)) {
            if (!empty($this->bounceServer->email)) {
                $params['returnPath'] = $this->bounceServer->email;
                $hasBounceServer      = true;
            } elseif (FilterVarHelper::email($this->bounceServer->username)) {
                $params['returnPath'] = $this->bounceServer->username;
                $hasBounceServer      = true;
            }
        }
        
        // 1.3.7.1
        if (!$hasBounceServer) {
            list($_fromEmail) = $this->getMailer()->findEmailAndName($params['from']);
            if (!empty($_fromEmail) && FilterVarHelper::email($_fromEmail)) {
                $sendingDomain = SendingDomain::model()->findVerifiedByEmail($_fromEmail, $customer_id);
                if (!empty($sendingDomain)) {
                    $params['returnPath'] = $_fromEmail;
                }
            }
        }
        //
        
        // changed since 1.3.5.3
        if (!empty($params['trackingEnabled'])) {
            // since 1.3.5.4 - we disabled the action hook in the favor of the direct method.
            $params = $this->_handleTrackingDomain($params);
        }
        
        // since 1.3.5.9
        foreach ($params['headers'] as $index => $header) {
            if (!isset($header['name'], $header['value'])) {
                continue;
            }
            if (strtolower($header['name']) == 'x-force-return-path') {
                $header['value'] = preg_replace('#\[([a-z0-9\_]+)\](\-)?#six', '', $header['value']);
                $header['value'] = trim($header['value'], '- ');
	            $header['value'] = str_replace('-@', '@', $header['value']);

                $params['headers'][$index]['value'] = $header['value'];
                $params['returnPath'] = $header['value'];
                break;
            }
        }
        //
        
        // and trigger the attached filters
        return (array)Yii::app()->hooks->applyFilters('delivery_server_get_params_array', $params);
    }

    /**
     * @return string
     */
    public function getFromEmail()
    {
        return $this->from_email;
    }

    /**
     * @return string
     */
    public function getFromName()
    {
        return $this->from_name;
    }

    /**
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->from_email;
    }

    /**
     * Can be used in order to do checks against missing requirements!
     * If must return false if all requirements are fine, otherwise a message about missing requirements!
     */
    public function requirementsFailed()
    {
        return false;
    }

    /**
     * @return null|string
     */
    public function getTypeName()
    {
        return self::getNameByType($this->type);
    }

    /**
     * @param $type
     * @return null|string
     */
    public static function getNameByType($type)
    {
        $mapping = self::getTypesMapping();
        if (!isset($mapping[$type])) {
            return null;
        }
	    if ($type == self::TRANSPORT_SMTP_MYSMTPCOM) {
		    return 'SMTP mySMTP.com';
	    }
        return ucwords(str_replace(array('-'), ' ', Yii::t('servers', $type)));
    }

    /**
     * @return array
     */
    public static function getTypesMapping()
    {
        static $mapping;
        if ($mapping !== null) {
            return (array)$mapping;
        }

        $mapping = array(
            self::TRANSPORT_MAILGUN_WEB_API      => 'DeliveryServerMailgunWebApi',
            self::TRANSPORT_SPARKPOST_WEB_API    => 'DeliveryServerSparkpostWebApi',
            self::TRANSPORT_SENDGRID_WEB_API     => 'DeliveryServerSendgridWebApi',
            self::TRANSPORT_POSTAL_WEB_API       => 'DeliveryServerPostalWebApi',

            self::TRANSPORT_ELASTICEMAIL_WEB_API => 'DeliveryServerElasticemailWebApi',
            self::TRANSPORT_AMAZON_SES_WEB_API   => 'DeliveryServerAmazonSesWebApi',
            self::TRANSPORT_PEPIPOST_WEB_API     => 'DeliveryServerPepipostWebApi',

            self::TRANSPORT_MAILJET_WEB_API      => 'DeliveryServerMailjetWebApi',
            self::TRANSPORT_SENDINBLUE_WEB_API   => 'DeliveryServerSendinblueWebApi',
            self::TRANSPORT_NEWSMAN_WEB_API      => 'DeliveryServerNewsManWebApi',
            
            self::TRANSPORT_SMTP                 => 'DeliveryServerSmtp',
            self::TRANSPORT_SMTP_AMAZON          => 'DeliveryServerSmtpAmazon',
            self::TRANSPORT_SMTP_POSTMASTERY     => 'DeliveryServerSmtpPostmastery',
            self::TRANSPORT_SMTP_POSTAL          => 'DeliveryServerSmtpPostal',
            self::TRANSPORT_SMTP_MYSMTPCOM       => 'DeliveryServerSmtpMySmtpCom',
            
            self::TRANSPORT_DYN_WEB_API          => 'DeliveryServerDynWebApi',
            self::TRANSPORT_TIPIMAIL_WEB_API     => 'DeliveryServerTipimailWebApi',
            self::TRANSPORT_LEADERSEND_WEB_API   => 'DeliveryServerLeadersendWebApi',
            
            self::TRANSPORT_POSTMARK_WEB_API     => 'DeliveryServerPostmarkWebApi',
            self::TRANSPORT_MAILERQ_WEB_API      => 'DeliveryServerMailerqWebApi',
            self::TRANSPORT_MANDRILL_WEB_API     => 'DeliveryServerMandrillWebApi',
            
            self::TRANSPORT_SENDMAIL             => 'DeliveryServerSendmail',
            self::TRANSPORT_PHP_MAIL             => 'DeliveryServerPhpMail',
            self::TRANSPORT_PICKUP_DIRECTORY     => 'DeliveryServerPickupDirectory',
        );

        $mapping = (array)Yii::app()->hooks->applyFilters('delivery_servers_get_types_mapping', $mapping);
        
        foreach ($mapping as $type => $class) {
            $server = new $class();
            if ($server->requirementsFailed()) {
               unset($mapping[$type]); 
            }
        }
        
        return $mapping;
    }

    /**
     * @param Customer|null $customer
     * @return array
     */
    public static function getCustomerTypesMapping(Customer $customer = null)
    {
        static $mapping;
        if ($mapping !== null) {
            return (array)$mapping;
        }

        $mapping = self::getTypesMapping();
        if (!$customer) {
            $allowed = (array)Yii::app()->options->get('system.customer_servers.allowed_server_types', array());
        } else {
            $allowed = (array)$customer->getGroupOption('servers.allowed_server_types', array());
        }

        foreach ($mapping as $type => $name) {
            if (!in_array($type, $allowed)) {
                unset($mapping[$type]);
                continue;
            }
        }

        return $mapping = (array)Yii::app()->hooks->applyFilters('delivery_servers_get_customer_types_mapping', $mapping);
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_ACTIVE     => ucfirst(Yii::t('app', self::STATUS_ACTIVE)),
            self::STATUS_IN_USE     => ucfirst(Yii::t('app', self::STATUS_IN_USE)),
            self::STATUS_INACTIVE   => ucfirst(Yii::t('app', self::STATUS_INACTIVE)),
            self::STATUS_DISABLED   => ucfirst(Yii::t('app', self::STATUS_DISABLED)),
        );
    }

    /**
     * @return array
     */
    public static function getTypesList()
    {
        $list = array();
        foreach (self::getTypesMapping() as $key => $value) {
            $list[$key] = self::getNameByType($key);
        }
        return $list;
    }

    /**
     * @return array
     */
    public static function getCustomerTypesList()
    {
        $list = array();
        foreach (self::getCustomerTypesMapping() as $key => $value) {
            $list[$key] = self::getNameByType($key);
        }
        return $list;
    }

    /**
     * @param $object
     * @return $this
     */
    public function setDeliveryObject($object)
    {
        $this->_deliveryObject = $object;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryObject()
    {
        return $this->_deliveryObject;
    }

    /**
     * @param $deliveryFor
     * @return $this
     */
    public function setDeliveryFor($deliveryFor)
    {
        $this->_deliveryFor = $deliveryFor;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryFor()
    {
        return $this->_deliveryFor;
    }

    /**
     * @param $for
     * @return bool
     */
    public function isDeliveryFor($for)
    {
        return $this->_deliveryFor == $for;
    }

    /**
     * This is deprecated and must be removed in future
     */
    public function markHourlyUsage($refresh = true)
    {
        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function logUsage(array $params = array())
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_DS_LOG_USAGE) {
            return $this;
        }

        // since 1.3.5
        if (!$this->_logUsage) {
            return $this;
        }

        $log = new DeliveryServerUsageLog();
        $log->server_id = (int)$this->server_id;

        if ($customer = $this->getCustomerByDeliveryObject()) {
            $log->customer_id = (int)$customer->customer_id;
            
            // 1.3.9.5
            $log->addRelatedRecord('customer', $customer, false);
            
            if (!$this->getDeliveryIsCountableForCustomer()) {
                $log->customer_countable = DeliveryServerUsageLog::TEXT_NO;
            }
        }

        // 1.4.4
        if (!empty($params['force_customer_countable'])) {
            $log->customer_countable = DeliveryServerUsageLog::TEXT_YES;
        }

        $log->delivery_for = $this->getDeliveryFor();

        if ($this->getCanHaveHourlyQuota() || $this->getCanHaveDailyQuota() || $this->getCanHaveMonthlyQuota() || (!empty($log->customer_id) && $log->customer_countable == DeliveryServerUsageLog::TEXT_YES)) {
            $log->save(false);
            $this->decreaseHourlyQuota();
            $this->decreaseDailyQuota();
            $this->decreaseMonthlyQuota();
        }

        // 1.3.9.5
        if (!empty($log->customer_id) && $log->customer_countable == DeliveryServerUsageLog::TEXT_YES) {
            $log->customer->increaseLastQuotaMarkCachedUsage();
            
            // since 1.3.9.7
            if ($log->customer->getCanHaveHourlyQuota()) {
                $log->customer->increaseHourlyUsageCached();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getDeliveryIsCountableForCustomer()
    {
        if (!($deliveryObject = $this->getDeliveryObject())) {
            return false;
        }

        if (!($customer = $this->getCustomerByDeliveryObject())) {
            return false;
        }

        $trackableDeliveryFor = array(
            self::DELIVERY_FOR_CAMPAIGN, 
            self::DELIVERY_FOR_CAMPAIGN_TEST, 
            self::DELIVERY_FOR_TEMPLATE_TEST, 
            self::DELIVERY_FOR_LIST
        );
        
        if (!in_array($this->getDeliveryFor(), $trackableDeliveryFor)) {
            return false;
        }

        if($deliveryObject instanceof Campaign) {
            if ($this->isDeliveryFor(self::DELIVERY_FOR_CAMPAIGN) && $customer->getGroupOption('quota_counters.campaign_emails', self::TEXT_YES) == self::TEXT_YES) {
                return true;
            }
            if ($this->isDeliveryFor(self::DELIVERY_FOR_CAMPAIGN_TEST) && $customer->getGroupOption('quota_counters.campaign_test_emails', self::TEXT_YES) == self::TEXT_YES) {
                return true;
            }
            return false;
        }

        if($deliveryObject instanceof CustomerEmailTemplate) {
            if ($this->isDeliveryFor(self::DELIVERY_FOR_TEMPLATE_TEST) && $customer->getGroupOption('quota_counters.template_test_emails', self::TEXT_YES) == self::TEXT_YES) {
                return true;
            }
            return false;
        }

        if($deliveryObject instanceof Lists) {
            if ($this->isDeliveryFor(self::DELIVERY_FOR_LIST) && $customer->getGroupOption('quota_counters.list_emails', self::TEXT_YES) == self::TEXT_YES) {
                return true;
            }
            return false;
        }

        if($deliveryObject instanceof TransactionalEmail) {
            if ($this->isDeliveryFor(self::DELIVERY_FOR_TRANSACTIONAL) && $customer->getGroupOption('quota_counters.transactional_emails', self::TEXT_YES) == self::TEXT_YES) {
                return true;
            }
            return false;
        }

        return false;
    }

    /**
     * @return int
     */
    public function countHourlyUsage()
    {
        $count = 0;
        try {
            $criteria = new CDbCriteria();
            $criteria->compare('server_id', (int)$this->server_id);
            $criteria->addCondition('`date_added` BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d %H:00:00") AND DATE_FORMAT(NOW() + INTERVAL 1 HOUR, "%Y-%m-%d %H:00:00")');
            $count = DeliveryServerUsageLog::model()->count($criteria);
        } catch (Exception $e) {
            
        }
        
        return $count;
    }

    /**
     * @return bool
     */
    public function getCanHaveHourlyQuota()
    {
        return !$this->isNewRecord && $this->hourly_quota > 0;
    }

    /**
     * @return int
     */
    public function countDailyUsage()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('server_id', (int)$this->server_id);
        $criteria->addCondition('`date_added` BETWEEN DATE_FORMAT(NOW(), "%Y-%m-%d 00:00:00") AND DATE_FORMAT(NOW() + INTERVAL 1 DAY, "%Y-%m-%d 00:00:00")');
        return DeliveryServerUsageLog::model()->count($criteria);
    }

    /**
     * @return bool
     */
    public function getCanHaveDailyQuota()
    {
        return !$this->isNewRecord && $this->daily_quota > 0;
    }
    
    /**
     * @return int
     */
    public function countMonthlyUsage()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('server_id', (int)$this->server_id);
        $criteria->addCondition('`date_added` BETWEEN DATE_FORMAT(NOW(), "%Y-%m-01 00:00:00") AND DATE_FORMAT(NOW() + INTERVAL 1 MONTH, "%Y-%m-01 00:00:00")');
        return (int)DeliveryServerUsageLog::model()->count($criteria);
    }

    /**
     * @return bool
     */
    public function getCanHaveMonthlyQuota()
    {
        return !$this->isNewRecord && $this->monthly_quota > 0;
    }

    /**
     * @param bool $useMutex
     * @return int
     */
    public function getHourlyQuotaLeft($useMutex = true)
    {
        if (!$this->getCanHaveHourlyQuota()) {
            return PHP_INT_MAX;
        }
        
        $accessKey = sha1(sprintf($this->_hourlyQuotaAccessKey, (int)$this->server_id));

        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }
        
        if (($sendingsLeft = Yii::app()->cache->get($accessKey)) !== false) {
            if ($useMutex) {
                Yii::app()->mutex->release($accessKey);
            }
            return (int)$sendingsLeft;
        }

        $sendingsLeft = $this->hourly_quota - $this->countHourlyUsage();
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;
        
        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);
        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }
        
        return (int)$sendingsLeft;
    }

    /**
     * @param int $by
     * @param bool $useMutex
     * @return int
     */
    public function decreaseHourlyQuota($by = 1, $useMutex = true)
    {
        if (!$this->getCanHaveHourlyQuota()) {
            return PHP_INT_MAX;
        }

        $accessKey = sha1(sprintf($this->_hourlyQuotaAccessKey, (int)$this->server_id));

        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }
        
        $sendingsLeft = $this->getHourlyQuotaLeft(!$useMutex) - (int)$by;
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;
        
        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);
        
        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }
        
        return (int)$sendingsLeft;
    }
    
    /**
     * @param bool $useMutex
     * @return int
     */
    public function getDailyQuotaLeft($useMutex = true)
    {
        if (!$this->getCanHaveDailyQuota()) {
            return PHP_INT_MAX;
        }

        $accessKey = sha1(sprintf($this->_dailyQuotaAccessKey, (int)$this->server_id));
        
        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }
        
        if (($sendingsLeft = Yii::app()->cache->get($accessKey)) !== false) {
            if ($useMutex) {
                Yii::app()->mutex->release($accessKey);
            }
            return (int)$sendingsLeft;
        }

        $sendingsLeft = $this->daily_quota - $this->countDailyUsage();
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;

        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);
        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }

        return (int)$sendingsLeft;
    }

    /**
     * @param int $by
     * @param bool $useMutex
     * @return int
     */
    public function decreaseDailyQuota($by = 1, $useMutex = true)
    {
        if (!$this->getCanHaveDailyQuota()) {
            return PHP_INT_MAX;
        }

        $accessKey = sha1(sprintf($this->_dailyQuotaAccessKey, (int)$this->server_id));

        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }

        $sendingsLeft = $this->getDailyQuotaLeft(!$useMutex) - (int)$by;
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;

        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);

        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }

        return (int)$sendingsLeft;
    }
    
    /**
     * @param bool $useMutex
     * @return int
     */
    public function getMonthlyQuotaLeft($useMutex = true)
    {
        if (!$this->getCanHaveMonthlyQuota()) {
            return PHP_INT_MAX;
        }
        
        $accessKey = sha1(sprintf($this->_monthlyQuotaAccessKey, (int)$this->server_id));
        
        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }
        
        if (($sendingsLeft = Yii::app()->cache->get($accessKey)) !== false) {
            if ($useMutex) {
                Yii::app()->mutex->release($accessKey);
            }
            return (int)$sendingsLeft;
        }

        $sendingsLeft = $this->monthly_quota - $this->countMonthlyUsage();
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;
        
        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);
        
        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }
        
        return (int)$sendingsLeft;
    }

    /**
     * @param int $by
     * @param bool $useMutex
     * @return int
     */
    public function decreaseMonthlyQuota($by = 1, $useMutex = true)
    {
        if (!$this->getCanHaveMonthlyQuota()) {
            return PHP_INT_MAX;
        }

        $accessKey = sha1(sprintf($this->_monthlyQuotaAccessKey, (int)$this->server_id));

        if ($useMutex && !Yii::app()->mutex->acquire($accessKey, 5)) {
            return 0;
        }
        
        $sendingsLeft = $this->getMonthlyQuotaLeft(!$useMutex) - (int)$by;
        $sendingsLeft = $sendingsLeft > 0 ? $sendingsLeft : 0;
        
        Yii::app()->cache->set($accessKey, $sendingsLeft, self::QUOTA_CACHE_SECONDS);
        
        if ($useMutex) {
            Yii::app()->mutex->release($accessKey);
        }
        
        return (int)$sendingsLeft;
    }

    /**
     * @since 1.5.8
     * @return bool
     */
    public function getCanHaveQuota()
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_DS_QUOTA_CHECK) {
            return false;
        }

        if ($this->isNewRecord) {
            return false;
        }
        
        return $this->getCanHaveHourlyQuota() || $this->getCanHaveDailyQuota() || $this->getCanHaveMonthlyQuota();
    }

    /**
     * @return bool
     */
    public function getIsOverQuota()
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_DS_QUOTA_CHECK) {
            return false;
        }
        
        if ($this->isNewRecord) {
            return false;
        }
        
        if ($this->getHourlyQuotaLeft() == 0) {
            return true;
        }

        if ($this->getDailyQuotaLeft() == 0) {
            return true;
        }

        if ($this->getMonthlyQuotaLeft() == 0) {
            return true;
        }
        
        return false;
    }

    /**
     * @return bool
     */
    public function getCanBeDeleted()
    {
        return !in_array($this->status, array(self::STATUS_IN_USE));
    }

    /**
     * @return bool
     */
    public function getCanBeUpdated()
    {
        return !in_array($this->status, array(self::STATUS_IN_USE, self::STATUS_HIDDEN, self::STATUS_PENDING_DELETE));
    }

    /**
     * @param bool $refresh
     * @return $this
     */
    public function setIsInUse($refresh = true)
    {
        if ($this->getIsInUse()) {
            return $this;
        }

        $this->status = self::STATUS_IN_USE;
        $this->save(false);

        if ($refresh) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * @param bool $refresh
     * @return $this
     */
    public function setIsNotInUse($refresh = true)
    {
        if (!$this->getIsInUse()) {
            return $this;
        }

        $this->status = self::STATUS_ACTIVE;
        $this->save(false);

        if ($refresh) {
            $this->refresh();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsInUse()
    {
        return $this->status === self::STATUS_IN_USE;
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->locked === self::TEXT_YES;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return empty($this->name) ? $this->hostname : $this->name;
    }

	/**
	 * @return bool
	 */
	public function getIsPendingDelete()
	{
		return $this->status === self::STATUS_PENDING_DELETE;
	}

    /**
     * @param $emailAddress
     * @return bool
     */
    public function canSendToDomainOf($emailAddress)
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_DS_CAN_SEND_TO_DOMAIN_OF_CHECK) {
            return true;
        }
        return DeliveryServerDomainPolicy::canSendToDomainOf($this->server_id, $emailAddress);
    }

    /**
     * @return array
     */
    public function getNeverAllowedHeaders()
    {
        $neverAllowed = array(
            'From', 'To', 'Subject', 'Date', 'Return-Path', 'Sender',
            'Reply-To', 'Message-Id', 'List-Unsubscribe',
            'Content-Type', 'Content-Transfer-Encoding', 'Content-Length', 'MIME-Version',
            'X-Sender', 'X-Receiver', 'X-Report-Abuse', 'List-Id'
        );

        $neverAllowed = (array)Yii::app()->hooks->applyFilters('delivery_server_never_allowed_headers', $neverAllowed);
        return $neverAllowed;
    }

    /**
     * @return Customer|null
     */
    public function getCustomerByDeliveryObject()
    {
        return self::parseDeliveryObjectForCustomer($this->getDeliveryObject());
    }

    /**
     * @param $deliveryObject
     * @return Customer|null
     */
    public static function parseDeliveryObjectForCustomer($deliveryObject)
    {
        $customer = null;
        if ($deliveryObject && is_object($deliveryObject)) {
            if ($deliveryObject instanceof Customer) {
                $customer = $deliveryObject;
            } elseif ($deliveryObject instanceof Campaign) {
                $customer = !empty($deliveryObject->list) && !empty($deliveryObject->list->customer) ? $deliveryObject->list->customer : null;
            } elseif ($deliveryObject instanceof Lists) {
                $customer = !empty($deliveryObject->customer) ? $deliveryObject->customer : null;
            } elseif ($deliveryObject instanceof CustomerEmailTemplate) {
                $customer = !empty($deliveryObject->customer) ? $deliveryObject->customer : null;
            } elseif ($deliveryObject instanceof TransactionalEmail && !empty($deliveryObject->customer_id)) {
                $customer = !empty($deliveryObject->customer) ? $deliveryObject->customer : null;
            }
        }
        if (!$customer && Yii::app()->apps->isAppName('customer') && Yii::app()->hasComponent('customer') && Yii::app()->customer->getId() > 0) {
            $customer = Yii::app()->customer->getModel();
        }
        return $customer;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateAdditionalHeaders($attribute, $params)
    {
        $headers = $this->$attribute;
        if (empty($headers) || !is_array($headers)) {
            $headers = array();
        }

        $this->$attribute   = array();
        $_headers           = array();

        $notAllowedHeaders  = (array)$this->getNeverAllowedHeaders();
        $notAllowedHeaders  = array_map('strtolower', $notAllowedHeaders);

        // try to be a bit restrictive
        $namePattern        = '/([a-z0-9\-\_])*/i';
        $valuePattern       = '/.*/i';

        foreach ($headers as $index => $header) {

            if (!is_array($header) || !isset($header['name'], $header['value'])) {
                unset($headers[$index]);
                continue;
            }

            $prefix = Yii::app()->params['email.custom.header.prefix'];
            $name   = preg_replace('/:\s/', '', trim($header['name']));
            $value  = trim($header['value']);

            if (empty($name) || in_array(strtolower($name), $notAllowedHeaders) || stripos($name, $prefix) === 0 || !preg_match($namePattern, $name)) {
                unset($headers[$index]);
                continue;
            }

            if (empty($value) || !preg_match($valuePattern, $value)) {
                unset($headers[$index]);
                continue;
            }

            $_headers[] = array('name' => $name, 'value' => $value);
        }

        $this->$attribute = $_headers;
    }

    /**
     * @param int $currentServerId
     * @param null $deliveryObject
     * @param array $params
     * @return bool|mixed
     */
    public static function pickServer($currentServerId = 0, $deliveryObject = null, $params = array())
    {
        // since 1.3.6.3
        if (!isset($params['excludeServers']) || !is_array($params['excludeServers'])) {
            $params['excludeServers'] = array();
        }
        
        if (!empty($currentServerId)) {
            $params['excludeServers'][] = $currentServerId;
        }

        $params['excludeServers'] = array_filter(array_unique(array_map('intval', $params['excludeServers'])));
        //
        
        // 1.4.2
        static $excludeServers = array();
        foreach ($params['excludeServers'] as $srvId) {
            $excludeServers[] = $srvId;
        }
        $excludeServers = array_filter(array_unique(array_map('intval', $excludeServers)));
        //
        
        if ($customer = self::parseDeliveryObjectForCustomer($deliveryObject)) {
            $checkQuota = is_array($params) && isset($params['customerCheckQuota']) ? $params['customerCheckQuota'] : true;
            if ($checkQuota && $customer->getIsOverQuota()) {
                
                // 1.4.2
                if (empty($params['__afterExcludeServers']) && count($excludeServers)) {
                    $excludeServers = array();
                    $params['excludeServers']        = array();
                    $params['__afterExcludeServers'] = true;
                    return self::pickServer($currentServerId, $deliveryObject, $params);
                }
                //
                
                return false;
            }
            
            // load the servers for this customer only
            $serverIds = array();
            $criteria  = new CDbCriteria();
            $criteria->select = 't.server_id, t.monthly_quota, t.daily_quota, t.hourly_quota';
            $criteria->compare('t.customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('t.server_id', $excludeServers);
            $criteria->addInCondition('t.status', array(self::STATUS_ACTIVE, self::STATUS_IN_USE));
            $servers = self::model()->findAll($criteria);
            
            // remove the ones over quota
            foreach ($servers as $server) {
                if (!$server->getIsOverQuota()) {
                    $serverIds[] = $server->server_id;
                }
            }
            
            // if we have any left, we pass them further
            if (!empty($serverIds)) {
                $criteria = new CDbCriteria();
                $criteria->addInCondition('t.server_id', $serverIds);

                $pickData = self::processPickServerCriteria($criteria, $currentServerId, $deliveryObject, $params);
                if (!empty($pickData['server'])) {
                    return $pickData['server'];
                }
                if (!$pickData['continue']) {

                    // 1.4.2
                    if (empty($params['__afterExcludeServers']) && count($excludeServers)) {
                        $excludeServers = array();
                        $params['excludeServers']        = array();
                        $params['__afterExcludeServers'] = true;
                        return self::pickServer($currentServerId, $deliveryObject, $params);
                    }
                    //
                    
                    return false;
                }
            }
            //
            
            if (!empty($customer->group_id)) {
                
                // local cache
                static $groupServers = array();
                
                if (!isset($groupServers[$customer->group_id])) {
                    $groupServers[$customer->group_id] = array();
                    $criteria = new CDbCriteria();
                    $criteria->select = 't.server_id';
                    $criteria->compare('t.group_id', (int)$customer->group_id);
                    $criteria->addNotInCondition('t.server_id', $excludeServers);
                    $models = DeliveryServerToCustomerGroup::model()->findAll($criteria);
                    foreach ($models as $model) {
                        $groupServers[$customer->group_id][] = (int)$model->server_id;
                    }
                }
                
                if (!empty($groupServers[$customer->group_id])) {
                    
                    // load the servers assigned to this group alone
                    $serverIds = array();
                    $servers   = self::model()->findAll(array(
                        'select'    => 't.server_id, t.monthly_quota, t.daily_quota, t.hourly_quota',
                        'condition' => 't.server_id IN('. implode(', ', array_map('intval', $groupServers[$customer->group_id])) .') AND 
                                        t.`status` IN("' . self::STATUS_ACTIVE . '", "' . self::STATUS_IN_USE . '") AND 
                                        t.customer_id IS NULL',
                    ));
                    
                    // remove the ones over quota
                    foreach ($servers as $server) {
                        if (!$server->getIsOverQuota()) {
                            $serverIds[] = $server->server_id;
                        }
                    }
                    
                    // use what is left, if any
                    if (!empty($serverIds)) {
                        $criteria = new CDbCriteria();
                        $criteria->addInCondition('t.server_id', $serverIds);

                        // since 1.8.4
	                    // This flag should not allow campaigns to use other servers than the ones selected at setup time.
	                    //
	                    // This avoids a issue where you select a delivery server for a campaign 
	                    // and then if this server hits the quota and there are servers assigned to the customer group, those
	                    // servers would be used as a fallback.
	                    // This would trick the customer, campaign the server is selected for a good reason and we should respect that.
                        $params['customerGroupServerIds'] = $serverIds;
                        
                        $pickData = self::processPickServerCriteria($criteria, $currentServerId, $deliveryObject, $params);
                        if (!empty($pickData['server'])) {
                            return $pickData['server'];
                        }
                        if (!$pickData['continue']) {

                            // 1.4.2
                            if (empty($params['__afterExcludeServers']) && count($excludeServers)) {
                                $excludeServers = array();
                                $params['excludeServers']        = array();
                                $params['__afterExcludeServers'] = true;
                                return self::pickServer($currentServerId, $deliveryObject, $params);
                            }
                            //
                            
                            return false;
                        }
                    }
                }
            }

            if ($customer->getGroupOption('servers.can_send_from_system_servers', 'yes') != 'yes') {
                $excludeServers = array(); // reset this
                return false;
            }
        }
        
        // load all system servers
        $serverIds = array();
        $criteria  = new CDbCriteria();
        $criteria->select = 't.server_id, t.monthly_quota, t.daily_quota, t.hourly_quota';
        $criteria->addCondition('t.customer_id IS NULL');
        $criteria->addInCondition('t.status', array(self::STATUS_ACTIVE, self::STATUS_IN_USE));
        $criteria->addNotInCondition('t.server_id', $excludeServers);
        $servers   = self::model()->findAll($criteria);
        
        // remove the ones over quota
        foreach ($servers as $server) {
            if (!$server->getIsOverQuota()) {
                $serverIds[] = $server->server_id;
            }
        }
        
        // use what's left, if any
        if (!empty($serverIds)) {
            $criteria = new CDbCriteria();
            $criteria->addInCondition('t.server_id', $serverIds);
            
            $pickData = self::processPickServerCriteria($criteria, $currentServerId, $deliveryObject, $params);
            if (!empty($pickData['server'])) {
                return $pickData['server'];
            }
            if (!$pickData['continue']) {

                // 1.4.2
                if (empty($params['__afterExcludeServers']) && count($excludeServers)) {
                    $excludeServers = array();
                    $params['excludeServers']        = array();
                    $params['__afterExcludeServers'] = true;
                    return self::pickServer($currentServerId, $deliveryObject, $params);
                }
                //
                
                return false;
            }
        }
        //

        // 1.4.2
        if (empty($params['__afterExcludeServers']) && count($excludeServers)) {
            $excludeServers = array();
            $params['excludeServers']        = array();
            $params['__afterExcludeServers'] = true;
            return self::pickServer($currentServerId, $deliveryObject, $params);
        }
        //
        
        return false;
    }

    /**
     * @param CDbCriteria $criteria
     * @param int $currentServerId
     * @param null $deliveryObject
     * @param array $params
     * @return array
     */
    protected static function processPickServerCriteria(CDbCriteria $criteria, $currentServerId = 0, $deliveryObject = null, $params = array())
    {
        static $campaignServers = array();
        static $campaignHasAssignedServers = array();
        $campaign_id = !empty($deliveryObject) && $deliveryObject instanceof Campaign ? (int)$deliveryObject->campaign_id : 0;
        
        if ($campaign_id > 0 && !isset($campaignServers[$campaign_id])) {
            $campaignServers[$campaign_id] = array();
            $campaignHasAssignedServers[$campaign_id] = false;
            
            $customer  = $deliveryObject->customer;
            $canSelect = $customer->getGroupOption('servers.can_select_delivery_servers_for_campaign', 'no') == 'yes';

            $_campaignServers = CampaignToDeliveryServer::model()->findAllByAttributes(array(
                'campaign_id' => $deliveryObject->campaign_id,
            ));
            
            // 1.3.6.7
            $_serverIds = array();
            foreach ($_campaignServers as $mdl) {
                $_serverIds[] = $mdl->server_id;    
            }

            $_campaignServers = array();
            if (!empty($_serverIds)) {
                $_criteria = new CDbCriteria();
                $_criteria->select = 't.server_id, t.hourly_quota, t.daily_quota, t.monthly_quota';
                $_criteria->addInCondition('t.server_id', $_serverIds);
                $_criteria->addInCondition('t.status', array(self::STATUS_ACTIVE, self::STATUS_IN_USE));
                $_campaignServers = self::model()->findAll($_criteria);
                $campaignHasAssignedServers[$campaign_id] = !empty($_campaignServers);
            }
            // 
 
            if ($canSelect) {
                foreach ($_campaignServers as $server) {
                    $checkQuota = is_array($params) && isset($params['serverCheckQuota']) ? $params['serverCheckQuota'] : true;
                    if ($checkQuota && !$server->getIsOverQuota()) {
                        $campaignServers[$campaign_id][] = $server->server_id;
                    } elseif (!$checkQuota) {
                        $campaignServers[$campaign_id][] = $server->server_id;
                    }
                }
                
                // if there are campaign servers specified but there are no valid servers, we stop!
                if (count($_campaignServers) > 0 && empty($campaignServers[$campaign_id])) {
                    return array('server' => null, 'continue' => true);
                }
                unset($_campaignServers);
            }
        }
        
        $_criteria = new CDbCriteria();
        $_criteria->select = 't.server_id, t.type';
        if ($campaign_id > 0 && !empty($campaignHasAssignedServers[$campaign_id])) {
            // since 1.3.6.6
            if (empty($campaignServers[$campaign_id])) {
                $_criteria->compare('t.server_id', 0);
            } else {
                $_criteria->addInCondition('t.server_id', $campaignServers[$campaign_id]);
            }

	        // since 1.8.4 - reset group servers if any
	        if (!empty($params['customerGroupServerIds'])) {
		        $criteria = new CDbCriteria();
	        }
	        //
        }
        $_criteria->addInCondition('t.status', array(self::STATUS_ACTIVE, self::STATUS_IN_USE));

        // since 1.3.5
        if (!empty($params['useFor']) && is_array($params['useFor']) && array_search(self::USE_FOR_ALL, $params['useFor']) === false) {
            $_criteria->addInCondition('t.use_for', array_merge(array(self::USE_FOR_ALL), $params['useFor']));
        }
        //

        $_criteria->order = 't.probability DESC';
        $_criteria->mergeWith($criteria);
        
        $_servers = self::model()->findAll($_criteria);
        if (empty($_servers)) {
            return array('server' => null, 'continue' => true);
        }

        $mapping = self::getTypesMapping();
        foreach ($_servers as $index => $srv) {
            if (!isset($mapping[$srv->type])) {
                unset($_servers[$index]);
                continue;
            }
            
            // since 1.3.6.2
            // this avoids issues when different configs from cli/web
            if ($failMessage = self::model($mapping[$srv->type])->requirementsFailed()) {
                Yii::log((string)$failMessage, CLogger::LEVEL_ERROR);
                unset($_servers[$index]);
                continue;
            }
            
            $_servers[$index] = self::model($mapping[$srv->type])->findByPk($srv->server_id);
        }

        if (empty($_servers)) {
            return array('server' => null, 'continue' => true);
        }

        // 1.4.4
        // reset the indexes
        $_servers = array_values($_servers);

        $probabilities  = array();
        foreach ($_servers as $srv) {
            if (!isset($probabilities[$srv->probability])) {
                $probabilities[$srv->probability] = array();
            }
            $probabilities[$srv->probability][] = $srv;
        }

        $server                 = null;
        $probabilitySum         = array_sum(array_keys($probabilities));
        $probabilityPercentage  = array();
        $cumulative             = array();

        foreach ($probabilities as $probability => $probabilityServers) {
            $probabilityPercentage[$probability] = ($probability / $probabilitySum) * 100;
        }
        asort($probabilityPercentage);

        foreach ($probabilityPercentage as $probability => $percentage) {
            $cumulative[$probability] = end($cumulative) + $percentage;
        }
        asort($cumulative);

        $lowest      = floor(current($cumulative));
        $probability = rand($lowest, 100);

        foreach($cumulative as $key => $value) {
            if ($value > $probability)  {
                $rand   = array_rand(array_keys($probabilities[$key]), 1);
                $server = $probabilities[$key][$rand];
                break;
            }
        }

        if (empty($server)) {
            $rand   = array_rand(array_keys($_servers), 1);
            $server = $_servers[$rand];
        }

        if (count($_servers) > 1 && $currentServerId > 0 && $server->server_id == $currentServerId) {
            return self::processPickServerCriteria($criteria, $server->server_id, $deliveryObject, $params);
        }

        $server->getMailer()->reset();

        if (empty($deliveryObject)) {
            $server->setDeliveryFor(self::DELIVERY_FOR_SYSTEM);
        } elseif ($deliveryObject instanceof Campaign) {
            $server->setDeliveryFor(self::DELIVERY_FOR_CAMPAIGN);
        } elseif ($deliveryObject instanceof Lists) {
            $server->setDeliveryFor(self::DELIVERY_FOR_LIST);
        } elseif ($deliveryObject instanceof CustomerEmailTemplate) {
            $server->setDeliveryFor(self::DELIVERY_FOR_TEMPLATE_TEST);
        }

        return array('server' => $server, 'continue' => true);
    }

    /**
     * @param null $status
     * @return bool
     */
    public function saveStatus($status = null)
    {
        if (empty($this->server_id)) {
            return false;
        }

        if ($status && $status == $this->status) {
            return true;
        }
        
        if ($status) {
            $this->status = $status;
        }
        
        $attributes = array('status' => $this->status);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'before_savestatus')), $this);
	    //
	    
        $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'server_id = :sid', array(':sid' => (int)$this->server_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
        
        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    public function _handleTrackingDomain(array $params)
    {
        $trackingDomainModel = null;
        if (!empty($params['trackingDomainModel'])) {
            $trackingDomainModel = $params['trackingDomainModel'];
        } elseif (!empty($this->tracking_domain_id) && !empty($this->trackingDomain)) {
            $params['trackingDomainModel'] = $trackingDomainModel = $this->trackingDomain;
        }

        if (empty($trackingDomainModel)) {
            return $params;    
        }
  
        if (!empty($params['body']) || !empty($params['plainText'])) {
            $currentDomainName  = parse_url(Yii::app()->options->get('system.urls.frontend_absolute_url'), PHP_URL_HOST);
            $trackingDomainName = strpos($trackingDomainModel->name, 'http') !== 0 ? 'http://' . $trackingDomainModel->name : $trackingDomainModel->name;
            $trackingDomainName = parse_url($trackingDomainName, PHP_URL_HOST);
            
            if (!empty($currentDomainName) && !empty($trackingDomainName)) {
                $searchReplace = array(
                    'https://www.' . $currentDomainName => 'http://' . $trackingDomainName,
                    'http://www.' . $currentDomainName  => 'http://' . $trackingDomainName,
                    'https://' . $currentDomainName     => 'http://' . $trackingDomainName,
                    'http://' . $currentDomainName      => 'http://' . $trackingDomainName,
                );
                
                // since 1.5.8
                if (!empty($trackingDomainModel->scheme) && $trackingDomainModel->scheme ==  TrackingDomain::SCHEME_HTTPS) {
                    foreach ($searchReplace as $key => $value) {
                        $searchReplace[$key] = str_replace('http://', 'https://', $value);
                    }
                }

                // since 1.3.5.9
                if (stripos($trackingDomainName, $currentDomainName) === false) {
                    $searchReplace[$currentDomainName] = $trackingDomainName;
                }

                $searchFor   = array_keys($searchReplace);
                $replaceWith = array_values($searchReplace);

                $params['body']      = str_replace($searchFor, $replaceWith, $params['body']);
                $params['plainText'] = str_replace($searchFor, $replaceWith, $params['plainText']);
                if (!empty($params['headers']) && is_array($params['headers'])) {
                    foreach ($params['headers'] as $idx => $header) {
                        if (strpos($header['value'], $currentDomainName) !== false) {
                            $params['headers'][$idx]['value'] = str_replace($searchFor, $replaceWith, $header['value']);
                        }
                    }
                }
                $params['trackingDomain'] = $trackingDomainName;
                $params['currentDomain']  = $currentDomainName;
            }
        }
        return $params;
    }

    /**
     * @return bool|DeliveryServer
     */
    public function copy()
    {
        $copied = false;

        if ($this->isNewRecord) {
            return $copied;
        }

        $transaction = Yii::app()->db->beginTransaction();

        try {

            $server = clone $this;
            $server->isNewRecord  = true;
            $server->server_id    = null;
            $server->status       = self::STATUS_DISABLED;
            $server->date_added   = new CDbExpression('NOW()');
            $server->last_updated = new CDbExpression('NOW()');

            if (!empty($server->name)) {
                if (preg_match('/\#(\d+)$/', $server->name, $matches)) {
                    $counter = (int)$matches[1];
                    $counter++;
                    $server->name = preg_replace('/\#(\d+)$/', '#' . $counter, $server->name);
                } else {
                    $server->name .= ' #1';
                }
            }

            if (!$server->save(false)) {
                throw new CException($server->shortErrors->getAllAsString());
            }

            if (!empty($this->domainPolicies)) {
                foreach ($this->domainPolicies as $policy) {
                    $policy = clone $policy;
                    $policy->domain_id = null;
                    $policy->server_id = $server->server_id;
                    $policy->date_added   = new CDbExpression('NOW()');
                    $policy->last_updated = new CDbExpression('NOW()');
                    $policy->save(false);
                }
            }

            $transaction->commit();
            $copied = $server;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $copied;
    }

    /**
     * @return bool
     */
    public function getIsDisabled()
    {
        return $this->status == self::STATUS_DISABLED;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function enable()
    {
        if (!$this->getIsDisabled()) {
            return false;
        }
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function disable()
    {
        if (!$this->getIsActive()) {
            return false;
        }
        $this->status = self::STATUS_DISABLED;
        return $this->save(false);
    }

    /**
     * @return array
     */
    public function getForceFromOptions()
    {
        return array(
            self::FORCE_FROM_NEVER => ucfirst(Yii::t('servers', self::FORCE_FROM_NEVER)),
            self::FORCE_FROM_ALWAYS => ucfirst(Yii::t('servers', self::FORCE_FROM_ALWAYS)),
            self::FORCE_FROM_WHEN_NO_SIGNING_DOMAIN => ucfirst(Yii::t('servers', self::FORCE_FROM_WHEN_NO_SIGNING_DOMAIN)),
        );
    }

    /**
     * @return array
     */
    public function getForceReplyToOptions()
    {
        return array(
            self::FORCE_REPLY_TO_NEVER  => ucfirst(Yii::t('servers', self::FORCE_REPLY_TO_NEVER)),
            self::FORCE_REPLY_TO_ALWAYS => ucfirst(Yii::t('servers', self::FORCE_REPLY_TO_ALWAYS)),
        );
    }

    /**
     * @return array
     */
    public function getUseForOptions()
    {
        return array(
            self::USE_FOR_ALL           => ucfirst(Yii::t('servers', self::USE_FOR_ALL)),
            self::USE_FOR_CAMPAIGNS     => ucfirst(Yii::t('servers', self::USE_FOR_CAMPAIGNS)),
            self::USE_FOR_TRANSACTIONAL => ucfirst(Yii::t('servers', self::USE_FOR_TRANSACTIONAL . ' emails')),
            self::USE_FOR_EMAIL_TESTS   => Yii::t('servers', 'Email tests'),
            self::USE_FOR_REPORTS       => Yii::t('servers', 'Reports'),
            self::USE_FOR_LIST_EMAILS   => Yii::t('servers', 'List emails'),
            self::USE_FOR_INVOICES      => Yii::t('servers', 'Invoices'),
        );
    }

    /**
     * @param $for
     * @return bool
     */
    public function getUseFor($for)
    {
        return in_array($this->use_for, array(self::USE_FOR_ALL, $for));
    }

    /**
     * @return bool
     */
    public function getUseForCampaigns()
    {
        return $this->getUseFor(self::USE_FOR_CAMPAIGNS);
    }

    /**
     * @return bool
     */
    public function getUseForTransactional()
    {
        return $this->getUseFor(self::USE_FOR_TRANSACTIONAL);
    }

    /**
     * @return bool
     */
    public function getUseForEmailTests()
    {
        return $this->getUseFor(self::USE_FOR_EMAIL_TESTS);
    }

    /**
     * @return bool
     */
    public function getUseForReports()
    {
        return $this->getUseFor(self::USE_FOR_REPORTS);
    }

    /**
     * @return bool
     */
    public function getUseForListEmails()
    {
        return $this->getUseFor(self::USE_FOR_LIST_EMAILS);
    }

    /**
     * @return $this
     */
    public function enableLogUsage()
    {
        $this->_logUsage = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disableLogUsage()
    {
        $this->_logUsage = false;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSigningEnabled()
    {
        return $this->signing_enabled == self::TEXT_YES && in_array($this->type, $this->getSigningSupportedTypes());
    }

    /**
     * @return bool
     */
    public function getTrackingEnabled()
    {
        return !empty($this->tracking_domain_id) && !empty($this->trackingDomain) && !empty($this->trackingDomain->name);
    }

    /**
     * @return array
     */
    public function getImportExportAllowedAttributes()
    {
        $allowedAttributes = array(
            'type',
            'name',
            'hostname',
            'username',
            'password',
            'port',
            'protocol',
            'timeout',
            'from_email',
            'from_name',
            'reply_to_email',
            'hourly_quota',
            'daily_quota',
            'monthly_quota',
            'pause_after_send',
        );
        return (array)Yii::app()->hooks->applyFilters('delivery_servers_get_import_export_allowed_attributes', $allowedAttributes);
    }

    /**
     * @return mixed|string
     */
    public function getDswhUrl()
    {
        $url = Yii::app()->options->get('system.urls.frontend_absolute_url') . sprintf('dswh/%d', $this->server_id);
        if (MW_IS_CLI) {
            return $url;
        }
        if (Yii::app()->request->isSecureConnection && parse_url($url, PHP_URL_SCHEME) == 'http') {
            $url = substr_replace($url, 'https', 0, 4);
        }
        return $url;
    }

    /**
     * @return bool
     */
    public function getCanEmbedImages()
    {
        return false;
    }
    
    /**
     * @param array $fields
     * @return array
     */
    public function getFormFieldsDefinition(array $fields = array())
    {
        $form     = new CActiveForm();
        $defaults = array(
            'name' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'name', $this->getHtmlOptions('name')),
            ),
            'hostname' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'hostname', $this->getHtmlOptions('hostname')),
            ),
            'username' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'username', $this->getHtmlOptions('username')),
            ),
            'password' => array(
                'visible'   => true,
                'fieldHtml' => $form->passwordField($this, 'password', $this->getHtmlOptions('password')),
            ),
            'port' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'port', $this->getHtmlOptions('port')),
            ),
            'protocol' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'protocol', $this->getProtocolsArray(), $this->getHtmlOptions('protocol')),
            ),
            'timeout' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'timeout', $this->getHtmlOptions('timeout')),
            ),
            'from_email' => array(
                'visible'   => true,
                'fieldHtml' => $form->emailField($this, 'from_email', $this->getHtmlOptions('from_email')),
            ),
            'from_name' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'from_name', $this->getHtmlOptions('from_name')),
            ),
            'probability' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'probability', $this->getProbabilityArray(), $this->getHtmlOptions('probability')),
            ),
            'hourly_quota' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'hourly_quota', $this->getHtmlOptions('hourly_quota')),
            ),
            'daily_quota' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'daily_quota', $this->getHtmlOptions('daily_quota')),
            ),
            'monthly_quota' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'monthly_quota', $this->getHtmlOptions('monthly_quota')),
            ),
            'pause_after_send' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'pause_after_send', $this->getHtmlOptions('pause_after_send')),
            ),
            'bounce_server_id' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'bounce_server_id', $this->getBounceServersArray(), $this->getHtmlOptions('bounce_server_id')),
            ),
            'tracking_domain_id'  => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'tracking_domain_id', $this->getTrackingDomainsArray(), $this->getHtmlOptions('tracking_domain_id')),
            ),
            'use_for' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'use_for', $this->getUseForOptions(), $this->getHtmlOptions('use_for')),
            ),
            'signing_enabled' => array(
                'visible'    => true,
                'fieldHtml'  => $form->dropDownList($this, 'signing_enabled', $this->getYesNoOptions(), $this->getHtmlOptions('signing_enabled')),
            ),
            'force_from' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'force_from', $this->getForceFromOptions(), $this->getHtmlOptions('force_from')),
            ),
            'force_sender' => array(
                'visible'    => true,
                'fieldHtml'  => $form->dropDownList($this, 'force_sender', $this->getYesNoOptions(), $this->getHtmlOptions('force_sender')),
            ),
            'reply_to_email' => array(
                'visible'    => true,
                'fieldHtml'  => $form->emailField($this, 'reply_to_email', $this->getHtmlOptions('reply_to_email')),
            ),
            'force_reply_to' => array(
                'visible'   => true,
                'fieldHtml' => $form->dropDownList($this, 'force_reply_to', $this->getForceReplyToOptions(), $this->getHtmlOptions('force_reply_to')),
            ),
            'max_connection_messages' => array(
                'visible'   => true,
                'fieldHtml' => $form->numberField($this, 'max_connection_messages', $this->getHtmlOptions('max_connection_messages')),
            ),
        );

        foreach ($fields as $fieldName => $props) {
            if ((!is_array($props) || empty($props)) && array_key_exists($fieldName, $defaults)) {
                unset($defaults[$fieldName]);
                unset($fields[$fieldName]);
                continue;
            }
        }
        
        $fields = CMap::mergeArray($defaults, $fields);
        $fields = Yii::app()->hooks->applyFilters('delivery_server_form_fields_definition', $fields, $this);

        foreach ($fields as $fieldName => $props) {
            if (!is_array($props) || empty($props) || empty($props['fieldHtml']) || empty($props['visible'])) {
                unset($fields[$fieldName]);
                continue;
            }
        }
        
        return $fields;
    }

    /**
     * @return string
     */
    public function getProviderUrl()
    {
        if (!Yii::app()->params['delivery_servers.show_provider_url']) {
            return '';
        }
        
        $url = $this->_providerUrl;
        foreach (self::loadRemoteServers() as $server) {
            if ($server->type == $this->type) {
                if (!empty($server->provider_url)) {
                    $url = $server->provider_url;
                }
                break;
            }
        }

        $url = Yii::app()->hooks->applyFilters('delivery_server_get_provider_url', $url, $this);
        
        return !empty($url) && FilterVarHelper::url($url) ? $url : '';
    }

    /**
     * @return bool
     */
    public function getIsRecommended()
    {
        foreach (self::loadRemoteServers() as $server) {
            if ($server->type == $this->type) {
                return !empty($server->recommended);
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public static function loadRemoteServers()
    {
        static $servers;
        if (!empty($servers) && is_array($servers)) {
            return $servers;
        }
        
        $cacheTtl = 3600 * 24;
        $cacheKey = sha1(__METHOD__);
        if (($servers = Yii::app()->cache->get($cacheKey)) !== false) {
            return (array)$servers;
        }

        $response = AppInitHelper::makeRemoteRequest('https://www.mailwizz.com/api/delivery-servers/index', array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
        ));

        if (empty($response) || empty($response['message']) || empty($response['status']) || $response['status'] != 'success') {
            Yii::app()->cache->set($cacheKey, array(), $cacheTtl);
            return array();
        }

        $servers = @json_decode($response['message']);
        if (empty($servers) || !is_array($servers)) {
            Yii::app()->cache->set($cacheKey, array(), $cacheTtl);
            return array();
        }
        Yii::app()->cache->set($cacheKey, (array)$servers, $cacheTtl);
        
        return (array)$servers;
    }
}
