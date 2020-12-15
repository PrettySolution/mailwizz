<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeMultiselectSubscriber
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
 * @since 1.3.4.5
 */
 
class FieldBuilderTypeMultiselectSubscriber extends CBehavior
{
    // callback to save the fields when the form is submitted
    public function _saveFields(CEvent $event)
    {
        $models = array();
        
        $fieldType   = $this->owner->getFieldType();
        $list        = $this->owner->getList();
        $subscriber  = $this->owner->getSubscriber();
        $typeName    = $fieldType->identifier;
        $request     = Yii::app()->request;
        $valueModels = $this->getValueModels();
        $fields      = array();
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }
        
        // run validation so that fields will get the errors if any.
        foreach ($valueModels as $models) {   
            $validModels = $invalidModels = array();
            foreach ($models as $model) {
                if (!$model->validate()) {
                    $invalidModels[] = $model;
                } else {
                    $validModels[] = $model;
                }
            }
            if (count($validModels) == 0) {
                foreach ($invalidModels as $model) {
                    $this->owner->errors[] = array(
                        'show'      => false, 
                        'message'   => $model->shortErrors->getAllAsString()
                    );
                }
            } else {
                foreach ($models as $model) {
                    $model->clearErrors();
                }
            }
            unset($validModels, $invalidModels);
            $fields[] = $this->buildFieldArray($models);
        }
        
        // make the fields available
        $event->params['fields'][$typeName] = $fields;
        
        // do the actual saving of fields if there are no errors.
        if (empty($this->owner->errors)) {
            foreach ($valueModels as $models) {
                foreach ($models as $model) {
                    if (strlen(trim((string)$model->value)) == 0) {
                        if (!$model->isNewRecord) {
                            $model->delete();
                        }
                        continue;
                    }
                    $model->save(false);
                }
            }    
        }
    }
    
    // callback to display the fields before the form is submitted.
    public function _displayFields(CEvent $event)
    {
        $fieldType   = $this->owner->getFieldType();
        $typeName    = $fieldType->identifier;
        $list        = $this->owner->getList();
        
        // fields created in the save action.
        if (isset($event->params['fields'][$typeName]) && is_array($event->params['fields'][$typeName])) {
            return;
        }
        
        if (!isset($event->params['fields'][$typeName]) || !is_array($event->params['fields'][$typeName])) {
            $event->params['fields'][$typeName] = array();
        }
        
        $valueModels = $this->getValueModels();
        $fields      = array();

        foreach ($valueModels as $models) {
            $fields[] = $this->buildFieldArray($models);
        }

        $event->params['fields'][$typeName] = $fields;
    }
    
    // helper method to build the field values.
    protected function getValueModels()
    {
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $subscriber = $this->owner->getSubscriber();
        $request    = Yii::app()->request;
        
        $models = ListField::model()->findAllByAttributes(array(
            'type_id' => (int)$fieldType->type_id,
            'list_id' => (int)$list->list_id,
        ));
        
        $valueModels = array();
        foreach ($models as $model) {
            $_valueModels = array();
            $modelOptions = !empty($model->options) ? $model->options : array();
            $defaultValue = !empty($model->default_value) ? array_map(array('ListField', 'parseDefaultValueTags'), explode(',', $model->default_value)) : array();
            $defaultValue = array_map('trim', $defaultValue);
            
            $hasOptionsSet = ListFieldValue::model()->countByAttributes(array(
                'field_id'      => (int)$model->field_id,
                'subscriber_id' => (int)$subscriber->subscriber_id,
            ));
            
            foreach ($modelOptions as $modelOption) {
                $valueModel = ListFieldValue::model()->findByAttributes(array(
                    'field_id'      => (int)$model->field_id,
                    'subscriber_id' => (int)$subscriber->subscriber_id,
                    'value'         => $modelOption->value,
                ));
                if (empty($valueModel)) {
                    $valueModel = new ListFieldValue();
                }
                
                $valueModel->onAttributeLabels = array($this, '_setCorrectLabel');
                $valueModel->onRules = array($this, '_setCorrectValidationRules');
                $valueModel->onAttributeHelpTexts = array($this, '_setCorrectHelpText');
                $valueModel->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addInputErrorClass');
                $valueModel->fieldDecorator->onHtmlOptionsSetup = array($this->owner, '_addFieldNameClass');
                
                $postValues  = (array)$request->getPost($model->tag, array());
                if ($request->isPostRequest) {
                    $foundValue = false;
                    foreach ($postValues as $val) {
                        if ($val == $modelOption->value) {
                            $valueModel->value = $val;
                            $foundValue = true;
                            break;
                        }
                    }
                    if (!$foundValue) {
                        $valueModel->value = null;
                    }
                } else {
                    if (!$hasOptionsSet && in_array($modelOption->value, $defaultValue)) {
                        $valueModel->value = $modelOption->value;
                    }    
                }
                
                // assign props
                $valueModel->field          = $model;
                $valueModel->field_id       = $model->field_id;
                $valueModel->subscriber_id  = $subscriber->subscriber_id;

                $_valueModels[] = $valueModel;
            }

            $valueModels[] = $_valueModels;
        }
        
        return $valueModels;
    }
    
    protected function buildFieldArray($models)
    {
        if (empty($models)) {
            return array(
                'sort_order' => -100, 
                'field_html' => null
            );
        }
        
        $field      = $models[0]->field;
        $fieldHtml  = null;
        $viewFile   = realpath(dirname(__FILE__) . '/../views/field-display.php');
        $options    = array();
        
        if ($field->required != 'yes') {
            $options[''] = Yii::t('app', 'Please choose');
        }
        
        if (!empty($field->options)) {
            foreach ($field->options as $option) {
                $options[$option->value] = $option->name;
            }
        }
        
        $values = array();
        foreach ($models as $model) {
	        if (strlen(trim((string)$model->value)) == 0) {
		        continue;
	        }
            $values[] = $model->value;
        }
        
        // NOTE: maybe this should go into the view file with a display:none style ? 
        if ($field->visibility == ListField::VISIBILITY_VISIBLE || Yii::app()->apps->isAppName('customer')) {
            $fieldHtml = $this->owner->renderInternal($viewFile, compact('model', 'field', 'values', 'options'), true);
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
        // get the CList instance of rules.
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