<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeDate
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
class FieldBuilderTypeDate extends FieldBuilderType
{
    public function run()
    {
        $apps       = Yii::app()->apps;
        $hooks      = Yii::app()->hooks;
        $baseAlias  = 'customer.components.field-builder.date.behaviors.FieldBuilderTypeDate';
        $controller = Yii::app()->getController();

        // since this is a widget always running inside a controller, there is no reason for this to not be set.
        if (!$controller) {
            return;
        }
        
        // register assets
        Yii::app()->clientScript->registerCssFile(Yii::app()->apps->getAppUrl('frontend', 'assets/js/datepicker/css/datepicker3.css', false, true));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getAppUrl('frontend', 'assets/js/datepicker/js/bootstrap-datepicker.js', false, true));
        if ($language = $this->detectLanguage()) {
            Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getAppUrl('frontend', 'assets/js/datepicker/js/locales/bootstrap-datepicker.' . $language . '.js', false, true));
        }
        
        $this->attachBehaviors(array(
            '_crud' => array(
                'class' => $baseAlias . 'Crud',
            ),
            '_subscriber' => array(
                'class' => $baseAlias . 'Subscriber',
            )
        ));
        
        if ($apps->isAppName('customer')) {
            
            if (in_array($controller->id, array('list_fields'))) {
                // create/view/update/delete fields
                // this event is triggered only on a post action
                $controller->callbacks->onListFieldsSave = array($this->_crud, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onListFieldsDisplay = array($this->_crud, '_displayFields');
            } elseif (in_array($controller->id, array('list_subscribers'))) {
                // this event is triggered only on a post action
                $controller->callbacks->onSubscriberSave = array($this->_subscriber, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onSubscriberFieldsDisplay = array($this->_subscriber, '_displayFields');
            }
        
        } elseif ($apps->isAppName('frontend')) {
            
            if (in_array($controller->id, array('lists'))) {
                // this event is triggered only on a post action
                $controller->callbacks->onSubscriberSave = array($this->_subscriber, '_saveFields');
                // this event is triggered always.
                $controller->callbacks->onSubscriberFieldsDisplay = array($this->_subscriber, '_displayFields');
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
        $event->params['htmlOptions']['class'] .= ' field-' . strtolower($event->sender->owner->field->tag) . ' field-type-' . strtolower($event->sender->owner->field->type->identifier);
    }
    
    public function detectLanguage()
    {
        static $detectedLang;
        if ($detectedLang !== null) {
            return $detectedLang;
        }
        $language = str_replace('_', '-', Yii::app()->language);
        if (strpos($language, '-') !== false) {
            $language = explode('-', $language);
            $locale   = array_pop($language);
            $language = implode('-', $language) . '-' . strtoupper($locale);
        }

        $languagesPath = Yii::getPathOfAlias('root.assets.js.datepicker.js.locales.bootstrap-datepicker');
        
        if (is_file($languagesPath . '.' . $language . '.js')) {
            $detectedLang = $language;    
        }
        
        if ($detectedLang === null && strpos($language, '-') !== false) {
            $language = explode('-', $language);
            $language = $language[0];
            if (is_file($languagesPath . '.' . $language.'.js')) {
                $detectedLang = $language;
            }
        }
        
        if ($detectedLang === null) {
            $detectedLang = false;
        }
        
        return $detectedLang;
    }
    
}