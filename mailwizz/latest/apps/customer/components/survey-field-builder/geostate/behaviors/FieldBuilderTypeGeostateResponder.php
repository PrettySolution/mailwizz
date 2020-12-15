<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeGeostateResponder
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
 
class FieldBuilderTypeGeostateResponder extends CBehavior
{
    // callback to save the fields when the form is submitted
    public function _saveFields(CEvent $event)
    {
        $models = array();
        
        $fieldType   = $this->owner->getFieldType();
        $survey      = $this->owner->getSurvey();
        $responder   = $this->owner->getResponder();
        $typeName    = $fieldType->identifier;
        $request     = Yii::app()->request;
        $valueModels = $this->getValueModels();
        $fields      = array();
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }
        
        // run validation so that fields will get the errors if any.
        foreach ($valueModels as $model) {
            if (!$model->validate()) {
                $this->owner->errors[] = array(
                    'show'      => false, 
                    'message'   => $model->shortErrors->getAllAsString()
                );
            }
            $fields[] = $this->buildFieldArray($model);
        }
        
        // make the fields available
        $event->params['fields'][$typeName] = $fields;
        
        // do the actual saving of fields if there are no errors.
        if (empty($this->owner->errors)) {
            foreach ($valueModels as $model) {
                $model->save(false);
            }    
        }
    }
    
    // callback to display the fields before the form is submitted.
    public function _displayFields(CEvent $event)
    {
        $fieldType   = $this->owner->getFieldType();
        $typeName    = $fieldType->identifier;
        $survey      = $this->owner->getSurvey();
        
        // fields created in the save action.
        if (isset($event->params['fields'][$typeName]) && is_array($event->params['fields'][$typeName])) {
            return;
        }

        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }
        
        $valueModels = $this->getValueModels();
        $fields      = array();
        
        foreach ($valueModels as $model) {
            $fields[] = $this->buildFieldArray($model);
        }
        
        $event->params['fields'][$typeName] = $fields;
    }
    
    // helper method to build the field values.
    protected function getValueModels()
    {
        $fieldType = $this->owner->getFieldType();
        $survey    = $this->owner->getSurvey();
        $responder = $this->owner->getResponder();
        $request   = Yii::app()->request;
        
        $models = SurveyField::model()->findAllByAttributes(array(
            'type_id'   => (int)$fieldType->type_id,
            'survey_id' => (int)$survey->survey_id,
        ));
        
        $valueModels = array();
        foreach ($models as $model) {
            $valueModel = SurveyFieldValue::model()->findByAttributes(array(
                'field_id'     => (int)$model->field_id,
                'responder_id' => (int)$responder->responder_id,
            ));
            
            if (empty($valueModel)) {
                $valueModel = new SurveyFieldValue();
            }

            // setup rules and labels here.
            $valueModel->onAttributeLabels = array($this, '_setCorrectLabel');
            $valueModel->onRules = array($this, '_setCorrectValidationRules');
            $valueModel->onAttributeHelpTexts = array($this, '_setCorrectHelpText');
            $valueModel->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addInputErrorClass');
            $valueModel->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addFieldNameClass');

            $geoState = '';
            if ($location = IpLocation::findByIp($request->getUserHostAddress())) {
                $geoState = $location->zone_name;
            }
            
            // assign props
            $valueModel->field         = $model;
            $valueModel->field_id      = $model->field_id;
            $valueModel->responder_id  = $responder->responder_id;
            $valueModel->value         = $geoState;

            $valueModels[] = $valueModel;
        }
        
        return $valueModels;
    }
    
    protected function buildFieldArray($model)
    {
        $field      = $model->field;
        $fieldHtml  = null;
        $viewFile   = realpath(dirname(__FILE__) . '/../views/field-display.php');
        
        // NOTE: maybe this should go into the view file with a display:none style ? 
        if ($field->visibility == SurveyField::VISIBILITY_VISIBLE || Yii::app()->apps->isAppName('customer')) {
            $fieldHtml = $this->owner->renderInternal($viewFile, compact('model', 'field'), true);
        }

        return array(
            'sort_order' => (int)$field->sort_order,
            'field_html' => $fieldHtml,
        );
    }
    
    public function _setCorrectLabel(CModelEvent $event)
    {
        $event->params['labels']['value'] = $event->sender->field->label;    
    }
    
    public function _setCorrectValidationRules(CModelEvent $event)
    {
        // get the Survey instance of rules.
        $rules = $event->params['rules'];
        
        // clear any other rule we have so far
        $rules->clear();
        
        // start adding new rules.
        if ($event->sender->field->required === 'yes') {
            $rules->add(array('value', 'required'));
        }
        
        $rules->add(array('value', 'length', 'max' => 255));
    }

    public function _setCorrectHelpText(CModelEvent $event)
    {
        $event->params['texts']['value'] = $event->sender->field->help_text;
    }
}