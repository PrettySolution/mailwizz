<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFieldsControllerCallbacksBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ListFieldsControllerCallbacksBehavior extends CBehavior
{
    // event handlers
    public function _orderFields(CEvent $event) 
    {
        $fields = array();
        $sort   = array();
        
        foreach ($event->params['fields'] as $type => $_fields) {
            foreach ($_fields as $index => $field) {
                if (!isset($field['sort_order'], $field['field_html'])) {
                    unset($event->params['fields'][$type][$index]);
                    continue;
                }
                $fields[] = $field;
                $sort[] = (int)$field['sort_order'];    
            }
        }
        
        array_multisort($sort, $fields);
        
        return $event->params['fields'] = $fields;
    }
    
    public function _collectAndShowErrorMessages(CEvent $event)
    {
        $instances = isset($event->params['instances']) ? (array)$event->params['instances'] : array();
        
        // collect and show visible errors.
        foreach ($instances as $instance) {
            if (empty($instance->errors)) {
                continue;
            }
            foreach ($instance->errors as $error) {
                if (empty($error['show']) || empty($error['message'])) {
                    continue;
                }
                Yii::app()->notify->addError($error['message']);
            }
        }
    }

    // events list for CRUD
    public function onListFieldsSave(CEvent $event)
    {
        $this->raiseEvent('onListFieldsSave', $event);
    }
    
    public function onListFieldsDisplay(CEvent $event)
    {
        $this->raiseEvent('onListFieldsDisplay', $event);
    }
    
    public function onListFieldsSorting(CEvent $event)
    {
        $this->raiseEvent('onListFieldsSorting', $event);
    }
    
    public function onListFieldsSaveSuccess(CEvent $event)
    {
        $this->raiseEvent('onListFieldsSaveSuccess', $event);
    }
    
    public function onListFieldsSaveError(CEvent $event)
    {
        $this->raiseEvent('onListFieldsSaveError', $event);
    }
    
    // events list for Subscriber
    public function onSubscriberFieldsSorting(CEvent $event)
    {
        $this->raiseEvent('onSubscriberFieldsSorting', $event);
    }
    
    public function onSubscriberSave(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSave', $event);
    }
    
    public function onSubscriberFieldsDisplay(CEvent $event)
    {
        $this->raiseEvent('onSubscriberFieldsDisplay', $event);
    }

    public function onSubscriberSaveSuccess(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSaveSuccess', $event);
    }
    
    public function onSubscriberSaveError(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSaveError', $event);
    }

}