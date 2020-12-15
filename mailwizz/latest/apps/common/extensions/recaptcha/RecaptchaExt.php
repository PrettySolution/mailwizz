<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RecaptchaExt
 * 
 * @package MailWizz EMA
 * @subpackage recaptcha
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class RecaptchaExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Recaptcha';
    
    // description of the extension as shown in backend panel
    public $description = 'Protect the public forms using Google\'s Recaptcha';
    
    // current version of this extension
    public $version = '1.0';
    
    // minimum app version
    public $minAppVersion = '1.3.5.8';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';
    
    // in which apps this extension is allowed to run
    public $allowedApps = array('backend', 'frontend', 'customer');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // run the extension
    public function run()
    {
        Yii::import('ext-recaptcha.common.models.*');

        if ($this->isAppName('backend')) {
            // add the url rules
            Yii::app()->urlManager->addRules(array(
                array('ext_recaptcha_settings/index', 'pattern' => 'extensions/recaptcha/settings'),
                array('ext_recaptcha_settings/<action>', 'pattern' => 'extensions/recaptcha/settings/*'),
            ));
            
            // add the controller
            Yii::app()->controllerMap['ext_recaptcha_settings'] = array(
                'class'     => 'ext-recaptcha.backend.controllers.Ext_recaptcha_settingsController',
                'extension' => $this,
            );
        }

        // keep these globally for easier access from the callback.
        Yii::app()->params['extensions.recaptcha.data.enabled']                = $this->getOption('enabled') == 'yes';
        Yii::app()->params['extensions.recaptcha.data.enabled_for_list_forms'] = $this->getOption('enabled_for_list_forms') == 'yes';

        Yii::app()->params['extensions.recaptcha.data.enabled_for_registration'] = $this->getOption('enabled_for_registration') == 'yes';
        Yii::app()->params['extensions.recaptcha.data.enabled_for_login']        = $this->getOption('enabled_for_login') == 'yes';
        Yii::app()->params['extensions.recaptcha.data.enabled_for_forgot']       = $this->getOption('enabled_for_forgot') == 'yes';
        
        Yii::app()->params['extensions.recaptcha.data.site_key']               = $this->getOption('site_key');
        Yii::app()->params['extensions.recaptcha.data.secret_key']             = $this->getOption('secret_key');
        
        if ($this->getOption('enabled') != 'yes' || strlen($this->getOption('site_key')) < 20 || strlen($this->getOption('secret_key')) < 20) {
            return;
        }

        if ($this->isAppName('frontend') && Yii::app()->params['extensions.recaptcha.data.enabled_for_list_forms']) {
        	
	        Yii::app()->hooks->addAction('frontend_list_subscribe_at_transaction_start', array($this, '_listFormCheckSubmission'));
	        Yii::app()->hooks->addFilter('frontend_list_subscribe_before_transform_list_fields', array($this, '_listFormAppendHtml'));
	
	        Yii::app()->hooks->addAction('frontend_list_update_profile_at_transaction_start', array($this, '_listFormCheckSubmission'));
	        Yii::app()->hooks->addFilter('frontend_list_update_profile_before_transform_list_fields', array($this, '_listFormAppendHtml'));
        }
        
        if ($this->isAppName('customer') || $this->isAppName('backend')) {
            $appName = Yii::app()->apps->getCurrentAppName();
            Yii::app()->hooks->addAction($appName . '_controller_guest_before_action', array($this, '_guestActions'));
        }
        
        if ($this->isAppName('customer') && Yii::app()->params['extensions.recaptcha.data.enabled_for_list_forms']) {
        	Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_customerListAfterActiveFormFields'));
        	Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_customerListControllerActionSaveData'));
        }
    }

    // Add the landing page for this extension (settings/general info/etc)
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_recaptcha_settings/index');
    }

    // callback to respond to the action hook: frontend_list_subscribe_at_transaction_start
    // this is inside a try/catch block so we have to throw an exception on failure.
    public function _listFormCheckSubmission()
    {
	    if (!$this->getListFormModel()->getIsEnabled()) {
		    return;
	    }
	    
        $request  = Yii::app()->request;
        $response = AppInitHelper::simpleCurlPost('https://www.google.com/recaptcha/api/siteverify', array(
            'secret'   => Yii::app()->params['extensions.recaptcha.data.secret_key'],
            'response' => $request->getPost('g-recaptcha-response'),
            'remoteip' => $request->getUserHostAddress(),
        ));
        
        $response = CJSON::decode($response['message']);
        if (empty($response['success'])) {
            throw new Exception(Yii::t("lists", "Invalid captcha response!"));
        }
    }

    // callback to respond to the filter hook: frontend_list_subscribe_before_transform_list_fields
    public function _listFormAppendHtml($content)
    {
	    if (!$this->getListFormModel()->getIsEnabled()) {
		    return $content;
	    }
	    
        $controller = Yii::app()->getController();
        $controller->getData('pageScripts')->add(array('src' => 'https://www.google.com/recaptcha/api.js'));
        
        $append  = sprintf('<div class="g-recaptcha pull-left" data-sitekey="%s"></div>', Yii::app()->params['extensions.recaptcha.data.site_key']);
        $append .= '<div class="clearfix"><!-- --></div>';

        return preg_replace('/\[LIST_FIELDS\]/', "[LIST_FIELDS]\n" . $append, $content, 1, $count);
    }
    
    public function _guestActions($action)
    {
        if (!in_array($action->id, array('index', 'register', 'forgot_password'))) {
            return;
        }
        
        $canShow = Yii::app()->params['extensions.recaptcha.data.enabled_for_registration'] || 
                   Yii::app()->params['extensions.recaptcha.data.enabled_for_login'] ||
                   Yii::app()->params['extensions.recaptcha.data.enabled_for_forgot'];
        
        if (!$canShow) {
            return;
        }
        
        $action->controller->getData('pageScripts')->add(array('src' => 'https://www.google.com/recaptcha/api.js'));
        
        if (Yii::app()->params['extensions.recaptcha.data.enabled_for_registration'] && $action->id == 'register') {
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_guestProcessForm'));
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_guestFormAppendHtml'));
        } elseif (Yii::app()->params['extensions.recaptcha.data.enabled_for_login'] && $action->id == 'index') {
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_guestProcessForm'));
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_guestFormAppendHtml'));
        } elseif (Yii::app()->params['extensions.recaptcha.data.enabled_for_forgot'] && $action->id == 'forgot_password') {
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_guestProcessForm'));
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_guestFormAppendHtml'));
        }
    }
    
    /**
     * @param $collection
     */
    public function _guestProcessForm($collection)
    {
        $response = $this->getRecaptchaResponse();
        if (empty($response['success'])) {
            $collection->success = false;
            Yii::app()->notify->addError(Yii::t("lists", "Invalid captcha response!"));
            return;
        }
    }

    /**
     * @param $collection
     */
    public function _guestFormAppendHtml($collection)
    {
        $append  = sprintf('<div class="row"><hr /><div class="col-lg-12 g-recaptcha" data-sitekey="%s"></div></div>', Yii::app()->params['extensions.recaptcha.data.site_key']);
        $append .= '<div class="clearfix"><!-- --></div>';
        echo $append;
    }

	/**
	 * @param $collection
	 */
    public function _customerListAfterActiveFormFields($collection)
    {
    	if (Yii::app()->controller->id != 'lists') {
    		return;
	    }
    	
	    $collection->controller->renderFile($this->getPathOfAlias('customer.views.lists') . '/_form.php', array(
	    	'model' => $this->getListFormModel(),
		    'form'  => new CActiveForm(),
	    ));
    }

	/**
	 * @param $collection
	 */
    public function _customerListControllerActionSaveData($collection)
    {
	    if (Yii::app()->controller->id != 'lists' || !$collection->success) {
		    return;
	    }

	    $model = $this->getListFormModel();
    	$model->attributes = Yii::app()->request->getPost($model->modelName);
	    
	    if (!$model->save()) {
		    $collection->success = false;
	    }
    }

    /**
     * @return mixed
     */
    protected function getRecaptchaResponse()
    {
        $request  = Yii::app()->request;
        $response = AppInitHelper::simpleCurlPost('https://www.google.com/recaptcha/api/siteverify', array(
            'secret'   => Yii::app()->params['extensions.recaptcha.data.secret_key'],
            'response' => $request->getPost('g-recaptcha-response'),
            'remoteip' => $request->getUserHostAddress(),
        ));
        return CJSON::decode($response['message']);
    }

	/**
	 * @return RecaptchaExtListForm
	 */
    protected function getListFormModel()
    {
	    $model = new RecaptchaExtListForm();
	    $model->list_uid = Yii::app()->request->getQuery('list_uid');
	    $model->populate();
	    
	    return $model;
    }
}