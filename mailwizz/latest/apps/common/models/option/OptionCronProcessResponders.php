<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCronProcessResponders
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class OptionCronProcessResponders extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.cron.process_responders';
    
    // should we sync the new custom fields
    public $sync_custom_fields_values = 'no';
    
    public function rules()
    {
        $rules = array(
            array('sync_custom_fields_values', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'sync_custom_fields_values' => Yii::t('settings', 'Custom fields sync'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array();
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'sync_custom_fields_values' => Yii::t('settings', 'Enable this if you need to populate all the custom fields with their default values if they are freshly created in a survey and they have no value'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
