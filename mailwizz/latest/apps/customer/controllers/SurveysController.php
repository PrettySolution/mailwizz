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
	    $customer = Yii::app()->customer->getModel();
	    if ((int)$customer->getGroupOption('surveys.max_surveys', -1) == 0) {
		    $this->redirect(array('dashboard/index'));
	    }
	    
        $this->getData('pageStyles')->add(array('src' => AssetsUrl::js('datetimepicker/css/bootstrap-datetimepicker.min.css')));
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('datetimepicker/js/bootstrap-datetimepicker.min.js')));

        $languageCode = LanguageHelper::getAppLanguageCode();
        if (Yii::app()->language != Yii::app()->sourceLanguage && is_file(AssetsPath::js($languageFile = 'datetimepicker/js/locales/bootstrap-datetimepicker.'.$languageCode.'.js'))) {
            $this->getData('pageScripts')->add(array('src' => AssetsUrl::js($languageFile)));
        }

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('surveys.js')));
        
        parent::init();
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
     * Show available surveys
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $survey  = new Survey('search');
        $survey->unsetAttributes();
        $survey->attributes  = (array)$request->getQuery($survey->modelName, array());
        $survey->customer_id = (int)Yii::app()->customer->getId();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Your surveys'),
            'pageHeading'       => Yii::t('surveys', 'Surveys'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('survey'));
    }

    /**
     * Create a new survey
     */
    public function actionCreate()
    {
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $customer = Yii::app()->customer->getModel();

        if (($maxSurveys = (int)$customer->getGroupOption('surveys.max_surveys', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Survey::STATUS_PENDING_DELETE));

            $surveysCount = Survey::model()->count($criteria);
            if ($surveysCount >= $maxSurveys) {
                $notify->addWarning(Yii::t('surveys', 'You have reached the maximum number of allowed surveys.'));
                $this->redirect(array('surveys/index'));
            }
        }

        $survey = new Survey();
        $survey->customer_id = $customer->customer_id;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($survey->modelName, array()))) {

            $survey->attributes = $attributes;

            if (isset(Yii::app()->params['POST'][$survey->modelName]['description'])) {
                $survey->description = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$survey->modelName]['description']);
            }

            if (!$survey->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->surveyCreated($survey);
                }
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'survey'     => $survey,
            )));

            if ($collection->success) {
                $this->redirect(array('surveys/fields', 'survey_uid' => $survey->survey_uid));
            }
        }

        $survey->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Create new survey'),
            'pageHeading'       => Yii::t('surveys', 'Create new survey'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact(
            'survey'
        ));
    }

    /**
     * Update existing survey
     */
    public function actionUpdate($survey_uid)
    {
        $survey  = $this->loadModel($survey_uid);
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$survey->editable) {
            $this->redirect(array('surveys/index'));
        }

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($survey->modelName, array()))) {

            $survey->attributes = $attributes;

            if (isset(Yii::app()->params['POST'][$survey->modelName]['description'])) {
                $survey->description = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$survey->modelName]['description']);
            }

            if (!$survey->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->surveyUpdated($survey);
                }
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'survey'     => $survey,
            )));

            if ($collection->success) {
                $this->redirect(array('surveys/update', 'survey_uid' => $survey->survey_uid));
            }
        }

        $survey->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Update survey'),
            'pageHeading'       => Yii::t('surveys', 'Update survey'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact(
            'survey'
        ));
    }

    /**
     * Copy survey
     * The copy will include all the survey base data.
     */
    public function actionCopy($survey_uid)
    {
        $survey   = $this->loadModel($survey_uid);
        $customer = $survey->customer;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $canCopy  = true;

        if ($survey->isPendingDelete) {
            $this->redirect(array('surveys/index'));
        }

        if (($maxSurveys = $customer->getGroupOption('surveys.max_surveys', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Survey::STATUS_PENDING_DELETE));

            $surveysCount = Survey::model()->count($criteria);
            if ($surveysCount >= $maxSurveys) {
                $notify->addWarning(Yii::t('surveys', 'You have reached the maximum number of allowed surveys.'));
                $canCopy = false;
            }
        }

        if ($canCopy && $survey->copy()) {
            $notify->addSuccess(Yii::t('surveys', 'Your survey was successfully copied!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('surveys/index')));
        }
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

            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
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
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Confirm survey removal')
            )
        ));

        $this->render('delete', compact('survey'));
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

        $apps = Yii::app()->apps;
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.categories.min.js')),
            array('src' => AssetsUrl::js('survey-overview.js'))
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('surveys', 'Survey overview'),
            'pageHeading'       => Yii::t('surveys', 'Survey overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Overview')
            )
        ));
        
        $customer           = Yii::app()->customer->getModel();
        $canSegmentSurveys  = $customer->getGroupOption('surveys.can_segment_surveys', 'yes') == 'yes';
        $respondersCount    = $survey->respondersCount;
        $segmentsCount      = $survey->activeSegmentsCount;
        $customFieldsCount  = $survey->fieldsCount;

        $this->render('overview', compact(
            'survey',
            'canSegmentSurveys',
            'respondersCount',
            'segmentsCount',
            'customFieldsCount'
        ));
    }
    
    /**
     * Responds to the ajax calls from the country list fields
     */
    public function actionFields_country_states_by_country_name()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('dashboard/index'));
        }

        $countryName = $request->getQuery('country');
        $country = Country::model()->findByAttributes(array('name' => $countryName));
        if (empty($country)) {
            return $this->renderJson(array());
        }

        $statesList = array();
        $states     = !empty($country->zones) ? $country->zones : array();

        foreach ($states as $state) {
            $statesList[$state->name] = $state->name;
        }

        return $this->renderJson($statesList);
    }

	/**
	 * Responds to the ajax calls from the state list fields
	 */
    public function actionFields_country_by_zone()
    {
	    $request = Yii::app()->request;
	    if (!$request->isAjaxRequest) {
		    return $this->redirect(array('dashboard/index'));
	    }

	    $zone = Zone::model()->findByAttributes(array(
	    	'name' => $request->getQuery('zone')
	    ));
	    
	    if (empty($zone)) {
		    return $this->renderJson(array());
	    }

	    return $this->renderJson(array(
	    	'country' => array(
	    		'name' => $zone->country->name,
			    'code' => $zone->country->code,
		    ),
	    ));
    }

    /**
     * Helper method to load the list AR model
     */
    public function loadModel($survey_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('survey_uid', $survey_uid);
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
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

    /**
     * Callback method to set the editor options
     */
    public function _setupEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('description'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }

        $options['id']     = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $options['height'] = 100;

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
