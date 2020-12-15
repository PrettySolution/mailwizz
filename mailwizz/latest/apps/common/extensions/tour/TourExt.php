<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TourExt
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class TourExt extends ExtensionInit
{
    // name of the extension as shown in the backend panel
    public $name = 'Tour';

    // description of the extension as shown in backend panel
    public $description = 'MailWizz EMA Tour';

    // current version of this extension
    public $version = '1.0';

    // the author name
    public $author = 'Cristian Serban';

    // author website
    public $website = 'https://www.mailwizz.com/';

    // contact email address
    public $email = 'cristian.serban@mailwizz.com';

    // in which apps this extension is not allowed to run
    public $allowedApps = array('backend', 'customer');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;

    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // the assets url
    private $_assetsUrl;
    
    public function run()
    {
        // import the models
        Yii::import('ext-tour.common.models.*');
        
        if ($this->isAppName('backend')) {

            /**
             * Add the url rules.
             */
            Yii::app()->urlManager->addRules(array(
                // settings
                array('ext_tour_settings/index', 'pattern'    => 'extensions/tour/settings'),
                array('ext_tour_settings/<action>', 'pattern' => 'extensions/tour/settings/*'),
                
                // slideshow slides
                array('ext_tour_slideshow_slides/index', 'pattern'    => 'extensions/tour/slideshows/<slideshow_id:\d+>/slides'),
                array('ext_tour_slideshow_slides/<action>/*', 'pattern' => 'extensions/tour/slideshows/<slideshow_id:\d+>/slides/<action>/*'),
                array('ext_tour_slideshow_slides/<action>', 'pattern' => 'extensions/tour/slideshows/<slideshow_id:\d+>/slides/<action>'),

                // slideshow
                array('ext_tour_slideshows/index', 'pattern'    => 'extensions/tour/slideshows'),
                array('ext_tour_slideshows/<action>/*', 'pattern' => 'extensions/tour/slideshows/<action>/*'),
                array('ext_tour_slideshows/<action>', 'pattern' => 'extensions/tour/slideshows/<action>'),

            ));

            /**
             * And now we register the controllers for the above rules.
             */
            Yii::app()->controllerMap['ext_tour_settings'] = array(
                'class'     => 'ext-tour.backend.controllers.Ext_tour_settingsController',
                'extension' => $this,
            );
            Yii::app()->controllerMap['ext_tour_slideshows'] = array(
                'class'     => 'ext-tour.backend.controllers.Ext_tour_slideshowsController',
                'extension' => $this,
            );
            Yii::app()->controllerMap['ext_tour_slideshow_slides'] = array(
                'class'     => 'ext-tour.backend.controllers.Ext_tour_slideshow_slidesController',
                'extension' => $this,
            );
        }

        /**
         * Now we can continue only if the extension is enabled from its settings:
         */
        if ($this->getOption('enabled', 'no') != 'yes') {
            return;
        }
        
        //
        Yii::app()->controllerMap['ext_tour_slideshow_skip'] = array(
            'class'     => 'ext-tour.common.controllers.Ext_tour_slideshow_skipController',
            'extension' => $this,
        );
        
        // insert the hook.
        Yii::app()->hooks->addAction('after_opening_body_tag', array($this, '_injectTourData'));
    }

    /**
     * Add the landing page for this extension (settings/general info/etc)
     */
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_tour_settings/index');
    }
    
    public function beforeEnable()
    {
        // run the install queries
        $this->runQueriesFromSqlFile(dirname(__FILE__) . '/common/data/install.sql');
        
        // insert default data
        $this->runQueriesFromSqlFile(dirname(__FILE__) . '/common/data/insert.sql');
        
        // run parent
        return parent::beforeEnable();
    }
    
    public function beforeDisable()
    {
        // run the uninstall queries
        $this->runQueriesFromSqlFile(dirname(__FILE__) . '/common/data/uninstall.sql');
        
        // run parent
        return parent::beforeDisable();
    }

    // the assets url, publish if needed.
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl !== null) {
            return $this->_assetsUrl;
        }
        return $this->_assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets', false, -1, MW_DEBUG);
    }
    
    // the callback
    public function _injectTourData($controller)
    {
        if (!in_array($controller->id, array('dashboard'))) {
            return;
        }
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $id      = null;
        
        if ($appName == TourSlideshow::APPLICATION_BACKEND) {
            $id = Yii::app()->user->getId();
        } elseif ($appName == TourSlideshow::APPLICATION_CUSTOMER) {
            $id = Yii::app()->customer->getId();
        }
        
        if (empty($id)) {
            return;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('application', $appName);
        $criteria->compare('status', TourSlideshow::STATUS_ACTIVE);
        $criteria->order = 'slideshow_id DESC';
        $slideshow = TourSlideshow::model()->find($criteria);

        if (empty($slideshow)) {
            return;
        }

        $key       = 'views.' . $appName . '.' . $id . '.viewed';
        $extension = Yii::app()->extensionsManager->getExtensionInstance('tour');

        if ($extension->getOption($key, 0) == $slideshow->slideshow_id) {
            return;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('slideshow_id', $slideshow->slideshow_id);
        $criteria->compare('status', TourSlideshowSlide::STATUS_ACTIVE);
        $criteria->order = 'sort_order ASC, slide_id ASC';
        $slides = TourSlideshowSlide::model()->findAll($criteria);

        if (empty($slides)) {
            return;
        }
        
        $viewFile = $extension->getPathOfAlias('common.views.slideshow') . '.php';
        $controller->renderFile($viewFile, compact('extension', 'slideshow', 'slides'));
        
    }
    
    public function replaceContentTags($content)
    {
        static $searchReplace = array();
        if (empty($searchReplace)) {
            $searchReplace = array(
                '[FULL_NAME]'       => '',
                '[FIRST_NAME]'      => '',
                '[LAST_NAME]'       => '',
                '[APP_NAME]'        => Yii::app()->options->get('system.common.site_name'),
                '[BACKEND_URL]'     => rtrim(Yii::app()->options->get('system.urls.backend_absolute_url'), '/'),
                '[CUSTOMER_URL]'    => rtrim(Yii::app()->options->get('system.urls.customer_absolute_url'), '/'),
                '[API_URL]'         => rtrim(Yii::app()->options->get('system.urls.api_absolute_url'), '/'),
                '[FRONTEND_URL]'    => rtrim(Yii::app()->options->get('system.urls.frontend_absolute_url'), '/'),
                '[SUPPORT_URL]'     => defined('MW_SUPPORT_FORUM_URL') ? MW_SUPPORT_FORUM_URL : '',
                '[ASSETS_URL]'      => $this->getAssetsUrl(),
            );

            $appName = Yii::app()->apps->getCurrentAppName();
            if ($appName == TourSlideshow::APPLICATION_BACKEND) {
                $searchReplace['[FULL_NAME]']   = Yii::app()->user->getModel()->getFullName();
                $searchReplace['[FIRST_NAME]']  = Yii::app()->user->getModel()->first_name;
                $searchReplace['[LAST_NAME]']   = Yii::app()->user->getModel()->last_name;
            } elseif ($appName == TourSlideshow::APPLICATION_CUSTOMER) {
                $searchReplace['[FULL_NAME]']   = Yii::app()->customer->getModel()->getFullName();
                $searchReplace['[FIRST_NAME]']  = Yii::app()->customer->getModel()->first_name;
                $searchReplace['[LAST_NAME]']   = Yii::app()->customer->getModel()->last_name;
            }
        }
        
        return str_replace(array_keys($searchReplace), array_values($searchReplace), StringHelper::decodeSurroundingTags($content));
    }
}
