<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Survey_fieldsController
 * 
 * Handles the actions for list fields related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class Survey_fieldsController extends Controller
{
    /**
     * @return BaseController|void
     * @throws CException
     */
    public function init()
    {
        Yii::import('customer.components.survey-field-builder.*');
        parent::init();
    }

    /**
     * List of behaviors attached to this controller
     * The behaviors are merged with the one from parent implementation
     */
    public function behaviors()
    {
        return CMap::mergeArray(array(
            'callbacks' => array(
                'class' => 'customer.components.behaviors.SurveyFieldsControllerCallbacksBehavior',
            ),
        ), parent::behaviors());
    }
    
    /**
     * Handle the CRUD actions for survey fields
     */
    public function actionIndex($survey_uid)
    {
        $survey = $this->loadSurveyModel($survey_uid);

        $types = SurveyFieldType::model()->findAll();

        if (empty($types)) {
            throw new CHttpException(400, Yii::t('survey_fields', 'There is no field type defined yet, please contact the administrator.'));
        }

        $instances = array();
        
        foreach ($types as $type) {
            
            if (empty($type->identifier) || !is_file(Yii::getPathOfAlias($type->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $type->class_alias, array(
                'fieldType'   => $type,
                'survey'      => $survey,
            ));
            
            if (!($component instanceof FieldBuilderType)) {
                continue;
            }
            
            // run the component to hook into next events
            $component->run();
            
            $instances[] = $component;
        }
        
        $hooks   = Yii::app()->hooks;
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $fields  = array();
        
        // if the fields are saved
        if ($request->isPostRequest) {
            
            $transaction = Yii::app()->db->beginTransaction();
            
            try {
                
                // raise event
                $this->callbacks->onSurveyFieldsSave(new CEvent($this->callbacks, array(
                    'fields' => &$fields,
                )));
                
                // if no error thrown but still there are errors in any of the instances, stop.
                foreach ($instances as $instance) {
                    if (!empty($instance->errors)) {
                        throw new Exception(Yii::t('app', 'Your form has a few errors. Please fix them and try again!'));
                    }
                }
                
                // add the default success message
                Yii::app()->notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                
                // raise event. at this point everything seems to be fine.
                $this->callbacks->onSurveyFieldsSaveSuccess(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                )));

                $transaction->commit();
                
            } catch (Exception $e) {
                
                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());
                
                // bind default save error event handler
                $this->callbacks->onResponderSaveError = array($this->callbacks, '_collectAndShowErrorMessages');
                
                // raise event
                $this->callbacks->onSurveyFieldsSaveError(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                )));
            }
        }
        
        // raise event. simply the fields are shown
        $this->callbacks->onSurveyFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        ))); 
        
        // add the default sorting of fields actions and raise the event
        $this->callbacks->onSurveyFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onSurveyFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));
        
        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_fields', 'Your survey fields'),
            'pageHeading'       => Yii::t('survey_fields', 'Survey fields'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys')    => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_fields', 'Fields')
            )
        ));
        
        $this->render('index', compact('fieldsHtml', 'survey'));
    }
    
    /**
     * Helper method to load the survey AR model
     */
    public function loadSurveyModel($survey_uid)
    {
        $model = Survey::model()->findByAttributes(array(
            'survey_uid'    => $survey_uid,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
}