<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCronProcessDeliveryBounce
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OptionCronProcessDeliveryBounce extends FormModel
{
    private $_categoryName = 'system.cron.process_delivery_bounce';
    
    public $memory_limit;
    
    // how many logs to process at once
    public $process_at_once = 100;
    
    // how many fatal errors a subscriber can have till blacklisted
    public $max_fatal_errors = 1;
    
    // how many soft errors a subscriber can have till blacklisted
    public $max_soft_errors = 5;
    
    // how many hard bounces a subscriber can have till blacklisted
    public $max_hard_bounce = 1;
    
    // how many soft bounces a subscriber can have till blacklisted
    public $max_soft_bounce = 5;
    
    // purge delivery server logs older than this amount of days
    public $delivery_servers_usage_logs_removal_days = 90;

    public function rules()
    {
        $rules = array(
            array('process_at_once, max_fatal_errors, max_soft_errors, max_hard_bounce, max_soft_bounce, delivery_servers_usage_logs_removal_days', 'required'),
            array('memory_limit', 'in', 'range' => array_keys($this->getMemoryLimitOptions())),
            array('process_at_once, max_fatal_errors, max_soft_errors, max_hard_bounce, max_soft_bounce', 'numerical', 'integerOnly' => true),
            array('process_at_once', 'numerical', 'min' => 50, 'max' => 10000),
            array('max_fatal_errors, max_hard_bounce', 'numerical', 'min' => 1, 'max' => 10000),
            array('max_soft_errors, max_soft_bounce', 'numerical', 'min' => 1, 'max' => 10000),
            array('delivery_servers_usage_logs_removal_days', 'numerical', 'min' => 1, 'max' => 10000),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'memory_limit'      => Yii::t('settings', 'Memory limit'),
            'process_at_once'   => Yii::t('settings', 'Process at once'),
            'max_fatal_errors'  => Yii::t('settings', 'Max. fatal errors'),
            'max_hard_bounce'   => Yii::t('settings', 'Max. hard bounce'),
            'max_soft_errors'   => Yii::t('settings', 'Max. soft errors'),
            'max_soft_bounce'   => Yii::t('settings', 'Max. soft bounce'),
            
            'delivery_servers_usage_logs_removal_days' => Yii::t('settings', 'Delivery servers logs removal days'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'memory_limit'      => null,
            'process_at_once'   => null,
            'max_fatal_errors'  => null,
            'max_hard_bounce'   => null,
            'max_soft_errors'   => null,
            'max_soft_bounce'   => null,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'memory_limit'      => Yii::t('settings', 'The maximum memory amount the cron process is allowed to use while processing one batch of logs.'),
            'process_at_once'   => Yii::t('settings', 'How many logs to process at once. Please note that this number will be 4 times higher on the server.'),
            'max_fatal_errors'  => Yii::t('settings', 'Maximum allowed number of fatal errors a subscriber is allowed to have while we try to deliver the email.'),
            'max_hard_bounce'   => Yii::t('settings', 'Maximum allowed number of hard bounces a subscriber is allowed to have after we delivered the email.'),
            'max_soft_errors'   => Yii::t('settings', 'Maximum allowed number of soft errors a subscriber is allowed to have while we try to deliver the email.'),
            'max_soft_bounce'   => Yii::t('settings', 'Maximum allowed number of soft bounces a subscriber is allowed to have after we delivered the email.'),
            
            'delivery_servers_usage_logs_removal_days' => Yii::t('settings', 'The number of days to keep the delivery server logs in the system.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function afterConstruct()
    {
        parent::afterConstruct();
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            $this->$attributeName = Yii::app()->options->get($this->_categoryName . '.' . $attributeName, $this->$attributeName);
        }
    }
    
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            Yii::app()->options->set($this->_categoryName . '.' . $attributeName, $attributeValue);
        }
        
        return true;
    }
    
    public function getMemoryLimitOptions()
    {
        return array(
            ''      => Yii::t('settings', 'System default'),
            '64M'   => Yii::t('settings', '{n} Megabytes', 64),
            '128M'  => Yii::t('settings', '{n} Megabytes', 128),
            '256M'  => Yii::t('settings', '{n} Megabytes', 256),
            '512M'  => Yii::t('settings', '{n} Megabytes', 512),
            '768M'  => Yii::t('settings', '{n} Megabytes', 768),
            '1G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 1),
            '2G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 2),
            '3G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 3),
            '4G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 4),
            '5G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 5),
        );
    }
}
