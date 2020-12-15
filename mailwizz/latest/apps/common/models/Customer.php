<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Customer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "customer".
 *
 * The followings are the available columns in table 'customer':
 * @property integer $customer_id
 * @property string $customer_uid
 * @property integer $group_id
 * @property integer $language_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $timezone
 * @property string $avatar
 * @property string $removable
 * @property string $confirmation_key
 * @property integer $oauth_uid
 * @property string $oauth_provider
 * @property string $status
 * @property string $birth_date
 * @property string $phone
 * @property string $twofa_enabled
 * @property string $twofa_secret
 * @property integer $twofa_timestamp
 * @property string $date_added
 * @property string $last_updated
 * @property string $last_login
 * @property string $inactive_at
 *
 * The followings are the available model relations:
 * @property BounceServer[] $bounceServers
 * @property Campaign[] $campaigns
 * @property CustomerCampaignTag[] $campaignTags
 * @property CustomerMessage[] $messages
 * @property CampaignGroup[] $campaignGroups
 * @property CustomerGroup $group
 * @property CustomerApiKey[] $apiKeys
 * @property CustomerCompany $company
 * @property CustomerAutoLoginToken[] $autoLoginTokens
 * @property CustomerEmailTemplate[] $emailTemplates
 * @property CustomerEmailTemplateCategory[] $emailTemplateCategories
 * @property CustomerActionLog[] $actionLogs
 * @property CustomerQuotaMark[] $quotaMarks
 * @property DeliveryServer[] $deliveryServers
 * @property FeedbackLoopServer[] $fblServers
 * @property Language $language
 * @property DeliveryServerUsageLog[] $usageLogs
 * @property Lists[] $lists
 * @property PricePlanOrder[] $pricePlanOrders
 * @property PricePlanOrderNote[] $pricePlanOrderNotes
 * @property TrackingDomain[] $trackingDomains
 * @property SendingDomain[] $sendingDomains
 * @property TransactionalEmail[] $transactionalEmails
 * @property CustomerEmailBlacklist[] $blacklistedEmails
 * @property CustomerSuppressionList[] $suppressionLists
 */
class Customer extends ActiveRecord
{
    const TEXT_NO = 'no';

    const TEXT_YES = 'yes';

    const STATUS_PENDING_CONFIRM = 'pending-confirm';

    const STATUS_PENDING_ACTIVE = 'pending-active';
    
    const STATUS_PENDING_DELETE = 'pending-delete';
    
    const STATUS_PENDING_DISABLE = 'pending-disable';
    
    const STATUS_DISABLED = 'disabled';

    /**
     * @var string
     */
    protected $_lastQuotaMark;

    /**
     * @var int 
     */
    protected $_lastQuotaCheckTime = 0;

    /**
     * @var int 
     */
    protected $_lastQuotaCheckTimeDiff = 30;

    /**
     * @var int 
     */
    protected $_lastQuotaCheckMaxDiffCounter = 500;

    /**
     * @var bool 
     */
    protected $_lastQuotaCheckTimeOverQuota = false;

    /**
     * @var string
     */
    public $fake_password;

    /**
     * @var string
     */
    public $confirm_password;

    /**
     * @var string
     */
    public $confirm_email;

    /**
     * @var string
     */
    public $tc_agree;

    /**
     * @var string
     */
    public $newsletter_consent;

    /**
     * @var int
     */
    public $sending_quota_usage;

    /**
     * @var string
     */
    public $company_name;

    /**
     * @var string
     */
    public $new_avatar;

    /**
     * @var string
     */
    public $countUsageFromQuotaMarkCachePattern = 'Customer::countUsageFromQuotaMark:cid:%d:date_added:%s';

    /**
     * @var string
     */
    public $countHourlyUsageCachePattern = 'Customer::countHourlyUsage:cid:%d:date_added:%s:hourly_quota:%d';

    /**
     * @var string
     */
    public $email_details = 'no';

    /**
     * @var string
     */
    protected $birthDateInit;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $avatarMimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $avatarMimes = Yii::app()->extensionMimes->get(array('png', 'jpg', 'jpeg', 'gif'))->toArray();
        }

        $rules = array(
            // when new customer is created by a user.
            array('first_name, last_name, email, confirm_email, fake_password, confirm_password, timezone, birthDate, status', 'required', 'on' => 'insert'),

            // when a customer is updated by a user
            array('first_name, last_name, email, confirm_email, timezone, birthDate, status', 'required', 'on' => 'update'),

            // when a customer updates his profile
            array('first_name, last_name, email, confirm_email, timezone, birthDate', 'required', 'on' => 'update-profile'),
            
            // when a customer registers
            array('first_name, last_name, email, confirm_email, fake_password, confirm_password, timezone, birthDate, tc_agree', 'required', 'on' => 'register'),

            array('group_id', 'numerical', 'integerOnly' => true),
            array('group_id', 'exist', 'className' => 'CustomerGroup'),
            array('language_id', 'numerical', 'integerOnly' => true),
            array('language_id', 'exist', 'className' => 'Language'),
            array('first_name, last_name', 'length', 'min' => 1, 'max' => 100),
            array('email, confirm_email', 'length', 'min' => 4, 'max' => 100),
            array('email, confirm_email', 'email', 'validateIDN' => true),
            array('timezone', 'in', 'range' => array_keys(DateTimeHelper::getTimeZones())),
            array('fake_password, confirm_password', 'length', 'min' => 6, 'max' => 100),
            array('confirm_password', 'compare', 'compareAttribute' => 'fake_password'),
            array('confirm_email', 'compare', 'compareAttribute' => 'email'),
            array('email', 'unique'),
            array('email_details', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('birthDate', 'type', 'dateFormat' => 'yyyy-MM-dd'),
            array('birthDate', '_validateMinimumAge'),
	        array('phone', 'length', 'max' => 32),
	        array('phone', 'match', 'pattern' => '/[0-9\s\-]+/'),
            array('inactiveAt', 'date', 'format' => 'yyyy-mm-dd hh:mm:ss'),
            array('inactiveAt', '_validateInactiveAt'),

            // avatar
            array('new_avatar', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $avatarMimes, 'allowEmpty' => true),

            // unsafe
            array('group_id, status, email_details, inactiveAt', 'unsafe', 'on' => 'update-profile, register'),

            // mark them as safe for search
            array('customer_uid, first_name, last_name, email, group_id, status, company_name', 'safe', 'on' => 'search'),
            
            array('newsletter_consent', 'safe'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'bounceServers'             => array(self::HAS_MANY, 'BounceServer', 'customer_id'),
            'campaigns'                 => array(self::HAS_MANY, 'Campaign', 'customer_id'),
            'campaignGroups'            => array(self::HAS_MANY, 'CampaignGroup', 'customer_id'),
            'campaignTags'              => array(self::HAS_MANY, 'CustomerCampaignTags', 'customer_id'),
            'messages'                  => array(self::HAS_MANY, 'CustomerMessage', 'customer_id'),
            'group'                     => array(self::BELONGS_TO, 'CustomerGroup', 'group_id'),
            'apiKeys'                   => array(self::HAS_MANY, 'CustomerApiKey', 'customer_id'),
            'company'                   => array(self::HAS_ONE, 'CustomerCompany', 'customer_id'),
            'autoLoginTokens'           => array(self::HAS_MANY, 'CustomerAutoLoginToken', 'customer_id'),
            'emailTemplates'            => array(self::HAS_MANY, 'CustomerEmailTemplate', 'customer_id'),
            'emailTemplateCategories'   => array(self::HAS_MANY, 'CustomerEmailTemplateCategory', 'customer_id'),
            'actionLogs'                => array(self::HAS_MANY, 'CustomerActionLog', 'customer_id'),
            'quotaMarks'                => array(self::HAS_MANY, 'CustomerQuotaMark', 'customer_id'),
            'deliveryServers'           => array(self::HAS_MANY, 'DeliveryServer', 'customer_id'),
            'fblServers'                => array(self::HAS_MANY, 'FeedbackLoopServer', 'customer_id'),
            'language'                  => array(self::BELONGS_TO, 'Language', 'language_id'),
            'usageLogs'                 => array(self::HAS_MANY, 'DeliveryServerUsageLog', 'customer_id'),
            'lists'                     => array(self::HAS_MANY, 'Lists', 'customer_id'),
            'pricePlanOrders'           => array(self::HAS_MANY, 'PricePlanOrder', 'customer_id'),
            'pricePlanOrderNotes'       => array(self::HAS_MANY, 'PricePlanOrderNote', 'customer_id'),
            'trackingDomains'           => array(self::HAS_MANY, 'TrackingDomain', 'customer_id'),
            'sendingDomains'            => array(self::HAS_MANY, 'SendingDomain', 'customer_id'),
            'transactionalEmails'       => array(self::HAS_MANY, 'TransactionalEmail', 'customer_id'),
            'blacklistedEmails'         => array(self::HAS_MANY, 'CustomerEmailBlacklist', 'customer_id'),
            'suppressionLists'          => array(self::HAS_MANY, 'CustomerSuppressionList', 'customer_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'customer_id'   => Yii::t('customers', 'ID'),
            'customer_uid'  => Yii::t('customers', 'Unique ID'),
            'group_id'      => Yii::t('customers', 'Group'),
            'language_id'   => Yii::t('customers', 'Language'),
            'first_name'    => Yii::t('customers', 'First name'),
            'last_name'     => Yii::t('customers', 'Last name'),
            'email'         => Yii::t('customers', 'Email'),
            'password'      => Yii::t('customers', 'Password'),
            'timezone'      => Yii::t('customers', 'Timezone'),
            'avatar'        => Yii::t('customers', 'Avatar'),
            'new_avatar'    => Yii::t('customers', 'New avatar'),
            'removable'     => Yii::t('customers', 'Removable'),

            'confirm_email'         => Yii::t('customers', 'Confirm email'),
            'fake_password'         => Yii::t('customers', 'Password'),
            'confirm_password'      => Yii::t('customers', 'Confirm password'),
            'tc_agree'              => Yii::t('customers', 'Terms and conditions'),
            'sending_quota_usage'   => Yii::t('customers', 'Sending quota usage'),
            'company_name'          => Yii::t('customers', 'Company'),
            
            'email_details'         => Yii::t('customers', 'Send details via email'),
            'birth_date'            => Yii::t('customers', 'Birth date'),
            'birthDate'             => Yii::t('customers', 'Birth date'),
            'phone'                 => Yii::t('customers', 'Phone'),
            'inactive_at'           => Yii::t('customers', 'Inactivate at'),
            'inactiveAt'            => Yii::t('customers', 'Inactivate at'),

            'newsletter_consent' => Yii::t('settings', 'Newsletter'),
	        
	        'twofa_enabled' => Yii::t('customers', '2FA enabled'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeHelpTexts()
    {
    	$texts = array(
    		'twofa_enabled' => Yii::t('customers', 'Please make sure you scan the QR code in your authenticator application before enabling this feature, otherwise you will be locked out from your account'),
            'inactive_at'   => Yii::t('customers', 'Leave it empty for no future inactivation'),
            'inactiveAt'    => Yii::t('customers', 'Leave it empty for no future inactivation')
	    );

	    return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
    * Retrieves a list of models based on the current search/filter conditions.
    * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
    */
    public function search()
    {
        $criteria = new CDbCriteria;

	    $criteria->compare('t.customer_uid', $this->customer_uid, true);
        $criteria->compare('t.first_name', $this->first_name, true);
        $criteria->compare('t.last_name', $this->last_name, true);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.group_id', $this->group_id);
        $criteria->compare('t.status', $this->status);

        if ($this->company_name) {
            $criteria->with['company'] = array(
                'together' => true,
                'joinType' => 'INNER JOIN',
            );
            $criteria->compare('company.name', $this->company_name, true);
        }
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort' => array(
                'defaultOrder' => 't.customer_id DESC',
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Customer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        parent::afterValidate();
        $this->handleUploadedAvatar();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if (empty($this->customer_uid)) {
            $this->customer_uid = $this->generateUid();
        }

        if (!empty($this->fake_password)) {
            $this->password = Yii::app()->passwordHasher->hash($this->fake_password);
        }

        if ($this->removable === self::TEXT_NO) {
            $this->status = self::STATUS_ACTIVE;
        }

        if (empty($this->confirmation_key)) {
            $this->confirmation_key = sha1($this->customer_uid . StringHelper::uniqid());
        }

        if (empty($this->timezone)) {
            $this->timezone = 'UTC';
        }
        
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        parent::afterFind();
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        parent::afterSave();
    }

    /**
     * @inheritdoc
     */
    protected function beforeDelete()
    {
        if ($this->removable != self::TEXT_YES) {
            return false;
        }

        // since 1.3.5
        if ($this->status != self::STATUS_PENDING_DELETE) {
            $this->status = self::STATUS_PENDING_DELETE;
            $this->save(false);
            return false;
        }
        
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    protected function afterDelete()
    {
        if (!empty($this->customer_uid)) {
            // clean customer files, if any.
            $storagePath = Yii::getPathOfAlias('root.frontend.files.customer');
            $customerFiles = $storagePath.'/'.$this->customer_uid;
            if (file_exists($customerFiles) && is_dir($customerFiles)) {
                FileSystemHelper::deleteDirectoryContents($customerFiles, true, 1);
            }
        }

        parent::afterDelete();
    }

    /**
     * @return bool
     */
    public function getIsRemovable()
    {
        if ($this->removable != self::TEXT_YES) {
            return false;
        }

        if (in_array($this->status, array(self::STATUS_PENDING_DELETE))) {
            return false;
        }
        
        return true;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name.' '.$this->last_name;
        }
        return $this->email;
    }

    /**
     * @return array
     */
    public function getStatusesArray()
    {
        return array(
            self::STATUS_ACTIVE          => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE        => Yii::t('app', 'Inactive'),
            self::STATUS_PENDING_CONFIRM => Yii::t('app', 'Pending confirm'),
            self::STATUS_PENDING_ACTIVE  => Yii::t('app', 'Pending active'),
            self::STATUS_PENDING_DELETE  => Yii::t('app', 'Pending delete'),
            self::STATUS_PENDING_DISABLE => Yii::t('app', 'Pending disable'),
            self::STATUS_DISABLED        => Yii::t('app', 'Disabled'),
        );
    }

    /**
     * @return array
     */
    public function getTimeZonesArray()
    {
        return DateTimeHelper::getTimeZones();
    }

    /**
     * @param $customer_uid
     * @return static
     */
    public function findByUid($customer_uid)
    {
        return self::model()->findByAttributes(array(
            'customer_uid' => $customer_uid,
        ));
    }

    /**
     * @return string
     */
    public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);

        if (!empty($exists)) {
            return $this->generateUid();
        }

        return $unique;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->customer_uid;
    }

	/**
	 * For compatibility with the Customer component
	 * 
	 * @return int
	 */
    public function getId()
    {
    	return $this->customer_id;
    }

    /**
     * @return array
     */
    public function getAvailableDeliveryServers()
    {
        static $deliveryServers;
        if ($deliveryServers !== null) {
            return $deliveryServers;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'server_id, hostname, name';
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addInCondition('status', array(DeliveryServer::STATUS_ACTIVE, DeliveryServer::STATUS_IN_USE));
        // since 1.3.5
        $criteria->addInCondition('use_for', array(DeliveryServer::USE_FOR_ALL, DeliveryServer::USE_FOR_CAMPAIGNS));
        
        //
        $deliveryServers = DeliveryServer::model()->findAll($criteria);

        // merge with existing customer servers, but avoid duplicates
        if (!empty($this->group_id)) {
            
            // 1.5.5
            $deliveryServersIds = array();
            if (!empty($deliveryServers)) {
                foreach ($deliveryServers as $deliveryServer) {
                    $deliveryServersIds[] = $deliveryServer->server_id;
                }
            }
            
            // 1.5.5 
            $criteria = new CDbCriteria();
            $criteria->compare('group_id', (int)$this->group_id);
            if (!empty($deliveryServersIds)) {
                $criteria->addNotInCondition('server_id', $deliveryServersIds);
            }
            
            $groupServerIds = array();
            $groupServers   = DeliveryServerToCustomerGroup::model()->findAll($criteria);
            foreach ($groupServers as $group) {
                $groupServerIds[] = (int)$group->server_id;
            }

            if (!empty($groupServerIds)) {
                $criteria = new CDbCriteria();
                $criteria->select = 'server_id, hostname, name';
                $criteria->addInCondition('server_id', $groupServerIds);
                $criteria->addCondition('customer_id IS NULL');
                $criteria->addInCondition('status', array(DeliveryServer::STATUS_ACTIVE, DeliveryServer::STATUS_IN_USE));
                
                // since 1.3.5
                $criteria->addInCondition('use_for', array(DeliveryServer::USE_FOR_ALL, DeliveryServer::USE_FOR_CAMPAIGNS));
                
                //
                $models = DeliveryServer::model()->findAll($criteria);
                
                // since 1.5.5
                if (!empty($models)) {
                    foreach ($models as $model) {
                        $deliveryServers[] = $model;
                    }
                }
            }
        }

        if (empty($deliveryServers) && $this->getGroupOption('servers.can_send_from_system_servers', 'yes') == 'yes') {
            $criteria = new CDbCriteria();
            $criteria->select = 'server_id, hostname, name';
            $criteria->addCondition('customer_id IS NULL');
            $criteria->addInCondition('status', array(DeliveryServer::STATUS_ACTIVE, DeliveryServer::STATUS_IN_USE));
            // since 1.3.5
            $criteria->addInCondition('use_for', array(DeliveryServer::USE_FOR_ALL, DeliveryServer::USE_FOR_CAMPAIGNS));
            //
            $deliveryServers = DeliveryServer::model()->findAll($criteria);
        }

        return $deliveryServers;
    }

    /**
     * @return int
     */
    public function getHourlyQuota()
    {
        static $cache = array();
        if (isset($cache[$this->customer_id])) {
            return (int)$cache[$this->customer_id];
        }
        return $cache[$this->customer_id] = (int)$this->getGroupOption('sending.hourly_quota', 0);
    }
    
    /**
     * @return bool
     */
    public function getCanHaveHourlyQuota()
    {
        return $this->getHourlyQuota() > 0;
    }

    /**
     * @return int
     */
    public function countHourlyUsage()
    {
        if (!$this->getCanHaveHourlyQuota()) {
            return 0;
        }
        
        $dateAdded = date('Y-m-d H:00:00');
        $cacheKey  = sha1(sprintf($this->countHourlyUsageCachePattern, (int)$this->customer_id, (string)$dateAdded, (int)$this->getHourlyQuota()));

        if (!Yii::app()->mutex->acquire($cacheKey, 60)) {
            return 0;
        }
        
        if (($count = Yii::app()->cache->get($cacheKey)) !== false) {
            Yii::app()->mutex->release($cacheKey);
            return $count;
        }

        $count = 0;
        try {
            
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$this->customer_id);
            $criteria->compare('customer_countable', self::TEXT_YES);
            $criteria->addCondition('`date_added` >= :startDateTime');
            $criteria->params[':startDateTime'] = $dateAdded;
            $count = DeliveryServerUsageLog::model()->count($criteria);
            
        } catch (Exception $e) {
            
        }

        Yii::app()->cache->set($cacheKey, $count, 3600);
        Yii::app()->mutex->release($cacheKey);

        return (int)$count;
    }

    /**
     * @return int
     */
    public function getHourlyQuotaLeft()
    {
        if (!$this->getCanHaveHourlyQuota()) {
            return PHP_INT_MAX;
        }
        
        $maxHourlyQuota = $this->getHourlyQuota();
        $hourlyUsage    = (int)$this->countHourlyUsage();
        $hourlyLeft     = $maxHourlyQuota - $hourlyUsage;
        $hourlyLeft     = $hourlyLeft < 0 ? 0 : $hourlyLeft;
        
        return $hourlyLeft;
    }

    /**
     * @param int $by
     * @return $this
     */
    public function increaseHourlyUsageCached($by = 1)
    {
        if (!$this->getCanHaveHourlyQuota()) {
            return $this;
        }
        
        $dateAdded = date('Y-m-d H:00:00');
        $cacheKey  = sha1(sprintf($this->countHourlyUsageCachePattern, (int)$this->customer_id, (string)$dateAdded, (int)$this->getHourlyQuota()));
        
        if (!Yii::app()->mutex->acquire($cacheKey, 60)) {
            return $this;
        }
  
        $count  = (int)Yii::app()->cache->get($cacheKey);
        $count += (int)$by;

        Yii::app()->cache->set($cacheKey, $count, 3600);
        Yii::app()->mutex->release($cacheKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getSendingQuotaUsageDisplay()
    {
        $formatter = Yii::app()->format;
        $_allowed  = (int)$this->getGroupOption('sending.quota', -1);
        $_count    = (int)$this->countUsageFromQuotaMark();
        $allowed   = !$_allowed ? 0 : ($_allowed == -1 ? '&infin;' : $formatter->formatNumber($_allowed));
        $count     = $formatter->formatNumber($_count);
        $percent   = ($_allowed < 1 ? 0 : ($_count > $_allowed ? 100 : round(($_count / $_allowed) * 100, 2)));

        return sprintf('%s (%s/%s)', $percent . '%', $count, $allowed);
    }

    /**
     * @return $this
     */
    public function resetSendingQuota()
    {
        // 1.3.7.3
        $this->removeOption('sending_quota.last_notification');
        CustomerQuotaMark::model()->deleteAllByAttributes(array('customer_id' => (int)$this->customer_id));

        // reset the hourly quota, if any
        $dateAdded = date('Y-m-d H:00:00');
        $cacheKey  = sha1(sprintf($this->countHourlyUsageCachePattern, (int)$this->customer_id, (string)$dateAdded, (int)$this->getHourlyQuota()));
        if (Yii::app()->mutex->acquire($cacheKey, 60)) {
            Yii::app()->cache->set($cacheKey, 0);
            Yii::app()->mutex->release($cacheKey);
        }
        //
        
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOverQuota()
    {
        if ($this->isNewRecord) {
            return false;
        }

        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_CUSTOMER_QUOTA_CHECK) {
            return false;
        }
        
        // since 1.3.9.7 - max number of emails customer is able to send in one hour
        if ($this->getCanHaveHourlyQuota() && !$this->getHourlyQuotaLeft()) {
            return true;
        }

        $timeNow = time();
        if ($this->_lastQuotaCheckTime > 0 && ($this->_lastQuotaCheckTime + $this->_lastQuotaCheckTimeDiff) > $timeNow) {
            return $this->_lastQuotaCheckTimeOverQuota;
        }
        $this->_lastQuotaCheckTime = $timeNow;

        $quota     = (int)$this->getGroupOption('sending.quota', -1);
        $timeValue = (int)$this->getGroupOption('sending.quota_time_value', -1);

        if ($quota == 0 || $timeValue == 0) {
            $this->_lastQuotaCheckTime += $timeNow;
            return $this->_lastQuotaCheckTimeOverQuota = true;
        }

        if ($quota == -1 && $timeValue == -1) {
            $this->_lastQuotaCheckTime += $timeNow;
            return $this->_lastQuotaCheckTimeOverQuota = false;
        }

        $timestamp = 0;
        if ($timeValue > 0) {
            $timeUnit  = $this->getGroupOption('sending.quota_time_unit', 'month');
            $seconds   = strtotime(sprintf('+ %d %s', $timeValue, ($timeValue == 1 ? $timeUnit : $timeUnit . 's')), $timeNow) - $timeNow;
            $timestamp = strtotime($this->getLastQuotaMark()->date_added) + $seconds;

            if ($timeNow >= $timestamp) {
                $this->_takeQuotaAction();
                // SINCE 1.3.5.9
                if ($this->getGroupOption('sending.action_quota_reached') == 'reset') {
                    return $this->_lastQuotaCheckTimeOverQuota = false;
                }
                //
                return $this->_lastQuotaCheckTimeOverQuota = true; // keep an eye on it
            }
        }

        if ($quota == -1) {
            $this->_lastQuotaCheckTime += $timeNow;
            return $this->_lastQuotaCheckTimeOverQuota = false;
        }

        $currentUsage = $this->countUsageFromQuotaMark();

        if ($currentUsage >= $quota) {
            // force waiting till end of ts
            if ($this->getGroupOption('sending.quota_wait_expire', 'yes') == 'yes' && $timeNow <= $timestamp) {
                $this->_lastQuotaCheckTime += $timeNow;
                return $this->_lastQuotaCheckTimeOverQuota = true;
            }
            $this->_takeQuotaAction();
            return $this->_lastQuotaCheckTimeOverQuota = true;
        }

        if (($quota - $currentUsage) > $this->_lastQuotaCheckMaxDiffCounter) {
            $this->_lastQuotaCheckTime += $timeNow;
            return $this->_lastQuotaCheckTimeOverQuota = false;
        }

        return $this->_lastQuotaCheckTimeOverQuota = false;
    }

    /**
     * @return int
     */
    public function countUsageFromQuotaMark()
    {
        $quotaMark = $this->getLastQuotaMark();
        $cacheKey  = sha1(sprintf($this->countUsageFromQuotaMarkCachePattern, (int)$this->customer_id, (string)$quotaMark->date_added));
        
        if (!Yii::app()->mutex->acquire($cacheKey, 60)) {
            return 0;
        }
        
        if (($count = Yii::app()->cache->get($cacheKey)) !== false) {
            Yii::app()->mutex->release($cacheKey);
            return (int)$count;
        }
        
        $count = 0;
        
        try {

            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$this->customer_id);
            $criteria->compare('customer_countable', self::TEXT_YES);
            $criteria->addCondition('`date_added` >= :startDateTime');
            $criteria->params[':startDateTime'] = $quotaMark->date_added;

            $count = DeliveryServerUsageLog::model()->count($criteria);
        
        } catch (Exception $e) {
            
        }
        
        Yii::app()->cache->set($cacheKey, $count, 3600);
        Yii::app()->mutex->release($cacheKey);
        
        return (int)$count;
    }

    /**
     * @param int $by
     * @return $this
     */
    public function increaseLastQuotaMarkCachedUsage($by = 1)
    {
        $quotaMark = $this->getLastQuotaMark();
        $cacheKey  = sha1(sprintf($this->countUsageFromQuotaMarkCachePattern, (int)$this->customer_id, (string)$quotaMark->date_added));

        if (!Yii::app()->mutex->acquire($cacheKey, 60)) {
            return $this;
        }
        
        $count  = (int)Yii::app()->cache->get($cacheKey);
        $count += (int)$by;
        
        Yii::app()->cache->set($cacheKey, $count, 3600);
        Yii::app()->mutex->release($cacheKey);
        
        return $this;
    }

    /**
     * @return CustomerQuotaMark
     */
    public function getLastQuotaMark()
    {
        if ($this->_lastQuotaMark !== null) {
            return $this->_lastQuotaMark;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->order = 'mark_id DESC';
        $criteria->limit = 1;
        $quotaMark = CustomerQuotaMark::model()->find($criteria);
        if (empty($quotaMark)) {
            $quotaMark = $this->createQuotaMark(false);
        }
        return $this->_lastQuotaMark = $quotaMark;
    }

    /**
     * @param bool $deleteOlder
     * @return CustomerQuotaMark
     */
    public function createQuotaMark($deleteOlder = true)
    {
        if ($deleteOlder) {
            $this->resetSendingQuota();
        }

        $quotaMark = new CustomerQuotaMark();
        $quotaMark->customer_id = $this->customer_id;
        $quotaMark->save(false);
        $quotaMark->refresh(); // because of date_added being an expression
        
        return $this->_lastQuotaMark = $quotaMark;
    }

    /**
     * @return bool|CustomerGroup
     */
    public function getHasGroup()
    {
        if (!$this->hasAttribute('group_id') || !$this->group_id) {
            return false;
        }
        return !empty($this->group) ? $this->group : false;
    }

    /**
     * @param $option
     * @param null $defaultValue
     * @return null|string
     */
    public function getGroupOption($option, $defaultValue = null)
    {
        static $loaded = array();

        if (!isset($loaded[$this->customer_id])) {
            $loaded[$this->customer_id] = array();
        }

        if (strpos($option, 'system.customer_') !== 0) {
            $option = 'system.customer_' . $option;
        }

        if (array_key_exists($option, $loaded[$this->customer_id])) {
            return $loaded[$this->customer_id][$option];
        }

        if (!($group = $this->getHasGroup())) {
            return $loaded[$this->customer_id][$option] = Yii::app()->options->get($option, $defaultValue);
        }

        return $loaded[$this->customer_id][$option] = $group->getOptionValue($option, $defaultValue);
    }

    /**
     * @param int $size
     * @return mixed
     */
    public function getGravatarUrl($size = 50)
    {
        $gravatar = sprintf('//www.gravatar.com/avatar/%s?s=%d', md5(strtolower(trim($this->email))), (int)$size);
        return Yii::app()->hooks->applyFilters('customer_get_gravatar_url', $gravatar, $this, $size);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed
     */
    public function getAvatarUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->avatar)) {
            return $this->getGravatarUrl($width);
        }
        return ImageHelper::resize($this->avatar, $width, $height, $forceSize);
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return mixed
     */
    public function getAllListsIds()
    {
        static $ids = array();
        if (isset($ids[$this->customer_id])) {
            return $ids[$this->customer_id];
        }
        $ids[$this->customer_id] = array();

        $criteria = new CDbCriteria();
        $criteria->select    = 'list_id';
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));
        
        $models = Lists::model()->findAll($criteria);
        foreach ($models as $model) {
            $ids[$this->customer_id][] = $model->list_id;
        }
        return $ids[$this->customer_id];
    }

    /**
     * @return mixed
     */
    public function getAllListsIdsNotMerged()
    {
        static $ids = array();
        if (isset($ids[$this->customer_id])) {
            return $ids[$this->customer_id];
        }
        $ids[$this->customer_id] = array();

	    $criteria = new CDbCriteria();
	    $criteria->select = 'list_id';
	    $criteria->compare('customer_id', (int)$this->customer_id);
	    $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));
	    $criteria->compare('merged', Lists::TEXT_NO);
	    
        $models = Lists::model()->findAll($criteria);
        foreach ($models as $model) {
            $ids[$this->customer_id][] = $model->list_id;
        }
        return $ids[$this->customer_id];
    }

	/**
	 * @return mixed
	 */
	public function getAllListsIdsNotMergedNotArchived()
	{
		static $ids = array();
		if (isset($ids[$this->customer_id])) {
			return $ids[$this->customer_id];
		}
		$ids[$this->customer_id] = array();

		$criteria = new CDbCriteria();
		$criteria->select = 'list_id';
		$criteria->compare('customer_id', (int)$this->customer_id);
		$criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
		$criteria->compare('merged', Lists::TEXT_NO);
		
		$models = Lists::model()->findAll($criteria);
		foreach ($models as $model) {
			$ids[$this->customer_id][] = $model->list_id;
		}
		return $ids[$this->customer_id];
	}

    /**
     * @return mixed
     */
    public function getAllSurveysIds()
    {
        static $ids = array();
        if (isset($ids[$this->customer_id])) {
            return $ids[$this->customer_id];
        }
        $ids[$this->customer_id] = array();

        $criteria = new CDbCriteria();
        $criteria->select    = 'survey_id';
        $criteria->condition = 'customer_id = :cid AND `status` != :st';
        $criteria->params    = array(
            ':cid' => (int)$this->customer_id,
            ':st' => Survey::STATUS_PENDING_DELETE
        );
        $models = Survey::model()->findAll($criteria);
        foreach ($models as $model) {
            $ids[$this->customer_id][] = $model->survey_id;
        }
        return $ids[$this->customer_id];
    }

    /**
     * @since 1.3.6.2
     * @param PricePlan $pricePlan
     * @return CAttributeCollection
     * @throws CException
     */
    public function isOverPricePlanLimits(PricePlan $pricePlan)
    {
        $default = new CAttributeCollection(array(
            'overLimit' => false,
            'object'    => '',
            'limit'     => 0,
            'count'     => 0,
        ));
        
        $in = clone $default;
        $in->overLimit = true;
        
        $kp  = 'system.customer_';
        $grp = $pricePlan->customerGroup;
        
        if (($in->limit = (int)$grp->getOptionValue($kp . 'servers.max_bounce_servers', 0)) > 0) {
            $in->count  = BounceServer::model()->countByAttributes(array('customer_id' => (int)$this->customer_id));
            $in->object = 'bounce servers';
            if ($in->count > $in->limit) {
                return $in;
            }
        }

        if (($in->limit = (int)$grp->getOptionValue($kp . 'servers.max_delivery_servers', 0)) > 0) {
            $in->count  = DeliveryServer::model()->countByAttributes(array('customer_id' => (int)$this->customer_id));
            $in->object = 'delivery servers';
            if ($in->count > $in->limit) {
                return $in;
            }
        }

        if (($in->limit = (int)$grp->getOptionValue($kp . 'servers.max_fbl_servers', 0)) > 0) {
            $in->count  = FeedbackLoopServer::model()->countByAttributes(array('customer_id' => (int)$this->customer_id));
            $in->object = 'feedback loop servers';
            if ($in->count > $in->limit) {
                return $in;
            }
        }
        
        if (($in->limit = (int)$grp->getOptionValue($kp . 'campaigns.max_campaigns', 0)) > 0) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$this->customer_id);
            $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
            $in->count  = Campaign::model()->count($criteria);
            $in->object = 'campaigns';
            if ($in->count > $in->limit) {
                return $in;
            }
        }
        
        if (($in->limit = (int)$grp->getOptionValue($kp . 'lists.max_subscribers', 0)) > 0) {
            $criteria = new CDbCriteria();
            $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';
            $criteria->addInCondition('t.list_id', $this->getAllListsIdsNotMerged());
            $in->count  = ListSubscriber::model()->count($criteria);
            $in->object = 'subscribers';
            if ($in->count > $in->limit) {
                return $in;
            }
        }

        if (($in->limit = (int)$grp->getOptionValue($kp . 'lists.max_lists', 0)) > 0) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$this->customer_id);
            $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));
            $in->count  = Lists::model()->count($criteria);
            $in->object = 'lists';
            if ($in->count > $in->limit) {
                return $in;
            }
        }

        if (($in->limit = (int)$grp->getOptionValue($kp . 'sending_domains.max_sending_domains', 0)) > 0) {
            $in->count  = SendingDomain::model()->countByAttributes(array('customer_id' => (int)$this->customer_id));
            $in->object = 'sending domains';
            if ($in->count > $in->limit) {
                return $in;
            }
        }
        
        return $default;
    }

    /**
     * @return void
     */
    protected function handleUploadedAvatar()
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!($avatar = CUploadedFile::getInstance($this, 'new_avatar'))) {
            return;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.avatars');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError('new_avatar', Yii::t('customers', 'The avatars storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return;
            }
        }

        $newAvatarName = uniqid(rand(0, time())) . '-' . $avatar->getName();
        if (!$avatar->saveAs($storagePath . '/' . $newAvatarName)) {
            $this->addError('new_avatar', Yii::t('customers', 'Cannot move the avatar into the correct storage folder!'));
            return;
        }

        $this->avatar = '/frontend/assets/files/avatars/' . $newAvatarName;
    }

    /**
     * @return bool
     */
    protected function _takeQuotaAction()
    {
        $quotaAction = $this->getGroupOption('sending.action_quota_reached', '');
        if (empty($quotaAction)) {
            return true;
        }

        $this->createQuotaMark();

        if ($quotaAction != 'move-in-group') {
            return true;
        }

        $moveInGroupId = (int)$this->getGroupOption('sending.move_to_group_id', '');
        if (empty($moveInGroupId)) {
            return true;
        }

        $group = CustomerGroup::model()->findByPk($moveInGroupId);
        if (empty($group)) {
            return true;
        }

        $this->group_id = $group->group_id;
        $this->addRelatedRecord('group', $group, false);
        $this->save(false);

        return true;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        Yii::app()->options->set('customers.' . (int)$this->customer_id . '.' . $key, $value);
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return Yii::app()->options->get('customers.' . (int)$this->customer_id . '.' . $key, $default);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function removeOption($key)
    {
        return Yii::app()->options->remove('customers.' . (int)$this->customer_id . '.' . $key);
    }

    /**
     * @return $this
     */
    public function updateLastLogin()
    {
        if (!array_key_exists('last_login', $this->getAttributes())) {
            return $this;
        }
        $attributes = array('last_login' => new CDbExpression('NOW()'));
        $params  = array(':id' => $this->customer_id);
        Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'customer_id = :id', $params);
        $this->last_login = date('Y-m-d H:i:s');
        return $this;
    }

    /**
     * @param null $status
     * @return bool
     */
    public function saveStatus($status = null)
    {
        if (empty($this->customer_id)) {
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
	    
	    $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'customer_id = :cid', array(':cid' => (int)$this->customer_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
	    
	    return $result;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        if (empty($this->birth_date) || $this->birth_date == '0000-00-00') {
            return null;
        }
        return $this->birth_date = date('Y-m-d', strtotime($this->birth_date));
    }

    /**
     * @param $value
     */
    public function setBirthDate($value)
    {
        if (empty($value)) {
            $this->birth_date = null;
            return;
        }
        $this->birth_date = date('Y-m-d', strtotime($value));
    }

    /**
     * @return mixed
     */
    public function getInactiveAt()
    {
        if (empty($this->inactive_at)) {
            return null;
        }
        return $this->inactive_at;
    }

    /**
     * @param $value
     */
    public function setInactiveAt($value)
    {
        if (empty($value)) {
            $this->inactive_at = null;
            return;
        }
        $this->inactive_at = date('Y-m-d H:i:s', strtotime($value));
    }

    /**
     * @return string
     */
    public function getDatePickerFormat()
    {
        return 'yy-mm-dd';
    }
    
    /**
     * @return string
     */
    public function getDatePickerLanguage()
    {
        $language = Yii::app()->getLanguage();
        if (strpos($language, '_') === false) {
            return $language;
        }
        $language = explode('_', $language);
        return $language[0];
    }

	/**
	 * @return bool
	 */
    public function getTwoFaEnabled()
    {
    	return $this->twofa_enabled === self::TEXT_YES;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateMinimumAge($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        $currentYear  = date('Y');
        $selectedYear = date('Y', strtotime($this->$attribute));
        
        if ($selectedYear >= $currentYear) {
            $this->addError($attribute, Yii::t('customers', 'Please select a past date!'));
            return;
        }

        $minimum  = (int)Yii::app()->options->get('system.customer_registration.minimum_age', 16);
        if (($age = $currentYear - $selectedYear) < $minimum) {
            $this->addError($attribute, Yii::t('customers', 'Age is {age} but minimum is {min}!', array(
                '{age}' => $age,
                '{min}' => $minimum,
            )));
            return;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateInactiveAt($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        if (!$this->$attribute) {
            return;
        }

        $currentDate  = date('Y-m-d H:i:s');
        $selectedDate = date('Y-m-d H:i:s', strtotime($this->$attribute));

        if ($selectedDate <= $currentDate) {
            $this->addError($attribute, Yii::t('customers', 'Please select a future date!'));
            return;
        }
    }
}
