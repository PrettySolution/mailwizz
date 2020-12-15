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
 * @since 1.7.8
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
        $survey     = $this->owner->getSurvey();
        $typeName   = $fieldType->identifier;
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }

        $postModels = Yii::app()->request->getPost('SurveyFieldConsentCheckbox', array());
        if (!isset($postModels[$typeName]) || !is_array($postModels[$typeName])) {
            $postModels[$typeName] = array();
        }
        
        $models = array();
        foreach ($postModels[$typeName] as $index => $attributes) {
            $model = null;
            if (!empty($attributes['field_id'])) {
                $model = SurveyFieldConsentCheckbox::model()->findByAttributes(array(
                    'field_id'  => (int)$attributes['field_id'],
                    'type_id'   => (int)$fieldType->type_id,
                    'survey_id' => (int)$survey->survey_id,
                ));
            }
            
            if (isset($attributes['field_id'])) {
                unset($attributes['field_id']);
            }
            
            if (empty($model)) {
                $model = new SurveyFieldConsentCheckbox();
            }
            
            $model->attributes = $attributes;
            $model->type_id    = $fieldType->type_id;
            $model->survey_id  = $survey->survey_id;

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
            $criteria->compare('survey_id', $survey->survey_id);
            $criteria->compare('type_id', $fieldType->type_id);
            $criteria->addNotInCondition('field_id', $modelsToKeep);
            SurveyFieldConsentCheckbox::model()->deleteAll($criteria);
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
        $survey     = $this->owner->getSurvey();
        $typeName   = $fieldType->identifier;
        
        // register the add button.
        $hooks->addAction('customer_controller_survey_fields_render_buttons', array($this, '_renderAddButton'));
        
        // register the javascript template
        $hooks->addAction('customer_controller_survey_fields_after_form', array($this, '_registerJavascriptTemplate'));
        
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

        $models = SurveyFieldConsentCheckbox::model()->findAllByAttributes(array(
            'type_id'   => (int)$fieldType->type_id,
            'survey_id' => (int)$survey->survey_id,
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
        $survey     = $this->owner->getSurvey();
        $typeName   = $fieldType->identifier;

        // so that it increments properly!
        $index = $this->owner->getIndex();

        $viewFile = realpath(dirname(__FILE__) . '/../views/field-tpl.php');
        $model->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addInputErrorClass');
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addReadOnlyAttributes');

        return array(
            'sort_order' => (int)$model->sort_order,
            'field_html' => $this->owner->renderInternal($viewFile, compact('model', 'index', 'fieldType', 'survey'), true),
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
        $model     = new SurveyFieldConsentCheckbox();
        $fieldType = $this->owner->getFieldType();
        $survey    = $this->owner->getSurvey();

        // default view file
        $viewFile = realpath(dirname(__FILE__) . '/../views/field-tpl-js.php');

        // and render
        $this->owner->renderInternal($viewFile, compact('model', 'fieldType', 'survey'));
    }

    /**
     * @param CEvent $event
     */
    public function _addReadOnlyAttributes(CEvent $event)
    {
    }
}