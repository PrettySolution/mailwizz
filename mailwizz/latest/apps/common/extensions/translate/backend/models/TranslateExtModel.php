<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TranslateExtModel
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class TranslateExtModel extends FormModel
{
    public $enabled = 0;
    
    public $translate_extensions = 0;
    
    public function rules()
    {
        $rules = array(
            array('enabled, translate_extensions', 'required'),
            array('enabled, translate_extensions', 'in', 'range' => array(0, 1)),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'enabled'               => Yii::t('ext_translate', 'Enable automatic translation'),
            'translate_extensions'  => Yii::t('ext_translate', 'Enable extensions translation'),
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
            'enabled'               => Yii::t('ext_translate', 'Enable writing the missing translations in file.'),
            'translate_extensions'  => Yii::t('ext_translate', 'Whether to translate extensions too.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getOptionsDropDown()
    {
        return array(
            0 => Yii::t('app', 'No'),
            1 => Yii::t('app', 'Yes'),
        );
    }
    
    public function populate($extensionInstance)
    {
        $this->enabled              = (int)$extensionInstance->getOption('enabled', $this->enabled);
        $this->translate_extensions = (int)$extensionInstance->getOption('translate_extensions', $this->translate_extensions);
        
        return $this;
    }
    
    public function save($extensionInstance)
    {
        $extensionInstance->setOption('enabled', (int)$this->enabled);
        $extensionInstance->setOption('translate_extensions', (int)$this->translate_extensions);
        
        return $this;
    }
}
