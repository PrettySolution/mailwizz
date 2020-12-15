<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionEmailBlacklist
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */
 
class OptionEmailBlacklist extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.email_blacklist';
    
    public $local_check = 'yes';
    
    public $allow_new_records = 'yes';
    
    public $reconfirm_blacklisted_subscribers_on_blacklist_delete = 'yes';
    
    public $regular_expressions;

    public $allow_md5 = 'no';
    
    public $regex_test_email = '';

    public function rules()
    {
        $rules = array(
            array('local_check, allow_new_records, allow_md5', 'required'),
            array('local_check, allow_new_records, allow_md5', 'in', 'range' => array_keys($this->getCheckOptions())),
            array('regular_expressions', 'safe'),
            
            array('reconfirm_blacklisted_subscribers_on_blacklist_delete', 'required'),
            array('reconfirm_blacklisted_subscribers_on_blacklist_delete', 'in', 'range' => array_keys($this->getCheckOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'local_check'         => Yii::t('settings', 'Local checks'),
            'allow_new_records'   => Yii::t('settings', 'Allow adding new records'),
            'regular_expressions' => Yii::t('settings', 'Regular expressions'),
            'allow_md5'           => Yii::t('settings', 'Allow md5 emails'),
            'regex_test_email'    => Yii::t('settings', 'Regex email address test'),
            
            'reconfirm_blacklisted_subscribers_on_blacklist_delete' => Yii::t('settings', 'Reconfirm subscribers on delete'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'local_check'         => '',
            'regular_expressions' => "/abuse@(.*)/i\n/spam@(.*)/i\n/(.*)@abc\.com/i",
            'regex_test_email'    => Yii::t('settings', 'Enter here one or more email addresses, separated by a comma, to test above regular expressions'),
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'local_check'         => Yii::t('settings', 'Whether to check the email addresses against local database.'),
            'allow_new_records'   => Yii::t('settings', 'Whether to allow adding new records to the email blacklist'),
            'regular_expressions' => Yii::t('settings', 'List of regular expressions for blacklisting an email. Please use one expression per line and make sure it is correct.'),
            'allow_md5'           => Yii::t('settings', 'Whether to allow md5 email addresses in the blacklists'),
            'reconfirm_blacklisted_subscribers_on_blacklist_delete' => Yii::t('settings', 'Deleting email addresses from the global blacklist will reconfirm those subscribers if their current status is blacklisted.'),
	        'regex_test_email'    => Yii::t('settings', 'Enter here one or more email addresses, separated by a comma, to test above regular expressions'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function getCheckOptions()
    {
        return $this->getYesNoOptions();
    }
}
