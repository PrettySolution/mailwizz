<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerSurveys
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class OptionCustomerSurveys extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_surveys';

    // maximum number of surveys a customer can have, -1 is unlimited
    public $max_surveys = -1;
    
    // maximum number of responders, -1 is unlimited
    public $max_responders = -1 ;
    
    //maximum number of responders allowed into a survey, -1 is unlimited
    public $max_responders_per_survey = -1;

    // can the customer delete his surveys?
    public $can_delete_own_surveys = 'yes';

    // whether the customers are allowed to export
    public $can_export_responders = 'yes';

    // can the customer segment surveys?
    public $can_segment_surveys = 'yes';

    // max number of segment conditions
    public $max_segment_conditions = 3;

    // max wait timeout for a segment to load
    public $max_segment_wait_timeout = 5;

    // can the customer delete his responders?
    public $can_delete_own_responders = 'yes';

    // whether the customers are allowed to edit responders
    public $can_edit_own_responders = 'yes';

    public $show_7days_responders_activity_graph = 'yes';
    
    public function rules()
    {
        $rules = array(
            array('max_surveys, max_responders, max_responders_per_survey, can_delete_own_surveys, can_delete_own_responders, can_edit_own_responders, can_export_responders, show_7days_responders_activity_graph, can_segment_surveys, max_segment_conditions, max_segment_wait_timeout,', 'required'),
            array('can_delete_own_surveys, can_delete_own_responders, can_edit_own_responders, can_export_responders, show_7days_responders_activity_graph, can_segment_surveys', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('max_surveys, max_responders, max_responders_per_survey', 'numerical', 'integerOnly' => true, 'min' => -1),
            array('max_segment_conditions, max_segment_wait_timeout', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 60),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'max_surveys'                          => Yii::t('settings', 'Max. surveys'),
            'max_responders'                       => Yii::t('settings', 'Max. responders'),
            'max_responders_per_survey'            => Yii::t('settings', 'Max. responders per survey'),
            'can_delete_own_surveys'               => Yii::t('settings', 'Can delete own surveys'),
            'can_delete_own_responders'            => Yii::t('settings', 'Can delete own responders'),
            'can_edit_own_responders'              => Yii::t('settings', 'Can edit own responders'),
            'can_export_responders'                => Yii::t('settings', 'Can export responders'),
            'can_segment_surveys'                  => Yii::t('settings', 'Can segment surveys'),
            'max_segment_conditions'               => Yii::t('settings', 'Max. segment conditions'),
            'max_segment_wait_timeout'             => Yii::t('settings', 'Max. segment wait timeout'),
            'show_7days_responders_activity_graph' => Yii::t('settings', 'Show 7 days responders activity'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'max_surveys'               => '',
            'max_responders'            => '',
            'max_responders_per_survey' => '',
            'can_edit_own_responders'   => '',
            'can_export_responders'     => '',
            'max_segment_conditions'    => '',
            'max_segment_wait_timeout'  => '',

        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'max_surveys'                          => Yii::t('settings', 'Maximum number of surveys a customer can have, set to -1 for unlimited'),
            'max_responders'                       => Yii::t('settings', 'Maximum number of responders a customer can have, set to -1 for unlimited'),
            'max_responders_per_survey'            => Yii::t('settings', 'Maximum number of responders per survey a customer can have, set to -1 for unlimited'),
            'can_delete_own_surveys'               => Yii::t('settings', 'Whether customers are allowed to delete their own surveys'),
            'can_delete_own_responders'            => Yii::t('settings', 'Whether customers are allowed to delete their own responders'),
            'can_edit_own_responders'              => Yii::t('settings', 'Whether customers are allowed to edit their own responders'),
            'can_export_responders'                => Yii::t('settings', 'Whether customers are allowed to export responders'),
            'can_segment_surveys'                  => Yii::t('settings', 'Whether customers are allowed to segment their surveys'),
            'max_segment_conditions'               => Yii::t('settings', 'Maximum number of conditions a survey segment can have. This affects performance drastically, keep the number as low as possible'),
            'max_segment_wait_timeout'             => Yii::t('settings', 'Maximum number of seconds a segment is allowed to take in order to load responders.'),
            'show_7days_responders_activity_graph' => Yii::t('settings', 'Whether to show, in survey overview, the survey responders activity for the last 7 days'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
