<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerLists
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
class OptionCustomerLists extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_lists';
    
    // whether the customers are allowed to import
    public $can_import_subscribers = 'yes';
    
    // whether the customers are allowed to export
    public $can_export_subscribers = 'yes';
    
    // whether the customers are allowed to copy subscribers between the lists
    public $can_copy_subscribers = 'yes';
    
    // maximum number of lists a customer can have, -1 is unlimited
    public $max_lists = -1;
    
    // maximum number of subscribers, -1 is unlimited
    public $max_subscribers = -1 ;
    
    //maximum number of subscribers allowed into a list, -1 is unlimited
    public $max_subscribers_per_list = -1;
    
    //
    public $copy_subscribers_memory_limit;
    
    //
    public $copy_subscribers_at_once = 100;
    
    // can the customer delete his lists?
    public $can_delete_own_lists = 'yes';
    
    // can the customer delete his subscribers?
    public $can_delete_own_subscribers = 'yes';
    
    // can the customer segment lists?
    public $can_segment_lists = 'yes';
    
    // max number of segment conditions
    public $max_segment_conditions = 3;
    
    // max wait timeout for a segment to load
    public $max_segment_wait_timeout = 5;
    
    // whether is allowed to mark blacklisted emails as subscribed again
    public $can_mark_blacklisted_as_confirmed = 'no';

    // whether is allowed use own blacklist
    public $can_use_own_blacklist = 'no';

    // whether the customers are allowed to edit subscribers
    public $can_edit_own_subscribers = 'yes';
    
    public $subscriber_profile_update_optin_history = 'yes';
    
    public $can_create_list_from_filters = 'yes';
    
    public $show_7days_subscribers_activity_graph = 'yes';
    
    public $force_optin_process = '';
    
    public $force_optout_process = '';
    
    public function rules()
    {
        $rules = array(
            array('can_import_subscribers, can_export_subscribers, can_copy_subscribers, max_lists, max_subscribers, max_subscribers_per_list, copy_subscribers_at_once, can_delete_own_lists, can_delete_own_subscribers, can_segment_lists, max_segment_conditions, max_segment_wait_timeout, can_mark_blacklisted_as_confirmed, can_use_own_blacklist, can_edit_own_subscribers, subscriber_profile_update_optin_history, can_create_list_from_filters, show_7days_subscribers_activity_graph', 'required'),
            array('can_import_subscribers, can_export_subscribers, can_copy_subscribers, can_delete_own_lists, can_delete_own_subscribers, can_segment_lists, can_use_own_blacklist, can_edit_own_subscribers, subscriber_profile_update_optin_history, can_create_list_from_filters, show_7days_subscribers_activity_graph', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('max_lists, max_subscribers, max_subscribers_per_list', 'numerical', 'integerOnly' => true, 'min' => -1),
            array('copy_subscribers_memory_limit', 'in', 'range' => array_keys($this->getMemoryLimitOptions())),
            array('copy_subscribers_at_once', 'numerical', 'integerOnly' => true, 'min' => 50, 'max' => 10000),
            array('max_segment_conditions, max_segment_wait_timeout', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 60),
            array('can_mark_blacklisted_as_confirmed', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('force_optin_process', 'in', 'range' => array_keys($this->getOptInOutOptions())),
            array('force_optout_process', 'in', 'range' => array_keys($this->getOptInOutOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'can_import_subscribers'                  => Yii::t('settings', 'Can import subscribers'),
            'can_export_subscribers'                  => Yii::t('settings', 'Can export subscribers'),
            'can_copy_subscribers'                    => Yii::t('settings', 'Can copy subscribers'),
            'max_lists'                               => Yii::t('settings', 'Max. lists'),
            'max_subscribers'                         => Yii::t('settings', 'Max. subscribers'),
            'max_subscribers_per_list'                => Yii::t('settings', 'Max. subscribers per list'),
            'copy_subscribers_memory_limit'           => Yii::t('settings', 'Copy subscribers memory limit'),
            'copy_subscribers_at_once'                => Yii::t('settings', 'Copy subscribers at once'),
            'can_delete_own_lists'                    => Yii::t('settings', 'Can delete own lists'),
            'can_delete_own_subscribers'              => Yii::t('settings', 'Can delete own subscribers'),
            'can_segment_lists'                       => Yii::t('settings', 'Can segment lists'),
            'max_segment_conditions'                  => Yii::t('settings', 'Max. segment conditions'),
            'max_segment_wait_timeout'                => Yii::t('settings', 'Max. segment wait timeout'),
            'can_mark_blacklisted_as_confirmed'       => Yii::t('settings', 'Mark blacklisted as confirmed'),
            'can_use_own_blacklist'                   => Yii::t('settings', 'Use own blacklist'),
            'can_edit_own_subscribers'                => Yii::t('settings', 'Can edit own subscribers'),
            'subscriber_profile_update_optin_history' => Yii::t('settings', 'Subscriber profile update optin history'),
            'can_create_list_from_filters'            => Yii::t('settings', 'Can create list from filtered search results'),
            'show_7days_subscribers_activity_graph'   => Yii::t('settings', 'Show 7 days subscribers activity'),
            'force_optin_process'                     => Yii::t('settings', 'Force the OPT-IN process'),
            'force_optout_process'                    => Yii::t('settings', 'Force the OPT-OUT process'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'can_import_subscribers'                  => '',
            'can_export_subscribers'                  => '',
            'can_copy_subscribers'                    => '',
            'max_lists'                               => '',
            'max_subscribers'                         => '',
            'max_subscribers_per_list'                => '',
            'copy_subscribers_memory_limit'           => '',
            'copy_subscribers_at_once'                => '',
            'max_segment_conditions'                  => '',
            'max_segment_wait_timeout'                => '',
            'can_edit_own_subscribers'                => '',
            'subscriber_profile_update_optin_history' => '',
            'force_optin_process'                     => '',
            'force_optout_process'                    => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'can_import_subscribers'                  => Yii::t('settings', 'Whether customers are allowed to import subscribers'),
            'can_export_subscribers'                  => Yii::t('settings', 'Whether customers are allowed to export subscribers'),
            'can_copy_subscribers'                    => Yii::t('settings', 'Whether customers are allowed to copy subscribers from a list into another'),
            'max_lists'                               => Yii::t('settings', 'Maximum number of lists a customer can have, set to -1 for unlimited'),
            'max_subscribers'                         => Yii::t('settings', 'Maximum number of subscribers a customer can have, set to -1 for unlimited'),
            'max_subscribers_per_list'                => Yii::t('settings', 'Maximum number of subscribers per list a customer can have, set to -1 for unlimited'),
            'copy_subscribers_memory_limit'           => Yii::t('settings', 'Maximum memory the copy subscribers process is allowed to use'),
            'copy_subscribers_at_once'                => Yii::t('settings', 'How many subscribers to copy at once'),
            'can_delete_own_lists'                    => Yii::t('settings', 'Whether customers are allowed to delete their own lists'),
            'can_delete_own_subscribers'              => Yii::t('settings', 'Whether customers are allowed to delete their own subscribers'),
            'can_segment_lists'                       => Yii::t('settings', 'Whether customers are allowed to segment their lists'),
            'max_segment_conditions'                  => Yii::t('settings', 'Maximum number of conditions a list segment can have. This affects performance drastically, keep the number as low as possible'),
            'max_segment_wait_timeout'                => Yii::t('settings', 'Maximum number of seconds a segment is allowed to take in order to load subscribers.'),
            'can_mark_blacklisted_as_confirmed'       => Yii::t('settings', 'Whether customers can mark blacklisted subscribers as confirmed. Please note that this will remove blacklisted emails from the main blacklist'),
            'can_use_own_blacklist'                   => Yii::t('settings', 'Whether customers can use their own blacklist. Please note that the global blacklist has priority over the customer one.'),
            'can_edit_own_subscribers'                => Yii::t('settings', 'Whether customers are allowed to edit their own subscribers'),
            'subscriber_profile_update_optin_history' => Yii::t('settings', 'Whether missing subscriber optin history can be updated when the subscriber will update his profile'),
            'can_create_list_from_filters'            => Yii::t('settings', 'Whether customers can create new lists based on the search results for the filters from All Subscribers area'),
            'show_7days_subscribers_activity_graph'   => Yii::t('settings', 'Whether to show, in list overview, the list subscribers activity for the last 7 days'),
            'force_optin_process'                     => Yii::t('settings', 'Whether to force the customer to certain OPT-IN process. Leave empty to let the customer select the process'),
            'force_optout_process'                    => Yii::t('settings', 'Whether to force the customer to certain OPT-OUT process. Leave empty to let the customer select the process'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getOptInOutOptions()
    {
        return array(
            ''  => '',
            'single' => Yii::t('settings', 'Single'),
            'double' => Yii::t('settings', 'Double'),
        );
    }
}
