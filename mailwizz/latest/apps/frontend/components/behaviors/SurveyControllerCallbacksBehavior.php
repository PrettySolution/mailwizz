<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyControllerCallbacksBehavior
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class SurveyControllerCallbacksBehavior extends CBehavior
{
    /**
     * @param CEvent $event
     * @return array
     */
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

    /**
     * @param CEvent $event
     */
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

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onResponderFieldsSorting(CEvent $event)
    {
        $this->raiseEvent('onResponderFieldsSorting', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onResponderSave(CEvent $event)
    {
        $this->raiseEvent('onResponderSave', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onResponderFieldsDisplay(CEvent $event)
    {
        $this->raiseEvent('onResponderFieldsDisplay', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onResponderSaveSuccess(CEvent $event)
    {
        $this->raiseEvent('onResponderSaveSuccess', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onResponderSaveError(CEvent $event)
    {
        $this->raiseEvent('onResponderSaveError', $event);
    }
}
