<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionMonetizationMonetization
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */
 
class OptionMonetizationMonetization extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.monetization.monetization';

    public $enabled = 'no';

    public function rules()
    {
        $rules = array(
            array('enabled', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

    public function attributeLabels()
    {
        $labels = array(
            'enabled' => Yii::t('settings', 'Enabled'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled' => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled' => Yii::t('settings', 'Whether the whole monetization module is enabled'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
