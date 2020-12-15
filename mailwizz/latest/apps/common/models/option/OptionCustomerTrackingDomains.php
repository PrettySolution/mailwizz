<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerTrackingDomains
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
class OptionCustomerTrackingDomains extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_tracking_domains';

    // 
    public $can_manage_tracking_domains = 'no';
    
    // 
    public $can_select_for_delivery_servers = 'no';

    // 
    public $can_select_for_campaigns = 'no';
    
    public function rules()
    {
        $rules = array(
            array('can_manage_tracking_domains, can_select_for_delivery_servers, can_select_for_campaigns', 'required'),
            array('can_manage_tracking_domains, can_select_for_delivery_servers, can_select_for_campaigns', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'can_manage_tracking_domains'     => Yii::t('settings', 'Can manage tracking domains'),
            'can_select_for_delivery_servers' => Yii::t('settings', 'Can select tracking domains for delivery servers'),
            'can_select_for_campaigns'        => Yii::t('settings', 'Can select tracking domains for campaigns'),
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
            'can_manage_tracking_domains'     => Yii::t('settings', 'Whether the customer is allowed to manage tracking domains. Please note that additional DNS settings must be done for this domain in order to allow the feature.'),
            'can_select_for_delivery_servers' => Yii::t('settings', 'Whether customers are allowed to select tracking domains for delivery servers'),
            'can_select_for_campaigns'        => Yii::t('settings', 'Whether customers are allowed to select tracking domains for campaigns'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
