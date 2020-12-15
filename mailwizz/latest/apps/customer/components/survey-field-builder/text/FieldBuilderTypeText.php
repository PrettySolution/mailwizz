<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeText
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class FieldBuilderTypeText extends FieldBuilderType
{
    public function run()
    {
        $apps       = Yii::app()->apps;
        $hooks      = Yii::app()->hooks;
        $baseAlias  = 'customer.components.survey-field-builder.text.behaviors.FieldBuilderTypeText';
        $controller = Yii::app()->getController();

        // since this is a widget always running inside a controller, there is no reason for this to not be set.
        if (!$controller) {
            return;
        }
        
        $this->attachBehaviors(array(
            '_crud' => array(
                'class' => $baseAlias . 'Crud',
            ),
            '_responder' => array(
                'class' => $baseAlias . 'Responder',
            )
        ));
        
        if ($apps->isAppName('customer')) {
            
            if (in_array($controller->id, array('survey_fields'))) {
                // create/view/update/delete fields
                // this event is triggered only on a post action
                $controller->callbacks->onSurveyFieldsSave = array($this->_crud, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onSurveyFieldsDisplay = array($this->_crud, '_displayFields');
            } elseif (in_array($controller->id, array('survey_responders'))) {
                // this event is triggered only on a post action
                $controller->callbacks->onResponderSave = array($this->_responder, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onResponderFieldsDisplay = array($this->_responder, '_displayFields');
            }
        
        } elseif ($apps->isAppName('frontend')) {
            
            if (in_array($controller->id, array('surveys'))) {
                // this event is triggered only on a post action
                $controller->callbacks->onResponderSave = array($this->_responder, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onResponderFieldsDisplay = array($this->_responder, '_displayFields');
            }
            
        }
    }
    
    public function _addInputErrorClass(CEvent $event)
    {
        if ($event->sender->owner->hasErrors($event->params['attribute'])) {
            if (!isset($event->params['htmlOptions']['class'])) {
                $event->params['htmlOptions']['class'] = '';
            }
            $event->params['htmlOptions']['class'] .= ' error';
        }
    }
    
    public function _addFieldNameClass(CEvent $event)
    {
        if (!isset($event->params['htmlOptions']['class'])) {
            $event->params['htmlOptions']['class'] = '';
        }
        $event->params['htmlOptions']['class'] .= ' field-' . strtolower($event->sender->owner->field->field_id) . ' field-type-' . strtolower($event->sender->owner->field->type->identifier);
    }
    
}