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
 * @since 1.3.8.7
 */

class ListsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('lists.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }
    
    /**
     * Show available lists
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $list = new Lists('search');
        $list->unsetAttributes();
        $list->attributes = (array)$request->getQuery($list->modelName, array());

	    // 1.8.8
	    $refreshRoute = array('lists/index');
	    $gridAjaxUrl  = $this->createUrl($this->route);
	    if ($list->getIsArchived()) {
		    $refreshRoute = array('lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED);
		    $gridAjaxUrl = $this->createUrl($this->route, array('Lists[status]' => Lists::STATUS_ARCHIVED));
	    }

	    $this->setData([
		    'refreshRoute' => $refreshRoute,
		    'gridAjaxUrl'  => $gridAjaxUrl,
	    ]);

	    $pageHeading = Yii::t('lists', 'Lists');
	    $breadcrumbs = array(Yii::t('lists', 'Lists') => $this->createUrl('lists/index'));
	    if ($list->getIsArchived()) {
		    $pageHeading = Yii::t('lists', 'Archived lists');
		    $breadcrumbs[Yii::t('lists', 'Archived lists')] = $this->createUrl('lists/index', array('Lists[status]' => Lists::STATUS_ARCHIVED));
	    }
	    $breadcrumbs[] = Yii::t('app', 'View all');
	    //
	    
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Email lists'),
            'pageHeading'       => $pageHeading,
            'pageBreadcrumbs'   => $breadcrumbs
        ));

        $this->render('list', compact('list'));
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
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'List overview'),
            'pageHeading'       => Yii::t('lists', 'List overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('lists', 'Overview')
            )
        ));
        
        $confirmedSubscribersCount = $list->getConfirmedSubscribersCount(true);
        $subscribersCount          = $list->getSubscribersCount(true);
        $segmentsCount             = $list->activeSegmentsCount;
        $customFieldsCount         = $list->fieldsCount;
        $pagesCount                = ListPageType::model()->count();

        $this->render('overview', compact(
            'list', 
            'confirmedSubscribersCount', 
            'subscribersCount', 
            'segmentsCount', 
            'customFieldsCount', 
            'pagesCount'
        ));
    }

	/**
	 * Toggle list as an archive
	 * 
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

            if ($logAction = $list->customer->asa('logAction')) {
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

        $this->render('delete', compact('list', 'campaign'));
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
        if (!$filter->isViewAction) {
        	
	        if ($request->getPost('confirm', null) === null) {
		        return $this->render('confirm-filters-action');
	        }
	        if ($request->getPost('confirm', '') !== 'true') {
		        $this->redirect(array($this->route));
	        }
        }
	    // 

        // the export action
        if ($filter->isExportAction) {
	        
            /* Set the download headers */
            HeaderHelper::setDownloadHeaders('all-subscribers.csv');

            echo implode(",", array('"Email"', '"Source"', '"Ip address"', '"Status"')) . "\n";

            $subscribers = $filter->getSubscribers($limit, $offset);
            while (!empty($subscribers)) {
                foreach ($subscribers as $subscriber) {
                    $out = CMap::mergeArray(array($subscriber->email), $subscriber->getAttributes(array('source', 'ip_address', 'status')));
                    echo implode(",", $out) . "\n";
                }
                $offset = $limit + $offset;
                $subscribers = $filter->getSubscribers($limit, $offset);
            }

            Yii::app()->end();
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
        if ($filter->isBlacklistAction) {
	        
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
        if ($filter->isDeleteAction) {
	        
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
     * Helper method to load the list AR model
     */
    public function loadModel($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
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
