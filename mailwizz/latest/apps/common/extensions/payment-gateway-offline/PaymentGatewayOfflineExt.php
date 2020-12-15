<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Payment gateway - Offline
 * 
 * Retrieve payments using offline payments
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Offline
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class PaymentGatewayOfflineExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Payment gateway - Offline';
    
    // description of the extension as shown in backend panel
    public $description = 'Retrieve payments offline';
    
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
        Yii::import('ext-payment-gateway-offline.common.models.*');
        
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
        return Yii::app()->createUrl('payment_gateway_ext_offline/index');
    }
    
    // handle all backend related tasks
    protected function backendApp()
    {
        $hooks = Yii::app()->hooks;
        
        // register the url rule to resolve the extension page.
        Yii::app()->urlManager->addRules(array(
            array('payment_gateway_ext_offline/index', 'pattern' => 'payment-gateways/offline'),
            array('payment_gateway_ext_offline/<action>', 'pattern' => 'payment-gateways/offline/*'),
        ));
        
        // add the backend controller
        Yii::app()->controllerMap['payment_gateway_ext_offline'] = array(
            'class'     => 'ext-payment-gateway-offline.backend.controllers.Payment_gateway_ext_offlineController',
            'extension' => $this,
        );
        
        // register the gateway in the list of available gateways.
        $hooks->addFilter('backend_payment_gateways_display_list', array($this, '_registerGatewayForBackendDisplay'));
    }
    
    // register the gateway in the available gateways list
    public function _registerGatewayForBackendDisplay(array $registeredGateways = array())
    {
        if (isset($registeredGateways['offline'])) {
            return $registeredGateways;
        }
        
        $registeredGateways['offline'] = array(
            'id'            => 'offline',
            'name'          => Yii::t('ext_payment_gateway_offline', 'Offline'),
            'description'   => Yii::t('ext_payment_gateway_offline', 'Retrieve payments offline'),
            'status'        => $this->getOption('status', 'disabled'),
            'sort_order'    => (int)$this->getOption('sort_order', 2),
            'page_url'      => $this->getPageUrl(),
        );
        
        return $registeredGateways;
    }
    
    // handle all customer related tasks
    protected function customerApp()
    {
        $hooks = Yii::app()->hooks;
        
        // import the utils
        Yii::import('ext-payment-gateway-offline.customer.components.utils.*');

        // hook into drop down list and add the offline option
        $hooks->addFilter('customer_price_plans_payment_methods_dropdown', array($this, '_registerGatewayInCustomerDropDown'));
    }

    // this is called by the customer app to process the payment
    // must be implemented by all payment gateways
    public function getPaymentHandler()
    {
        return Yii::createComponent(array(
            'class'  => 'ext-payment-gateway-offline.customer.components.utils.OfflinePaymentHandler',
        ));
    }
    
    // extension main model
    public function getExtModel()
    {
        if ($this->_extModel !== null) {
            return $this->_extModel;
        }
        
        $this->_extModel = new PaymentGatewayOfflineExtModel();
        return $this->_extModel->setExtensionInstance($this)->populate();
    }

    //
    public function _registerGatewayInCustomerDropDown($paymentMethods)
    {
        if (isset($paymentMethods['offline'])) {
            return $paymentMethods;
        }
        $paymentMethods['offline'] = Yii::t('ext_payment_gateway_offline', 'Offline payment');
        return $paymentMethods;
    }
}