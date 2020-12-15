<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * 
 * Campaign activity map
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3
 */
 
class CampaignActivityMapExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Campaign activity map';
    
    // description of the extension as shown in backend panel
    public $description = 'Pinpoint the activity of subscribers on a map.';
    
    // current version of this extension
    public $version = '1.2';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';
    
    // in which apps this extension is allowed to run
    public $allowedApps = array('backend', 'customer', 'frontend');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;

    // run the extension
    public function run()
    {
        $hooks = Yii::app()->hooks;
        
        if ($this->isAppName('backend')) {
            
            // register the url rule to resolve the extension page.
            Yii::app()->urlManager->addRules(array(
                array('ext_campaign_activity_map/index', 'pattern' => 'extensions/campaign-activity-map'),
            ));
            
            // add the backend controller
            Yii::app()->controllerMap['ext_campaign_activity_map'] = array(
                'class' => 'ext-campaign-activity-map.backend.controllers.Ext_campaign_activity_mapController',
            );

        } elseif ($this->isAppName('customer') || $this->isAppName('frontend')) {
            
            // if showing the map, that is any enabled map!
            if ($this->getOption('show_opens_map') || $this->getOption('show_clicks_map') || $this->getOption('show_unsubscribes_map')) {
                
            	$appName = Yii::app()->apps->getCurrentAppName();
            	
                // register the ajax actions that will return the json payload to populate the map.
                $hooks->addFilter($appName . '_controller_campaigns_actions', array($this, '_registerAction'));
                
                // register the extension assets for when the called controller is the campaign one
                $hooks->addAction($appName . '_controller_campaigns_before_action', array($this, '_registerAssets'));
                
                // register the view display action of the map
                $hooks->addAction('customer_campaigns_overview_after_tracking_stats', array($this, '_showMapView'));    
            }    
        }
    }
    
    /**
     * Add the landing page for this extension (settings/general info/etc)
     */
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_campaign_activity_map/index');
    }

    // insert the actions into the customer controller
    public function _registerAction(CMap $actions)
    {
        $actions->add('opens_activity_map', array(
            'class' => 'ext-campaign-activity-map.customer.actions.ActivityMapOpensAction',
        ));
        $actions->add('clicks_activity_map', array(
            'class' => 'ext-campaign-activity-map.customer.actions.ActivityMapClicksAction',
        ));
        $actions->add('unsubscribes_activity_map', array(
            'class' => 'ext-campaign-activity-map.customer.actions.ActivityMapUnsubscribesAction',
        ));
        return $actions;
    }
    
    // register the assets needed to render the map
    public function _registerAssets(CAction $action)
    {
        if ($action->id != 'overview') {
            return;
        }
        
        $assetsUrl = $this->getAssetsUrl();
        
        $mapsApiUrl = '//maps.googleapis.com/maps/api/js?v=3&sensor=false';
        if ($this->getOption('translate_map')) {
            $mapsApiUrl .= '&language=' . Yii::app()->locale->getLanguageID(Yii::app()->language);
        }
        if ($key = $this->getOption('google_maps_api_key')) {
            $mapsApiUrl .= '&key=' . $key;
        }
        
        $action->controller->getData('pageScripts')->mergeWith(array(
            array('src' => $mapsApiUrl),
            // array('src' => '//rawgit.com/googlemaps/js-marker-clusterer/gh-pages/src/markerclusterer.js'),
            array('src' => $assetsUrl . '/markerclusterer.js'),
            array('src' => $assetsUrl . '/gmaps.min.js'),
            array('src' => $assetsUrl . '/maps.js')
        ));
        
        $action->controller->getData('pageStyles')->add(array('src' => $assetsUrl . '/gmaps.css'));
    }
    
    // show the view containing the map.
    public function _showMapView($collectionData)
    {
        $controller = $collectionData->controller;
        $campaign   = $controller->getData('campaign');
        $context    = $this;
        $controller->renderFile(dirname(__FILE__) . '/customer/views/map.php', compact('campaign', 'context'));
    }
    
    // publish the assets and return the url
    public function getAssetsUrl()
    {
        static $assetsUrl;
        
        if ($assetsUrl !== null) {
            return $assetsUrl;
        }
        
        return $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__).'/customer/assets', false, -1, MW_DEBUG);
    }
}