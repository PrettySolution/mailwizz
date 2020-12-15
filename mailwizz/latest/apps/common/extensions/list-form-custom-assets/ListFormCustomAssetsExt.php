<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List form custom assets extension
 * 
 * Will add the ability to add custom assets (css/js) to a form.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class ListFormCustomAssetsExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'List form custom assets';
    
    // description of the extension as shown in backend panel
    public $description = 'Will add the ability to add custom assets (css/js) to a form.';
    
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
        'subscribe_pending'     => 'subscribe-pending',
        'subscribe_confirm'     => 'subscribe-confirm',
        'update_profile'        => 'update-profile',
        'unsubscribe_confirm'   => 'unsubscribe-confirm',
        'unsubscribe'           => 'unsubscribe-form',
    );
    
    // run the extension
    public function run()
    {
        if ($this->isAppName('customer')) {
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_insertCustomerFields'));
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_saveCustomerData'));
            Yii::app()->hooks->addAction('customer_controller_list_page_before_action', array($this, '_loadCustomerAssets'));
        } elseif ($this->isAppName('frontend')) {
            Yii::app()->hooks->addAction('frontend_controller_lists_before_render', array($this, '_loadFrontendAssets'));
        }
    }
    
    public function beforeEnable()
    {
        $db = Yii::app()->getDb();
        $db->createCommand('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""')->execute();
        
        $db->createCommand('
        CREATE TABLE IF NOT EXISTS `{{list_form_custom_asset}}` (
          `asset_id` INT NOT NULL AUTO_INCREMENT,
          `list_id` INT(11) NOT NULL,
          `type_id` INT(11) NOT NULL,
          `asset_url` TEXT NOT NULL,
          `asset_type` VARCHAR(10) NOT NULL,
          `date_added` DATETIME NOT NULL,
          `last_updated` DATETIME NOT NULL,
          PRIMARY KEY (`asset_id`),
          INDEX `fk_list_form_custom_asset_list1_idx` (`list_id` ASC),
          INDEX `fk_list_form_custom_asset_list_page_type1_idx` (`type_id` ASC),
          CONSTRAINT `fk_list_form_custom_asset_list1`
            FOREIGN KEY (`list_id`)
            REFERENCES `{{list}}` (`list_id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION,
          CONSTRAINT `fk_list_form_custom_asset_list_page_type1`
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
        
        $db->createCommand('DROP TABLE IF EXISTS `{{list_form_custom_asset}}`')->execute();
        
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
            $models = ListFormCustomAsset::model()->findAllByAttributes(array(
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
        $model = new ListFormCustomAsset();
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
        
        ListFormCustomAsset::model()->deleteAllByAttributes(array(
            'list_id'   => $data->list->list_id,
            'type_id'   => $data->pageType->type_id,
        ));
            
        $postModels = (array)Yii::app()->request->getPost('ListFormCustomAsset', array());
        $models     = array();
        $errors     = false;
        
        foreach ($postModels as $index => $attributes) {
            $model = new ListFormCustomAsset();
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
    
    public function _loadFrontendAssets($view)
    {
        $controller = Yii::app()->getController();
        $action     = $controller->getAction();
        
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
        
        $assets = ListFormCustomAsset::model()->findAllByAttributes(array(
            'list_id'   => $list->list_id,
            'type_id'   => $pageType->type_id,
        ));
        
        if (empty($assets)) {
            return;
        }
        
        foreach ($assets as $asset) {
            if ($asset->asset_type == ListFormCustomAsset::ASSET_TYPE_CSS) {
                $controller->getData('pageStyles')->add(array('src' => $asset->asset_url, 'priority' => 1000));
            } elseif ($asset->asset_type == ListFormCustomAsset::ASSET_TYPE_JS) {
                $controller->getData('pageScripts')->add(array('src' => $asset->asset_url, 'priority' => 1000));
            }
        }
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
}