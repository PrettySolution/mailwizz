<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerApi
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */
 
class OptionCustomerApi extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_api';
    
    // whether the api is enabled
    public $enabled = 'yes';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('enabled', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'enabled' => Yii::t('settings', 'Enabled'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }

    /**
     * @inheritdoc
     */
    public function attributePlaceholders()
    {
        $placeholders = array();
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled' => Yii::t('settings', 'Whether the API is enabled'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
