<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerSuppressionListEmail
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4 
 */

/**
 * This is the model class for table "{{customer_suppression_list_email}}".
 *
 * The followings are the available columns in table '{{customer_suppression_list_email}}':
 * @property integer $email_id
 * @property integer $list_id
 * @property string $email
 * @property string $email_md5
 *
 * The followings are the available model relations:
 * @property CustomerSuppressionList $list
 */
class CustomerSuppressionListEmail extends ActiveRecord
{
    /**
     * @var $file uploaded file containing the suppressed emails
     */
    public $file;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_suppression_list_email}}';
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
            
            array('email', 'unsafe', 'on' => 'import'),
            array('file', 'required', 'on' => 'import'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => 512000000, 'allowEmpty' => true),

            array('email', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'list' => array(self::BELONGS_TO, 'CustomerSuppressionList', 'list_id'),
		);

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
        $labels = array(
			'email_id'  => Yii::t('suppression_lists', 'Email'),
			'email_uid' => Yii::t('suppression_lists', 'Email'),
			'list_id'   => Yii::t('suppression_lists', 'List'),
			'email'     => Yii::t('suppression_lists', 'Email'),
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
        
		$criteria->compare('list_id', (int)$this->list_id);
        $criteria->order = 'email_id DESC';

        if (!empty($this->email)) {
            $criteria->addCondition('(email LIKE :e OR email_md5 LIKE :m)');
            $criteria->params[':e'] = '%' . $this->email . '%';
            $criteria->params[':m'] = '%' . $this->email . '%';
        }
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    'email_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

    /**
     * @inheritdoc
     */
	protected function beforeSave()
    {
        if (!empty($this->email) && StringHelper::isMd5($this->email)) {
            $this->email_md5 = $this->email;
            $this->email = null;
        }
        
        if (!empty($this->email) && (empty($this->email_md5) || !StringHelper::isMd5($this->email_md5))) {
            $this->email_md5 = StringHelper::md5Once($this->email);
        }
        
        if (empty($this->email) && empty($this->email_md5)) {
            return false;
        }
        
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        if (empty($this->email) && !empty($this->email_md5)) {
            $this->email = $this->email_md5;
        } 
        parent::afterFind();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerSuppressionListEmail the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return string
     */
	public function getDisplayEmail()
    {
        return !empty($this->email) ? $this->email : $this->email_md5;
    }

    /**
     * @param ListSubscriber $subscriber
     * @param Campaign $campaign
     * @return bool
     * @throws CException
     */
    public static function isSubscriberListedByCampaign(ListSubscriber $subscriber, Campaign $campaign)
    {
        if ($campaign->isNewRecord || $subscriber->isNewRecord) {
            return false;
        }
        
        static $suppressionLists = array();
        if (!array_key_exists($campaign->campaign_id, $suppressionLists)) {
            $lists = CustomerSuppressionListToCampaign::model()->findAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
            ));
            foreach ($lists as $list) {
                $suppressionLists[$campaign->campaign_id][] = (int)$list->list_id;
            }
        }
        
        if (empty($suppressionLists[$campaign->campaign_id])) {
            return false;
        }

        $lists = $suppressionLists[$campaign->campaign_id];
        
        // 1.5.0
        $sql = "
        SELECT COUNT(*) FROM (
            SELECT email_id, list_id FROM `{{customer_suppression_list_email}}` WHERE email IS NOT NULL AND email = :e 
            UNION 
            SELECT email_id, list_id FROM `{{customer_suppression_list_email}}` WHERE email_md5 IS NOT NULL AND email_md5 = :m
        ) t1 
        WHERE email_id != 0 AND list_id IN(" . implode(',', $lists) . ") LIMIT 1 
        ";

        $count = (int)Yii::app()->getDb()->createCommand($sql)->queryScalar(array(
            ':e' => $subscriber->email,
            ':m' => StringHelper::md5Once($subscriber->email),
        ));
        
        return $count > 0;
    }

    /**
     * @param $attribute
     * @param $params
     * @throws CException
     */
    public function _validateEmailUnique($attribute, $params)
    {
        if ($this->hasErrors() || empty($this->$attribute)) {
            return;
        }
        
        // 1.5.0
        $sql = "
        SELECT COUNT(*) FROM (
            SELECT email_id, list_id FROM `{{customer_suppression_list_email}}` WHERE email IS NOT NULL AND email = :e 
            UNION 
            SELECT email_id, list_id FROM `{{customer_suppression_list_email}}` WHERE email_md5 IS NOT NULL AND email_md5 = :m
        ) t1 
        WHERE email_id != :eid AND list_id = :lid LIMIT 1
        ";

        $count = (int)Yii::app()->getDb()->createCommand($sql)->queryScalar(array(
            ':e'   => $this->$attribute,
            ':m'   => StringHelper::md5Once($this->$attribute),
            ':lid' => (int)$this->list->list_id,
            ':eid' => (int)$this->email_id,
        ));
        
        if ($count > 0) {
            $this->addError('email', Yii::t('suppression_lists', 'The email address {email} is already in your suppression list!', array(
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

        if (StringHelper::isMd5($this->$attribute)) {
            return;
        }

        $this->addError($attribute, Yii::t('suppression_lists', 'Please enter a valid email address!'));
    }
}
