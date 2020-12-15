<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Survey_respondersController
 *
 * Handles the actions for survey responders related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class Survey_respondersController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::import('customer.components.survey-field-builder.*');

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('responders.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete',
        ), parent::filters());
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
     * List available responders for a survey
     */
    public function actionIndex($survey_uid)
    {
        $survey     = $this->loadSurveyModel($survey_uid);
        $request    = Yii::app()->request;
        $postFilter = (array)$request->getPost('filter', array());
        $responder  = new SurveyResponder();

        $responderStatusesList = $responder->getStatusesList();

        /**
         * NOTE:
         * Following criteria will use filesort and create a temp table because of the group by condition.
         * So far, beside subqueries this is the only optimal way i have found to work fine.
         * Needs optimization in the future if will cause problems.
         */
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(DISTINCT t.responder_id) as counter';
        $criteria->compare('t.survey_id', $survey->survey_id);
        $criteria->order = 't.responder_id DESC';
        
        $criteria->with['subscriber'] = array(
        	'joinType' => 'LEFT JOIN',
	        'together' => false,
	        'select'   => 'subscriber.list_id, subscriber.subscriber_uid, subscriber.email',
        );
        
        foreach ($postFilter as $field_id => $value) {
            if (empty($value)) {
                unset($postFilter[$field_id]);
                continue;
            }

            if (is_numeric($field_id)) {
                $model = SurveyField::model()->findByAttributes(array(
                    'field_id'  => $field_id,
                    'survey_id' => $survey->survey_id,
                ));
                if (empty($model)) {
                    unset($postFilter[$field_id]);
                }
            }
        }

        if (!empty($postFilter['status']) && in_array($postFilter['status'], array_keys($responderStatusesList))) {
            $criteria->compare('status', $postFilter['status']);
        }

        if (!empty($postFilter['uid']) && strlen($postFilter['uid']) == 13) {
            $criteria->compare('responder_uid', $postFilter['uid']);
        }

        if (!empty($postFilter)) {

            $with = array();
            foreach ($postFilter as $field_id => $value) {
                if (!is_numeric($field_id)) {
                    continue;
                }

                $i = (int)$field_id;
                $with['fieldValues'.$i] = array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => '`fieldValues'.$i.'`.`field_id` = :field_id'.$i.' AND `fieldValues'.$i.'`.`value` LIKE :value'.$i,
                    'params'    => array(
                        ':field_id'.$i  => (int)$field_id,
                        ':value'.$i     => '%'.$value.'%',
                    ),
                );
            }

            $md = $responder->getMetaData();
            foreach ($postFilter as $field_id => $value) {
                if (!is_numeric($field_id)) {
                    continue;
                }
                if ($md->hasRelation('fieldValues'.$field_id)) {
                    continue;
                }
                $md->addRelation('fieldValues'.$field_id, array(SurveyResponder::HAS_MANY, 'SurveyFieldValue', 'responder_id'));
            }

            if (!empty($with)) {
                $criteria->with = $with;
            }
        }

        // count all confirmed responders of this survey
        $count = $responder->count($criteria);

        // instantiate the pagination and apply the limit statement to the query
        $pages = new CPagination($count);
        $pages->pageSize = (int)$responder->paginationOptions->getPageSize();
        $pages->applyLimit($criteria);

        // load the required models
        $criteria->select = 't.survey_id, t.responder_id, t.responder_uid, t.subscriber_id, t.ip_address, t.status, t.date_added';
        $criteria->group = 't.responder_id';
        $responders = $responder->findAll($criteria);

        // 1.3.8.8
        $modelName  = get_class($responder) . '_survey_' . $survey->survey_id;
        $optionKey  = sprintf('%s:%s:%s', $modelName, $this->id, $this->action->id);
        $customerId = (int)Yii::app()->customer->getId();
        $optionKey  = sprintf('system.views.grid_view_columns.customers.%d.%s', $customerId, $optionKey);
        
        $storedToggleColumns      = Yii::app()->options->get($optionKey, array());
        $storedToggleColumnsEmpty = empty($storedToggleColumns);
        $displayToggleColumns     = array();
        //
        
        // now, we need to know what columns this survey has, that is, all the tags available for this survey.
        $columns = array();
        $rows = array();

        $criteria = new CDbCriteria();
        $criteria->compare('t.survey_id', $survey->survey_id);
        $criteria->order = 't.sort_order ASC';

        $fields = SurveyField::model()->findAll($criteria);

        $columns[] = array(
            'label'         => Yii::t('app', 'Options'),
            'field_type'    => null,
            'field_id'      => null,
            'value'         => null,
            'htmlOptions'   => array('class' => 'empty-options-header options'),
        );

        $columns[] = array(
            'label'     => Yii::t('survey_responders', 'Unique ID'),
            'field_type'=> 'text',
            'field_id'  => 'uid',
            'value'     => isset($postFilter['uid']) ? CHtml::encode($postFilter['uid']) : null,
        );
        
        $columns[] = array(
            'label'         => Yii::t('app', 'Date added'),
            'field_type'    => null,
            'field_id'      => 'date_added',
            'value'         => null,
            'htmlOptions'   => array('class' => 'responder-date-added'),
        );

        $columns[] = array(
            'label'         => Yii::t('app', 'Ip address'),
            'field_type'    => null,
            'field_id'      => 'ip_address',
            'value'         => null,
            'htmlOptions'   => array('class' => 'responder-date-added'),
        );

	    $columns[] = array(
		    'label'         => Yii::t('app', 'Subscriber'),
		    'field_type'    => null,
		    'field_id'      => 'subscriber',
		    'value'         => null,
	    );
	    
        $columns[] = array(
            'label'     => Yii::t('app', 'Status'),
            'field_type'=> 'select',
            'field_id'  => 'status',
            'value'     => isset($postFilter['status']) ? CHtml::encode($postFilter['status']) : null,
            'options'   => CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $responderStatusesList),
        );
        

        foreach ($fields as $field) {
            $columns[] = array(
                'label'     => $field->label,
                'field_type'=> 'text',
                'field_id'  => $field->field_id,
                'value'     => isset($postFilter[$field->field_id]) ? CHtml::encode($postFilter[$field->field_id]) : null,
            );
        }
        
        // 1.3.8.8
        foreach ($columns as $index => $column) {
            if (empty($column['field_id'])) {
                continue;
            }
            $displayToggleColumns[] = $column;
            if ($storedToggleColumnsEmpty) {
                $storedToggleColumns[] = $column['field_id'];
                continue;
            }
            if (array_search($column['field_id'], $storedToggleColumns) === false) {
                unset($columns[$index]);
                continue;
            }
        }

        foreach ($responders as $index => $responder) {
            $responderRow = array('columns' => array());

            $actions = array();

            if ($responder->getCanBeEdited()) {
                $actions[] = CHtml::link(IconHelper::make('update'), array('survey_responders/update', 'survey_uid' => $survey->survey_uid, 'responder_uid' => $responder->responder_uid), array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat btn-xs'));
            }

            if ($responder->getCanBeDeleted()) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-remove-circle'), array('survey_responders/delete', 'survey_uid' => $survey->survey_uid, 'responder_uid' => $responder->responder_uid), array('class' => 'btn btn-danger btn-flat delete', 'title' => Yii::t('app', 'Delete'), 'data-message' => Yii::t('app', 'Are you sure you want to delete this item? There is no coming back after you do it.')));
            }
            
            $responderRow['columns'][] = $this->renderPartial('_options-column', compact('actions'), true);
            
            if (in_array('uid', $storedToggleColumns)) {
                $responderRow['columns'][] = CHtml::link($responder->responder_uid, Yii::app()->createUrl('survey_responders/update', array('survey_uid' => $survey->survey_uid, 'responder_uid' => $responder->responder_uid)));
            }
            if (in_array('date_added', $storedToggleColumns)) {
                $responderRow['columns'][] = $responder->dateAdded;
            }
            if (in_array('ip_address', $storedToggleColumns)) {
                $responderRow['columns'][] = $responder->ip_address;
            }
	        if (in_array('subscriber', $storedToggleColumns)) {
		        $responderRow['columns'][] = !empty($responder->subscriber_id) && !empty($responder->subscriber) ? CHtml::link($responder->subscriber->getDisplayEmail(), Yii::app()->createUrl('list_subscribers/update', array('list_uid' => $responder->subscriber->list->list_uid, 'subscriber_uid' => $responder->subscriber->subscriber_uid))) : '';
	        }
            if (in_array('status', $storedToggleColumns)) {
                $responderRow['columns'][] = $responder->statusName;
            }

            foreach ($fields as $field) {
                if (!in_array($field->field_id, $storedToggleColumns)) {
                    continue;
                }

                $criteria = new CDbCriteria();
                $criteria->select = 't.value';
                $criteria->compare('field_id', $field->field_id);
                $criteria->compare('responder_id', $responder->responder_id);
                $values = SurveyFieldValue::model()->findAll($criteria);

                $value = array();
                foreach ($values as $val) {
                    $value[] = $val->value;
                }
                
                $responderRow['columns'][] = CHtml::encode(implode(', ', $value));
            }

            if (count($responderRow['columns']) == count($columns)) {
                $rows[] = $responderRow;
            }
        }

        if ($request->isPostRequest && $request->isAjaxRequest) {
            return $this->renderPartial('_list', compact('survey', 'responder', 'columns', 'rows', 'pages', 'count'));
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_responders', 'Your survey responders'),
            'pageHeading'       => Yii::t('survey_responders', 'Survey responders'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_responders', 'Responders') => $this->createUrl('survey_responders/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('index', compact('survey', 'responder', 'columns', 'rows', 'pages', 'count', 'displayToggleColumns'));
    }

    /**
     * Create / Add a new responder in a survey
     */
    public function actionCreate($survey_uid)
    {
        $survey  = $this->loadSurveyModel($survey_uid);
        $request = Yii::app()->request;
        $hooks   = Yii::app()->hooks;

        $surveyFields = SurveyField::model()->findAll(array(
            'condition' => 'survey_id = :lid',
            'params'    => array(':lid' => $survey->survey_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($surveyFields)) {
            throw new CHttpException(404, Yii::t('survey_fields', 'Your survey does not have any field defined.'));
        }

        $usedTypes = array();
        foreach ($surveyFields as $field) {
            $usedTypes[] = $field->type->type_id;
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $types = SurveyFieldType::model()->findAll($criteria);

        $responder = new SurveyResponder();
        $responder->survey_id = $survey->survey_id;

        $instances = array();

        foreach ($types as $type) {

            if (empty($type->identifier) || !is_file(Yii::getPathOfAlias($type->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $type->class_alias, array(
                'fieldType' => $type,
                'survey'    => $survey,
                'responder' => $responder,
            ));

            if (!($component instanceof FieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        $fields = array();

        // if the fields are saved
        if ($request->isPostRequest) {

            $transaction = Yii::app()->db->beginTransaction();

            try {

                $customer               = $survey->customer;
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

                $attributes = (array)$request->getPost($responder->modelName, array());
                if (empty($responder->ip_address)) {
                    $responder->ip_address = Yii::app()->request->getUserHostAddress();
                }
                if (isset($attributes['status']) && in_array($attributes['status'], array_keys($responder->getStatusesList()))) {
                    $responder->status = $attributes['status'];
                } else {
                    $responder->status = SurveyResponder::STATUS_ACTIVE;
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
                $this->callbacks->onResponderSaveSuccess(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey,
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

            } catch (Exception $e) {

                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());

                // bind default save error event handler
                $this->callbacks->onResponderSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onResponderSaveError(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey
                )));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => Yii::app()->notify->hasSuccess,
                'responder'  => $responder,
            )));

            if ($collection->success) {
                if ($request->getPost('next_action') && $request->getPost('next_action') == 'create-new') {
                    $this->redirect(array('survey_responders/create', 'survey_uid' => $responder->survey->survey_uid));
                }
                $this->redirect(array('survey_responders/update', 'survey_uid' => $responder->survey->survey_uid, 'responder_uid' => $responder->responder_uid));
            }
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

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_responders', 'Add a new responder to your survey.'),
            'pageHeading'       => Yii::t('survey_responders', 'Add a new responder to your survey.'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_responders', 'Responders') => $this->createUrl('survey_responders/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact('fieldsHtml', 'survey', 'responder'));
    }

    /**
     * Update existing survey responder
     */
    public function actionUpdate($survey_uid, $responder_uid)
    {
        $survey    = $this->loadSurveyModel($survey_uid);
        $responder = $this->loadResponderModel($survey->survey_id, $responder_uid);
        $request   = Yii::app()->request;
        $notify    = Yii::app()->notify;
        
        if ($survey->customer->getGroupOption('surveys.can_edit_own_responders', 'yes') != 'yes') {
            $notify->addError(Yii::t('survey_responders', 'You are not allowed to edit responders at this time!'));
            $this->redirect(array('survey_responders/index', 'survey_uid' => $survey->survey_uid));
        }

        $surveyFields = SurveyField::model()->findAll(array(
            'condition' => 'survey_id = :lid',
            'params'    => array(':lid' => $survey->survey_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($surveyFields)) {
            throw new CHttpException(404, Yii::t('survey', 'Your survey does not have any field defined.'));
        }

        $usedTypes = array();
        foreach ($surveyFields as $field) {
            $usedTypes[] = $field->type->type_id;
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $types = SurveyFieldType::model()->findAll($criteria);

        $instances = array();

        foreach ($types as $type) {

            if (empty($type->identifier) || !is_file(Yii::getPathOfAlias($type->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $type->class_alias, array(
                'fieldType' => $type,
                'survey'    => $survey,
                'responder' => $responder,
            ));

            if (!($component instanceof FieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        $fields = array();

        // if the fields are saved
        if ($request->isPostRequest) {

            $transaction = Yii::app()->db->beginTransaction();

            try {

                $attributes = (array)$request->getPost($responder->modelName, array());
                if (empty($responder->ip_address)) {
                    $responder->ip_address = Yii::app()->request->getUserHostAddress();
                }
                if (isset($attributes['status']) && in_array($attributes['status'], array_keys($responder->getStatusesList()))) {
                    $responder->status = $attributes['status'];
                } else {
                    $responder->status = SurveyResponder::STATUS_ACTIVE;
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
                $this->callbacks->onResponderSaveSuccess(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey,
                )));

                // since 1.8.2
                $options  = Yii::app()->options;
                $customer = $survey->customer;
                if ($options->get('system.customer.action_logging_enabled', true)) {
                    $customer->attachBehavior('logAction', array(
                        'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
                    ));
                    $customer->logAction->responderUpdated($responder);
                }

                $transaction->commit();

            } catch (Exception $e) {

                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());

                // bind default save error event handler
                $this->callbacks->onResponderSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onResponderSaveError(new CEvent($this->callbacks, array(
                    'instances' => $instances,
                    'responder' => $responder,
                    'survey'    => $survey
                )));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => Yii::app()->notify->hasSuccess,
                'responder'  => $responder,
            )));

            if ($collection->success) {
                if ($request->getPost('next_action') && $request->getPost('next_action') == 'create-new') {
                    $this->redirect(array('survey_responders/create', 'survey_uid' => $responder->survey->survey_uid));
                }
            }
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

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('survey_responders', 'Update existing survey responder.'),
            'pageHeading'       => Yii::t('survey_responders', 'Update existing survey responder.'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('survey_responders', 'Responders') => $this->createUrl('survey_responders/index', array('survey_uid' => $survey->survey_uid)),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('fieldsHtml', 'survey', 'responder'));
    }
    
    /**
     * Delete existing survey responder
     */
    public function actionDelete($survey_uid, $responder_uid)
    {
        $request   = Yii::app()->request;
        $notify    = Yii::app()->notify;
        $survey    = $this->loadSurveyModel($survey_uid);
        $responder = $this->loadResponderModel($survey->survey_id, $responder_uid);

        if ($responder->canBeDeleted) {
            $responder->delete();

            // since 1.8.2
            $options  = Yii::app()->options;
            $customer = $survey->customer;
            if ($options->get('system.customer.action_logging_enabled', true)) {
                $customer->attachBehavior('logAction', array(
                    'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
                ));
                $customer->logAction->responderDeleted($responder);
            }
        }

        $redirect = null;
        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('survey_responders', 'Your survey responder was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('survey_responders/index', 'survey_uid' => $survey->survey_uid));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'survey'     => $survey,
            'responder'  => $responder,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
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

    /**
     * Helper method to load the survey responder AR model
     */
    public function loadResponderModel($survey_id, $responder_uid)
    {
        $model = SurveyResponder::model()->findByAttributes(array(
            'responder_uid'    => $responder_uid,
            'survey_id'        => (int)$survey_id,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
