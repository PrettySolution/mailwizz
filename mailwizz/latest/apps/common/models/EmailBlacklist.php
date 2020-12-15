<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklist
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0 
 */

/**
 * This is the model class for table "email_blacklist".
 *
 * The followings are the available columns in table 'email_blacklist':
 * @property integer $email_id
 * @property integer $subscriber_id
 * @property string $email
 * @property string $reason
 * @property string $date_added
 * @property string $last_updated
 */
class EmailBlacklist extends ActiveRecord
{
    /**
     * flag for emails that are check when a campaign is sent
     */
    const CHECK_ZONE_CAMPAIGN = 'campaign sending';

    /**
     * flag for emails that are check when a subscriber is added in a list
     */
    const CHECK_ZONE_LIST_SUBSCRIBE = 'list subscribe';

    /**
     * flag for emails that are check when a subscriber is imported in a list
     */
    const CHECK_ZONE_LIST_IMPORT = 'list import';

    /**
     * flag for emails that are check when a subscriber is exported from a list
     */
    const CHECK_ZONE_LIST_EXPORT = 'list export';

    /**
     * flag for emails that are check when transactional email is sent
     */
    const CHECK_ZONE_TRANSACTIONAL_EMAILS = 'transactional emails sending';

    /**
     * @var $file - the uploaded file for import
     */
    public $file;

    // store email => bool (whether is blacklisted or not)
    protected static $emailsStore = array();

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{email_blacklist}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }

        $rules = array(
            array('email', 'required', 'on' => 'insert, update'),
            array('email', 'length', 'max' => 150),
            array('email', '_validateEmail'),
            array('email', '_validateEmailUnique'),

            array('reason', 'safe'),
            array('email', 'safe', 'on' => 'search'),

            array('email, reason', 'unsafe', 'on' => 'import'),
            array('file', 'required', 'on' => 'import'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => 512000000, 'allowEmpty' => true),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'email_id'      => Yii::t('email_blacklist', 'Email'),
            'subscriber_id' => Yii::t('email_blacklist', 'Subscriber'),
            'email'         => Yii::t('email_blacklist', 'Email'),
            'reason'        => Yii::t('email_blacklist', 'Reason'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $criteria->compare('email', $this->email, true);
        $criteria->compare('reason', $this->reason, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder'  => array(
                    'email_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EmailBlacklist the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if ($this->getIsNewRecord()) {
            if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_NEW_BLACKLIST_RECORDS) {
                return false;
            }
            
            // since 1.3.9.3
            if (Yii::app()->options->get('system.email_blacklist.allow_new_records', 'yes') == 'no') {
                return false;
            }
        }
        
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        // since 1.3.5
        if (!empty($this->email)) {
            
            try {
                
                $criteria = new CDbCriteria();
                $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);

                if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
                    $criteria->addCondition('email = :e');
                    $criteria->params[':e'] = $this->email;
                } else {
                    if (StringHelper::isMd5($this->email)) {
                        $criteria->addCondition('(email = :e OR MD5(email) = :e)');
                        $criteria->params[':e'] = $this->email;
                    } else {
                        $criteria->addCondition('(email = :e OR email = :m)');
                        $criteria->params[':e'] = $this->email;
                        $criteria->params[':m'] = md5($this->email);
                    }   
                }
                
                ListSubscriber::model()->updateAll(array(
                    'status' => ListSubscriber::STATUS_BLACKLISTED
                ), $criteria);
                
            } catch (Exception $e) {
                
            }
        }
        
        parent::afterSave();
    }

    /**
     * @return bool
     */
    public function delete()
    {
        // when taken out of blacklist remove all the log records
        // NOTE: when a subscriber is deleted the column subscriber_id gets nulled so that we keep
        // the blacklist email for future additions.
        if (!empty($this->subscriber_id)) {
            try {
                $attributes = array('subscriber_id' => (int)$this->subscriber_id);
                CampaignDeliveryLog::model()->deleteAllByAttributes($attributes);
                CampaignDeliveryLogArchive::model()->deleteAllByAttributes($attributes);
                CampaignBounceLog::model()->deleteAllByAttributes($attributes);
            } catch (Exception $e) {

            }
        }

        // since 1.3.5.9 - mark back as confirmed
        try {
            
            $criteria = new CDbCriteria();
            $criteria->compare('status', ListSubscriber::STATUS_BLACKLISTED);

            if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
                $criteria->addCondition('email = :e');
                $criteria->params[':e'] = $this->email;
            } else {
                if (StringHelper::isMd5($this->email)) {
                    $criteria->addCondition('(email = :e OR MD5(email) = :e)');
                    $criteria->params[':e'] = $this->email;
                } else {
                    $criteria->addCondition('(email = :e OR email = :m)');
                    $criteria->params[':e'] = $this->email;
                    $criteria->params[':m'] = md5($this->email);
                }    
            }
            
            ListSubscriber::model()->updateAll(array(
                'status' => ListSubscriber::STATUS_CONFIRMED
            ), $criteria);
        
        } catch (Exception $e) {

        }

        // delete from store
        self::deleteFromStore($this->email);

        return parent::delete();
    }

    /**
     * @param $subscriber
     * @param null $reason
     * @return bool
     */
    public static function addToBlacklist($subscriber, $reason = null)
    {
        // since 1.4.5
        if (is_object($subscriber) && $subscriber instanceof ListSubscriber && !$subscriber->getIsConfirmed()) {
            return false;
        }
        
        // since 1.3.6.2
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_NEW_BLACKLIST_RECORDS) {
            if (is_object($subscriber) && $subscriber instanceof ListSubscriber && !empty($subscriber->subscriber_id)) {
                if (!$subscriber->getIsConfirmed()) {
                    return false;
                }
                $subscriber->saveStatus(ListSubscriber::STATUS_BLACKLISTED);
                return true;
            }
            return false;
        }
        
        // since 1.3.9.3
        if (Yii::app()->options->get('system.email_blacklist.allow_new_records', 'yes') == 'no') {
            if (is_object($subscriber) && $subscriber instanceof ListSubscriber && !empty($subscriber->subscriber_id)) {
                if (!$subscriber->getIsConfirmed()) {
                    return false;
                }
                $subscriber->saveStatus(ListSubscriber::STATUS_BLACKLISTED);
                return true;
            }
            return false;
        }
        
        $email = $subscriber_id = null;

        if (is_object($subscriber) && $subscriber instanceof ListSubscriber && !empty($subscriber->subscriber_id)) {
            $subscriber_id = $subscriber->subscriber_id;
            $email         = $subscriber->email;
        } elseif (is_string($subscriber)) {
            $email = $subscriber;
        } else {
            return false;
        }

        if ($data = self::getFromStore($email)) {
            return $data['blacklisted'];
        }

        $exists = self::model()->findByAttributes(array('email' => $email));
        if (!empty($exists)) {
            self::addToStore($email, array(
                'blacklisted' => true,
                'reason'      => $exists->reason,
            ));
	        if (
		        is_object($subscriber) && 
		        $subscriber instanceof ListSubscriber && 
		        !empty($subscriber->subscriber_id) && 
		        $subscriber->getIsConfirmed()
	        ) {
		        $subscriber->saveStatus(ListSubscriber::STATUS_BLACKLISTED);
	        }
            return true;
        }

        // since 1.3.5.9
        $customer = null;
        try {
            if (Yii::app()->hasComponent('customer') && Yii::app()->customer->getId() > 0) {
                $customer = Yii::app()->customer->getModel();
            }
            if (empty($customer) && !empty($subscriber) && !empty($subscriber->list)) {
                $customer = $subscriber->list->customer;
            }
        } catch (Exception $e) {
            $customer = null;
        }
        //

        // since 1.3.5.9
        Yii::app()->hooks->doAction('email_blacklist_before_add_email_to_blacklist', $collection = new CAttributeCollection(array(
            'email'    => $email,
            'customer' => $customer,
            'continue' => true,
        )));
        if (!$collection->continue) {
            Yii::app()->hooks->doAction('email_blacklist_after_add_email_to_blacklist', new CAttributeCollection(array(
                'email'    => $email,
                'saved'    => false,
                'customer' => $customer,
            )));
            return false;
        }
        //

        $saved = false;
        try {
            $model = new self();
            $model->email         = $email;
            $model->subscriber_id = $subscriber_id;
            $model->reason        = $reason;
            $saved = $model->save(false);
        } catch (Exception $e) {}

        if ($saved) {
            self::addToStore($email, array(
                'blacklisted' => true,
                'reason'      => $reason
            ));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('email_blacklist_after_add_email_to_blacklist', new CAttributeCollection(array(
            'email'    => $email,
            'saved'    => $saved,
            'customer' => $customer,
        )));

        return $saved;
    }

    /**
     * EmailBlacklist::isBlacklisted
     *
     * @param string $email
     * @param ListSubscriber $subscriber
     * @param Customer $customer
     * @param array $params
     * @return mixed
     * 
     * Return boolean false means the email is not blacklisted, anything else means the email is blacklisted.
     * Please keep in mind that since 1.3.6.2 if false is not returned, then a EmailBlacklistCheckInfo object will be returned
     * 
     * @since 1.3.5.9 added $customer
     * @since 1.3.7.3 added $params
     **/
    public static function isBlacklisted($email, ListSubscriber $subscriber = null, Customer $customer = null, array $params = array())
    {
        // since 1.3.6.2
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_SUBSCRIBER_BLACKLIST_CHECK) {
            return false;
        }
        
        if (Yii::app()->options->get('system.email_blacklist.local_check', 'yes') == 'no') {
            return false;
        }

        if ($data = self::getFromStore($email)) {
            return !empty($data['blacklisted']) ? new EmailBlacklistCheckInfo($data) : false;
        }

        // since 1.3.5.4
        static $regularExpressions;
        if ($regularExpressions === null) {
            $regularExpressions = explode("\n", Yii::app()->options->get('system.email_blacklist.regular_expressions'));
            $regularExpressions = (array)Yii::app()->hooks->applyFilters('email_blacklist_regular_expressions', $regularExpressions);
            $regularExpressions = array_unique(array_map('trim', $regularExpressions));
            foreach ($regularExpressions as $index => $expr) {
                if (empty($expr)) {
                    unset($regularExpressions[$index]);
                }
            }
        }
        if (!empty($regularExpressions) && is_array($regularExpressions)) {
            foreach ($regularExpressions as $regex) {
                if (@preg_match($regex, $email)) {
                    self::addToStore($email, $blCheckInfo = array(
                        'email'       => $email,
                        'blacklisted' => true,
                        'reason'      => Yii::t('email_blacklist', 'Matched regex: {regex}', array('{regex}' => CHtml::encode($regex))),
                    ));
                    self::addToBlacklist($email, $blCheckInfo['reason']);
                    return new EmailBlacklistCheckInfo($blCheckInfo);
                }
            }
        }
        // end 1.3.5.4 additions
        
        // 1.3.6.7
        if (!FilterVarHelper::email($email)) {
            self::addToStore($email, $blCheckInfo = array(
                'email'       => $email,
                'blacklisted' => true,
                'reason'      => Yii::t('email_blacklist', 'Invalid email address format!'),
            ));
            self::addToBlacklist($email, $blCheckInfo['reason']);
            return new EmailBlacklistCheckInfo($blCheckInfo);
        }
        //
        
        /**
         * AR was switched to Query Builder in this use case for performance reasons!
         * @since 1.4.4 - added md5 and sha1 checks - the $email will always be an email address
         */
        $command = Yii::app()->getDb()->createCommand();
        $command->select('email_id, reason')->from('{{email_blacklist}}');

        if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
            $command->where('email = :e', array(
                ':e' => $email,
            ));
        } else {
            if (StringHelper::isMd5($email)) {
                $command->where('(email = :e OR MD5(email) = :e)', array(
                    ':e' => $email,
                ));
            } else {
                $command->where('(email = :e OR email = :m)', array(
                    ':e' => $email,
                    ':m' => md5($email),
                ));
            }    
        }
        
        $blacklisted = $command->queryRow();

        if (!empty($blacklisted)) {
            self::addToStore($email, $blCheckInfo = array(
                'email'       => $email,
                'blacklisted' => true,
                'reason'      => !empty($blacklisted['reason']) ? (string)$blacklisted['reason'] : Yii::t('email_blacklist', 'Blacklisted'),
            ));
            unset($blacklisted);
            return new EmailBlacklistCheckInfo($blCheckInfo);
        }
        
        // since 1.3.5.9
        try {
            if (empty($customer) && Yii::app()->hasComponent('customer') && Yii::app()->customer->getId() > 0) {
                $customer = Yii::app()->customer->getModel();
            }
            if (empty($customer) && !empty($subscriber) && !empty($subscriber->list)) {
                $customer = $subscriber->list->customer;
            }
        } catch (Exception $e) {
            $customer = null;
        }
        //

        // return false or the reason for why blacklisted
        $hooks         = Yii::app()->hooks;
        $blacklisted   = $hooks->applyFilters('email_blacklist_is_email_blacklisted', false, $email, $subscriber, $customer, $params);
        $isBlacklisted = (is_object($blacklisted) && $blacklisted instanceof EmailBlacklistCheckInfo) ? $blacklisted->blacklisted : ($blacklisted !== false);
        $bReason       = $isBlacklisted ? (string)$blacklisted : null;
        
        if ($isBlacklisted) {
            self::addToBlacklist($email, $bReason);
        }

        self::addToStore($email, $blCheckInfo = array(
            'email'       => $email,
            'blacklisted' => $isBlacklisted,
            'reason'      => $bReason
        ));
        
        if ($isBlacklisted) {
            return new EmailBlacklistCheckInfo($blCheckInfo);
        }
        
        // since 1.3.6.2
        if (!empty($customer) && $customer->getGroupOption('lists.can_use_own_blacklist', 'no') == 'yes') {
            
            if ($data = CustomerEmailBlacklist::getFromStore($customer->customer_id, $email)) {
                return !empty($data['blacklisted']) ? new EmailBlacklistCheckInfo($data) : false;
            }
            
            $command = Yii::app()->getDb()->createCommand();
            $command->select('email_id')
                    ->from('{{customer_email_blacklist}}')
                    ->where('customer_id = :cid', array(':cid' => $customer->customer_id));

            if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
                $command->andWhere('email = :e', array(
                    ':e' => $email,
                ));
            } else {
                if (StringHelper::isMd5($email)) {
                    $command->andWhere('(email = :e OR MD5(email) = :e)', array(
                        ':e' => $email,
                    ));
                } else {
                    $command->andWhere('(email = :e OR email = :m)', array(
                        ':e' => $email,
                        ':m' => md5($email),
                    ));
                }
            }
            
            $blacklisted = $command->queryRow();
            $blCheckInfo = array(
                'blacklisted' => false,
                'email'       => $email,
            );
            
            if (!empty($blacklisted)) {
                $blCheckInfo = array(
                    'email'             => $email,
                    'reason'            => 'Found in suppression list!',
                    'blacklisted'       => true,
                    'customerBlacklist' => true,
                );
            }
            
            CustomerEmailBlacklist::addToStore($customer->customer_id, $email, $blCheckInfo);
            unset($blacklisted);
            
            if (!empty($blCheckInfo['blacklisted'])) {
                return new EmailBlacklistCheckInfo($blCheckInfo);
            }
        }
        
        return false;
    }

    /**
     * @param $email
     * @return static
     */
    public function findByEmail($email)
    {
        $criteria = new CDbCriteria();

        if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
            $criteria->addCondition('email = :e');
            $criteria->params[':e'] = $email;
        } else {
            if (StringHelper::isMd5($email)) {
                $criteria->addCondition('(email = :e OR MD5(email) = :e)');
                $criteria->params[':e'] = $email;
            } else {
                $criteria->addCondition('(email = :e OR email = :m)');
                $criteria->params[':e'] = $email;
                $criteria->params[':m'] = md5($email);
            }    
        }

        return self::model()->find($criteria);
    }

    /**
     * @param $email
     * @return bool
     */
    public static function removeByEmail($email)
    {
        if (!($model = self::model()->findByEmail($email))) {
            return false;
        }
        return $model->delete();
    }

    /**
     * @param $email
     * @param array $storeData
     * @return bool
     */
    public static function addToStore($email, array $storeData = array())
    {
        if (!isset($storeData['blacklisted'])) {
            return false;
        }
        self::$emailsStore[$email] = $storeData;
        return true;
    }

    /**
     * @param $email
     * @return bool|mixed
     */
    public static function getFromStore($email)
    {
        return isset(self::$emailsStore[$email]) ? self::$emailsStore[$email] : false;
    }

    /**
     * @param $email
     * @return bool
     */
    public static function deleteFromStore($email)
    {
        if (isset(self::$emailsStore[$email])) {
            unset(self::$emailsStore[$email]);
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public static function getCheckZones()
    {
        return array(
            self::CHECK_ZONE_CAMPAIGN, 
            self::CHECK_ZONE_LIST_IMPORT, 
            self::CHECK_ZONE_LIST_SUBSCRIBE,
            self::CHECK_ZONE_LIST_EXPORT,
            self::CHECK_ZONE_TRANSACTIONAL_EMAILS,
        );
    }

    /**
     * @param $attribute
     * @param $params
     * @return bool|void
     */
    public function _validateEmailUnique($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('email_id != :i');
        $criteria->params[':i'] = (int)$this->email_id;
        
        if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') != 'yes') {
            $criteria->addCondition('email = :e');
            $criteria->params[':e'] = $this->$attribute;
        } else {
            if (StringHelper::isMd5($this->$attribute)) {
                $criteria->addCondition('(email = :e OR MD5(email) = :e)');
                $criteria->params[':e'] = $this->$attribute;
            } else {
                $criteria->addCondition('(email = :e OR email = :m)');
                $criteria->params[':e'] = $this->$attribute;
                $criteria->params[':m'] = md5($this->$attribute);
            }
        }
        
        $duplicate = self::model()->find($criteria);

        if (!empty($duplicate)) {
            $this->addError('email', Yii::t('email_blacklist', 'The email address {email} is already in your blacklist!', array(
                '{email}' => $this->$attribute
            )));
            return;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateEmail($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        if (empty($this->$attribute)) {
            return;
        }
        
        if (FilterVarHelper::email($this->$attribute)) {
            return;
        }
        
        if (Yii::app()->options->get('system.email_blacklist.allow_md5', 'no') == 'yes' && StringHelper::isMd5($this->$attribute)) {
            return;
        }
        
        $this->addError($attribute, Yii::t('email_blacklist', 'Please enter a valid email address!'));
    }
}
