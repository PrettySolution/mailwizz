<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeConsentCheckboxCrud
 * 
 * The save action is running inside an active transaction.
 * For fatal errors, an exception must be thrown, otherwise the errors array must be populated.
 * If an exception is thrown, or the errors array is populated, the transaction is rolled back.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
 
class FieldBuilderTypeConsentCheckboxCrud extends CBehavior
{
    /**
     * @param CEvent $event
     * @throws CException
     */
    public function _saveFields(CEvent $event)
    {
        $hooks      = Yii::app()->hooks;
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $typeName   = $fieldType->identifier;
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }

        $postModels = Yii::app()->request->getPost('ListFieldConsentCheckbox', array());
        if (!isset($postModels[$typeName]) || !is_array($postModels[$typeName])) {
            $postModels[$typeName] = array();
        }
        
        $models = array();
        foreach ($postModels[$typeName] as $index => $attributes) {
            $model = null;
            if (!empty($attributes['field_id'])) {
                $model = ListFieldConsentCheckbox::model()->findByAttributes(array(
                    'field_id'  => (int)$attributes['field_id'],
                    'type_id'   => (int)$fieldType->type_id,
                    'list_id'   => (int)$list->list_id,
                ));
            }
            
            if (isset($attributes['field_id'])) {
                unset($attributes['field_id']);
            }
            
            if (empty($model)) {
                $model = new ListFieldConsentCheckbox();
            }
            
            $model->attributes = $attributes;
            $model->type_id    = $fieldType->type_id;
            $model->list_id    = $list->list_id;

            $models[] = $model;
        }

        $modelsToKeep = array();
        foreach ($models as $model) {
            if (!$model->save()) {
                $this->owner->errors[] = array(
                    'show'      => false, 
                    'message'   => $model->shortErrors->getAllAsString()
                );
            } else {
                $modelsToKeep[] = $model->field_id;
            }
        }
        
        if (empty($this->owner->errors)) {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', $list->list_id);
            $criteria->compare('type_id', $fieldType->type_id);
            $criteria->addNotInCondition('field_id', $modelsToKeep);
            ListFieldConsentCheckbox::model()->deleteAll($criteria);    
        }
        
        $fields = array();
        foreach ($models as $model) {
            $fields[] = $this->buildFieldArray($model);
        }

        $event->params['fields'][$typeName] = $fields;
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function _displayFields(CEvent $event)
    {
        $hooks      = Yii::app()->hooks;
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $typeName   = $fieldType->identifier;
        
        // register the add button.
        $hooks->addAction('customer_controller_list_fields_render_buttons', array($this, '_renderAddButton'));
        
        // register the javascript template
        $hooks->addAction('customer_controller_list_fields_after_form', array($this, '_registerJavascriptTemplate'));
        
        // register the assets
        $assetsUrl = Yii::app()->assetManager->publish(realpath(dirname(__FILE__) . '/../assets/'), false, -1, MW_DEBUG);
        
        // push the file into the queue.
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/field.js');
        
        // fields created in the save action.
        if (isset($event->params['fields'][$typeName]) && is_array($event->params['fields'][$typeName])) {
            return;
        }
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }

        $models = ListFieldConsentCheckbox::model()->findAllByAttributes(array(
            'type_id' => (int)$fieldType->type_id,
            'list_id' => (int)$list->list_id,
        ));
        
        $fields = array();
        foreach ($models as $model) {
            $fields[] = $this->buildFieldArray($model);
        }

        $event->params['fields'][$typeName] = $fields;
    }

    /**
     * @param $model
     * @return array
     * @throws CException
     */
    protected function buildFieldArray($model)
    {
        $hooks      = Yii::app()->hooks;
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $typeName   = $fieldType->identifier;

        // replace the company name placeholder
        $companyName = !empty($list->company->name) ? $list->company->name : '...';
        $model->consent_text = str_replace('[COMPANY_NAME]', $companyName, $model->consent_text);
        
        // so that it increments properly!
        $index = $this->owner->getIndex();
        
        $viewFile = realpath(dirname(__FILE__) . '/../views/field-tpl.php');
        $model->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addInputErrorClass');
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addReadOnlyAttributes');

        return array(
            'sort_order' => (int)$model->sort_order,
            'field_html' => $this->owner->renderInternal($viewFile, compact('model', 'index', 'fieldType', 'list'), true),
        );
    }

    /**
     * @throws CException
     */
    public function _renderAddButton()
    {
        // default view file
        $viewFile = realpath(dirname(__FILE__) . '/../views/add-button.php');

        // and render
        $this->owner->renderInternal($viewFile); 
    }

    /**
     * @throws CException
     */
    public function _registerJavascriptTemplate()
    {
        $model      = new ListFieldConsentCheckbox();
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        
        // replace the company name placeholder
        $companyName = !empty($list->company->name) ? $list->company->name : '...';
        $model->consent_text = str_replace('[COMPANY_NAME]', $companyName, $model->consent_text);
        
        // default view file
        $viewFile = realpath(dirname(__FILE__) . '/../views/field-tpl-js.php');

        // and render
        $this->owner->renderInternal($viewFile, compact('model', 'fieldType', 'list')); 
    }

    /**
     * @param CEvent $event
     */
    public function _addReadOnlyAttributes(CEvent $event)
    {
    }
    
}