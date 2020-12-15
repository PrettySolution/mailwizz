<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerEmailBlacklist
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2 
 */

/**
 * This is the model class for table "customer_suppression_list".
 *
 * The followings are the available columns in table 'customer_suppression_list':
 * @property integer $email_id
 * @property integer $customer_id
 * @property string $email
 * @property string $reason
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerEmailBlacklist extends ActiveRecord
{
    /**
     * @var $file uploaded file containing the suppressed emails
     */
    public $file;

    // store email => bool (whether is blacklisted or not)
    protected static $emailsStore = array();
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_email_blacklist}}';
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
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
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
		$criteria->compare('customer_id', (int)$this->customer_id);
		$criteria->compare('email', $this->email, true);

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
	 * @return CustomerEmailBlacklist the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        if ($this->getIsNewRecord() && MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_CUSTOMER_NEW_BLACKLIST_RECORDS) {
            return false;
        }
        
        if (empty($this->email_uid)) {
            $this->email_uid = $this->generateUid();
        }

        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        if (!empty($this->email)) {
            
            try {
                
                $criteria = new CDbCriteria();
                $criteria->addInCondition('list_id', $this->customer->getAllListsIdsNotMerged());
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
     * @throws CDbException
     */
    public function delete()
    {
        try {
            
            $criteria = new CDbCriteria();
            $criteria->addInCondition('list_id', $this->customer->getAllListsIdsNotMerged());
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
        self::deleteFromStore($this->customer_id, $this->email);

        return parent::delete();
    }

    /**
     * @param $email_uid
     * @return static
     */
    public function findByUid($email_uid)
    {
        return self::model()->findByAttributes(array(
            'email_uid' => $email_uid,
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
     * @throws CDbException
     */
    public static function removeByEmail($email)
    {
        if (!($model = self::model()->findByEmail($email))) {
            return false;
        }
        return $model->delete();
    }

    /**
     * @param $customerId
     * @param $email
     * @param array $storeData
     * @return bool
     */
    public static function addToStore($customerId, $email, array $storeData = array())
    {
        if (!isset($storeData['blacklisted'])) {
            return false;
        }
        if (!isset(self::$emailsStore[$customerId])) {
            self::$emailsStore[$customerId] = array();
        }
        self::$emailsStore[$customerId][$email] = $storeData;
        return true;
    }

    /**
     * @param $customerId
     * @param $email
     * @return bool
     */
    public static function getFromStore($customerId, $email)
    {
        if (!isset(self::$emailsStore[$customerId])) {
            self::$emailsStore[$customerId] = array();
        }
        return isset(self::$emailsStore[$customerId][$email]) ? self::$emailsStore[$customerId][$email] : false;
    }

    /**
     * @param $customerId
     * @param $email
     * @return bool
     */
    public static function deleteFromStore($customerId, $email)
    {
        if (!isset(self::$emailsStore[$customerId])) {
            self::$emailsStore[$customerId] = array();
        }
        if (isset(self::$emailsStore[$customerId][$email])) {
            unset(self::$emailsStore[$customerId][$email]);
            return true;
        }
        return false;
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
        $criteria->compare('customer_id', (int)$this->customer_id);
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
            return false;
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

    /**
     * @return string
     */
    public function getDisplayEmail()
    {
        if (Yii::app()->apps->isAppName('backend')) {
            return $this->email;
        }

        if ($this->isNewRecord || empty($this->customer_id)) {
            return $this->email;
        }

        $customer = $this->customer;
        if ($customer->getGroupOption('common.mask_email_addresses', 'no') == 'yes') {
            return StringHelper::maskEmailAddress($this->email);
        }

        return $this->email;
    }
}
