<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List form custom redirect extension
 * 
 * Will add custom redirect for list forms.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class ListFormCustomRedirectExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'List form custom redirect';
    
    // description of the extension as shown in backend panel
    public $description = 'Will add custom redirect for list forms where applicable.';
    
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
        'subscribe_pending'     => 'subscribe-pending',
        'subscribe_confirm'     => 'subscribe-confirm',
        'unsubscribe_confirm'   => 'unsubscribe-confirm'
    );
    
    // run the extension
    public function run()
    {
        if ($this->isAppName('customer')) {
            Yii::app()->hooks->addAction('after_active_form_fields', array($this, '_insertCustomerField'));
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_saveCustomerData'));
        } elseif ($this->isAppName('frontend')) {
            Yii::app()->hooks->addAction('frontend_controller_lists_before_render', array($this, '_redirectIfNeeded'));
        }
    }
    
    public function beforeEnable()
    {
        $db = Yii::app()->getDb();
        $db->createCommand('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0')->execute();
        $db->createCommand('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""')->execute();
        
        $db->createCommand('
        CREATE TABLE IF NOT EXISTS `{{list_form_custom_redirect}}` (
          `redirect_id` INT NOT NULL AUTO_INCREMENT,
          `list_id` INT(11) NOT NULL,
          `type_id` INT(11) NOT NULL,
          `url` TEXT NOT NULL,
          `timeout` INT NOT NULL DEFAULT 0,
          `date_added` DATETIME NOT NULL,
          `last_updated` DATETIME NOT NULL,
          PRIMARY KEY (`redirect_id`),
          INDEX `fk_list_form_custom_redirect_list1_idx` (`list_id` ASC),
          INDEX `fk_list_form_custom_redirect_list_page_type1_idx` (`type_id` ASC),
          CONSTRAINT `fk_list_form_custom_redirect_list1`
            FOREIGN KEY (`list_id`)
            REFERENCES `{{list}}` (`list_id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION,
          CONSTRAINT `fk_list_form_custom_redirect_list_page_type1`
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
        
        $db->createCommand('DROP TABLE IF EXISTS `{{list_form_custom_redirect}}`')->execute();
        
        $db->createCommand('SET SQL_MODE=@OLD_SQL_MODE')->execute();
        $db->createCommand('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS')->execute();
        $db->createCommand('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS')->execute();
        
        return true;
    }
    
    public function _insertCustomerField(CAttributeCollection $data)
    {
        $controller = $data->controller;
        if ($controller->id != 'list_page' || $controller->action->id != 'index') {
            return;
        }
        
        if (!in_array($controller->data->pageType->slug, array_values($this->actionToPageType))) {
            return;
        }
        
        Yii::import($this->getPathAlias() . '.models.*');
        
        if (!$this->getData('model')) {
            $model = ListFormCustomRedirect::model()->findByAttributes(array(
                'list_id'   => $controller->data->list->list_id,
                'type_id'   => $controller->data->pageType->type_id,
            ));
            
            if (empty($model)) {
                $model = new ListFormCustomRedirect();
            }
            
            $this->setData('model', $model);
        }
        
        $model = $this->getData('model');
        $model->list_id = $controller->data->list->list_id;
        $model->type_id = $controller->data->pageType->type_id;
        
        $form = $data->form;
        
        $controller->renderInternal(dirname(__FILE__).'/views/_form.php', compact('model', 'form'));
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
        
        if (!$this->getData('model')) {
            $model = ListFormCustomRedirect::model()->findByAttributes(array(
                'list_id'   => $data->list->list_id,
                'type_id'   => $data->pageType->type_id,
            ));
            
            if (empty($model)) {
                $model = new ListFormCustomRedirect();
            }
            
            $this->setData('model', $model);
        }
        
        $model = $this->getData('model');
        $model->attributes  = (array)Yii::app()->request->getPost($model->modelName, array());
        $model->url         = isset(Yii::app()->params['POST'][$model->modelName]['url']) ? str_replace('&amp;', '&', strip_tags(Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['url']))) : null;
        $model->list_id     = $data->list->list_id;
        $model->type_id     = $data->pageType->type_id;
        
        if (!$model->save()) {
            
            // prevent redirect
            $data->success = false;
            
            // remove success messages and add ours
            Yii::app()->notify->clearSuccess()->addError(Yii::t('app', 'Your form contains errors, please correct them and try again.'));
        }
    }
    
    public function _redirectIfNeeded($view)
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
        
        $redirect = ListFormCustomRedirect::model()->findByAttributes(array(
            'list_id'   => $list->list_id,
            'type_id'   => $pageType->type_id,
        ));
        
        if (empty($redirect) || empty($redirect->url)) {
            return;
        }
        
        // since 1.3.5, allow using custom subscriber tags in redirect url
        $subscriber_uid = Yii::app()->request->getQuery('subscriber_uid');
        if (!empty($subscriber_uid) && strpos($redirect->url, '[') !== false && strpos($redirect->url, ']') !== false) {
            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'        => $list->list_id,
                'subscriber_uid' => $subscriber_uid,
            ));
            if (!empty($subscriber)) {
                // fake it so we can use CampaignHelper class
                $campaign = new Campaign();
                $campaign->list_id = $list->list_id;
                $campaign->addRelatedRecord('list', $list, false);
                $searchReplace = CampaignHelper::getSubscriberFieldsSearchReplace($redirect->url, $campaign, $subscriber);
                $redirect->url = str_replace(array_keys($searchReplace), array_map('urlencode', array_values($searchReplace)), $redirect->url);
                unset($campaign);
            }
        }
        
        if (!FilterVarHelper::url($redirect->url)) {
            return;
        }
        
        if ($redirect->timeout == 0) {
            Yii::app()->request->redirect($redirect->url);
        }
        
        Yii::app()->clientScript->registerMetaTag($redirect->timeout .';'.$redirect->url, null, 'refresh');
    }
}