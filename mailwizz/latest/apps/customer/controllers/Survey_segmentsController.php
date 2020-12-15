<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Survey_segmentsController
 *
 * Handles the actions for survey segments related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class Survey_segmentsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('survey-segments.js')));
        parent::init();

        if (!(Yii::app()->customer->getModel()->getGroupOption('surveys.can_segment_surveys', 'yes') == 'yes')) {
            $this->redirect(array('surveys/index'));
        }

        // for when counting against defined timeout
        set_time_limit(0);
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + copy',
        ), parent::filters());
    }

    /**
     * List available segments
     */
    public function actionIndex($survey_uid)
    {
        $survey = $this->loadSurveyModel($survey_uid);
        $request = Yii::app()->request;

        $segment = new SurveySegment('search');
        $segment->attributes = (array)$request->getQuery($segment->modelName, array());
        $segment->survey_id  = $survey->survey_id;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_segments', 'Your mail survey segments'),
            'pageHeading'       => Yii::t('survey_segments', 'Survey segments'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_segments', ' Survey segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('survey', 'segment'));
    }

    /**
     * Create a new segment
     */
    public function actionCreate($survey_uid)
    {
        $survey     = $this->loadSurveyModel($survey_uid);
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        $segment = new SurveySegment();
        $segment->survey_id = $survey->survey_id;

        $condition   = new SurveySegmentCondition();
        $conditions  = array();
        $canContinue = true;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($segment->modelName, array()))) {
            $postConditions = (array)$request->getPost($condition->modelName, array());
            $maxAllowedConditions = (int)Yii::app()->customer->getModel()->getGroupOption('surveys.max_segment_conditions', 3);
            if (!empty($postConditions) && count($postConditions) > $maxAllowedConditions) {
                $notify->addWarning(Yii::t('survey_segments', 'You are only allowed to add {num} segment conditions.', array('{num}' => $maxAllowedConditions)));
                $canContinue = false;
            }
        }

        if ($canContinue && $request->isPostRequest && ($attributes = (array)$request->getPost($segment->modelName, array()))) {
            $postConditions = (array)$request->getPost($condition->modelName, array());
            if (!empty($postConditions)) {
                $hashedConditions = array();
                foreach ($postConditions as $index => $conditionAttributes) {
                    $cond = new SurveySegmentCondition();
                    $cond->attributes = $conditionAttributes;

                    $hashKey = sha1($cond->field_id.$cond->operator_id.$cond->value);
                    if (isset($hashedConditions[$hashKey])) {
                        continue;
                    }
                    $hashedConditions[$hashKey] = true;

                    $conditions[] = $cond;
                }
            }
            $segment->attributes = $attributes;
            $transaction = Yii::app()->db->beginTransaction();
            try {
                if (!$segment->save()) {
                    throw new Exception(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                }

                $conditionError = false;
                foreach ($conditions as $cond) {
                    $cond->segment_id = $segment->segment_id;
                    $cond->fieldDecorator->onHtmlOptionsSetup = array($this, '_addInputErrorClass');
                    if (!$cond->save()) {
                        $conditionError = true;
                    }
                }
                if ($conditionError) {
                    throw new Exception(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                }

                $timeNow = time();
                try {
                    $segment->countResponders();
                } catch (Exception $e) {}

                if ((time() - $timeNow) > (int)Yii::app()->customer->getModel()->getGroupOption('surveys.max_segment_wait_timeout', 5)) {
                    throw new Exception(Yii::t('survey_segments', 'Current segmentation is too deep and loads too slow, please revise your segment conditions!'));
                }

                $transaction->commit();

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->surveySegmentCreated($segment);
                }

                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } catch (Exception $e) {
                $notify->addError($e->getMessage());
                $transaction->rollback();
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'survey'     => $survey,
                'segment'    => $segment,
            )));

            if ($collection->success) {
                $this->redirect(array('survey_segments/update', 'survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_segments', 'Your survey segments'),
            'pageHeading'       => Yii::t('survey_segments', 'Create a new survey segment'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_segments', 'Segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'Create')
            )
        ));

        // since 1.3.5
        $conditionValueTags = SurveySegmentCondition::getValueTags();

        $this->render('form', compact('survey', 'segment', 'condition', 'conditions', 'conditionValueTags'));
    }

    /**
     * Update existing segment
     */
    public function actionUpdate($survey_uid, $segment_uid)
    {
        $survey     = $this->loadSurveyModel($survey_uid);
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        $segment = SurveySegment::model()->findByAttributes(array(
            'segment_uid'   => $segment_uid,
            'survey_id'     => $survey->survey_id,
        ));

        if (empty($segment)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $condition = new SurveySegmentCondition();
        $conditions = SurveySegmentCondition::model()->findAllByAttributes(array(
            'segment_id' => $segment->segment_id,
        ));

        $canContinue = true;
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($segment->modelName, array()))) {
            $postConditions = (array)$request->getPost($condition->modelName, array());
            $maxAllowedConditions = (int)Yii::app()->customer->getModel()->getGroupOption('surveys.max_segment_conditions', 3);
            if (!empty($postConditions) && count($postConditions) > $maxAllowedConditions) {
                $notify->addWarning(Yii::t('survey_segments', 'You are only allowed to add {num} segment conditions.', array('{num}' => $maxAllowedConditions)));
                $canContinue = false;
            }
        }

        if ($canContinue && $request->isPostRequest && ($attributes = (array)$request->getPost($segment->modelName, array()))) {
            $postConditions = (array)$request->getPost($condition->modelName, array());
            if (!empty($postConditions)) {
                $conditions = array();
                $hashedConditions = array();
                foreach ($postConditions as $index => $conditionAttributes) {
                    $cond = new SurveySegmentCondition();
                    $cond->attributes = $conditionAttributes;

                    $hashKey = sha1($cond->field_id.$cond->operator_id.$cond->value);
                    if (isset($hashedConditions[$hashKey])) {
                        continue;
                    }
                    $hashedConditions[$hashKey] = true;

                    $conditions[] = $cond;
                }
            }
            $segment->attributes = $attributes;
            $transaction = Yii::app()->db->beginTransaction();
            try {
                if (!$segment->save()) {
                    throw new Exception(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                }

                SurveySegmentCondition::model()->deleteAllByAttributes(array(
                    'segment_id' => $segment->segment_id,
                ));

                $conditionError = false;
                foreach ($conditions as $cond) {
                    $cond->segment_id = $segment->segment_id;
                    $cond->fieldDecorator->onHtmlOptionsSetup = array($this, '_addInputErrorClass');
                    if (!$cond->save()) {
                        $conditionError = true;
                    }
                }
                if ($conditionError) {
                    throw new Exception(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                }

                $timeNow = time();
                try {
                    $segment->countResponders();
                } catch (Exception $e) {}

                if ((time() - $timeNow) > (int)Yii::app()->customer->getModel()->getGroupOption('surveys.max_segment_wait_timeout', 5)) {
                    throw new Exception(Yii::t('survey_segments', 'Current segmentation is too deep and loads too slow, please revise your segment conditions!'));
                }

                $transaction->commit();

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->surveySegmentUpdated($segment);
                }

                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } catch (Exception $e) {
                $notify->addError($e->getMessage());
                $transaction->rollback();
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'survey'     => $survey,
                'segment'    => $segment,
            )));

            if ($collection->success) {
                $this->redirect(array('survey_segments/update', 'survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_segments', 'Your survey segments'),
            'pageHeading'       => Yii::t('survey_segments', 'Update survey segment'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_segments', 'Segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'Update')
            )
        ));

        // since 1.3.5
        $conditionValueTags = SurveySegmentCondition::getValueTags();
        
        // since 1.3.8.8
        $canExport = Yii::app()->customer->getModel()->getGroupOption('surveys.can_export_responders', 'yes') == 'yes';
        
        $this->render('form', compact('survey', 'segment', 'condition', 'conditions', 'conditionValueTags', 'canExport'));
    }

    /**
     * Show responders from belonging to a segment
     */
    public function actionResponders($survey_uid, $segment_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isAjaxRequest) {
            $this->redirect(array('surveys/index'));
        }

        $survey = $this->loadSurveyModel($survey_uid);

        $segment = SurveySegment::model()->findByAttributes(array(
            'segment_uid'    => $segment_uid,
            'survey_id'        => $survey->survey_id,
        ));

        if (empty($segment)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $count = $segment->countResponders();

        $pages = new CPagination($count);
        $pages->pageSize = (int)$segment->paginationOptions->getPageSize();

        $responders = $segment->findResponders($pages->getOffset(), $pages->getLimit());
        $responder  = new SurveyResponder();

        $columns = array(
            array(
                'label'    => $responder->getAttributeLabel('ip_address'),
                'field_id' => null,
                'value'    => null
            )
        );
        $rows = array();

        $criteria = new CDbCriteria();
        $criteria->compare('t.survey_id', $survey->survey_id);
        $criteria->order = 't.sort_order ASC';

        $fields = SurveyField::model()->findAll($criteria);

        foreach ($fields as $field) {
            $columns[] = array(
                'label'     => $field->label,
                'field_id'  => $field->field_id,
                'value'     => null,
            );
        }

        foreach ($responders as $index => $responder) {
            $responderRow = array('columns' => array(
                $responder->ip_address
            ));
            foreach ($fields as $field) {
                
                $criteria = new CDbCriteria();
                $criteria->select = 't.value';
                $criteria->compare('field_id', $field->field_id);
                $criteria->compare('responder_id', $responder->responder_id);
                $values = SurveyFieldValue::model()->findAll($criteria);

                $value = array();
                foreach ($values as $val) {
                    $value[] = $val->value;
                }

                $responderRow['columns'][] = Yii::app()->ioFilter->xssClean(implode(', ', $value));
            }

            if (count($responderRow['columns']) == count($columns)) {
                $rows[] = $responderRow;
            }
        }

        return $this->renderPartial('_responders', compact('survey', 'columns', 'rows', 'pages', 'count'));
    }

    /**
     * Copy segment
     */
    public function actionCopy($survey_uid, $segment_uid)
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $survey  = $this->loadSurveyModel($survey_uid);

        $segment = SurveySegment::model()->findByAttributes(array(
            'segment_uid'    => $segment_uid,
            'survey_id'      => $survey->survey_id,
        ));

        if (empty($segment)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($segment->copy()) {
            $notify->addSuccess(Yii::t('survey_segments', 'Your survey segment was successfully copied!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('survey_segments/index', 'survey_uid' => $survey->survey_uid)));
        }
    }

    /**
     * Delete existing segment
     */
    public function actionDelete($survey_uid, $segment_uid)
    {
        $survey = $this->loadSurveyModel($survey_uid);

        $segment = SurveySegment::model()->findByAttributes(array(
            'segment_uid' => $segment_uid,
            'survey_id'   => $survey->survey_id,
        ));

        if (empty($segment)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        if ($request->isPostRequest) {

            $segment->delete();

            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                $logAction->surveySegmentDeleted($segment);
            }

            $notify->addSuccess(Yii::t('app', 'Your item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('survey_segments/index', 'survey_uid' => $survey_uid));

            // since 1.3.5.9
            Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'survey'     => $survey,
                'segment'    => $segment,
                'redirect'   => $redirect,
            )));

            if ($collection->redirect) {
                $this->redirect($collection->redirect);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Confirm survey segment removal'),
            'pageHeading'       => Yii::t('surveys', 'Confirm survey segment removal'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                $segment->name . ' ' => $this->createUrl('survey_segments/update', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid)),
                Yii::t('surveys', 'Confirm survey segment removal')
            )
        ));

        $this->render('delete', compact('survey', 'segment'));
    }

    /**
     * Callback method to add attribute error class to the AR model
     */
    public function _addInputErrorClass(CEvent $event)
    {
        if ($event->sender->owner->hasErrors($event->params['attribute'])) {
            $event->params['htmlOptions']['class'] .= ' error';
        }
    }

    /**
     * Helper method to load the survey AR model
     */
    public function loadSurveyModel($survey_uid)
    {
        $model = Survey::model()->findByAttributes(array(
            'survey_uid'  => $survey_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
