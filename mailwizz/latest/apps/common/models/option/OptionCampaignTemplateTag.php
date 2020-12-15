<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignTemplateTag
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3
 */
 
class OptionCampaignTemplateTag extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.template_tags';

    protected $_campaignTemplateModel;
    
    public $template_tags = array();
    
    public function rules()
    {
        $rules = array(
            array('template_tags', 'safe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'template_tags' => Yii::t('settings', 'Template tags'),
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
        $texts = array();
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->template_tags = $this->getCampaignTemplateModel()->getAvailableTags();
    }

    protected function beforeValidate()
    {
        if (!is_array($this->template_tags)) {
            $this->template_tags = array();
        }
        
        $availableTags = $this->getCampaignTemplateModel()->getAvailableTags();
        
        if (isset($this->template_tags['tag'], $this->template_tags['required']) && is_array($this->template_tags['tag']) && is_array($this->template_tags['required'])) {
            if (count($this->template_tags['tag']) == count($this->template_tags['required'])) {
                $this->template_tags['tag']      = array_values($this->template_tags['tag']);
                $this->template_tags['required'] = array_values($this->template_tags['required']);
                $this->template_tags             = array_combine($this->template_tags['tag'], $this->template_tags['required']);
            }
        }
        
        foreach ($availableTags as $index => $tagInfo) {
            if (isset($this->template_tags[$tagInfo['tag']])) {
                $availableTags[$index]['required'] = (bool)$this->template_tags[$tagInfo['tag']];
            }
        }
        
        $this->template_tags = $availableTags;

        return parent::beforeValidate();
    }
    
    public function getRequiredOptions()
    {
        return array(
            0  => Yii::t('app', 'Not required'),
            1  => Yii::t('app', 'Required'),
        );
    }
    
    public function getCampaignTemplateModel()
    {
        if ($this->_campaignTemplateModel !== null) {
            return $this->_campaignTemplateModel;
        }
        return $this->_campaignTemplateModel = new CampaignTemplate();
    }
}
