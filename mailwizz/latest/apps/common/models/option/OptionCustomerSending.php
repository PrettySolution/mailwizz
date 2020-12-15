<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerSending
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
class OptionCustomerSending extends OptionBase
{
    const TIME_UNIT_MINUTE = 'minute';
    
    const TIME_UNIT_HOUR = 'hour';
    
    const TIME_UNIT_DAY = 'day';
    
    const TIME_UNIT_WEEK = 'week';
    
    const TIME_UNIT_MONTH = 'month';
    
    const TIME_UNIT_YEAR = 'year';
    
    // settings category
    protected $_categoryName = 'system.customer_sending';
    
    // how many emails the customer receive, -1 for unlimited
    public $quota = -1;
    
    // how many "time units" the sending email quota is available, -1 for unlimited
    public $quota_time_value = -1;
    
    // the time unit for sending quota
    public $quota_time_unit = 'month';
    
    // whether to wait for the quota to expire when reaches sending limit
    public $quota_wait_expire = 'yes';
    
    // what action to take after the quota is over
    public $action_quota_reached;
    
    // if the action is to move the customer into a group, which group
    public $move_to_group_id;

    /**
     * @var string
     */
    public $quota_notify_enabled = 'no';
    
    /**
     * @var string
     */
    public $quota_notify_email_content = '';

    /**
     * @var int
     */
    public $quota_notify_percent = 95;

    /**
     * @var int
     */
    public $hourly_quota = 0;
    
    public function rules()
    {
        $rules = array(
            array('quota, quota_time_value, quota_time_unit, quota_wait_expire', 'required'),
            array('quota, quota_time_value', 'numerical', 'integerOnly' => true, 'min' => -1),
            array('quota_time_unit', 'in', 'range' => array_keys($this->getTimeUnits())),
            array('action_quota_reached', 'in', 'range' => array_keys($this->getActionsQuotaReached())),
            array('quota_wait_expire', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('move_to_group_id', 'numerical', 'integerOnly' => true),
            array('move_to_group_id', 'exist', 'className' => 'CustomerGroup', 'attributeName' => 'group_id'),
            
            array('quota_notify_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('quota_notify_email_content', 'safe'),
            array('quota_notify_percent', 'numerical', 'min' => 50, 'max' => 98),
            
            array('hourly_quota', 'numerical', 'integerOnly' => true, 'min' => 0),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

    protected function beforeValidate()
    {
        if ($this->action_quota_reached == 'move-in-group' && empty($this->move_to_group_id)) {
            $this->move_to_group_id = -1; // not empty but still trigger validation
        }
        
        if ($this->action_quota_reached != 'move-in-group') {
            $this->move_to_group_id = '';
        }
        
        return parent::beforeValidate();
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'quota'                 => Yii::t('settings', 'Sending quota'),
            'quota_time_value'      => Yii::t('settings', 'Time value'),
            'quota_time_unit'       => Yii::t('settings', 'Time unit'),
            'quota_wait_expire'     => Yii::t('settings', 'Wait for quota to expire'),
            'action_quota_reached'  => Yii::t('settings', 'Action when quota reached'),
            'move_to_group_id'      => Yii::t('settings', 'Customer group'),
            
            'quota_notify_enabled'       => Yii::t('settings', 'Enable quota notifications'),
            'quota_notify_email_content' => Yii::t('settings', 'Email content'),
            'quota_notify_percent'       => Yii::t('settings', 'Sending percent notification'),
            
            'hourly_quota' => Yii::t('settings', 'Hourly quota'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'quota'                 => '',
            'quota_time_value'      => '',
            'quota_time_unit'       => '',
            'action_quota_reached'  => '',
            'move_to_group_id'      => '',
            'hourly_quota'          => 0,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'quota'                 => Yii::t('settings', 'How many emails the customers are allowed to send for the specified "time value", set to -1 for unlimited'),
            'quota_time_value'      => Yii::t('settings', 'How many "time units" the quota is available if not consumed, set to -1 for unlimited'),
            'quota_time_unit'       => Yii::t('settings', 'The time unit after which customers with remaining emails are denied sending'),
            'quota_wait_expire'     => Yii::t('settings', 'Whether to wait for the quota to expire when the sending quota has been reached'),
            'action_quota_reached'  => Yii::t('settings', 'What action to take when the sending quota is reached'),
            'move_to_group_id'      => Yii::t('settings', 'Move the customer into this group after the sending quota is reached'),
            
            'quota_notify_enabled'       => Yii::t('settings', 'Whether to enable the quota notifications so that customer get notified once they are close to reaching the sending quota'),
            'quota_notify_email_content' => Yii::t('settings', 'The email template for when the sending quota notifications are sent'),
            'quota_notify_percent'       => Yii::t('settings', 'The percent the sending quota has to be over in order to send the notification'),
            
            'hourly_quota' => Yii::t('settings', 'Maximum number of emails the customer is allowed to send in one hour. This is substracted from the overall sending quota'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function getActionsQuotaReached()
    {
        return array(
            ''              => Yii::t('settings', 'Do nothing, customer will not be able to send more emails'),
            'reset'         => Yii::t('settings', 'Reset the counters for a fresh start'),
            'move-in-group' => Yii::t('settings', 'Move customer into a specific group'),
        );
    }
    
    public function getTimeUnits()
    {
        return array(
            self::TIME_UNIT_MINUTE=> ucfirst(Yii::t('app', self::TIME_UNIT_MINUTE)),
            self::TIME_UNIT_HOUR  => ucfirst(Yii::t('app', self::TIME_UNIT_HOUR)),
            self::TIME_UNIT_DAY   => ucfirst(Yii::t('app', self::TIME_UNIT_DAY)),
            self::TIME_UNIT_WEEK  => ucfirst(Yii::t('app', self::TIME_UNIT_WEEK)),
            self::TIME_UNIT_MONTH => ucfirst(Yii::t('app', self::TIME_UNIT_MONTH)),
            self::TIME_UNIT_YEAR  => ucfirst(Yii::t('app', self::TIME_UNIT_YEAR)),
        );
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
            $options[$group->group_id] = $group->name;
        }
        
        return $options;
    }
}
