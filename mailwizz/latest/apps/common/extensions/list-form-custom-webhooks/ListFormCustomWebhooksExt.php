<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List form custom webhooks extension
 * 
 * Will add the ability to send back the data from a form to a specified url(s).
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class ListFormCustomWebhooksExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'List form custom webhooks';
    
    // description of the extension as shown in backend panel
    public $description = 'Will add the ability to send back the data from a form to specified url(s).';
    
    // current version of this extension
    public $version = '1.0';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';

    // in which apps this extension is allowed to run
    public $allowedApps = array('customer', 'frontend');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // mapping
    public $actionToPageType = array(
        'subscribe'             => 'subscribe-form',
        'subscribe_confirm'     => 'subscribe-confirm',
        'update_profile'        => 'update-profile',
        'unsubscribe_confirm'   => 'unsubscribe-confirm',
    );
    
    // run the extension
    public function run()
    {
        if ($this->isAppName('customer')) {
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_insertCustomerFields'));
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_saveCustomerData'));
            Yii::app()->hooks->addAction('customer_controller_list_page_before_action', array($this, '_loadCustomerAssets'));
        } elseif ($this->isAppName('frontend')) {
            Yii::app()->hooks->addAction('frontend_controller_lists_before_action', array($this, '_insertCallbacks'));
        }
        
        Yii::app()->hooks->addFilter('models_lists_after_copy_list', array($this, '_modelsListsAfterCopyList'));
    }
    
    public function beforeEnable()
    {
        $db = Yii::app()->getDb();
        $db->createCommand('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""')->execute();
        
        $db->createCommand('
        CREATE TABLE IF NOT EXISTS `{{list_form_custom_webhook}}` (
          `webhook_id` INT NOT NULL AUTO_INCREMENT,
          `list_id` INT(11) NOT NULL,
          `type_id` INT(11) NOT NULL,
          `request_url` TEXT NOT NULL,
          `request_type` VARCHAR(10) NOT NULL,
          `date_added` DATETIME NOT NULL,
          `last_updated` DATETIME NOT NULL,
          PRIMARY KEY (`webhook_id`),
          INDEX `fk_list_form_custom_webhook_list1_idx` (`list_id` ASC),
          INDEX `fk_list_form_custom_webhook_list_page_type1_idx` (`type_id` ASC),
          CONSTRAINT `fk_list_form_custom_webhook_list1`
            FOREIGN KEY (`list_id`)
            REFERENCES `{{list}}` (`list_id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION,
          CONSTRAINT `fk_list_form_custom_webhook_list_page_type1`
            FOREIGN KEY (`type_id`)
            REFERENCES `{{list_page_type}}` (`type_id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION)
        ENGINE = InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
        ')->execute();
        
        $db->createCommand('SET SQL_MODE=@OLD_SQL_MODE')->execute();
        $db->createCommand('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS')->execute();
        $db->createCommand('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS')->execute();
        
        return true;
    }
    
    public function beforeDisable()
    {
        $db = Yii::app()->getDb();
        $db->createCommand('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""')->execute();
        
        $db->createCommand('DROP TABLE IF EXISTS `{{list_form_custom_webhook}}`')->execute();
        
        $db->createCommand('SET SQL_MODE=@OLD_SQL_MODE')->execute();
        $db->createCommand('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS')->execute();
        $db->createCommand('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS')->execute();
        
        return true;
    }
    
    public function _insertCustomerFields(CAttributeCollection $data)
    {
        $controller = $data->controller;
        if ($controller->id != 'list_page' || $controller->action->id != 'index') {
            return;
        }
        
        if (!in_array($controller->data->pageType->slug, array_values($this->actionToPageType))) {
            return;
        }
        
        Yii::import($this->getPathAlias() . '.models.*');
        
        if (!$this->getData('models')) {
            $models = ListFormCustomWebhook::model()->findAllByAttributes(array(
                'list_id'   => $controller->data->list->list_id,
                'type_id'   => $controller->data->pageType->type_id,
            ));
            
            if (empty($models)) {
                $models = array();
            }
            
            $this->setData('models', $models);
        }
        
        $models = $this->getData('models');
        foreach ($models as $model) {
            $model->list_id = $controller->data->list->list_id;
            $model->type_id = $controller->data->pageType->type_id;
        }
        $model = new ListFormCustomWebhook();
        $form  = $data->form;
        
        $controller->renderInternal(dirname(__FILE__).'/views/_form.php', compact('models', 'model', 'form'));
    }
    
    public function _saveCustomerData(CAttributeCollection $data)
    {
        $controller = $data->controller;
        if ($controller->id != 'list_page' || $controller->action->id != 'index') {
            return;
        }
        
        if (!in_array($data->pageType->slug, array_values($this->actionToPageType))) {
            return;
        }
        
        if (!$data->success) {
            return;
        }
        
        Yii::import($this->getPathAlias() . '.models.*');
        
        ListFormCustomWebhook::model()->deleteAllByAttributes(array(
            'list_id'   => $data->list->list_id,
            'type_id'   => $data->pageType->type_id,
        ));
            
        $postModels = (array)Yii::app()->request->getPost('ListFormCustomWebhook', array());
        $models     = array();
        $errors     = false;
        
        foreach ($postModels as $index => $attributes) {
            $model = new ListFormCustomWebhook();
            $model->attributes  = $attributes;
            $model->list_id     = $data->list->list_id;
            $model->type_id     = $data->pageType->type_id;
            if (!$model->save()) {
                $errors = true;
            }
            $models[] = $model;
        }
        
        $this->setData('models', $models);

        if ($errors) {
            
            // prevent redirect
            $data->success = false;
            
            // remove success messages and add ours
            Yii::app()->notify->clearSuccess()->addError(Yii::t('app', 'Your form contains errors, please correct them and try again.'));
        }
    }
    
    public function _insertCallbacks($action)
    {
        if (!in_array($action->id, array_keys($this->actionToPageType))) {
            return;
        }
        
        $list_uid = Yii::app()->request->getQuery('list_uid');
        if (empty($list_uid)) {
            return;
        }
        
        $list = Lists::model()->findByUid($list_uid);
        if (empty($list)) {
            return;
        }
        
        $pageType = ListPageType::model()->findByAttributes(array('slug' => $this->actionToPageType[$action->id]));
        if (empty($pageType)) {
            return;
        }
        
        Yii::import($this->getPathAlias() . '.models.*');
        
        $webhooks = ListFormCustomWebhook::model()->findAllByAttributes(array(
            'list_id'   => $list->list_id,
            'type_id'   => $pageType->type_id,
        ));
        
        if (empty($webhooks)) {
            return;
        }
        
        $this->setData('webhooks', $webhooks);
        $this->setData('pageType', $pageType);
        
        if (!$action->getController()->asa('callbacks')) {
            return;
        }
        
        $action->getController()->callbacks->onSubscriberSaveSuccess = array($this, '_sendData');
    }
    
    public function _sendData(CEvent $event)
    {
        if (!($webhooks = $this->getData('webhooks')) || !($pageType = $this->getData('pageType'))) {
            return;
        }
        
        $actions = array('subscribe', 'subscribe-confirm', 'update-profile', 'unsubscribe-confirm');
        if (!isset($event->params['action']) || !in_array($event->params['action'], $actions)) {
            return;
        }
        
        $data       = array();
        $subscriber = $event->params['subscriber'];
        $list       = $event->params['list'];
        
        $data['action']      = $event->params['action'];
        $data['list']        = $list->getAttributes(array('list_uid', 'name'));
        $data['subscriber']  = $subscriber->getAttributes(array('subscriber_uid', 'email'));
        $data['form_fields'] = Yii::app()->request->getPost(null);

	    $data['optin_history'] = array();
	    if (!empty($subscriber->optinHistory)) {
		    $data['optin_history'] = $subscriber->optinHistory->getAttributes(array(
			    'optin_ip', 'optin_date', 'confirm_ip', 'confirm_date'
		    ));
	    }
	    
        if (isset($data['form_fields'][Yii::app()->request->csrfTokenName])) {
            unset($data['form_fields'][Yii::app()->request->csrfTokenName]);
        }
        
        $data = array('data' => $data);
        
        try {
            foreach ($webhooks as $webhook) {
	            $campaign = new Campaign();
	            $campaign->customer_id = $list->customer_id;
	            $campaign->list_id     = $list->list_id;
	            
	            list(,,$url) = CampaignHelper::parseContent($webhook->request_url, $campaign, $subscriber);
                if ($webhook->request_type == ListFormCustomWebhook::REQUEST_TYPE_POST) {
                    AppInitHelper::simpleCurlPost($url, urldecode(http_build_query($data, '', '&')), 5);
                } elseif ($webhook->request_type == ListFormCustomWebhook::REQUEST_TYPE_GET) {
                    $url .= (strpos($url, '?') === false) ? '?' : '&';
                    $url .= http_build_query($data, '', '&');
                    AppInitHelper::simpleCurlGet($url, 5);
                }
            }
        } catch (Exception $e) {}
    }
    
    public function _loadCustomerAssets($view)
    {
        $controller = Yii::app()->getController();

        if (empty($controller)) {
            return;
        }
        
        $assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets/', false, -1, MW_DEBUG);
        $controller->getData('pageScripts')->add(array('src' => $assetsUrl . '/customer.js', 'priority' => 1000));
    }
    
    public function _modelsListsAfterCopyList($copied, Lists $fromList) 
    {
    	if (!is_object($copied) || !($copied instanceof Lists)) {
    		return $copied;
	    }

	    Yii::import($this->getPathAlias() . '.models.*');

	    $webhooks = ListFormCustomWebhook::model()->findAllByAttributes(array('list_id' => $fromList->list_id));
	    foreach ($webhooks as $webhook) {
		    $webhook = clone $webhook;
		    $webhook->isNewRecord  = true;
		    $webhook->webhook_id   = null;
		    $webhook->list_id      = $copied->list_id;
		    $webhook->date_added   = new CDbExpression('NOW()');
		    $webhook->last_updated = new CDbExpression('NOW()');
		    $webhook->save(false);
	    }
    	
    	return $copied;
    }
}