<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Payment gateway - Paypal
 * 
 * Retrieve payments using paypal
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Paypal
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class PaymentGatewayPaypalExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Payment gateway - Paypal';
    
    // description of the extension as shown in backend panel
    public $description = 'Retrieve payments using paypal';
    
    // current version of this extension
    public $version = '1.0';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';
    
    // in which apps this extension is allowed to run
    public $allowedApps = array('customer', 'backend');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // the extension model
    protected $_extModel;
    
    // run the extension
    public function run()
    {
        Yii::import('ext-payment-gateway-paypal.common.models.*');
        
        if ($this->isAppName('backend')) {
            
            // handle all backend related tasks
            $this->backendApp();
        
        } elseif ($this->isAppName('customer') && $this->getOption('status', 'disabled') == 'enabled') {
        
            // handle all customer related tasks
            $this->customerApp();
        }
    }
    
    // Add the landing page for this extension (settings/general info/etc)
    public function getPageUrl()
    {
        return Yii::app()->createUrl('payment_gateway_ext_paypal/index');
    }
    
    // handle all backend related tasks
    protected function backendApp()
    {
        $hooks = Yii::app()->hooks;
        
        // register the url rule to resolve the extension page.
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_paypal/index', 'pattern' => 'payment-gateways/paypal'),
            array('payment_gateway_ext_paypal/<action>', 'pattern' => 'payment-gateways/paypal/*'),
        ));
        
        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_paypal'] = array(
            'class'     => 'ext-payment-gateway-paypal.backend.controllers.Payment_gateway_ext_paypalController',
            'extension' => $this,
        );
        
        // register the gateway in the list of available gateways.
        $hooks->addFilter('backend_payment_gateways_display_list', array($this, '_registerGatewayForBackendDisplay'));
    }
    
    // register the gateway in the available gateways list
    public function _registerGatewayForBackendDisplay(array $registeredGateways = array())
    {
        if (isset($registeredGateways['paypal'])) {
            return $registeredGateways;
        }
        
        $registeredGateways['paypal'] = array(
            'id'            => 'paypal',
            'name'          => Yii::t('ext_payment_gateway_paypal', 'Paypal'),
            'description'   => Yii::t('ext_payment_gateway_paypal', 'Retrieve payments using paypal'),
            'status'        => $this->getOption('status', 'disabled'),
            'sort_order'    => (int)$this->getOption('sort_order', 1),
            'page_url'      => $this->getPageUrl(),
        );
        
        return $registeredGateways;
    }
    
    // handle all customer related tasks
    protected function customerApp()
    {
        $hooks = Yii::app()->hooks;
        
        // import the utils
        Yii::import('ext-payment-gateway-paypal.customer.components.utils.*');
        
        // register the url rule to resolve the ipn request.
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_paypal/ipn', 'pattern' => 'payment-gateways/paypal/ipn'),
        ));
        
        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_paypal'] = array(
            'class'     => 'ext-payment-gateway-paypal.customer.controllers.Payment_gateway_ext_paypalController',
            'extension' => $this,
        );
        
        // set the controller unprotected so paypal can post freely
        $unprotected = (array)Yii::app()->params->itemAt('unprotectedControllers');
        array_push($unprotected, 'payment_gateway_ext_paypal');
        Yii::app()->params->add('unprotectedControllers', $unprotected);
        
        // remove the csrf token validation
        $request = Yii::app()->request;
        if ($request->isPostRequest && $request->enableCsrfValidation) {
            $url = Yii::app()->urlManager->parseUrl($request);
            $routes = array('price_plans', 'payment_gateway_ext_paypal/ipn');
            foreach ($routes as $route) {
                if (strpos($url, $route) === 0) {
                    Yii::app()->detachEventHandler('onBeginRequest', array($request, 'validateCsrfToken'));
                    Yii::app()->attachEventHandler('onBeginRequest', array($this, 'validateCsrfToken'));
                    break;
                }   
            }
        }

        // hook into drop down list and add the paypal option
        $hooks->addFilter('customer_price_plans_payment_methods_dropdown', array($this, '_registerGatewayInCustomerDropDown'));
    }
    
    // this replacement is needed to avoid csrf token validation and other errors
    public function validateCsrfToken()
    {
        Yii::app()->request->enableCsrfValidation = false;
    }
    
    // register the assets for customer area
    public function registerCustomerAssets()
    {
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__).'/assets/customer', false, -1, MW_DEBUG);
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/js/payment-form.js');
    }
    
    // this is called by the customer app to process the payment
    // must be implemented by all payment gateways
    public function getPaymentHandler()
    {
        return Yii::createComponent(array(
            'class' => 'ext-payment-gateway-paypal.customer.components.utils.PaypalPaymentHandler',
        ));
    }
    
    // extension main model
    public function getExtModel()
    {
        if ($this->_extModel !== null) {
            return $this->_extModel;
        }
        
        $this->_extModel = new PaymentGatewayPaypalExtModel();
        return $this->_extModel->setExtensionInstance($this)->populate();
    }

    //
    public function _registerGatewayInCustomerDropDown($paymentMethods)
    {
        if (isset($paymentMethods['paypal'])) {
            return $paymentMethods;
        }
        $paymentMethods['paypal'] = Yii::t('ext_payment_gateway_paypal', 'Paypal');
        return $paymentMethods;
    }
}