<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Lists_toolsController
 * 
 * Handles the actions for lists related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */
 
class Lists_toolsController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('lists-tools.js')));
        parent::init();
    }
    
    /**
     * Display list available tools
     */
    public function actionIndex()
    {
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('lists', 'Tools'),
            'pageHeading'       => Yii::t('lists', 'Tools'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => array('lists/index'),
                Yii::t('lists', 'Tools')
            )
        ));
        
        $options   = Yii::app()->options;
        $customer  = Yii::app()->customer->getModel();
        
        $syncTool  = new ListsSyncTool();
        $syncTool->customer_id = $customer->customer_id;
        
        $splitTool = new ListSplitTool();
        $splitTool->customer_id = $customer->customer_id;
        
        $this->render('index', compact('syncTool', 'splitTool'));
    }

    /**
     * @return BaseController
     */
    public function actionSync()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        if (!$request->isPostRequest) {
            $this->redirect(array('lists_tools/index'));
        }
        
        $customer = Yii::app()->customer->getModel();
        $syncTool = new ListsSyncTool();
        $syncTool->attributes = (array)$request->getPost($syncTool->modelName, array());
        $syncTool->customer_id = $customer->customer_id;
        
        if (!$syncTool->validate()) {
            $message = Yii::t('lists', 'Unable to validate your sync data!');
            if ($request->isAjaxRequest) {
                $syncTool->progress_text = $message;
                $syncTool->finished      = 1;
                return $this->renderJson(array(
                    'attributes'           => $syncTool->attributes,
                    'formatted_attributes' => $syncTool->getFormattedAttributes(),
                ));
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }
        
        if ($syncTool->primary_list_id == $syncTool->secondary_list_id) {
            $message = Yii::t('lists', 'The primary list and the secondary list cannot be the same!');
            if ($request->isAjaxRequest) {
                if ($request->isAjaxRequest) {
                    $syncTool->progress_text = $message;
                    $syncTool->finished      = 1;
                    return $this->renderJson(array(
                        'attributes'           => $syncTool->attributes,
                        'formatted_attributes' => $syncTool->getFormattedAttributes(),
                    ));
                }
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }
        
        $noAction = empty($syncTool->missing_subscribers_action);
        $noAction = $noAction && empty($syncTool->distinct_status_action);
        $noAction = $noAction && empty($syncTool->duplicate_subscribers_action);
        if ($noAction) {
            $message = Yii::t('lists', 'You need to select an action against one of the lists subscribers!');
            if ($request->isAjaxRequest) {
                if ($request->isAjaxRequest) {
                    $syncTool->progress_text = $message;
                    $syncTool->finished      = 1;
                    return $this->renderJson(array(
                        'attributes'           => $syncTool->attributes,
                        'formatted_attributes' => $syncTool->getFormattedAttributes(),
                    ));
                }
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }
        
        $primaryList = $syncTool->getPrimaryList();
        if (empty($primaryList)) {
            $message = Yii::t('lists', 'The primary list cannot be found!');
            if ($request->isAjaxRequest) {
                if ($request->isAjaxRequest) {
                    $syncTool->progress_text = $message;
                    $syncTool->finished      = 1;
                    return $this->renderJson(array(
                        'attributes'           => $syncTool->attributes,
                        'formatted_attributes' => $syncTool->getFormattedAttributes(),
                    ));
                }
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }
        
        $secondaryList = $syncTool->getSecondaryList();
        if (empty($secondaryList)) {
            $message = Yii::t('lists', 'The secondary list cannot be found!');
            if ($request->isAjaxRequest) {
                if ($request->isAjaxRequest) {
                    $syncTool->progress_text = $message;
                    $syncTool->finished      = 1;
                    return $this->renderJson(array(
                        'attributes'           => $syncTool->attributes,
                        'formatted_attributes' => $syncTool->getFormattedAttributes(),
                    ));
                }
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }

        if ($memoryLimit = $customer->getGroupOption('lists.copy_subscribers_memory_limit')) { 
            ini_set('memory_limit', $memoryLimit);
        }
        
        $syncTool->count  = $primaryList->subscribersCount;
        $syncTool->limit  = (int)$customer->getGroupOption('lists.copy_subscribers_at_once', 100);

        $jsonAttributes = CJSON::encode(array(
            'attributes'           => $syncTool->attributes,
            'formatted_attributes' => $syncTool->getFormattedAttributes(),
        ));
        
        if (!$request->isAjaxRequest) {
            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('lists', 'Sync lists'), 
                'pageHeading'       => Yii::t('lists', 'Sync lists'), 
                'pageBreadcrumbs'   => array(
                    Yii::t('lists', 'Tools') => $this->createUrl('tools/index'),
                    Yii::t('lists', 'Sync "{primary}" list with "{secondary}" list', array('{primary}' => $primaryList->name, '{secondary}' => $secondaryList->name)),
                ),
                'fromText' => Yii::t('lists', 'Sync "{primary}" list with "{secondary}" list', array('{primary}' => $primaryList->name, '{secondary}' => $secondaryList->name)),
            ));
            return $this->render('sync-lists', compact('syncTool', 'jsonAttributes'));
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$primaryList->list_id);
        $criteria->limit  = $syncTool->limit;
        $criteria->offset = $syncTool->offset;
        $subscribers = ListSubscriber::model()->findAll($criteria);
        
        if (empty($subscribers)) {
            $syncTool->progress_text = Yii::t('lists', 'The sync process is done.');
            $syncTool->finished      = 1;
            return $this->renderJson(array(
                'attributes'           => $syncTool->attributes,
                'formatted_attributes' => $syncTool->getFormattedAttributes(),
            ));
        }

        $syncTool->progress_text = Yii::t('lists', 'The sync process is running, please wait...');
        $syncTool->finished      = 0;
        
        $transaction = Yii::app()->getDb()->beginTransaction();

        try {
            
            foreach ($subscribers as $subscriber) {
                $syncTool->processed_total++;
                $syncTool->processed_success++;
                
                $exists = ListSubscriber::model()->findByAttributes(array(
                    'list_id' => $secondaryList->list_id,
                    'email'   => $subscriber->email,
                ));

                if (empty($exists) && $syncTool->missing_subscribers_action == ListsSyncTool::MISSING_SUBSCRIBER_ACTION_CREATE_SECONDARY) {
                    $subscriber->copyToList($secondaryList->list_id, false);
                    continue;
                }
                
                if (!empty($exists)) {
                    
                    if ($syncTool->duplicate_subscribers_action == ListsSyncTool::DUPLICATE_SUBSCRIBER_ACTION_DELETE_SECONDARY) {
                        $exists->delete();
                        continue;
                    }
        
                }
                
                if (!empty($exists) && $subscriber->status != $exists->status) {
                    if ($syncTool->distinct_status_action == ListsSyncTool::DISTINCT_STATUS_ACTION_UPDATE_PRIMARY) {
                        $subscriber->status = $exists->status;
                        $subscriber->save(false);
                        continue;
                    }
                    if ($syncTool->distinct_status_action == ListsSyncTool::DISTINCT_STATUS_ACTION_UPDATE_SECONDARY) {
                        $exists->status = $subscriber->status;
                        $exists->save(false);
                        continue;
                    }
                    if ($syncTool->distinct_status_action == ListsSyncTool::DISTINCT_STATUS_ACTION_DELETE_SECONDARY) {
                        $exists->delete();
                        continue;
                    }
                }
            }    
            
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
        }

        $syncTool->percentage  = round((($syncTool->processed_total / $syncTool->count) * 100), 2);
        $syncTool->offset += $syncTool->limit;
     
        return $this->renderJson(array(
            'attributes'           => $syncTool->attributes,
            'formatted_attributes' => $syncTool->getFormattedAttributes(),
        ));
    }

    /**
     * @return BaseController
     */
    public function actionSplit()
    {
        ini_set('memory_limit', -1);
        set_time_limit(0);
        
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        if (!$request->isPostRequest) {
            $this->redirect(array('lists_tools/index'));
        }
        
        $customer  = Yii::app()->customer->getModel();
        $splitTool = new ListSplitTool();
        $splitTool->attributes  = (array)$request->getPost($splitTool->modelName, array());
        $splitTool->customer_id = $customer->customer_id;
        
        if (!$splitTool->validate()) {
            $message = $splitTool->shortErrors->getAllAsString();
            if ($request->isAjaxRequest) {
                $splitTool->progress_text = $message;
                $splitTool->finished      = 1;
                return $this->renderJson(array(
                    'attributes'           => $splitTool->attributes,
                    'formatted_attributes' => $splitTool->getFormattedAttributes(),
                ));
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }
        
        if (!$splitTool->getList()) {
            $message = Yii::t('lists', 'Invalid list selection!');
            if ($request->isAjaxRequest) {
                $splitTool->progress_text = $message;
                $splitTool->finished      = 1;
                return $this->renderJson(array(
                    'attributes'           => $splitTool->attributes,
                    'formatted_attributes' => $splitTool->getFormattedAttributes(),
                ));
            }
            $notify->addError($message);
            $this->redirect(array('lists_tools/index'));
        }

        if (!$request->isAjaxRequest) {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', $splitTool->getList()->list_id);
            $criteria->addInCondition('status', array(Campaign::STATUS_PROCESSING, Campaign::STATUS_SENDING, Campaign::STATUS_PENDING_SENDING));
            $criteria->limit = 1;
            $campaigns = Campaign::model()->findAll($criteria);
            
            if (!empty($campaigns)) {
                $notify->addError(Yii::t('lists', 'It seems that you have ongoing campaigns using this list. Please pause them before running this action.'));
                $this->redirect(array('lists_tools/index'));
            }
            
            $splitTool->count = ListSubscriber::model()->countByAttributes(array('list_id' => $splitTool->getList()->list_id));
            if ($splitTool->count < $splitTool->sublists) {
                $splitTool->sublists = $splitTool->count;
            }
            $splitTool->per_list = floor($splitTool->count / $splitTool->sublists);
            if ($splitTool->limit > $splitTool->per_list) {
                $splitTool->limit = $splitTool->per_list;
            }
        }
  
        $jsonAttributes = CJSON::encode(array(
            'attributes'           => $splitTool->attributes,
            'formatted_attributes' => $splitTool->getFormattedAttributes(),
        ));
        
        if (!$request->isAjaxRequest) {
            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('lists', 'Split list'), 
                'pageHeading'       => Yii::t('lists', 'Split list'), 
                'pageBreadcrumbs'   => array(
                    Yii::t('lists', 'Tools') => $this->createUrl('tools/index'),
                    Yii::t('lists', 'Split list')
                ),
            ));
            return $this->render('split-list', compact('splitTool', 'jsonAttributes'));
        }
        
        if ($splitTool->page >= ($splitTool->sublists - 1)) {
            $splitTool->progress_text = Yii::t('lists', 'The split process is done.');
            $splitTool->finished      = 1;
            return $this->renderJson(array(
                'attributes'           => $splitTool->attributes,
                'formatted_attributes' => $splitTool->getFormattedAttributes(),
            ));
        }
        
        if (!($copyList = $splitTool->getList()->copy())) {
            $splitTool->progress_text = Yii::t('lists', 'Unable to create a copy from the initial list.');
            $splitTool->finished      = 1;
            return $this->renderJson(array(
                'attributes'           => $splitTool->attributes,
                'formatted_attributes' => $splitTool->getFormattedAttributes(),
            ));
        }
        
        $copyList->name = preg_replace('/\#(\d+)$/', '#' . ((int)$splitTool->page + 1), $copyList->name);
        $copyList->save(false);
        $counter = 0;
        
        $db   = Yii::app()->db;
        $rows = $db->createCommand()
            ->select('subscriber_id')
            ->from('{{list_subscriber}}')
            ->where('list_id = :lid', array(':lid' => $splitTool->getList()->list_id))
            ->order('subscriber_id DESC')
            ->limit($splitTool->limit)
            ->queryAll();

        // 1.6.4
        $splitTool->getList()->flushSubscribersCountCache();
        $copyList->flushSubscribersCountCache();
        //

        while (!empty($rows)) {
            $subscriberIDS = array();
            foreach ($rows as $row) {
                $subscriberIDS[] = (int)$row['subscriber_id'];
            }
            
            try {
                $condition = 'list_id = '.(int)$splitTool->getList()->list_id.' AND subscriber_id IN(' . implode(',', $subscriberIDS) . ')';
                $db->createCommand()->update('{{list_subscriber}}', array('list_id' => (int)$copyList->list_id), $condition);
                
                foreach ($copyList->copyListFieldsMap as $oldFieldId => $newFieldId) {
                    $condition = 'field_id = '.(int)$oldFieldId.' AND subscriber_id IN(' . implode(',', $subscriberIDS) . ')';
                    $db->createCommand()->update('{{list_field_value}}', array('field_id' => (int)$newFieldId), $condition);
                }    
            } catch (Exception $e) {
                $splitTool->progress_text = $e->getMessage();
                $splitTool->finished      = 1;
                return $this->renderJson(array(
                    'attributes'           => $splitTool->attributes,
                    'formatted_attributes' => $splitTool->getFormattedAttributes(),
                ));
            }
            
            $counter += $splitTool->limit;
            if ($counter >= $splitTool->per_list) {
                break;
            }
            
            $rows = $db->createCommand()
                ->select('subscriber_id')
                ->from('{{list_subscriber}}')
                ->where('list_id = :lid', array(':lid' => $splitTool->getList()->list_id))
                ->order('subscriber_id DESC')
                ->limit($splitTool->limit)
                ->queryAll();
        }
        
        $splitTool->page++;
        $splitTool->progress_text = Yii::t('lists', 'Successfully created and moved subscribers into {name} list. Going further, please wait...', array('{name}' => $copyList->name));
        $splitTool->percentage    = round((($splitTool->page / (($splitTool->sublists - 1))) * 100), 2);

        return $this->renderJson(array(
            'attributes'           => $splitTool->attributes,
            'formatted_attributes' => $splitTool->getFormattedAttributes(),
        ));
    }
}