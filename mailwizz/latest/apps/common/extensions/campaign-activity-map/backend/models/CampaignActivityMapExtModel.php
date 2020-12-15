<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignActivityMapExtModel
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class CampaignActivityMapExtModel extends FormModel
{
    public $show_opens_map = 0;
    
    public $show_clicks_map = 0;
    
    public $show_unsubscribes_map = 0;
    
    public $opens_at_once = 50;
    
    public $clicks_at_once = 50;
    
    public $unsubscribes_at_once = 50;
    
    public $google_maps_api_key;
    
    public function rules()
    {
        $rules = array(
            array('show_opens_map, show_clicks_map, opens_at_once, clicks_at_once, show_unsubscribes_map, unsubscribes_at_once', 'required'),
            array('show_opens_map, show_clicks_map, show_unsubscribes_map', 'in', 'range' => array(0, 1)),
            array('opens_at_once, clicks_at_once, unsubscribes_at_once', 'numerical', 'integerOnly' => true, 'min' => 10, 'max' => 500),
            array('google_maps_api_key', 'length', 'max' => 1000),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'show_opens_map'    => Yii::t('ext_campaign_activity_map', 'Show opens map'),
            'show_clicks_map'   => Yii::t('ext_campaign_activity_map', 'Show clicks map'),
            'opens_at_once'     => Yii::t('ext_campaign_activity_map', 'Opens at once'),
            'clicks_at_once'    => Yii::t('ext_campaign_activity_map', 'Clicks at once'),
            
            'show_unsubscribes_map'     => Yii::t('ext_campaign_activity_map', 'Show unsubscribes map'),
            'unsubscribes_at_once'      => Yii::t('ext_campaign_activity_map', 'Unsubscribes at once'),
            
            'google_maps_api_key'   => Yii::t('ext_campaign_activity_map', 'Google maps API key'),
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
            'show_opens_map'    => Yii::t('ext_campaign_activity_map', 'Whether to show a map with location opens in campaign overview'),
            'show_clicks_map'   => Yii::t('ext_campaign_activity_map', 'Whether to show a map with location clicks in campaign overview'),
            'opens_at_once'     => Yii::t('ext_campaign_activity_map', 'How many open records to load at once per ajax call? More records means more memory usage'),
            'clicks_at_once'    => Yii::t('ext_campaign_activity_map', 'How many click records to load at once per ajax call? More records means more memory usage'),
            
            'show_unsubscribes_map' => Yii::t('ext_campaign_activity_map', 'Whether to show a map with location from where subscribers unsubscribed in campaign overview'),
            'unsubscribes_at_once'  => Yii::t('ext_campaign_activity_map', 'How many unsubscribe records to load at once per ajax call? More records means more memory usage'),
            
            'google_maps_api_key'   => Yii::t('ext_campaign_activity_map', 'Your google maps API key. It is optional but needed if you go over the free quota assigned by Google'),
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
        $this->show_opens_map   = (int)$extensionInstance->getOption('show_opens_map', $this->show_opens_map);
        $this->show_clicks_map  = (int)$extensionInstance->getOption('show_clicks_map', $this->show_clicks_map);
        $this->opens_at_once    = (int)$extensionInstance->getOption('opens_at_once', $this->opens_at_once);
        $this->clicks_at_once   = (int)$extensionInstance->getOption('clicks_at_once', $this->clicks_at_once);
        
        $this->show_unsubscribes_map    = (int)$extensionInstance->getOption('show_unsubscribes_map', $this->show_unsubscribes_map);
        $this->unsubscribes_at_once     = (int)$extensionInstance->getOption('unsubscribes_at_once', $this->unsubscribes_at_once);

        $this->google_maps_api_key = $extensionInstance->getOption('google_maps_api_key', $this->google_maps_api_key);
        
        return $this;
    }
    
    public function save($extensionInstance)
    {
        $extensionInstance->setOption('show_opens_map', (int)$this->show_opens_map);
        $extensionInstance->setOption('show_clicks_map', (int)$this->show_clicks_map);
        $extensionInstance->setOption('opens_at_once', (int)$this->opens_at_once);
        $extensionInstance->setOption('clicks_at_once', (int)$this->clicks_at_once);
        $extensionInstance->setOption('show_unsubscribes_map', (int)$this->show_unsubscribes_map);
        $extensionInstance->setOption('unsubscribes_at_once', (int)$this->unsubscribes_at_once);
        $extensionInstance->setOption('google_maps_api_key', $this->google_maps_api_key);
        
        return $this;
    }
}
