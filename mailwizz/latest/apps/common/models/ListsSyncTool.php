<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListsSyncTool
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */
 
class ListsSyncTool extends FormModel
{
    const MISSING_SUBSCRIBER_ACTION_NONE              = '';
    const MISSING_SUBSCRIBER_ACTION_CREATE_SECONDARY  = 'create-secondary';
    
    const DISTINCT_STATUS_ACTION_NONE               = '';
    const DISTINCT_STATUS_ACTION_UPDATE_PRIMARY     = 'update-primary';
    const DISTINCT_STATUS_ACTION_UPDATE_SECONDARY   = 'update-secondary';
    const DISTINCT_STATUS_ACTION_DELETE_SECONDARY   = 'delete-secondary';
    
    const DUPLICATE_SUBSCRIBER_ACTION_NONE              = '';
    const DUPLICATE_SUBSCRIBER_ACTION_DELETE_SECONDARY  = 'delete-secondary';
    
    protected $_primaryList;
    protected $_secondaryList;
    
    public $customer_id                  = 0;
    public $primary_list_id              = 0;
    public $secondary_list_id            = 0;
    public $missing_subscribers_action   = '';
    public $duplicate_subscribers_action = '';
    public $distinct_status_action       = '';
    
    public $count               = 0;
    public $limit               = 100;
    public $offset              = 0;
    public $progress_text       = '';
    public $processed_total     = 0;
    public $processed_success   = 0;
    public $processed_error     = 0;
    public $percentage          = 0;
    public $finished            = 0;

    public function rules()
    {
        return array(
            array('primary_list_id, secondary_list_id', 'required'),
            array('primary_list_id, secondary_list_id', 'numerical', 'integerOnly' => true),
            array('missing_subscribers_action', 'in', 'range' => array_keys($this->getMissingSubscribersActions())),
            array('distinct_status_action', 'in', 'range' => array_keys($this->getDistinctStatusActions())),
            array('duplicate_subscribers_action', 'in', 'range' => array_keys($this->getDuplicateSubscribersActions())),

            array('count, limit, offset, processed_total, processed_success, processed_error, finished', 'numerical', 'integerOnly' => true),
            array('percentage', 'numerical'),
            array('progress_text', 'safe'),
            
            array('customer_id', 'unsafe'),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'primary_list_id'               => Yii::t('lists', 'Primary list'),
            'secondary_list_id'             => Yii::t('lists', 'Secondary list'),
            'missing_subscribers_action'    => Yii::t('lists', 'Action on missing subscribers'),
            'distinct_status_action'        => Yii::t('lists', 'Action when distinct subscriber status'),
            'duplicate_subscribers_action'  => Yii::t('lists', 'Action on duplicate subscribers'),
        );
    }
    
    public function attributeHelpTexts()
    {
        return array(
            'primary_list_id'               => Yii::t('lists', 'Primary list'),
            'secondary_list_id'             => Yii::t('lists', 'Secondary list'),
            'missing_subscribers_action'    => Yii::t('lists', 'What actions to take when a subscriber is found in the primary list but not in the secondary list'),
            'distinct_status_action'        => Yii::t('lists', 'What actions to take when same subscriber from primary list has a distinct status in the secondary list'),
            'duplicate_subscribers_action'  => Yii::t('lists', 'What actions to take when same subscriber is found in both lists'),
        );
    }
    
    public function getMissingSubscribersActions()
    {
        return array(
            self::MISSING_SUBSCRIBER_ACTION_NONE              => Yii::t('lists', 'Do nothing'),
            self::MISSING_SUBSCRIBER_ACTION_CREATE_SECONDARY  => Yii::t('lists', 'Create subscriber in secondary list'),
        );
    }
    
    public function getDistinctStatusActions()
    {
        return array(
            self::DISTINCT_STATUS_ACTION_NONE               => Yii::t('lists', 'Do nothing'),
            self::DISTINCT_STATUS_ACTION_UPDATE_PRIMARY     => Yii::t('lists', 'Update subscriber in primary list'), 
            self::DISTINCT_STATUS_ACTION_UPDATE_SECONDARY   => Yii::t('lists', 'Update subscriber in secondary list'),
            self::DISTINCT_STATUS_ACTION_DELETE_SECONDARY   => Yii::t('lists', 'Delete subscriber from secondary list'),
        );
    }
    
    public function getDuplicateSubscribersActions()
    {
        return array(
            self::DUPLICATE_SUBSCRIBER_ACTION_NONE              => Yii::t('lists', 'Do nothing'),
            self::DUPLICATE_SUBSCRIBER_ACTION_DELETE_SECONDARY  => Yii::t('lists', 'Delete subscriber from secondary list'),
        );
    }
    
    public function getPrimaryList()
    {
        if ($this->_primaryList !== null) {
            return $this->_primaryList;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->primary_list_id);
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        
        return $this->_primaryList = Lists::model()->find($criteria);
    }
    
    public function getSecondaryList()
    {
        if ($this->_secondaryList !== null) {
            return $this->_secondaryList;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->secondary_list_id);
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        
        return $this->_secondaryList = Lists::model()->find($criteria);
    }
    
    public function getAsDropDownOptionsByCustomerId()
    {
        $this->customer_id = (int)$this->customer_id;
        static $options = array();
        if (isset($options[$this->customer_id])) {
            return $options[$this->customer_id];
        }
        $options[$this->customer_id] = array();

	    $criteria = new CDbCriteria();
	    $criteria->select = 'list_id, name';
	    $criteria->compare('customer_id', $this->customer_id);
	    $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
	    $criteria->order = 'name ASC';
	    
        $models = Lists::model()->findAll($criteria);
        
        foreach ($models as $model) {
            $options[$this->customer_id][$model->list_id] = $model->name;
        }
        
        return $options[$this->customer_id];
    }
    
    public function getFormattedAttributes()
    {
        $out = array();
        foreach ($this->getAttributes() as $key => $value) {
            $out[sprintf('%s[%s]', $this->modelName, $key)] = $value;
        }
        return $out;
    }
}