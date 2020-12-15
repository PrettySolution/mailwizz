<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_toolsController
 * 
 * Handles the actions for lists related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
class List_toolsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('list-tools.js')));
        parent::init();
    }
    
    /**
     * Display list available tools, like import, export, copy subscribers, etc
     */
    public function actionIndex($list_uid)
    {
        $list = $this->loadListModel($list_uid);

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'List tools'),
            'pageHeading'       => Yii::t('lists', 'List tools'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('lists', 'List tools')
            )
        ));
        
        $options         = Yii::app()->options;
        $customer        = Yii::app()->customer->getModel();
        $canSegmentLists = $customer->getGroupOption('lists.can_segment_lists', 'yes') == 'yes';
        $importerEnabled = $options->get('system.importer.enabled', 'yes') == 'yes';
        $exporterEnabled = $options->get('system.exporter.enabled', 'yes') == 'yes';
        $subscriber      = new ListSubscriber();
            
        $canImport = $importerEnabled && $customer->getGroupOption('lists.can_import_subscribers', 'yes') == 'yes';
        $canExport = $exporterEnabled && $customer->getGroupOption('lists.can_export_subscribers', 'yes') == 'yes';
        $canCopy   = $customer->getGroupOption('lists.can_copy_subscribers', 'yes') == 'yes';
        
        $this->render('index', compact('canImport', 'canExport', 'canCopy', 'list', 'canSegmentLists', 'subscriber'));
    }

    /**
     * @param $list_uid
     * @return BaseController
     * @throws CHttpException
     */
    public function actionCopy_subscribers_ajax($list_uid)
    {
        $request = Yii::app()->request;
        
        if (!$request->isAjaxRequest) {
            $this->redirect(array('list_tools/index', 'list_uid' => $list_uid));
        }
        
        $list   = $this->loadListModel($list_uid);
        $listId = (int)$request->getQuery('list_id');
        $data   = array();
        
        // load all lists
        if (empty($listId)) {
            $options = array();
            
            $criteria = new CDbCriteria();
            $criteria->select = 'list_id, name';
            $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
            $criteria->compare('status', Lists::STATUS_ACTIVE);
            $criteria->addCondition('list_id != :lid');
            $criteria->order = 'list_id DESC';
            $criteria->params[':lid'] = (int)$list->list_id;
    
            $models  = Lists::model()->findAll($criteria);
  
            foreach ($models as $model) {
                $options[] = array(
                    'list_id' => $model->list_id, 
                    'name'    => $model->name,
                );
            }
            $data['lists'] = $options;
        }
        
        // load all segments
        if (!empty($listId)) {
            $options = array();
            $models = ListSegment::model()->findAllByListId((int)$listId);
            foreach ($models as $model) {
                $options[] = array(
                    'segment_id' => $model->segment_id, 
                    'name'       => $model->name, 
                );
            }
            $data['segments'] = $options;    
        }
        
        return $this->renderJson(array('result' => 'success', 'data' => $data));
    }

    /**
     * Handle the copy of subscribers from another list
     *
     * @param $list_uid
     * @return BaseController
     * @throws CHttpException
     */
    public function actionCopy_subscribers($list_uid)
    {
        $list     = $this->loadListModel($list_uid);
        $request  = Yii::app()->request;
        
        $listId       = (int)$request->getPost('copy_list_id');
        $segmentId    = (int)$request->getPost('copy_segment_id');
        $status       = (array)$request->getPost('copy_status', array());
        $status       = empty($status) ? array(ListSubscriber::STATUS_CONFIRMED) : $status;
        $statusAction = (int)$request->getPost('copy_status_action', 0);
        
        $customer  = Yii::app()->customer->getModel();
        $canCopy   = $customer->getGroupOption('lists.can_copy_subscribers', 'yes') == 'yes';

        if (!$request->isPostRequest || empty($listId) || !$canCopy) {
            $this->redirect(array('lists/tools', 'list_uid' => $list->list_uid));
        }
        
        $fromList = Lists::model()->findByAttributes(array(
            'list_id'     => $listId,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));
        
        if (empty($fromList)) {
            $this->redirect(array('lists/tools', 'list_uid' => $list->list_uid));
        }
        
        $fromSegment = null;
        if (!empty($segmentId)) {
            $fromSegment = ListSegment::model()->findByAttributes(array(
                'list_id'    => $fromList->list_id,
                'segment_id' => $segmentId,
            ));    
            
            if (empty($fromSegment)) {
                $this->redirect(array('lists/tools', 'list_uid' => $list->list_uid));
            }
        }
        
        if (!empty($fromSegment)) {
            $count = $fromSegment->countSubscribers(null, array(
                'status' => $status,
            ));
        } else {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$listId);
            $criteria->addInCondition('status', $status);
            $count = ListSubscriber::model()->count($criteria);
        }
        
        $fromText = Yii::t('lists', 'Copy {count} subscribers from "{fromList}" list into the "{intoList}" list', array(
            '{count}'    => Yii::app()->format->formatNumber($count),
            '{fromList}' => $fromList->name,
            '{intoList}' => $list->name,
        ));
        if (!empty($fromSegment)) {
            $fromText = Yii::t('lists', 'Copy {count} subscribers from "{fromList}" list using the "{fromSegment}" segment into the "{intoList}" list', array(
                '{count}'       => Yii::app()->format->formatNumber($count),
                '{fromList}'    => $fromList->name,
                '{fromSegment}' => $fromSegment->name,
                '{intoList}'    => $list->name,
            ));
        }
        
        if ($memoryLimit = $customer->getGroupOption('lists.copy_subscribers_memory_limit')) { 
            ini_set('memory_limit', $memoryLimit);
        }
        
        $limit  = (int)$customer->getGroupOption('lists.copy_subscribers_at_once', 100);
        $pages  = $count <= $limit ? 1 : ceil($count / $limit);
        $page   = (int)$request->getPost('page', 1);
        $page   = $page < 1 ? 1 : $page; 
        $offset = ($page - 1) * $limit;

        $attributes = array(
            'total'             => $count,
            'processed_total'   => 0,
            'processed_success' => 0,
            'processed_error'   => 0,
            'percentage'        => 0,
            'progress_text'     => Yii::t('lists', 'The copy process is starting, please wait...'),
            'post_url'          => $this->createUrl('list_tools/copy_subscribers', array('list_uid' => $list->list_uid)),
            'list_id'           => (int)$listId,
            'segment_id'        => (int)$segmentId,
            'status'            => (array)$status,
            'status_action'     => $statusAction,
            'page'              => $page,
        );
        
        $jsonAttributes = CJSON::encode($attributes);
        
        if (!$request->isAjaxRequest) {
            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('lists', 'Copy subscribers'), 
                'pageHeading'       => Yii::t('lists', 'Copy subscribers'), 
                'pageBreadcrumbs'   => array(
                    Yii::t('lists', 'Lists') => $this->createUrl('lists/index'), 
                    $list->name . ' '        => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                    Yii::t('lists', 'Tools') => $this->createUrl('list_tools/index', array('list_uid' => $list->list_uid)),
                    Yii::t('lists', 'Copy from "{from}" list', array('{from}' => $fromList->name))
                )
            ));
            return $this->render('copy-subscribers', compact('list', 'fromList', 'fromSegment', 'fromText', 'jsonAttributes'));
        }
        
        $totalSubscribersCount   = 0;
        $listSubscribersCount    = 0;
        $maxSubscribersPerList   = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);

        if ($maxSubscribersPerList > -1) {
            $criteria = new CDbCriteria();
            $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';
            $criteria->compare('t.list_id', (int)$list->list_id);
            $listSubscribersCount = ListSubscriber::model()->count($criteria);
            if ($listSubscribersCount >= $maxSubscribersPerList) {
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'),
                ));
            }
        }
        
        if (!empty($fromSegment)) {
            $criteria = new CDbCriteria;
            $criteria->select = 't.*';
            $subscribers = $fromSegment->findSubscribers($offset, $limit, $criteria, array(
                'status' => $status
            ));
        } else {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$listId);
            $criteria->addInCondition('status', $status);
            $criteria->limit  = $limit;
            $criteria->offset = $offset;
            $subscribers = ListSubscriber::model()->findAll($criteria);
        }
        
        if (empty($subscribers)) {
            return $this->renderJson(array(
                'finished'      => true,
                'progress_text' => Yii::t('lists', 'The copy process is done.'),
            ));
        }
        
        $processedTotal   = (int)$request->getPost('processed_total', 0);
        $processedSuccess = (int)$request->getPost('processed_success', 0);
        $processedError   = (int)$request->getPost('processed_error', 0);
        $progressText     = Yii::t('lists', 'The copy process is running, please wait...');
        $finished         = false;
        
        $transaction = Yii::app()->getDb()->beginTransaction();
        
        try {
            
            foreach ($subscribers as $subscriber) {
                if ($maxSubscribersPerList > -1 && $listSubscribersCount >= $maxSubscribersPerList) {
                    $progressText = Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.');
                    $finished = true;
                    break;
                }
                $processedTotal++;
                if ($statusAction == 1) {
                    $subscriber->status = ListSubscriber::STATUS_CONFIRMED;
                }
                if ($newSubscriber = $subscriber->copyToList($list->list_id, false)) {
                    $processedSuccess++;
                    if ($newSubscriber->subscriber_id != $subscriber->subscriber_id) {
                        $totalSubscribersCount++;
                        $listSubscribersCount++;
                    }
                } else {
                    $processedError++;
                }
            }    
            
            $transaction->commit();
            
        } catch (Exception $e) {
            
            $transaction->rollback();
            
        }
        
        $percentage   = round((($processedTotal / $count) * 100), 2);
        
        return $this->renderJson(array_merge($attributes, array(
            'processed_total'   => $processedTotal,
            'processed_success' => $processedSuccess,
            'processed_error'   => $processedError,
            'percentage'        => $percentage,
            'page'              => $page + 1,
            'progress_text'     => $progressText,
            'finished'          => $finished,
        )));
    }

    /**
     * Helper method to load the list AR model
     */
    public function loadListModel($list_uid)
    {
        $model = Lists::model()->findByAttributes(array(
            'list_uid'      => $list_uid,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
}