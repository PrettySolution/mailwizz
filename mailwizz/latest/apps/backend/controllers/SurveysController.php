<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveysController
 *
 * Handles the actions for surveys related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class SurveysController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('surveys.js')));
        parent::init();
    }
    
    /**
     * Show available surveys
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $survey  = new Survey('search');
        $survey->unsetAttributes();
        $survey->attributes = (array)$request->getQuery($survey->modelName, array());
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Surveys'),
            'pageHeading'       => Yii::t('surveys', 'Surveys'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('survey'));
    }
    
    /**
     * Display survey overview
     * This is a page containing shortcuts to the most important survey features.
     */
    public function actionOverview($survey_uid)
    {
        $survey = $this->loadModel($survey_uid);

        if ($survey->isPendingDelete) {
            $this->redirect(array('surveys/index'));
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Survey overview'),
            'pageHeading'       => Yii::t('surveys', 'Survey overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Overview')
            )
        ));
        
        $respondersCount   = $survey->respondersCount;
        $segmentsCount     = $survey->activeSegmentsCount;
        $customFieldsCount = $survey->fieldsCount;

        $this->render('overview', compact(
            'survey',
            'respondersCount',
            'segmentsCount',
            'customFieldsCount'
        ));
    }

    /**
     * Delete existing survey
     */
    public function actionDelete($survey_uid)
    {
        $survey  = $this->loadModel($survey_uid);
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$survey->isRemovable) {
            $this->redirect(array('surveys/index'));
        }

        if ($request->isPostRequest) {

            $survey->delete();

            if ($logAction = $survey->customer->asa('logAction')) {
                $logAction->surveyDeleted($survey);
            }

            $notify->addSuccess(Yii::t('app', 'Your item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('surveys/index'));

            // since 1.3.5.9
            Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'model'      => $survey,
                'redirect'   => $redirect,
            )));

            if ($collection->redirect) {
                $this->redirect($collection->redirect);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Confirm survey removal'),
            'pageHeading'       => Yii::t('surveys', 'Confirm survey removal'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Survey') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Confirm survey removal')
            )
        ));

        $this->render('delete', compact('survey'));
    }
    
    /**
     * Helper method to load the survey AR model
     */
    public function loadModel($survey_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('survey_uid', $survey_uid);
        $criteria->addNotInCondition('status', array(Survey::STATUS_PENDING_DELETE));

        $model = Survey::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($model->isPendingDelete) {
            $this->redirect(array('surveys/index'));
        }

        return $model;
    }
}
