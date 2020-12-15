<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerCommon
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
class OptionCustomerCommon extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_common';
    
    public $notification_message;
    
    public $show_articles_menu = 'no';
    
    public $mask_email_addresses = 'no';
    
    public $days_to_keep_disabled_account = 30;

    public function rules()
    {
        $rules = array(
            array('notification_message', 'safe'),
            array('show_articles_menu, mask_email_addresses', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('days_to_keep_disabled_account', 'numerical', 'integerOnly' => true, 'min' => -1, 'max' => 365),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'notification_message' => Yii::t('settings', 'Notification message'),
            'show_articles_menu'   => Yii::t('settings', 'Show articles menu'),
            'mask_email_addresses' => Yii::t('settings', 'Mask email addresses'),
            'days_to_keep_disabled_account' => Yii::t('settings', 'Days to keep disabled account'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'notification_message'  => '',
            'show_articles_menu'    => '',
            'mask_email_addresses'  => '',
            'days_to_keep_disabled_account' => 30,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'notification_message'  => Yii::t('settings', 'A small persistent notification message shown in customers area'),
            'show_articles_menu'    => Yii::t('settings', 'Whether to show the articles link in the menu'),
            'mask_email_addresses'  => Yii::t('settings', 'Whether to mask the email addresses, i.e: abcdef@gmail.com becomes a****f@gmail.com'),
            'days_to_keep_disabled_account' => Yii::t('settings', 'If the customer disables his account, how many days we should keep it in the system until we remove it for good. Set to -1 for unlimited'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
