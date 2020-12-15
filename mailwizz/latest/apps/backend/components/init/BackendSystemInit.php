<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BackendSystemInit
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BackendSystemInit extends CApplicationComponent 
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
     */
    public function _runOnBeginRequest(CEvent $event)
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }
        
        // a safety hook for logged in vs not logged in users.
        Yii::app()->hooks->addAction('backend_controller_init', array($this, '_checkControllerAccess'));

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
     * callback for user_controller_init and user_before_controller_action action.
     */
    public function _checkControllerAccess() 
    {
        static $_unprotectedControllersHookDone = false;
        static $_hookCalled = false;
        
        if ($_hookCalled || !($controller = Yii::app()->getController())) {
            return;
        }
        
        $_hookCalled = true;
        $unprotectedControllers = (array)Yii::app()->params->itemAt('unprotectedControllers');

        if (!$_unprotectedControllersHookDone) {
            Yii::app()->params->add('unprotectedControllers', $unprotectedControllers);
            $_unprotectedControllersHookDone = true;
        }

        if (!in_array($controller->id, $unprotectedControllers) && !Yii::app()->user->getId()) {
            // make sure we set a return url to the previous page that required the user to be logged in.
            Yii::app()->user->setReturnUrl(Yii::app()->request->requestUri);
            // and redirect to the login url.
            $controller->redirect(Yii::app()->user->loginUrl);
        }
        
        // since 1.3.5, user permission to controller action, aka route
        if (!in_array($controller->id, $unprotectedControllers) && Yii::app()->user->getId()) {
            $controller->onBeforeAction = array($this, '_checkRouteAccess');
        }
        
        // check version update right before executing the action!
        $controller->onBeforeAction = array($this, '_checkUpdateVersion');
        
        // check app wide messages
        $controller->onBeforeAction = array($this, '_checkAppWideMessages');
        
        // 1.5.1 - check if we pulsate the info icons to draq attention to them
        if (Yii::app()->user->getId() && Yii::app()->params['backend.pulsate_info.enabled']) {
            $controller->onBeforeAction = array($this, '_checkMakeIconsPulsate');
        }
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
            array('src' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css', 'priority' => -1000),
            array('src' => 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css', 'priority' => -1000),
            array('src' => $apps->getBaseUrl('assets/css/adminlte.css'), 'priority' => -1000),
            array('src' => AssetsUrl::css('style.css'), 'priority' => -1000),
        ));
        
        // 1.3.7.3
        Yii::app()->getController()->getData('bodyClasses')->add('sidebar-mini');
        $sidebarStatus = isset($_COOKIE['sidebar_status']) ? $_COOKIE['sidebar_status'] : '';
        $sidebarStatus = empty($sidebarStatus) || $sidebarStatus == 'closed' ? 'sidebar-collapse' : '';
        if ($sidebarStatus) {
            Yii::app()->getController()->getData('bodyClasses')->add($sidebarStatus);
        }
        //
        
        // since 1.3.5.4 - skin
        $skinName = null;
        if (($_skinName = Yii::app()->options->get('system.customization.backend_skin'))) {
            if (is_file(Yii::getPathOfAlias('root.backend.assets.css') . '/' . $_skinName . '.css')) {
                $styles->add(array('src' => $apps->getBaseUrl('backend/assets/css/' . $_skinName . '.css'), 'priority' => -1000));
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

        // since 1.3.4.8
        if (is_file(AssetsPath::css('style-custom.css'))) {
            $version = filemtime(AssetsPath::css('style-custom.css'));
            $styles->mergeWith(array(
                array('src' => AssetsUrl::css('style-custom.css') . '?v=' . $version, 'priority' => -1000),
            ));
        }
        
        return $styles;
    }

    /**
     * @since 1.3.5
     * @param $event
     * @throws CHttpException
     */
    public function _checkRouteAccess($event)
    {
        Yii::trace('Checking route access permission for controller ' . $event->sender->id . ', and action ' . $event->sender->action->id);
        if (Yii::app()->user->getModel()->hasRouteAccess($event->sender->route)) {
            return;
        }
        $message = Yii::t('user_groups', 'You do not have the permission to access this resource!');
        if (Yii::app()->request->isAjaxRequest) {
            return $event->sender->renderJson(array(
                'status'  => 'error', 
                'message' => $message,
            ));
        }
        throw new CHttpException(403, $message);
    }

    /**
     * @param $event
     */
    public function _checkUpdateVersion($event)
    {
        $controller = $event->sender;
        $options    = Yii::app()->options;
        $request    = Yii::app()->request;
        
        if ($request->isAjaxRequest) {
            return;
        }
        
        if (in_array($controller->id, array('update', 'guest'))) {
            return;
        }
        
        if ($controller->id == 'dashboard' && $controller->getAction() && $controller->getAction()->id != 'index') {
            return;
        }
        
        $checkEnabled   = $options->get('system.common.check_version_update', 'yes') == 'yes';
        $currentVersion = $options->get('system.common.version');
        $updateVersion  = $options->get('system.common.version_update.current_version', $currentVersion);
        
        if (!$checkEnabled || !$updateVersion || !version_compare($updateVersion, $currentVersion, '>')) {
            return;
        }
        
        Yii::app()->notify->addWarning('<strong><u>' . Yii::t('app', 'Version {version} is now available for download. Please update your application!', array(
            '{version}' => $updateVersion
        )).'</u></strong>');
    }

    /**
     * @param $event
     */
    public function _checkAppWideMessages($event)
    {
        $controller = $event->sender;
        $options    = Yii::app()->options;
        $request    = Yii::app()->request;
        
        if ($request->isAjaxRequest) {
            return;
        }
        
        if (in_array($controller->id, array('update', 'guest'))) {
            return;
        }
        
        $errorKeys = array('system.license.error_message');
        foreach ($errorKeys as $errorKey) {
            $error = $options->get($errorKey);
            if (!empty($error)) {
                Yii::app()->notify->addError($error);
            }
        }
    }

    /**
     * @since 1.5.1
     * @param $event
     */
    public function _checkMakeIconsPulsate($event)
    {
        $controller = $event->sender;
        $options    = Yii::app()->options;
        $key        = sprintf('system.pulsate_info.users.%d.start_ts', (int)Yii::app()->user->getId());
        
        if ($options->get($key, 0) == 0) {
            $options->set($key, time());
        }

        $showItTs       = 3600 * 24 * 7; // one week should be enough
        $pulsateStartTs = $options->get($key, 0);

        if (($pulsateStartTs + $showItTs) < time()) {
            return;
        }

        $apps    = Yii::app()->apps;
        $scripts = $controller->getData('pageScripts');
        $scripts->insertAt(0, array('src' => $apps->getBaseUrl('assets/js/pulsate/pulsate.min.js'), 'priority' => -1000));
        $scripts->add(array('src' => $apps->getBaseUrl('assets/js/pulsate/trigger.js'), 'priority' => -1000));
    }
}