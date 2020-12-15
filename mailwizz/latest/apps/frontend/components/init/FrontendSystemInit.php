<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FrontendSystemInit
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class FrontendSystemInit extends CApplicationComponent
{
    /**
     * @var bool 
     */
    protected $_hasRanOnBeginRequest = false;

    /**
     * @var bool 
     */
    protected $_hasRanOnEndRequest = false;

    /**
     * @inheritdoc
     * @throws CException
     */
    public function init()
    {
        parent::init();
        Yii::app()->attachEventHandler('onBeginRequest', array($this, '_runOnBeginRequest'));
        Yii::app()->attachEventHandler('onEndRequest', array($this, '_runOnEndRequest'));
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function _runOnBeginRequest(CEvent $event)
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }

        // register core assets if not cli mode and no theme active
        if (!MW_IS_CLI && (!Yii::app()->hasComponent('themeManager') || !Yii::app()->getTheme())) {
            $this->registerAssets();
        }

        // and mark the event as completed.
        $this->_hasRanOnBeginRequest = true;
    }

    /**
     * @param CEvent $event
     */
    public function _runOnEndRequest(CEvent $event)
    {
        if ($this->_hasRanOnEndRequest) {
            return;
        }

        // and mark the event as completed.
        $this->_hasRanOnEndRequest = true;
    }

    /**
     * Register the assets
     */
    public function registerAssets()
    {
        Yii::app()->hooks->addFilter('register_scripts', array($this, '_registerScripts'));
        Yii::app()->hooks->addFilter('register_styles', array($this, '_registerStyles'));
    }

    /**
     * @param CList $scripts
     * @return CList
     * @throws CException
     */
    public function _registerScripts(CList $scripts)
    {
        $apps = Yii::app()->apps;
        $scripts->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/js/bootstrap.min.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/knockout.min.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/notify.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/adminlte.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/cookie.js'), 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/js/app.js'), 'priority' => -1000),
            array('src' => AssetsUrl::js('app.js'), 'priority' => -1000),
        ));

        // since 1.3.4.8
        if (is_file(AssetsPath::js('app-custom.js'))) {
            $version = filemtime(AssetsPath::js('app-custom.js'));
            $scripts->mergeWith(array(
                array('src' => AssetsUrl::js('app-custom.js') . '?v=' . $version, 'priority' => -1000),
            ));
        }

        return $scripts;
    }

    /**
     * @param CList $styles
     * @return CList
     * @throws CException
     */
    public function _registerStyles(CList $styles)
    {
        $apps = Yii::app()->apps;
        $styles->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/css/bootstrap.min.css'), 'priority' => -1000),
            array('src' => 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,700', 'priority' => -1000),
            array('src' => 'https://fonts.googleapis.com/css?family=Lato:100,100i,300,300i,400,400i,700,700i,900,900i', 'priority' => -1000),
            array('src' => 'https://fonts.googleapis.com/css?family=Roboto:300,400,700,900', 'priority' => -1000),
            array('src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css', 'priority' => -1000),
            array('src' => 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css', 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/css/adminlte.css'), 'priority' => -1000),
            array('src' => AssetsUrl::css('style.css'), 'priority' => -1000),
        ));

        // since 1.3.5.4 - skin
        $skinName = null;
        if (($_skinName = Yii::app()->options->get('system.customization.frontend_skin'))) {
            if (is_file(Yii::getPathOfAlias('root.frontend.assets.css') . '/' . $_skinName . '.css')) {
                $styles->add(array('src' => $apps->getBaseUrl('frontend/assets/css/' . $_skinName . '.css'), 'priority' => -1000));
                $skinName = $_skinName;
            } elseif (is_file(Yii::getPathOfAlias('root.assets.css') . '/' . $_skinName . '.css')) {
                $styles->add(array('src' => $apps->getBaseUrl('assets/css/' . $_skinName . '.css'), 'priority' => -1000));
                $skinName = $_skinName;
            } else {
                $_skinName = null;
            }
        }
        if (!$skinName) {
            $styles->add(array('src' => $apps->getBaseUrl('assets/css/skin-blue.css'), 'priority' => -1000));
            $skinName = 'skin-blue';
        }
        Yii::app()->getController()->getData('bodyClasses')->add($skinName);
        // end 1.3.5.4

        // 1.3.7.3
        Yii::app()->getController()->getData('bodyClasses')->add('sidebar-hidden');

        // since 1.3.4.8
        if (is_file(AssetsPath::css('style-custom.css'))) {
            $version = filemtime(AssetsPath::css('style-custom.css'));
            $styles->mergeWith(array(
                array('src' => AssetsUrl::css('style-custom.css') . '?v=' . $version, 'priority' => -1000),
            ));
        }

        return $styles;
    }
}
