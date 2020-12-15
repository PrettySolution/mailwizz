<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSplitTool
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 */
 
class ListSplitTool extends FormModel
{
    public $customer_id  = 0;
    public $list_id      = 0;
    public $sublists     = 2;

    public $count          = 0;
    public $limit          = 500;
    public $page           = 0;
    public $per_list       = 0;
    public $progress_text  = '';
    public $percentage     = 0;
    public $finished       = 0;
    
    private $_list;
    
    public function rules()
    {
        return array(
            array('list_id, sublists, limit', 'required'),
            array('list_id, sublists, limit, count, page, per_list, finished', 'numerical', 'integerOnly' => true),
            array('sublists', 'numerical', 'min' => 2, 'max' => 100),
            array('limit', 'numerical', 'max' => 1000),
            array('percentage', 'numerical'),
            array('progress_text', 'safe'),
            
            array('customer_id', 'unsafe'),
        );
    }
    
    public function attributeLabels()
    {
        return array(
            'list_id'   => Yii::t('lists', 'List'),
            'sublists'  => Yii::t('lists', 'Number of sublists'),
            'limit'     => Yii::t('lists', 'How many subscribers to move at once'),
        );
    }
    
    public function attributeHelpTexts()
    {
        return array();
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
    
    public function getLimitOptions()
    {
        return array(
            100  => 100,
            300  => 300,
            500  => 500,
            1000 => 1000,
        );
    }
    
    public function getList()
    {
        if ($this->_list !== null) {
            return $this->_list;
        }
        if (empty($this->list_id) || empty($this->customer_id)) {
            return false;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        
        return $this->_list = Lists::model()->find($criteria);
    }
}