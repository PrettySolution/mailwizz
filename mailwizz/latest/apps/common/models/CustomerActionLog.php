<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerActionLog
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "customer_action_log".
 *
 * The followings are the available columns in table 'customer_action_log':
 * @property string $log_id
 * @property integer $customer_id
 * @property string $category
 * @property integer $reference_id
 * @property integer $reference_relation_id
 * @property string $message
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerActionLog extends ActiveRecord
{
    const CATEGORY_LISTS_CREATED = 'lists.created';
    
    const CATEGORY_LISTS_UPDATED = 'lists.updated';
    
    const CATEGORY_LISTS_DELETED = 'lists.deleted';
    
    const CATEGORY_LISTS_IMPORT_START = 'lists.import.start';
    
    const CATEGORY_LISTS_IMPORT_END = 'lists.import.end';
    
    const CATEGORY_LISTS_EXPORT_START = 'lists.export.start';
    
    const CATEGORY_LISTS_EXPORT_END = 'lists.export.end';
    
    const CATEGORY_LISTS_SEGMENT_CREATED = 'lists.segment.created';
    
    const CATEGORY_LISTS_SEGMENT_UPDATED = 'lists.segment.updated';
    
    const CATEGORY_LISTS_SEGMENT_DELETED = 'lists.segment.deleted';
    
    const CATEGORY_LISTS_CAMPAIGNS_CREATED = 'lists.campaigns.created';
    
    const CATEGORY_LISTS_CAMPAIGNS_SCHEDULED = 'lists.campaigns.scheduled';
    
    const CATEGORY_LISTS_CAMPAIGNS_SENT = 'lists.campaigns.sent';
    
    const CATEGORY_LISTS_CAMPAIGNS_UPDATED = 'lists.campaigns.updated';
    
    const CATEGORY_LISTS_CAMPAIGNS_DELETED = 'lists.campaigns.deleted';
    
    const CATEGORY_LISTS_SEGMENT_CAMPAIGNS_CREATED = 'lists.segment.campaigns.created';
    
    const CATEGORY_LISTS_SEGMENT_CAMPAIGNS_SCHEDULED = 'lists.segment.campaigns.scheduled';
    
    const CATEGORY_LISTS_SEGMENT_CAMPAIGNS_SENT = 'lists.segment.campaigns.sent';
    
    const CATEGORY_LISTS_SEGMENT_CAMPAIGNS_UPDATED = 'lists.segment.campaigns.updated';
    
    const CATEGORY_LISTS_SEGMENT_CAMPAIGNS_DELETED = 'lists.segment.campaigns.deleted';
    
    const CATEGORY_LISTS_SUBSCRIBERS_CREATED = 'lists.subscribers.created';
    
    const CATEGORY_LISTS_SUBSCRIBERS_UPDATED = 'lists.subscribers.updated';
    
    const CATEGORY_LISTS_SUBSCRIBERS_DELETED = 'lists.subscribers.deleted';
    
    const CATEGORY_LISTS_SUBSCRIBERS_UNSUBSCRIBED = 'lists.subscribers.unsubscribed';

    const CATEGORY_SURVEYS_CREATED = 'surveys.created';

    const CATEGORY_SURVEYS_UPDATED = 'surveys.updated';

    const CATEGORY_SURVEYS_DELETED = 'surveys.deleted';

    const CATEGORY_SURVEYS_RESPONDERS_CREATED = 'surveys.responders.created';

    const CATEGORY_SURVEYS_RESPONDERS_UPDATED = 'surveys.responders.updated';

    const CATEGORY_SURVEYS_RESPONDERS_DELETED = 'surveys.responders.deleted';

    const CATEGORY_SURVEYS_SEGMENT_CREATED = 'surveys.segment.created';

    const CATEGORY_SURVEYS_SEGMENT_UPDATED = 'surveys.segment.updated';

    const CATEGORY_SURVEYS_SEGMENT_DELETED = 'surveys.segment.deleted';



    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_action_log}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array();
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
            'log_id'        => Yii::t('customers', 'Log'),
            'customer_id'   => Yii::t('customers', 'Customer'),
            'category'      => Yii::t('customers', 'Category'),
            'reference_id'  => Yii::t('customers', 'Reference'),
            'message'       => Yii::t('customers', 'Message')
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
        
        if ($this->customer_id) {
            $criteria->compare('customer_id', (int)$this->customer_id);    
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'log_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerNotificationLog the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @return string
	 */
	public function getActionFromCategory()
	{
		$parts = explode('.', (string)$this->category);
		if (empty($parts)) {
			return '';
		}
		return end($parts);
	}

	/**
	 * @return bool
	 */
    public function getIsCreated()
    {
        return $this->getActionFromCategory() === 'created';
    }

	/**
	 * @return bool
	 */
    public function getIsUpdated()
    {
        return $this->getActionFromCategory() === 'updated';
    }

	/**
	 * @return bool
	 */
    public function getIsDeleted()
    {
        return $this->getActionFromCategory() === 'deleted';
    }

	/**
	 * @return string
	 */
    public function getCssClass()
    {
        $class = 'info';
        if ($this->getIsCreated()) {
            $class = 'success';
        } elseif ($this->getIsDeleted()) {
            $class = 'danger';
        }
        return $class;
    }
    
}
