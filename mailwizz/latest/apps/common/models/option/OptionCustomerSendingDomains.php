<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerSendingDomains
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
class OptionCustomerSendingDomains extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_sending_domains';

    // 
    public $can_manage_sending_domains = 'no';
    
    // 
    public $max_sending_domains = -1;
    
    public function rules()
    {
        $rules = array(
            array('can_manage_sending_domains, max_sending_domains', 'required'),
            array('can_manage_sending_domains', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('max_sending_domains', 'numerical', 'integerOnly' => true, 'min' => -1),
        );
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'can_manage_sending_domains'  => Yii::t('settings', 'Can manage sending domains'),
            'max_sending_domains'         => Yii::t('settings', 'Max. sending domains'),
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
            'can_manage_sending_domains'   => Yii::t('settings', 'Whether the customer is allowed to add sending domains.'),
            'max_sending_domains'          => Yii::t('settings', 'Max number of sending domains a customer is allowed to add. Set to -1 for unlimited.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
