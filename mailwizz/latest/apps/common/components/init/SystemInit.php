<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SystemInit
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class SystemInit extends CApplicationComponent
{
    protected $_hasRanOnBeginRequest = false;

    /**
     * SystemInit::init()
     *
     */
    public function init()
    {
        parent::init();
        Yii::app()->attachEventHandler('onBeginRequest', array($this, '_runOnBeginRequest'));
    }

    /**
     * SystemInit::_runOnBeginRequest()
     *
     */
    public function _runOnBeginRequest()
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }

        // check if app is read only and take proper action
        $this->_checkIfAppIsReadOnly();

        $options = Yii::app()->options;
        $appName = Yii::app()->apps->getCurrentAppName();

        if (!MW_IS_CLI) {
            Yii::app()->hooks->addAction($appName . '_controller_before_action', array($this, '_reindexGetArray'));
        }

        if (!in_array($appName, array('backend', 'console')) && $options->get('system.common.site_status') === 'offline') {
            Yii::app()->hooks->addAction($appName . '_controller_before_action', array($this, '_setRedirectToOfflinePage'), -1000);
        }

        if (!MW_IS_CLI) {

            // clean the globals.
            Yii::app()->ioFilter->cleanGlobals();

            // nice urls
            if ($options->get('system.common.clean_urls')) {
                Yii::app()->urlManager->showScriptName = false;
            }

            // verify the stored urls.
            $this->checkAppsStoredUrls();

            // set the application display language
            $this->setApplicationLanguage();

            if (!in_array($appName, array('api'))) {
                // check if we need to upgrade
                $this->checkUpgrade();
            }
        }

        // since 1.3.5.4 - CDN Support
        if (!MW_IS_CLI && !in_array($appName, array('api')) && $options->isTrue('system.cdn.enabled') && ($cdnDomain = $options->get('system.cdn.subdomain'))) {
            if (stripos($cdnDomain, 'http') !== 0) {
                $cdnDomain = 'http://' . $cdnDomain;
            }
            Yii::app()->assetManager->baseUrl = sprintf('%s/%s', $cdnDomain, ltrim(Yii::app()->assetManager->baseUrl, '/'));
            if (Yii::app()->hasComponent('themeManager') && Yii::app()->themeManager->setAppTheme() /*&& Yii::app()->getTheme()*/) {
                Yii::app()->themeManager->baseUrl = sprintf('%s/%s', $cdnDomain, ltrim(Yii::app()->themeManager->baseUrl, '/'));
            }
        }

        // load all extensions.
        Yii::app()->extensionsManager->loadAllExtensions();

        // setup theme or base view system if not cli mode
        if (!MW_IS_CLI && !in_array($appName, array('api'))) {
            // try to set the theme system
            if (Yii::app()->hasComponent('themeManager') /*&& Yii::app()->getTheme()*/) {
                // set the theme
                Yii::app()->themeManager->setAppTheme();
            }
        }

        if (!MW_IS_CLI && !MW_IS_AJAX && in_array($appName, array('backend', 'frontend', 'customer'))) {
            Yii::app()->hooks->addAction($appName . '_controller_before_action', array($this, '_checkStoredData'), -1000);
        }

        // and mark the event as completed.
        $this->_hasRanOnBeginRequest = true;
    }

    /**
     * SystemInit::checkAppsStoredUrls()
     *
     * @return
     */
    protected function checkAppsStoredUrls()
    {
        // base urls (needed from cli mode since $_SERVER is not available there)
        $apps        = Yii::app()->apps->getWebApps();
        $scheme      = Yii::app()->options->get('system.urls.scheme', 'http');
        $currentHash = sha1(basename(__FILE__) . Yii::app()->options->get('system.common.clean_urls', 'no'));
        $storedHash  = Yii::app()->options->get('system.urls.hash');
        $hashChanged = ($currentHash != $storedHash);
        foreach ($apps as $appName) {
            $storedBaseUrl = Yii::app()->options->get('system.urls.'.$appName.'_absolute_url');
            if (!empty($storedBaseUrl) && !$hashChanged) {
                continue;
            }
            $baseUrl = Yii::app()->apps->getAppUrl($appName, null, true);
            if ($scheme == 'https') {
                $baseUrl = preg_replace('#^http://#', 'https://', $baseUrl);
            } else {
                $baseUrl = preg_replace('#^https://#', 'http://', $baseUrl);
            }
            if ($storedBaseUrl != $baseUrl) {
                Yii::app()->options->set('system.urls.'.$appName.'_absolute_url', $baseUrl);
            }
        }
        if ($hashChanged) {
            Yii::app()->options->set('system.urls.hash', $currentHash);
        }
    }

    /**
     * SystemInit::setApplicationLanguage()
     *
     * Will set the application language in cascade.
     * It will default to english if there is no default language or if the client/user do not have a language set!
     *
     * @since 1.1
     */
    protected function setApplicationLanguage()
    {
        // multilanguage is available since 1.1 and the Language class does not exist prior to that version
        if (!version_compare(Yii::app()->options->get('system.common.version'), '1.1', '>=')) {
            return;
        }

        $languageCode = null;
        
        // 1.4.4
        if (($langCode = Yii::app()->request->getQuery('lang')) && strlen($langCode) <= 5) {
            $regionCode = null;
            if (strpos($langCode, '_') !== false) {
                list($langCode, $regionCode) = explode('_', $langCode);
            }
            $attributes = array(
                'language_code' => $langCode,
            );
            if (!empty($regionCode)) {
                $attributes['region_code'] = $regionCode;
            }
            $language = Language::model()->findByAttributes($attributes);
            if (!empty($language)) {
                Yii::app()->setLanguage($language->getLanguageAndLocaleCode());
            }
            return;
        }
        //
        
        if ($language = Language::getDefaultLanguage()) {
            $languageCode = $language->getLanguageAndLocaleCode();
        }
        
        if (Yii::app()->apps->isAppName('frontend')) {
            if (!empty($languageCode)) {
                Yii::app()->setLanguage($languageCode);
            }
            return;
        }
        
        $loadCustomerLanguage = Yii::app()->hasComponent('customer') && Yii::app()->customer->getId() > 0;
        $loadUserLanguage     = !$loadCustomerLanguage && Yii::app()->hasComponent('user') && Yii::app()->user->getId() > 0 ;

        if ($loadCustomerLanguage || $loadUserLanguage) {
            
            if ($loadCustomerLanguage && ($model = Yii::app()->customer->getModel())) {
                if (!empty($model->language_id)) {
                    $language = Language::model()->findByPk((int)$model->language_id);
                    if (!empty($language)) {
                        $languageCode = $language->getLanguageAndLocaleCode();
                    }
                }
            }

            if ($loadUserLanguage && ($model = Yii::app()->user->getModel())) {
                if (!empty($model->language_id)) {
                    $language = Language::model()->findByPk((int)$model->language_id);
                    if (!empty($language)) {
                        $languageCode = $language->getLanguageAndLocaleCode();
                    }
                }
            }
            
        }
        
        if (!empty($languageCode)) {
            Yii::app()->setLanguage($languageCode);
        }
    }

    /**
     * SystemInit::checkUpgrade()
     *
     * Will check and see if the application needs upgrade.
     * If it needs, will put it in maintenance mode untill upgrade is done.
     *
     * @since 1.1
     */
    protected function checkUpgrade()
    {
        $apps = Yii::app()->apps;
        if (!in_array($apps->getCurrentAppName(), array('backend', 'customer', 'frontend'))) {
            return;
        }

        $options     = Yii::app()->options;
        $fileVersion = MW_VERSION;
        $dbVersion   = $options->get('system.common.version');

        if (!version_compare($fileVersion, $dbVersion, '>')) {
            return;
        }

        $siteStatus = $options->get('system.common.site_status', 'online');
        if ($siteStatus == 'online') {
            $options->set('system.common.site_status', 'offline');
        }

        // only if the user is logged in
        if (Yii::app()->hasComponent('user') && Yii::app()->user->getId() > 0) {
            $appName = $apps->getCurrentAppName();
            Yii::app()->hooks->addAction($appName . '_controller_init', array($this, '_setRedirectToUpdatePage'));
        }
    }

    /**
     * SystemInit::_checkIfAppIsReadOnly
     */
    protected function _checkIfAppIsReadOnly()
    {
        if (!defined('MW_IS_APP_READ_ONLY') || !MW_IS_APP_READ_ONLY) {
            return;
        }

        $message = 'The application demo runs in READ-ONLY mode!';
        if (MW_IS_CLI) {
            exit($message);
        }

        if(isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], array_map('trim', explode(',', MW_DEVELOPERS_IPS)))) {
        	return;
        }

        $neverAllowed = array(
            '/api', '/customer/api-keys/generate', '/backend/settings/license',
            '/backend/misc/application-log', '/customer/guest/confirm-registration',
            '/backend/misc/phpinfo',
        );

        $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        $allowed = array('/backend/guest', '/customer/guest');
        $referer = '../';
        $allow   = false;

        $uri      = trim(str_replace('/index.php/', '/', $uri), '/');
        $uriParts = array_unique(explode('/', $uri));
        $webApps  = Yii::app()->apps->getWebApps();
        foreach ($uriParts as $index => $part) {
            if (!in_array($part, $webApps)) {
                unset($uriParts[$index]);
            }
            break;
        }
        $uri = '/' . implode('/', $uriParts);

        foreach ($neverAllowed as $uriString) {
            if (strpos($uri, $uriString) === 0) {
                if (!Yii::app()->request->isAjaxRequest) {
                    Yii::app()->notify->addWarning($message);
                    Yii::app()->request->redirect($referer);
                }
                Yii::app()->end();
            }
        }

        if (empty($_POST)) {
            return;
        }

        foreach ($allowed as $uriString) {
            if (strpos($uri, $uriString) === 0) {
                $allow = true;
                break;
            }
        }

        if (!$allow) {
            if (!Yii::app()->request->isAjaxRequest) {
                Yii::app()->notify->addWarning($message);
                Yii::app()->request->redirect($referer);
            }
            Yii::app()->end();
        }
    }

    /**
     * SystemInit::_setRedirectToUpdatePage
     *
     * Called in all controllers init() method, will redirect to update page.
     */
    public function _setRedirectToUpdatePage()
    {
        $apps = Yii::app()->apps;
        $controller = Yii::app()->getController();

        // leave the error page alone
        if (stripos(Yii::app()->errorHandler->errorAction, $controller->route) !== false) {
            return;
        }

        if (!$apps->isAppName('backend') || $controller->id != 'update') {
            Yii::app()->request->redirect($apps->getAppUrl('backend', 'update/index', true));
        }
    }

    /**
     * SystemInit::_setRedirectToOfflinePage()
     *
     * @param mixed $action
     * @return
     */
    public function _setRedirectToOfflinePage($action)
    {
        $apps = Yii::app()->apps;
        $controller = Yii::app()->getController();
        $controllerHandler = 'site';

        $isErrorPage = $controller->id == $controllerHandler && $controller->action->id == 'error';
        $isOfflinePage = $controller->id == $controllerHandler && $controller->action->id == 'offline';
        if (!$isErrorPage && !$isOfflinePage) {
            Yii::app()->request->redirect($apps->getAppUrl('frontend', $controllerHandler . '/offline', true));
        }
    }

    /**
     * SystemInit::_reindexGetArray()
     *
     * @return
     */
    public function _reindexGetArray()
    {
        if (empty($_GET)) {
            return;
        }
        Yii::app()->params['GET']->mergeWith($_GET);
        $_GET = Yii::app()->ioFilter->stripClean($_GET);
    }

    /**
     * SystemInit::_checkStoredData()
     *
     * @param mixed $action
     * @return
     */
    public function _checkStoredData($action)
    {
    	$license = new OptionLicense();
        if ($license->getPurchaseCode()) {
            return;
        }
        
        Yii::app()->notify->addError($license->getMissingPurchaseCodeMessage());
    }
}
