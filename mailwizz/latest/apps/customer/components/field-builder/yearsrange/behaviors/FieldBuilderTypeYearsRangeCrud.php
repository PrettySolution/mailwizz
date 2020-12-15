<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeYearsRangeCrud
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
 * @since 1.7.0
 */
 
class FieldBuilderTypeYearsRangeCrud extends CBehavior
{
	/**
	 * @param CEvent $event
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
        
        $postModels = Yii::app()->request->getPost('ListField', array());
        if (!isset($postModels[$typeName]) || !is_array($postModels[$typeName])) {
            $postModels[$typeName] = array();
        }
        
        $models = array();
        
        foreach ($postModels[$typeName] as $index => $attributes) {
            $model = null;
            if (!empty($attributes['field_id'])) {
                $model = ListField::model()->findByAttributes(array(
                    'field_id'  => (int)$attributes['field_id'],
                    'type_id'   => (int)$fieldType->type_id,
                    'list_id'   => (int)$list->list_id,
                ));
            }
            
            if (isset($attributes['field_id'])) {
                unset($attributes['field_id']);
            }
            
            if (empty($model)) {
                $model = new ListField();
            }

	        $this->prepareListFieldModel($model);
                
            $model->attributes = $attributes;
            $model->type_id = $fieldType->type_id;
            $model->list_id = $list->list_id;
            
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
            ListField::model()->deleteAll($criteria);    
        }
        
        $fields = array();
        foreach ($models as $model) {
            $fields[] = $this->buildFieldArray($model);
        }

        $event->params['fields'][$typeName] = $fields;
    }

	/**
	 * @param CEvent $event
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

        $models = ListField::model()->findAllByAttributes(array(
            'type_id' => (int)$fieldType->type_id,
            'list_id' => (int)$list->list_id,
        ));
        
        $fields = array();
        foreach ($models as $model) {
	        $this->prepareListFieldModel($model);
            $fields[] = $this->buildFieldArray($model);
        }

        $event->params['fields'][$typeName] = $fields;
    }

	/**
	 * @param $model
	 *
	 * @return array
	 */
    protected function buildFieldArray($model)
    {
        $hooks      = Yii::app()->hooks;
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        $typeName   = $fieldType->identifier;
        
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
	 * @return void
	 */
    public function _renderAddButton()
    {
        // default view file
        $viewFile = realpath(dirname(__FILE__) . '/../views/add-button.php');

        // and render
        $this->owner->renderInternal($viewFile); 
    }

	/**
	 * @return void
	 */
    public function _registerJavascriptTemplate()
    {
        $model = new ListField();
        $this->prepareListFieldModel($model);
	    
        $fieldType  = $this->owner->getFieldType();
        $list       = $this->owner->getList();
        
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

	/**
	 * @param ListField $model
	 */
    protected function prepareListFieldModel(ListField $model)
    {
	    $model->attachBehavior('_FieldBuilderTypeYearsRangeModelSettersGetters', array(
		    'class' => 'customer.components.field-builder.yearsrange.behaviors.FieldBuilderTypeYearsRangeModelSettersGetters'
	    ));
	    
	    $model->onRules                 = array($this, '_prepareListFieldModelSetRules');
	    $model->onAttributeLabels       = array($this, '_prepareListFieldModelSetLabels');
	    $model->onAttributeHelpTexts    = array($this, '_prepareListFieldModelSetHelpTexts');
    }

	/**
	 * @param CModelEvent $event
	 */
    public function _prepareListFieldModelSetRules(CModelEvent $event)
    {
	    $rules = $event->params['rules'];

        $rules->add(array('yearStart, yearEnd', 'length', 'is' => 4));
        $rules->add(array('yearStart, yearEnd', 'numerical', 'integerOnly' => true, 'min' => $event->sender->yearMin, 'max' => $event->sender->yearMax));
        $rules->add(array('yearStart', 'compare', 'compareAttribute' => 'yearEnd', 'operator' => '<'));
        $rules->add(array('yearEnd', 'compare', 'compareAttribute' => 'yearStart', 'operator' => '>'));
        $rules->add(array('yearStep', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 100));
    }

	/**
	 * @param CModelEvent $event
	 */
    public function _prepareListFieldModelSetLabels(CModelEvent $event)
    {
    	$labels = $event->params['labels'];

	    $labels->add('yearStart', Yii::t('list_fields', 'Year start'));
	    $labels->add('yearEnd', Yii::t('list_fields', 'Year end'));
	    $labels->add('yearStep', Yii::t('list_fields', 'Year step'));
    }

	/**
	 * @param CModelEvent $event
	 */
	public function _prepareListFieldModelSetHelpTexts(CModelEvent $event)
	{
		$texts = $event->params['texts'];

		$texts->add('yearStart', Yii::t('list_fields', 'Decides with which year to start'));
		$texts->add('yearEnd', Yii::t('list_fields', 'Decides with which year to end'));
		$texts->add('yearStep', Yii::t('list_fields', 'Decides the number of years it jump each iteration'));
	}
}