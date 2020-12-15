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
     * @return BaseController|void
     * @throws CException
     */
    public function init()
    {
        Yii::import('customer.components.survey-field-builder.*');
        parent::init();
    }

    /**
	 * @inheritdoc
	 */
    public function behaviors()
    {
        return CMap::mergeArray(array(
            'callbacks' => array(
                'class' => 'frontend.components.behaviors.SurveyControllerCallbacksBehavior',
            ),
        ), parent::behaviors());
    }

	/**
	 * Subscribe a new responder to a certain survey
	 * 
	 * @param $survey_uid
	 * @param string $subscriber_uid
	 * @param string $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionIndex($survey_uid, $subscriber_uid = '', $campaign_uid = '')
    {
        $survey   = $this->loadSurveyModel($survey_uid);
        $isOwner  = false;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $hooks    = Yii::app()->hooks;
        $viewName = 'index';

        if (!empty($survey->customer)) {
            $isOwner = $survey->customer_id == Yii::app()->customer->getId();
            $this->setCustomerLanguage($survey->customer);
        }
        
        // load the survey fields and bind the behavior.
        $surveyFields = SurveyField::model()->findAll(array(
            'condition' => 'survey_id = :lid',
            'params'    => array(':lid' => (int)$survey->survey_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($surveyFields)) {
            if (!$isOwner) {
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
            }
            throw new CHttpException(403, Yii::t('surveys', 'This survey has no fields yet.'));
        }

        if (!$survey->getIsStarted()) {
            $message = Yii::t('surveys', 'This survey hasn\'t started yet!');
            if (!$isOwner) {
                throw new CHttpException(403, $message);
            }
            $notify->addWarning($message);
        } elseif ($survey->getIsEnded()) {
            $message = Yii::t('surveys', 'This survey has ended!');
            if (!$isOwner) {
                throw new CHttpException(403, $message);
            }
            $notify->addWarning($message);
        }

        $responder = new SurveyResponder();
        $responder->survey_id  = $survey->survey_id;
        $responder->ip_address = Yii::app()->request->getUserHostAddress();

        if (!empty($subscriber_uid) && ($subscriber = ListSubscriber::model()->findByUid($subscriber_uid))) {
        	$responder->subscriber_id = $subscriber->subscriber_id;
        }
        
        $usedTypes = array();
        foreach ($surveyFields as $field) {
            $usedTypes[] = (int)$field->type->type_id;
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $surveyFieldTypes = SurveyFieldType::model()->findAll($criteria);
        $instances = array();

        foreach ($surveyFieldTypes as $fieldType) {

            if (empty($fieldType->identifier) || !is_file(Yii::getPathOfAlias($fieldType->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $fieldType->class_alias, array(
                'fieldType'    => $fieldType,
                'survey'       => $survey,
                'responder'    => $responder,
            ));

            if (!($component instanceof FieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        // since 1.3.9.7
        if (!$request->isPostRequest) {
            foreach ($surveyFields as $surveyField) {
                if ($tagValue = $request->getQuery($surveyField->tag)) {
                    $_POST[$surveyField->tag] = $tagValue;
                }
            }
        }

        $fields = array();

        // if the fields are saved
        if ($request->isPostRequest) {
        	
        	// last submission 
	        $criteria = new CDbCriteria();
	        $criteria->compare('ip_address', $responder->ip_address);
	        $criteria->addCondition('date_added > DATE_SUB(NOW(), INTERVAL 1 MINUTE)');
	        $criteria->order = 'responder_id DESC';
	        $criteria->limit = 1;
	        if (SurveyResponder::model()->find($criteria)) {
		        throw new CHttpException(403, Yii::t('surveys', 'We detected too many submissions from your IP address in a short period of time, please slow down!'));
	        }
	        //

            // since 1.3.5.6
            Yii::app()->hooks->doAction('frontend_survey_respond_before_transaction', $this);

            $transaction = Yii::app()->db->beginTransaction();

            try {

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_survey_respond_at_transaction_start', $this);
                
                // since 1.5.3
                $customer = Customer::model()->findByPk($survey->customer_id);
                
                $maxRespondersPerSurvey = (int)$customer->getGroupOption('surveys.max_responders_per_survey', -1);
                $maxResponders          = (int)$customer->getGroupOption('surveys.max_responders', -1);

                if ($maxResponders > -1 || $maxRespondersPerSurvey > -1) {
                    $criteria = new CDbCriteria();

                    if ($maxResponders > -1 && ($surveysIds = $customer->getAllSurveysIds())) {
                        $criteria->addInCondition('t.survey_id', $surveysIds);
                        $totalRespondersCount = SurveyResponder::model()->count($criteria);
                        if ($totalRespondersCount >= $maxResponders) {
                            throw new Exception(Yii::t('surveys', 'The maximum number of allowed responders has been reached.'));
                        }
                    }

                    if ($maxRespondersPerSurvey > -1) {
                        $criteria->compare('t.survey_id', (int)$survey->survey_id);
                        $surveyRespondersCount = SurveyResponder::model()->count($criteria);
                        if ($surveyRespondersCount >= $maxRespondersPerSurvey) {
                            throw new Exception(Yii::t('surveys', 'The maximum number of allowed responders for this survey has been reached.'));
                        }
                    }
                }

                if (!$responder->save()) {
                    if ($responder->hasErrors()) {
                        throw new Exception($responder->shortErrors->getAllAsString());
                    }
                    throw new Exception(Yii::t('app', 'Temporary error, please contact us if this happens too often!'));
                }

                // raise event
                $this->callbacks->onResponderSave(new CEvent($this->callbacks, array(
                    'fields' => &$fields,
                    'action' => 'respond',
                )));

                // if no exception thrown but still there are errors in any of the instances, stop.
                foreach ($instances as $instance) {
                    if (!empty($instance->errors)) {
                        throw new Exception(Yii::t('app', 'Your form has a few errors. Please fix them and try again!'));
                    }
                }

                // raise event. at this point everything seems to be fine.
                $this->callbacks->onResponderSaveSuccess(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey,
                    'action'    => 'respond',
                )));

                // since 1.8.2
                $options = Yii::app()->options;
                if ($options->get('system.customer.action_logging_enabled', true)) {
                    $customer->attachBehavior('logAction', array(
                        'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
                    ));
                    $customer->logAction->responderCreated($responder);
                }

                $transaction->commit();

                $viewName = 'thank-you';

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_survey_respond_at_transaction_end', $this);

                if (!empty($survey->finish_redirect)) {
                    $this->redirect($survey->finish_redirect);
                }

            } catch (Exception $e) {

                $transaction->rollback();

                if (($message = $e->getMessage())) {
                    Yii::app()->notify->addError($message);
                }

                // bind default save error event handler
                $this->callbacks->onResponderSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onResponderSaveError(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey,
                    'action'    => 'respond',
                )));
            }

            // since 1.3.5.6
            Yii::app()->hooks->doAction('frontend_survey_respond_after_transaction', $this);
        }

        // raise event. simply the fields are shown
        $this->callbacks->onResponderFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // add the default sorting of fields actions and raise the event
        $this->callbacks->onResponderFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onResponderFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        // embed output
        if ($request->getQuery('output') == 'embed') {
			$width  = (string)$request->getQuery('width', 400);
			$height = (string)$request->getQuery('height', 400);
			$width  = substr($width, -1)  == '%' ? (int)substr($width,  0, strlen($width) - 1)  . '%' : (int)$width  . 'px';
			$height = substr($height, -1) == '%' ? (int)substr($height, 0, strlen($height) - 1) . '%' : (int)$height . 'px';
	        
            $attributes = array(
                'width'  => $width,
                'height' => $height,
                'target' => $request->getQuery('target'),
            );
            $this->layout = 'embed';
            $this->setData('attributes', $attributes);
        }

        $this->render($viewName, compact('survey', 'fieldsHtml'));
    }

    /**
     * Responds to the ajax calls from the country survey fields
     */
    public function actionFields_country_states_by_country_name()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('site/index'));
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
	 * Responds to the ajax calls from the state survey fields
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
     * Helper method to load the survey AR model
     */
    public function loadSurveyModel($survey_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('survey_uid', $survey_uid);
        $criteria->addNotInCondition('status', array(Survey::STATUS_PENDING_DELETE));
        $model = Survey::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to load the survey responder AR model
     */
    public function loadResponderModel($responder_uid, $survey_id)
    {
        $model = SurveyResponder::model()->findByAttributes(array(
            'responder_uid'    => $responder_uid,
            'survey_id'        => (int)$survey_id
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to set the language for this customer.
     */
    public function setCustomerLanguage($customer)
    {
        if (empty($customer->language_id)) {
            return $this;
        }

        // 1.5.3 - language has been forced already at init
        if (($langCode = Yii::app()->request->getQuery('lang')) && strlen($langCode) <= 5) {
            return $this;
        }
        
        // multilanguage is available since 1.1 and the Language class does not exist prior to that version
        if (!version_compare(Yii::app()->options->get('system.common.version'), '1.1', '>=')) {
            return $this;
        }
        
        if (!empty($customer->language)) {
            Yii::app()->setLanguage($customer->language->getLanguageAndLocaleCode());
        }

        return $this;
    }
}
