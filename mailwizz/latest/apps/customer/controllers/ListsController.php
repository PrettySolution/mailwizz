<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListsController
 *
 * Handles the actions for lists related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ListsController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('lists.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + copy, all_subscribers_filters',
        ), parent::filters());
    }

    /**
     * Show available lists
     */
    public function actionIndex()
    {
        $session = Yii::app()->session;
        $request = Yii::app()->request;
        $list = new Lists('search');
        $list->unsetAttributes();
        $list->attributes = (array)$request->getQuery($list->modelName, array());
        $list->customer_id = (int)Yii::app()->customer->getId();

        if ($list->merged !== null) {
            $session->add('lists_grid_merged', $list->merged);
        }

        if ($list->merged === null && ($merged = $session->itemAt('lists_grid_merged'))) {
            $list->merged = $merged;
        }

	    // 1.8.8
	    $refreshRoute = array('lists/index');
	    $gridAjaxUrl  = $this->createUrl($this->route);
	    if ($list->getIsArchived()) {
		    $refreshRoute = array('lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED);
		    $gridAjaxUrl = $this->createUrl($this->route, array('Lists[status]' => Lists::STATUS_ARCHIVED));
	    }

	    $this->setData(array(
		    'refreshRoute' => $refreshRoute,
		    'gridAjaxUrl'  => $gridAjaxUrl,
	    ));

	    $pageHeading = Yii::t('lists', 'Lists');
	    $breadcrumbs = array(Yii::t('lists', 'Lists') => $this->createUrl('lists/index'));
	    if ($list->getIsArchived()) {
		    $pageHeading = Yii::t('lists', 'Archived lists');
		    $breadcrumbs[Yii::t('lists', 'Archived lists')] = $this->createUrl('lists/index', array('Lists[status]' => Lists::STATUS_ARCHIVED));
	    }
	    $breadcrumbs[] = Yii::t('app', 'View all');
	    //
	    
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Your lists'),
            'pageHeading'       => $pageHeading,
            'pageBreadcrumbs'   => $breadcrumbs
        ));

        $this->render('list', compact('list'));
    }

    /**
     * Create a new list
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $customer   = Yii::app()->customer->getModel();

        if (($maxLists = (int)$customer->getGroupOption('lists.max_lists', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

            $listsCount = Lists::model()->count($criteria);
            if ($listsCount >= $maxLists) {
                $notify->addWarning(Yii::t('lists', 'You have reached the maximum number of allowed lists.'));
                $this->redirect(array('lists/index'));
            }
        }

        $list = new Lists();
        $list->customer_id = $customer->customer_id;

        $listDefault = new ListDefault();
        $listCompany = new ListCompany();
        $listCustomerNotification = new ListCustomerNotification();

        // since 1.3.5 - this should be expanded in future
        $listSubscriberAction      = new ListSubscriberAction();
        $subscriberActionLists     = CMap::mergeArray(array(0 => Yii::t('app', 'Select all')), $list->findAllForSubscriberActions());
        $selectedSubscriberActions = array(ListSubscriberAction::ACTION_SUBSCRIBE => array(), ListSubscriberAction::ACTION_UNSUBSCRIBE => array());
        
        // to create the default mail list fields.
        $list->attachBehavior('listDefaultFields', array(
            'class' => 'customer.components.db.behaviors.ListDefaultFieldsBehavior',
        ));

        if (!empty($customer->company)) {
            $listCompany->mergeWithCustomerCompany($customer->company);
        }

        $listDefault->mergeWithCustomerInfo($customer);

        // since 1.5.3
        if (($forceOptIn = $customer->getGroupOption('lists.force_optin_process', '')) && in_array($forceOptIn, array_keys($list->getOptInArray()))) {
            $list->opt_in = $forceOptIn;
        }
        if (($forceOptOut = $customer->getGroupOption('lists.force_optout_process', '')) && in_array($forceOptOut, array_keys($list->getOptOutArray()))) {
            $list->opt_out = $forceOptOut;
        }
        //

        if ($request->isPostRequest && $request->getPost($list->modelName)) {
            $models = array($list, $listCompany, $listCustomerNotification, $listDefault);
            $hasErrors = false;
            foreach ($models as $model) {
                $model->attributes = (array)$request->getPost($model->modelName, array());
                if (!$model->validate()) {
                    $hasErrors = true; // don't break to collect errors for all models.
                }
            }
            
            if (!$hasErrors) {
                
                // 1.4.5
                $listSubscriberActions = $request->getPost($listSubscriberAction->modelName, array());
                $isSelectAll           = !empty($listSubscriberActions['subscribe']) && array_search(0, $listSubscriberActions['subscribe']) !== false ? 1 : 0;
                $list->setIsSelectAllAtActionWhenSubscribe($isSelectAll);
                $isSelectAll           = !empty($listSubscriberActions['unsubscribe']) && array_search(0, $listSubscriberActions['unsubscribe']) !== false ? 1 : 0;
                $list->setIsSelectAllAtActionWhenUnsubscribe($isSelectAll);
                //
                
                foreach ($models as $model) {
                    if (!($model instanceof Lists)) {
                        $model->list_id = $list->list_id;
                    }
                    $model->save(false);
                }

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listCreated($list);
                }

                // since 1.3.5 - this should be expanded in future
                if ($listSubscriberActions = (array)$request->getPost($listSubscriberAction->modelName, array())) {
                    $allowedActions = array_keys($listSubscriberAction->getActions());
                    foreach ($listSubscriberActions as $actionName => $targetLists) {
                        if (!in_array($actionName, $allowedActions)) {
                            continue;
                        }
                        foreach ($targetLists as $targetListId) {
                            $subscriberAction = new ListSubscriberAction();
                            $subscriberAction->source_list_id = $list->list_id;
                            $subscriberAction->source_action  = $actionName;
                            $subscriberAction->target_list_id = (int)$targetListId;
                            $subscriberAction->target_action  = ListSubscriberAction::ACTION_UNSUBSCRIBE;
                            $subscriberAction->save();
                        }
                    }
                }
                
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form contains errors, please correct them and try again.'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'success'       => $notify->hasSuccess,
                'list'          => $list,
            )));

            if ($collection->success) {
                $this->redirect(array('lists/update', 'list_uid' => $list->list_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Create new list'),
            'pageHeading'       => Yii::t('lists', 'Create new list'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact(
            'list',
            'listDefault',
            'listCompany',
            'listCustomerNotification',
            'listSubscriberAction',
            'subscriberActionLists',
            'selectedSubscriberActions',
            'forceOptIn',
            'forceOptOut'
        ));
    }

    /**
     * Update existing list
     */
    public function actionUpdate($list_uid)
    {
        $list       = $this->loadModel($list_uid);
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        if (!$list->editable) {
            $this->redirect(array('lists/index'));
        }

        $customer    = $list->customer;
        $listDefault = $list->default;
        $listCompany = $list->company;
        $listCustomerNotification = $list->customerNotification;

        // since 1.3.5 - this should be expanded in future
        $listSubscriberAction  = new ListSubscriberAction();
        $subscriberActionLists = CMap::mergeArray(array(0 => Yii::t('app', 'Select all')), $list->findAllForSubscriberActions());
        
        $selectedSubscriberActions = array(
            ListSubscriberAction::ACTION_SUBSCRIBE   => array(), 
            ListSubscriberAction::ACTION_UNSUBSCRIBE => array()
        );
        
        // 1.4.5
        if ($list->getIsSelectAllAtActionWhenSubscribe()) {
            $selectedSubscriberActions[ListSubscriberAction::ACTION_SUBSCRIBE][]  = 0;
        }
        if ($list->getIsSelectAllAtActionWhenUnsubscribe()) {
            $selectedSubscriberActions[ListSubscriberAction::ACTION_UNSUBSCRIBE][] = 0;
        }
        //
        
        if (!empty($list->subscriberSourceActions)) {
            foreach ($list->subscriberSourceActions as $model) {
                $selectedSubscriberActions[$model->source_action][] = $model->target_list_id;
            }
        }

        // since 1.5.3
        if (($forceOptIn = $customer->getGroupOption('lists.force_optin_process', '')) && in_array($forceOptIn, array_keys($list->getOptInArray()))) {
            $list->opt_in = $forceOptIn;
        }
        if (($forceOptOut = $customer->getGroupOption('lists.force_optout_process', '')) && in_array($forceOptOut, array_keys($list->getOptOutArray()))) {
            $list->opt_out = $forceOptOut;
        }
        //
        
        if ($request->isPostRequest && $request->getPost($list->modelName)) {
            $models = array($list, $listCompany, $listCustomerNotification, $listDefault);
            $hasErrors = false;
            foreach ($models as $model) {
                $model->attributes = (array)$request->getPost($model->modelName, array());
                if (!$model->validate()) {
                    $hasErrors = true; // don't break to collect errors for all models.
                }
            }
            if (!$hasErrors) {
                
                // 1.4.5
                $listSubscriberActions  = $request->getPost($listSubscriberAction->modelName, array());
                $isSelectAllSubscribe   = !empty($listSubscriberActions['subscribe']) && array_search(0, $listSubscriberActions['subscribe']) !== false ? 1 : 0;
                $list->setIsSelectAllAtActionWhenSubscribe($isSelectAllSubscribe);
                $isSelectAllUnsubscribe = !empty($listSubscriberActions['unsubscribe']) && array_search(0, $listSubscriberActions['unsubscribe']) !== false ? 1 : 0;
                $list->setIsSelectAllAtActionWhenUnsubscribe($isSelectAllUnsubscribe);
                //
                
                foreach ($models as $model) {
                    $model->save(false);
                }

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listUpdated($list);
                }

                // since 1.3.5 - this should be expanded in future
                ListSubscriberAction::model()->deleteAllByAttributes(array('source_list_id' => (int)$list->list_id));
                $listSubscriberActions = (array)$request->getPost($listSubscriberAction->modelName, array());
                
                // 1.4.5 
                $listIds  = array();
                if ($isSelectAllSubscribe || $isSelectAllUnsubscribe) {
                    $criteria = new CDbCriteria();
                    $criteria->compare('customer_id', $list->customer_id);
                    $criteria->addNotInCondition('list_id', array($list->list_id));
                    $criteria->select = 'list_id';
                    $models = Lists::model()->findAll($criteria);
                    foreach ($models as $model) {
                        $listIds[] = $model->list_id;
                    }
                }
                if ($isSelectAllSubscribe) {
                    $listSubscriberActions['subscribe'] = $listIds;
                }
                if ($isSelectAllUnsubscribe) {
                    $listSubscriberActions['unsubscribe'] = $listIds;
                }
                //
                
                if ($listSubscriberActions) {
                    $allowedActions = array_keys($listSubscriberAction->getActions());
                    foreach ($listSubscriberActions as $actionName => $targetLists) {
                        if (!in_array($actionName, $allowedActions)) {
                            continue;
                        }
                        foreach ($targetLists as $targetListId) {
                            $subscriberAction = new ListSubscriberAction();
                            $subscriberAction->source_list_id = $list->list_id;
                            $subscriberAction->source_action  = $actionName;
                            $subscriberAction->target_list_id = (int)$targetListId;
                            $subscriberAction->target_action  = ListSubscriberAction::ACTION_UNSUBSCRIBE;
                            $subscriberAction->save();
                        }
                    }
                }

                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form contains errors, please correct them and try again.'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'success'       => $notify->hasSuccess,
                'list'          => $list,
            )));

            if ($collection->success) {
                $this->redirect(array('lists/update', 'list_uid' => $list->list_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Update list'),
            'pageHeading'       => Yii::t('lists', 'Update list'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact(
            'list',
            'listDefault',
            'listCompany',
            'listCustomerNotification',
            'listSubscriberAction',
            'subscriberActionLists',
            'selectedSubscriberActions',
            'forceOptIn',
            'forceOptOut'
        ));
    }

    /**
     * Copy list
     * The copy will include all the list base data.
     */
    public function actionCopy($list_uid)
    {
        $list     = $this->loadModel($list_uid);
        $customer = $list->customer;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $canCopy  = true;

        if ($list->isPendingDelete) {
            $this->redirect(array('lists/index'));
        }

        if (($maxLists = $customer->getGroupOption('lists.max_lists', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

            $listsCount = Lists::model()->count($criteria);
            if ($listsCount >= $maxLists) {
                $notify->addWarning(Yii::t('lists', 'You have reached the maximum number of allowed lists.'));
                $canCopy = false;
            }
        }

        if ($canCopy && $list->copy()) {
            $notify->addSuccess(Yii::t('lists', 'Your list was successfully copied!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('lists/index')));
        }
    }


    /**
     * Delete existing list
     */
    public function actionDelete($list_uid)
    {
        $list    = $this->loadModel($list_uid);
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$list->isRemovable) {
            $this->redirect(array('lists/index'));
        }

        if ($request->isPostRequest) {

            $list->delete();

            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                $logAction->listDeleted($list);
            }

            $notify->addSuccess(Yii::t('app', 'Your item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('lists/index'));

            // since 1.3.5.9
            Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'model'      => $list,
                'redirect'   => $redirect,
            )));

            if ($collection->redirect) {
                $this->redirect($collection->redirect);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Confirm list removal'),
            'pageHeading'       => Yii::t('lists', 'Confirm list removal'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('lists', 'Confirm list removal')
            )
        ));

        $campaign = new Campaign();
        $campaign->unsetAttributes();
        $campaign->attributes  = (array)$request->getQuery($campaign->modelName, array());
        $campaign->list_id     = $list->list_id;
        $campaign->customer_id = (int)Yii::app()->customer->getId();

        $this->render('delete', compact('list', 'campaign'));
    }

    /**
     * Display list overview
     * This is a page containing shortcuts to the most important list features.
     */
    public function actionOverview($list_uid)
    {
        $list = $this->loadModel($list_uid);

        if ($list->isPendingDelete) {
            $this->redirect(array('lists/index'));
        }

        $apps = Yii::app()->apps;
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js')),
            array('src' => $apps->getBaseUrl('assets/js/flot/jquery.flot.categories.min.js')),
            array('src' => AssetsUrl::js('list-overview.js'))
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'List overview'),
            'pageHeading'       => Yii::t('lists', 'List overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('lists', 'Overview')
            )
        ));
        
        $customer                    = Yii::app()->customer->getModel();
        $canSegmentLists             = $customer->getGroupOption('lists.can_segment_lists', 'yes') == 'yes';
        $confirmedSubscribersCount   = $list->getConfirmedSubscribersCount(true);
        $subscribersCount            = $list->getSubscribersCount(true);
        $segmentsCount               = $list->activeSegmentsCount;
        $customFieldsCount           = $list->fieldsCount;
        $pagesCount                  = ListPageType::model()->count();

        $this->render('overview', compact(
            'list', 
            'confirmedSubscribersCount',
            'subscribersCount', 
            'segmentsCount', 
            'customFieldsCount', 
            'pagesCount', 
            'canSegmentLists'
        ));
    }

	/**
	 * @param $list_uid
	 *
	 * @throws CDbException
	 * @throws CHttpException
	 */
	public function actionToggle_archive($list_uid)
	{
		/** @var Lists $list */
		$list = $this->loadModel((string)$list_uid);

		/** @var array $returnRoute */
		$returnRoute = array('lists/index');

		if ($list->getIsPendingDelete()) {
			$this->redirect($returnRoute);
		}

		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		if ($list->getIsArchived()) {
			$list->saveAttributes(array(
				'status' => Lists::STATUS_ACTIVE,
			));
			$notify->addSuccess(Yii::t('lists', 'Your list was successfully unarchived!'));
			$returnRoute = array('lists/index');
		} elseif (!$list->getIsArchived()) {
			$list->saveAttributes(array(
				'status' => Lists::STATUS_ARCHIVED,
			));
			$notify->addSuccess(Yii::t('lists', 'Your list was successfully archived!'));
			$returnRoute = array('lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED);
		}

		if (!$request->getIsAjaxRequest()) {
			$this->redirect($request->getPost('returnUrl', $returnRoute));
		}
	}
	
    /**
     * Display a searchable table of subscribers from all lists
     */
    public function actionAll_subscribers()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        set_time_limit(0);
        ini_set('memory_limit', -1);
        
        $notify  = Yii::app()->notify;
        $request = Yii::app()->request;
        $limit   = 1000;
        $offset  = 0;
        
        // filter instance to create the form
        $filter = new AllListsSubscribersFilters();
        $filter->customer = Yii::app()->customer->getModel();
   
        if ($attributes = (array)$request->getQuery(null)) {
            $filter->attributes = CMap::mergeArray($filter->attributes, $attributes);
            $filter->hasSetFilters = true;
        }
        if ($attributes = (array)$request->getPost(null)) {
            $filter->attributes = CMap::mergeArray($filter->attributes, $attributes);
            $filter->hasSetFilters = true;
        }
        
        if ($filter->hasSetFilters && !$filter->validate()) {
            $notify->addError($filter->shortErrors->getAllAsString());
            $this->redirect(array($this->route));
        }

	    // 1.6.8
	    $showLoadingRedirect = false;
	    $showConfirm         = !$filter->isViewAction;
        if ($filter->isCreateListAction && $request->getQuery('created_list_id')) {
        	$showConfirm         = false;
	        $showLoadingRedirect = true;
        }
        
	    if ($showConfirm) {

		    if ($request->getPost('confirm', null) === null) {
			    return $this->render('confirm-filters-action');
		    }
		    
		    if ($request->getPost('confirm', '') !== 'true') {
			    $this->redirect(array($this->route));
		    }
	    }
	    // 
        
        // the export action
        $canExport = $filter->customer->getGroupOption('lists.can_export_subscribers', 'yes') == 'yes';
        if ($filter->isExportAction && $canExport) {
	        
            /* Set the download headers */
            HeaderHelper::setDownloadHeaders('all-subscribers.csv');

            echo implode(",", array('"Email"', '"Source"', '"Ip address"', '"Status"')) . "\n";

            $subscribers = $filter->getSubscribers($limit, $offset);
            while (!empty($subscribers)) {
                foreach ($subscribers as $subscriber) {
                    $out = CMap::mergeArray(array($subscriber->displayEmail), $subscriber->getAttributes(array('source', 'ip_address', 'status')));
                    echo implode(",", $out) . "\n";
                }
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            Yii::app()->end();
        }
        
        // create list from selection
        $canCreateList = $filter->customer->getGroupOption('lists.can_create_list_from_filters', 'yes') == 'yes';
        if ($canCreateList && $filter->isCreateListAction) {
        	
            if (($maxLists = (int)$filter->customer->getGroupOption('lists.max_lists', -1)) > -1) {
                $criteria = new CDbCriteria();
                $criteria->compare('customer_id', (int)$filter->customer->customer_id);
                $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

                $listsCount = Lists::model()->count($criteria);
                if ($listsCount >= $maxLists) {
                    $notify->addWarning(Yii::t('lists', 'You have reached the maximum number of allowed lists.'));
                    $this->redirect(array($this->route));
                }
            }

            $limit   = 500;
            $offset  = (int)$request->getQuery('offset', 0);
            $list_id = (int)$request->getQuery('created_list_id', 0);

            if (empty($list_id)) {
                $criteria = new CDbCriteria();
                $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
                $criteria->compare('status', Lists::STATUS_ACTIVE);
                $criteria->order = 'list_id DESC';
                $criteria->limit = 1;
                $list = Lists::model()->find($criteria);

                if (empty($list)) {
                    $notify->addError(Yii::t('list_subscribers', 'Please define at least one list before doing this action!'));
                    $this->redirect(array($this->route));
                }

                if (!($list = $list->copy())) {
                    $notify->addError(Yii::t('list_subscribers', 'Unable to copy the list!'));
                    $this->redirect(array($this->route));
                }

                $name = Yii::t('list_subscribers', 'Auto-generated at {datetime}', array(
                    '{datetime}' => $list->dateTimeFormatter->formatLocalizedDateTime(date('Y-m-d H:i:s'))
                ));

                $list->name = $name;
                $list->display_name = $name;
                $list->description = $name;
                $list->save(false);
            } else {
                $list = Lists::model()->findByAttributes(array(
                    'list_id'     => (int)$list_id,
                    'customer_id' => (int)$filter->customer->customer_id,
                ));
            }
            
            if (empty($list)) {
                $notify->addError(Yii::t('list_subscribers', 'Unable to copy the list!'));
                $this->redirect(array($this->route));
            }
            
            $filter->unique = AllListsSubscribersFilters::TEXT_YES;
            
            $totalSubscribersCount  = 0;
            $listSubscribersCount   = 0;
            $maxSubscribersPerList  = (int)$filter->customer->getGroupOption('lists.max_subscribers_per_list', -1);
            $maxSubscribers         = (int)$filter->customer->getGroupOption('lists.max_subscribers', -1);

            if ($maxSubscribers > -1) {
                $criteria = new CDbCriteria();
                $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

                if ($maxSubscribers > -1 && ($listsIds = $filter->customer->getAllListsIdsNotMerged())) {
                    $criteria->addInCondition('t.list_id', $listsIds);
                    $totalSubscribersCount = ListSubscriber::model()->count($criteria);
                    if ($totalSubscribersCount >= $maxSubscribers) {
                        $notify->addError(Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'));
                        $this->redirect(array($this->route));
                    }
                }
            }
            
            try {
                
                $subscribers = $filter->getSubscribers($limit, $offset);
                
                if (empty($subscribers)) {
                    throw new Exception('Done');
                }

                foreach ($subscribers as $subscriber) {

                    try {

                        $subscriber->copyToList($list->list_id, false, false);

                        $totalSubscribersCount++;
                        $listSubscribersCount++;
                        
                    } catch (Exception $e) {}

                    if ($maxSubscribersPerList > -1 && $listSubscribersCount >= $maxSubscribersPerList) {
                        $notify->addError(Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'));
                        $this->redirect(array($this->route));
                    }

                    if ($maxSubscribers > -1 && $totalSubscribersCount >= $maxSubscribers) {
                        $notify->addError(Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'));
                        $this->redirect(array($this->route));
                    }
                }
                
                $params = CMap::mergeArray($request->getQuery(null), array(
                    'offset'          => $offset + $limit, 
                    'created_list_id' => $list->list_id,
                    'unique'          => $filter->unique,
                ));
                
                if ($showLoadingRedirect) {
                	return $this->render('loading-redirect', array(
                		'redirect' => $this->createUrl($this->route, $params),
	                ));
                }
                
                $this->redirect($this->createUrl($this->route, $params));
                
            } catch(Exception $e) {}

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully, you can view the new list {view} and update it from {update}!', array(
                '{view}'   => CHtml::link(Yii::t('app', 'here'), $this->createUrl('lists/overview', array('list_uid' => $list->list_uid))),
                '{update}' => CHtml::link(Yii::t('app', 'here'), $this->createUrl('lists/update', array('list_uid' => $list->list_uid))),
            )));
            $this->redirect(array($this->route));
        }

        // the confirm action
        if ($filter->isConfirmAction) {
	        
            $subscribers = $filter->getSubscribers($limit, $offset);

            while (!empty($subscribers)) {
                $subscriberIds = array();
                foreach ($subscribers as $subscriber) {
                    $subscriberIds[] = $subscriber['subscriber_id'];
                }
                $filter->confirmSubscribersByIds($subscriberIds);
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully!'));
            $this->redirect(array($this->route));
        }
        
        // the unsubscribe action
        if ($filter->isUnsubscribeAction) {
        	
            $subscribers = $filter->getSubscribers($limit, $offset);

            while (!empty($subscribers)) {
                $subscriberIds = array();
                foreach ($subscribers as $subscriber) {
                    $subscriberIds[] = $subscriber['subscriber_id'];
                }
                $filter->unsubscribeSubscribersByIds($subscriberIds);
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully!'));
            $this->redirect(array($this->route));
        }

        // the disable action
        if ($filter->isDisableAction) {
	        
            $subscribers = $filter->getSubscribers($limit, $offset);

            while (!empty($subscribers)) {
                $subscriberIds = array();
                foreach ($subscribers as $subscriber) {
                    $subscriberIds[] = $subscriber['subscriber_id'];
                }
                $filter->disableSubscribersByIds($subscriberIds);
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully!'));
            $this->redirect(array($this->route));
        }
        
        // the blacklist action
        $canBlacklist = $filter->customer->getGroupOption('lists.can_use_own_blacklist', 'no') == 'yes';
        if ($filter->isBlacklistAction && $canBlacklist) {
        	
            $subscribers = $filter->getSubscribers($limit, $offset);

            while (!empty($subscribers)) {
                $filter->blacklistSubscribers($subscribers);
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully!'));
            $this->redirect(array($this->route));
        }

        // the delete action
        $canDelete = $filter->customer->getGroupOption('lists.can_delete_own_subscribers', 'yes') == 'yes';
        if ($filter->isDeleteAction && $canDelete) {
        	
            $deleteCount = 0;
            $subscribers = $filter->getSubscribers();

            while (!empty($subscribers)) {
                $subscriberIds = array();
                foreach ($subscribers as $subscriber) {
                    $subscriberIds[] = $subscriber['subscriber_id'];
                }
                $deleteCount += $filter->deleteSubscribersByIds($subscriberIds);
                $subscribers = $filter->getSubscribers();
            }

            $notify->addSuccess(Yii::t('list_subscribers', 'Action completed successfully, deleted {n} subscribers!', array('{n}' => $deleteCount)));
            $this->redirect(array($this->route));
        }
        
        // the view action, default one.
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('lists-all-subscribers.js')));
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Subscribers'),
            'pageHeading'       => Yii::t('lists', 'Subscribers from all your lists'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                Yii::t('lists', 'Subscribers')
            )
        ));
        
        $this->render('all-subscribers', compact('filter'));
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
     * Export
     */
    public function actionExport()
    {
        $notify = Yii::app()->notify;

        $models = Lists::model()->findAllByAttributes(array(
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('lists.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes);
        @fputcsv($fp, array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes)), ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->attributes);
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * Helper method to load the list AR model
     */
    public function loadModel($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

        $model = Lists::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($model->isPendingDelete) {
            $this->redirect(array('lists/index'));
        }

        return $model;
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('all_subscribers'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
