<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserLogin
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class CustomerMassEmail extends FormModel
{
    const STORAGE_ALIAS = 'common.runtime.customer-mass-email';
    
    public $subject;
    
    public $message;
    
    public $groups = array();
    
    public $message_id;
    
    public $batch_size = 300;
    
    public $page = 1;
    
    public $total = 0;
    
    public $customers = array();

    public $processed = 0;
    
    public $percentage = 0;
    
    public $progress_text;
    
    public $finished = false;
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('subject, message', 'required'),
            array('page, total, processed, percentage, batch_size', 'numerical', 'integerOnly' => true),
            array('groups, message_id', 'safe'),
        );
        
        return CMap::mergeArray(parent::rules(), $rules);
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'subject'    => Yii::t('customers', 'Subject'),
            'message'    => Yii::t('customers', 'Message'),
            'groups'     => Yii::t('customers', 'Groups'),
            'batch_size' => Yii::t('customers', 'Batch size'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }
    
    protected function afterValidate()
    {
        parent::afterValidate();
        if ($this->hasErrors()) {
            return;
        }
        $storage = Yii::getPathOfAlias(self::STORAGE_ALIAS);
        if ((!file_exists($storage) || !is_dir($storage)) && !mkdir($storage, 0777)) {
            $this->addError('message', Yii::t('customers', 'Unable to create the storage directory {dir}', array('{dir}' => $storage)));
            return;
        }
        $this->message_id = StringHelper::random(20);
        if (!file_put_contents($storage . '/' . $this->message_id, $this->message)) {
            $this->addError('message', Yii::t('customers', 'Unable to write in the storage directory {dir}', array('{dir}' => $storage)));
            return;
        }
        $this->message = null;
    }
    
    public function getGroupsList()
    {
        static $options;
        if ($options !== null) {
            return $options;
        }
        $options = array();
        $groups  = CustomerGroup::model()->findAll();
        foreach ($groups as $group) {
            $count = Customer::model()->countByAttributes(array('group_id' => $group->group_id, 'status' => Customer::STATUS_ACTIVE));
            if ($count == 0) {
                continue;
            }
            $options[$group->group_id] = Yii::t('customers', '{group} ({count} customers)', array('{group}' => $group->name, '{count}' => $count));
        }
        return $options;
    }
    
    public function loadCustomers()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('status', Customer::STATUS_ACTIVE);
        if (!empty($this->groups) && is_array($this->groups)) {
            $this->groups = array_map('intval', array_values($this->groups));
            $criteria->addInCondition('group_id', $this->groups);
        }
        $this->total = Customer::model()->count($criteria);
        if (empty($this->total)) {
            return;
        }
        $criteria->limit  = $this->batch_size;
        $criteria->offset = ($this->page - 1) * $this->batch_size;
        $this->customers  = Customer::model()->findAll($criteria); 
    }
    
    public function getFormattedAttributes()
    {
        $out = array();
        foreach ($this->getAttributes() as $key => $value) {
            $out[sprintf('%s[%s]', $this->modelName, $key)] = $value;
        }
        return $out;
    }
    
    public function getBatchSizes()
    {
        return array(
            100 => 100,
            300 => 300,
            500 => 500,
            1000 => 1000,
        );
    }
}
