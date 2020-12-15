<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCronProcessTransactionalEmails
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.9
 */
 
class OptionCronProcessTransactionalEmails extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.cron.transactional_emails';
    
    // select emails that are older that x days
    public $delete_days_back = -1;
    
    public function rules()
    {
        $rules = array(
            array('delete_days_back', 'required'),
            array('delete_days_back', 'numerical', 'min' => -1, 'max' => 60),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'delete_days_back' => Yii::t('settings', 'Delete days back'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'delete_days_back' => -1,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'delete_days_back' => Yii::t('settings', 'Delete emails that are older than this amount of days. Increasing the number of days increases the amount of emails to be processed. -1 means never delete these.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
