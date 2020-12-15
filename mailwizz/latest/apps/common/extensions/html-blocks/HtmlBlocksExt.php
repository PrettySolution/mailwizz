<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HtmlBlocksExt
 * 
 * Inject html blocks in various app sections
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Paypal
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class HtmlBlocksExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Html blocks';
    
    // description of the extension as shown in backend panel
    public $description = 'Inject html blocks in various app sections';
    
    // current version of this extension
    public $version = '1.0';
    
    // minimum app version
    public $minAppVersion = '1.3.4.5';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';
    
    // in which apps this extension is allowed to run
    public $allowedApps = array('backend', 'customer');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // the extension model
    protected $_extModel;
    
    // run the extension
    public function run()
    {
        Yii::import('ext-html-blocks.common.models.*');
        
        if ($this->isAppName('backend')) {
            
            // handle all backend related tasks
            $this->backendApp();
        
        } elseif ($this->isAppName('customer')) {
        
            // handle all customer related tasks
            $this->customerApp();
        }
    }
    
    // Add the landing page for this extension (settings/general info/etc)
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_html_blocks/index');
    }
    
    // handle all backend related tasks
    protected function backendApp()
    {
        $hooks = Yii::app()->hooks;
        
        // register the url rule to resolve the extension page.
        Yii::app()->urlManager->addRules(array(
            array('ext_html_blocks/index', 'pattern' => 'extensions/html-blocks'),
            array('ext_html_blocks/<action>', 'pattern' => 'extensions/html-blocks/*'),
        ));
        
        // add the backend controller
        Yii::app()->controllerMap['ext_html_blocks'] = array(
            'class'     => 'ext-html-blocks.backend.controllers.Ext_html_blocksController',
            'extension' => $this,
        );
    }

    // handle all customer related tasks
    protected function customerApp()
    {
        if (!Yii::app()->customer->getId()) {
            return;
        }
        Yii::app()->hooks->addAction('layout_footer_html', array($this, '_customerInjectFooterHtml'));
    }
    
    // extension main model
    public function getExtModel()
    {
        if ($this->_extModel !== null) {
            return $this->_extModel;
        }
        
        $this->_extModel = new HtmlBlocksExtModel();
        return $this->_extModel->setExtensionInstance($this)->populate();
    }
    
    // callback
    public function _customerInjectFooterHtml($controller)
    {
        echo $this->getOption('customer_footer');
    }
}