<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_subscribersController
 *
 * Handles the actions for list subscribers related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_subscribersController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::import('customer.components.field-builder.*');

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('subscribers.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete, subscribe, unsubscribe, disable, bulk_action',
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
                'class' => 'customer.components.behaviors.ListFieldsControllerCallbacksBehavior',
            ),
        ), parent::behaviors());
    }

    /**
     * List available subscribers for a list
     */
    public function actionIndex($list_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $request    = Yii::app()->request;
        $postFilter = (array)$request->getPost('filter', array());
        $subscriber = new ListSubscriber();

        $subscriberStatusesList = $subscriber->getFilterStatusesList();
        
        // since 1.3.6.2
        // filters
        $getFilterSet = false;
        $getFilter = array(
            'campaigns' => array(
                'campaign' => null,
                'action'   => null,
                'atu'      => null, // action time unit
                'atuc'     => null, // action time unit count
            )
        );
        if ($request->getQuery('filter') && is_array($request->getQuery('filter'))) {
            $getFilter = CMap::mergeArray($getFilter, $request->getQuery('filter'));
            $getFilterSet = true;
        }
        
        // list campaigns for filters
        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id, name';
        $criteria->compare('list_id', $list->list_id);
        $criteria->addInCondition('status', array(Campaign::STATUS_SENT, Campaign::STATUS_SENDING));
        $criteria->order = 'campaign_id DESC';
        $campaigns = Campaign::model()->findAll($criteria);
        
        $listCampaigns = array();
        foreach ($campaigns as $campaign) {
            $listCampaigns[$campaign->campaign_id] = $campaign->name;
        }
        //
        
        /**
         * NOTE:
         * Following criteria will use filesort and create a temp table because of the group by condition.
         * So far, beside subqueries this is the only optimal way i have found to work fine.
         * Needs optimization in the future if will cause problems.
         */
        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(DISTINCT t.subscriber_id) as counter';
        $criteria->compare('t.list_id', $list->list_id);
        $criteria->order = 't.subscriber_id DESC';
        
        // since 1.3.6.2
        if (!empty($getFilter['campaigns']['action'])) {
            $action      = $getFilter['campaigns']['action'];
            $campaignId  = !empty($getFilter['campaigns']['campaign']) ? (int)$getFilter['campaigns']['campaign'] : 0;
            $campaignIds = empty($campaignId) ? array_keys($listCampaigns) : array((int)$campaignId);
            $campaignIds = array_map('intval', $campaignIds);
	        $campaignIds = !empty($campaignIds) ? $campaignIds : array(0);
            $atu  = $subscriber->getFilterTimeUnitValueForDb(!empty($getFilter['campaigns']['atu']) ? (int)$getFilter['campaigns']['atu'] : 0);
            $atuc = !empty($getFilter['campaigns']['atuc']) ? (int)$getFilter['campaigns']['atuc'] : 0;
            $atuc = $atuc > 1024 ? 1024 : $atuc;
            $atuc = $atuc < 0 ? 0 : $atuc;
            
            if (in_array($action, array(ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_OPEN, ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN))) {
                $rel = array(
                    'select'   => false,
                    'together' => true,
                );
                
                if ($action == ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_OPEN) {
                    $rel['joinType']  = 'INNER JOIN';
                    $rel['condition'] = 'trackOpens.campaign_id IN (' . implode(',', $campaignIds) . ')';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' AND trackOpens.date_added >= DATE_SUB(NOW(), INTERVAL %d %s)', $atuc, $atu);
                    }
                } else {
                    $rel['on']        = 'trackOpens.campaign_id IN (' . implode(',', $campaignIds) . ')';
                    $rel['joinType']  = 'LEFT OUTER JOIN';
                    $rel['condition'] = 'trackOpens.subscriber_id IS NULL';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' OR (trackOpens.subscriber_id IS NOT NULL AND (SELECT date_added FROM {{campaign_track_open}} WHERE subscriber_id = trackOpens.subscriber_id ORDER BY date_added DESC LIMIT 1) <= DATE_SUB(NOW(), INTERVAL %d %s))', $atuc, $atu);
                    }
                }
                
                $criteria->with['trackOpens'] = $rel;
            }
            
            if (in_array($action, array(ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_CLICK, ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK))) {
                
                $ucriteria = new CDbCriteria();
                $ucriteria->select = 'url_id';
                $ucriteria->addInCondition('campaign_id', $campaignIds);
                $models = CampaignUrl::model()->findAll($ucriteria);
                $urlIds = array();
                foreach ($models as $model) {
                    $urlIds[] = $model->url_id;
                }

                if (empty($urlIds)) {
                    $urlIds = array(0);
                }
                
                $rel = array(
                    'select'   => false,
                    'together' => true,
                );

                if ($action == ListSubscriber::CAMPAIGN_FILTER_ACTION_DID_CLICK) {
                    $rel['joinType']  = 'INNER JOIN';
                    $rel['condition'] = 'trackUrls.url_id IN (' . implode(',', $urlIds) . ')';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' AND trackUrls.date_added >= DATE_SUB(NOW(), INTERVAL %d %s)', $atuc, $atu);
                    }
                } else {
                    $rel['on']        = 'trackUrls.url_id IN (' . implode(',', $urlIds) . ')';
                    $rel['joinType']  = 'LEFT OUTER JOIN';
                    $rel['condition'] = 'trackUrls.subscriber_id IS NULL';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' OR (trackUrls.subscriber_id IS NOT NULL AND (SELECT date_added FROM {{campaign_track_url}} WHERE subscriber_id = trackUrls.subscriber_id ORDER BY date_added DESC LIMIT 1) <= DATE_SUB(NOW(), INTERVAL %d %s))', $atuc, $atu);
                    }
                }

                $criteria->with['trackUrls'] = $rel;
            }
        }
        //
        
        foreach ($postFilter as $field_id => $value) {
            if (empty($value)) {
                unset($postFilter[$field_id]);
                continue;
            }

            if (is_numeric($field_id)) {
                $model = ListField::model()->findByAttributes(array(
                    'field_id'  => $field_id,
                    'list_id'   => $list->list_id,
                ));
                if (empty($model)) {
                    unset($postFilter[$field_id]);
                }
            }
        }

        if (!empty($postFilter['status']) && in_array($postFilter['status'], array_keys($subscriberStatusesList))) {
            $criteria->compare('status', $postFilter['status']);
        }

        if (!empty($postFilter['uid']) && strlen($postFilter['uid']) == 13) {
            $criteria->compare('subscriber_uid', $postFilter['uid']);
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

            $md = $subscriber->getMetaData();
            foreach ($postFilter as $field_id => $value) {
                if (!is_numeric($field_id)) {
                    continue;
                }
                if ($md->hasRelation('fieldValues'.$field_id)) {
                    continue;
                }
                $md->addRelation('fieldValues'.$field_id, array(ListSubscriber::HAS_MANY, 'ListFieldValue', 'subscriber_id'));
            }

            if (!empty($with)) {
                $criteria->with = $with;
            }
        }

        // count all confirmed subscribers of this list
        $count = $subscriber->count($criteria);

        // instantiate the pagination and apply the limit statement to the query
        $pages = new CPagination($count);
        $pages->pageSize = (int)$subscriber->paginationOptions->getPageSize();
        $pages->applyLimit($criteria);

        // load the required models
        $criteria->select = 't.list_id, t.subscriber_id, t.subscriber_uid, t.email, t.ip_address, t.status, t.date_added';
        $criteria->group = 't.subscriber_id';
        $subscribers = $subscriber->findAll($criteria);

        // 1.3.8.8
        $modelName  = get_class($subscriber) . '_list_' . $list->list_id;
        $optionKey  = sprintf('%s:%s:%s', $modelName, $this->id, $this->action->id);
        $customerId = (int)Yii::app()->customer->getId();
        $optionKey  = sprintf('system.views.grid_view_columns.customers.%d.%s', $customerId, $optionKey);
        
        $storedToggleColumns      = Yii::app()->options->get($optionKey, array());
        $storedToggleColumnsEmpty = empty($storedToggleColumns);
        $displayToggleColumns     = array();
        //
        
        // now, we need to know what columns this list has, that is, all the tags available for this list.
        $columns = array();
        $rows = array();

        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', $list->list_id);
        $criteria->order = 't.sort_order ASC';

        $fields = ListField::model()->findAll($criteria);

        $columns[] = array(
            'label'     => null,
            'field_type'=> 'checkbox',
            'field_id'  => 'bulk_select',
            'value'     => null,
            'checked'   => false,
            'htmlOptions'   => array(),
        );

        $columns[] = array(
            'label'         => Yii::t('app', 'Options'),
            'field_type'    => null,
            'field_id'      => null,
            'value'         => null,
            'htmlOptions'   => array('class' => 'empty-options-header options'),
        );

        $columns[] = array(
            'label'     => Yii::t('list_subscribers', 'Unique ID'),
            'field_type'=> 'text',
            'field_id'  => 'uid',
            'value'     => isset($postFilter['uid']) ? CHtml::encode($postFilter['uid']) : null,
        );
        
        $columns[] = array(
            'label'         => Yii::t('app', 'Date added'),
            'field_type'    => null,
            'field_id'      => 'date_added',
            'value'         => null,
            'htmlOptions'   => array('class' => 'subscriber-date-added'),
        );

        $columns[] = array(
            'label'         => Yii::t('app', 'Ip address'),
            'field_type'    => null,
            'field_id'      => 'ip_address',
            'value'         => null,
            'htmlOptions'   => array('class' => 'subscriber-date-added'),
        );

        $columns[] = array(
            'label'     => Yii::t('app', 'Status'),
            'field_type'=> 'select',
            'field_id'  => 'status',
            'value'     => isset($postFilter['status']) ? CHtml::encode($postFilter['status']) : null,
            'options'   => CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $subscriberStatusesList),
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
            if (empty($column['field_id']) || in_array($column['field_id'], array('bulk_select'))) {
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
        //
        
        // since 1.5.2
        $canSegmentLists = (Yii::app()->customer->getModel()->getGroupOption('lists.can_segment_lists', 'yes') == 'yes');
        
        foreach ($subscribers as $index => $subscriber) {
            $subscriberRow = array('columns' => array());

            // checkbox
            $subscriberRow['columns'][] = CHtml::checkBox('bulk_select[]', false, array('value' => $subscriber->subscriber_id, 'class' => 'bulk-select'));

            $actions = array();
            $actions[] = CHtml::link(IconHelper::make('fa-user'), array('list_subscribers/profile', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('title' => Yii::t('app', 'Profile info'), 'class' => 'btn btn-primary btn-flat btn-xs btn-subscriber-profile-info'));
            $actions[] = CHtml::link(IconHelper::make('envelope'), array('list_subscribers/campaigns', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('title' => Yii::t('app', 'Campaigns sent to this subscriber'), 'class' => 'btn btn-primary btn-flat btn-xs'));

            if ($subscriber->getCanBeEdited()) {
                $actions[] = CHtml::link(IconHelper::make('update'), array('list_subscribers/update', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat btn-xs'));
            }
            
            if ($subscriber->getCanBeUnsubscribed() && $subscriber->isConfirmed) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-log-out'), array('list_subscribers/unsubscribe', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat btn-xs unsubscribe', 'title' => Yii::t('app', 'Unsubscribe'), 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to unsubscribe this subscriber?')));
            } elseif ($subscriber->getCanBeConfirmed() && $subscriber->isUnconfirmed) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-log-in'), array('list_subscribers/subscribe', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat subscribe', 'title' => Yii::t('list_subscribers', 'Subscribe back'), 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to subscribe back this unsubscriber?')));
            } elseif ($subscriber->getCanBeConfirmed() && $subscriber->isUnsubscribed) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-log-in'), array('list_subscribers/subscribe', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat subscribe', 'title' => Yii::t('list_subscribers', 'Confirm subscriber'), 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to confirm this subscriber?')));
            } elseif ($subscriber->getCanBeConfirmed() && $subscriber->isUnapproved) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-log-in'), array('list_subscribers/subscribe', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat subscribe', 'title' => Yii::t('list_subscribers', 'Approve subscriber'), 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to approve this subscriber?')));
            } elseif ($subscriber->getCanBeConfirmed() && $subscriber->isDisabled) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-log-in'), array('list_subscribers/subscribe', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat subscribe', 'title' => Yii::t('list_subscribers', 'Enable subscriber'), 'data-message' => Yii::t('list_subscribers', 'This subscriber has been disabled, are you sure you want to enable it back?')));
            }

            // since 1.5.3
            $actions[] = CHtml::link(IconHelper::make('export'), array('list_subscribers/profile_export', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('target' => '_blank', 'title' => Yii::t('app', 'Export profile info'), 'class' => 'btn btn-primary btn-flat btn-xs btn-export-subscriber-profile-info'));

            // since 1.5.2
            if ($canSegmentLists) {
                $actions[] = CHtml::link(IconHelper::make('fa-envelope-o'), array('list_subscribers/campaign_for_subscriber', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('title' => Yii::t('app', 'Create campaign for this subscriber'), 'class' => 'btn btn-primary btn-flat btn-xs'));
            }

            if ($subscriber->getCanBeDisabled()) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-remove'), array('list_subscribers/disable', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat unsubscribe', 'title' => Yii::t('list_subscribers', 'Disable subscriber'), 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to disable this subscriber?')));
            }

            if ($subscriber->getCanBeDeleted()) {
                $actions[] = CHtml::link(IconHelper::make('glyphicon-remove-circle'), array('list_subscribers/delete', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-danger btn-flat delete', 'title' => Yii::t('app', 'Delete'), 'data-message' => Yii::t('app', 'Are you sure you want to delete this item? There is no coming back after you do it.')));
            }
            
            $subscriberRow['columns'][] = $this->renderPartial('_options-column', compact('actions'), true);
            
            if (in_array('uid', $storedToggleColumns)) {
                $subscriberRow['columns'][] = CHtml::link($subscriber->subscriber_uid, Yii::app()->createUrl('list_subscribers/update', array('list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid)));
            }
            if (in_array('date_added', $storedToggleColumns)) {
                $subscriberRow['columns'][] = $subscriber->dateAdded;
            }
            if (in_array('ip_address', $storedToggleColumns)) {
                $subscriberRow['columns'][] = $subscriber->ip_address;
            }
            if (in_array('status', $storedToggleColumns)) {
                $subscriberRow['columns'][] = $subscriber->getGridViewHtmlStatus();
            }

            foreach ($fields as $field) {
                if (!in_array($field->field_id, $storedToggleColumns)) {
                    continue;
                }
                
                if ($field->tag == 'EMAIL') {
                    $value = $subscriber->displayEmail;
                    $subscriberRow['columns'][] = CHtml::encode($value);
                    continue;
                }
                
                $criteria = new CDbCriteria();
                $criteria->select = 't.value';
                $criteria->compare('field_id', $field->field_id);
                $criteria->compare('subscriber_id', $subscriber->subscriber_id);
                $values = ListFieldValue::model()->findAll($criteria);

                $value = array();
                foreach ($values as $val) {
                    $value[] = $val->value;
                }
                
                $subscriberRow['columns'][] = CHtml::encode(implode(', ', $value));
            }

            if (count($subscriberRow['columns']) == count($columns)) {
                $rows[] = $subscriberRow;
            }
        }

        if ($request->isPostRequest && $request->isAjaxRequest) {
            return $this->renderPartial('_list', compact('list', 'subscriber', 'columns', 'rows', 'pages', 'count'));
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('list_subscribers', 'Your mail list subscribers'),
            'pageHeading'       => Yii::t('list_subscribers', 'List subscribers'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Subscribers') => $this->createUrl('list_subscribers/index', array('list_uid' => $list->list_uid)),
                Yii::t('app', 'View all')
            )
        ));

        $subBulkFromSource = new ListSubscriberBulkFromSource();
        $subBulkFromSource->list_id = $list->list_id;

        $this->render('index', compact('list', 'subscriber', 'columns', 'rows', 'pages', 'count', 'subBulkFromSource', 'getFilter', 'getFilterSet', 'listCampaigns', 'displayToggleColumns'));
    }

    /**
     * Create / Add a new subscriber in a list
     */
    public function actionCreate($list_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $request    = Yii::app()->request;
        $hooks      = Yii::app()->hooks;

        $listFields = ListField::model()->findAll(array(
            'condition' => 'list_id = :lid',
            'params'    => array(':lid' => $list->list_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($listFields)) {
            throw new CHttpException(404, Yii::t('list_fields', 'Your mail list does not have any field defined.'));
        }

        $usedTypes = array();
        foreach ($listFields as $field) {
            $usedTypes[] = $field->type->type_id;
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $types = ListFieldType::model()->findAll($criteria);

        $subscriber = new ListSubscriber();
        $subscriber->list_id = $list->list_id;

        $instances = array();

        foreach ($types as $type) {

            if (empty($type->identifier) || !is_file(Yii::getPathOfAlias($type->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $type->class_alias, array(
                'fieldType'     => $type,
                'list'          => $list,
                'subscriber'    => $subscriber,
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

                $customer                = $list->customer;
                $maxSubscribersPerList   = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
                $maxSubscribers          = (int)$customer->getGroupOption('lists.max_subscribers', -1);

                if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
                    $criteria = new CDbCriteria();
                    $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

                    if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
                        $criteria->addInCondition('t.list_id', $listsIds);
                        $totalSubscribersCount = ListSubscriber::model()->count($criteria);
                        if ($totalSubscribersCount >= $maxSubscribers) {
                            throw new Exception(Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'));
                        }
                    }

                    if ($maxSubscribersPerList > -1) {
                        $criteria->compare('t.list_id', (int)$list->list_id);
                        $listSubscribersCount = ListSubscriber::model()->count($criteria);
                        if ($listSubscribersCount >= $maxSubscribersPerList) {
                            throw new Exception(Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'));
                        }
                    }
                }

                $attributes = (array)$request->getPost($subscriber->modelName, array());
                if (empty($subscriber->ip_address)) {
                    $subscriber->ip_address = Yii::app()->request->getUserHostAddress();
                }
                if (isset($attributes['status']) && in_array($attributes['status'], array_keys($subscriber->getStatusesList()))) {
                    $subscriber->status = $attributes['status'];
                } else {
                    $subscriber->status = ListSubscriber::STATUS_UNCONFIRMED;
                }

                if (!$subscriber->save()) {
                    if ($subscriber->hasErrors()) {
                        throw new Exception($subscriber->shortErrors->getAllAsString());
                    }
                    throw new Exception(Yii::t('app', 'Temporary error, please contact us if this happens too often!'));
                }

                // raise event
                $this->callbacks->onSubscriberSave(new CEvent($this->callbacks, array(
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
                $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                )));

                $transaction->commit();

            } catch (Exception $e) {

                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());

                // bind default save error event handler
                $this->callbacks->onSubscriberSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onSubscriberSaveError(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list
                )));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'   => $this,
                'success'      => Yii::app()->notify->hasSuccess,
                'subscriber'   => $subscriber,
            )));

            if ($collection->success) {
                if ($request->getPost('next_action') && $request->getPost('next_action') == 'create-new') {
                    $this->redirect(array('list_subscribers/create', 'list_uid' => $subscriber->list->list_uid));
                }
                $this->redirect(array('list_subscribers/update', 'list_uid' => $subscriber->list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid));
            }
        }

        // raise event. simply the fields are shown
        $this->callbacks->onSubscriberFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // add the default sorting of fields actions and raise the event
        $this->callbacks->onSubscriberFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onSubscriberFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('list_subscribers', 'Add a new subscriber to your list.'),
            'pageHeading'       => Yii::t('list_subscribers', 'Add a new subscriber to your list.'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Subscribers') => $this->createUrl('list_subscribers/index', array('list_uid' => $list->list_uid)),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact('fieldsHtml', 'list', 'subscriber'));
    }

    /**
     * Update existing list subscriber
     */
    public function actionUpdate($list_uid, $subscriber_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        
        if ($list->customer->getGroupOption('lists.can_edit_own_subscribers', 'yes') != 'yes') {
            $notify->addError(Yii::t('list_subscribers', 'You are not allowed to edit subscribers at this time!'));
            $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }

        $listFields = ListField::model()->findAll(array(
            'condition' => 'list_id = :lid',
            'params'    => array(':lid' => $list->list_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($listFields)) {
            throw new CHttpException(404, Yii::t('list', 'Your mail list does not have any field defined.'));
        }

        $usedTypes = array();
        foreach ($listFields as $field) {
            $usedTypes[] = $field->type->type_id;
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $types = ListFieldType::model()->findAll($criteria);

        $instances = array();

        foreach ($types as $type) {

            if (empty($type->identifier) || !is_file(Yii::getPathOfAlias($type->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $type->class_alias, array(
                'fieldType'     => $type,
                'list'          => $list,
                'subscriber'    => $subscriber,
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

                $attributes = (array)$request->getPost($subscriber->modelName, array());
                if (empty($subscriber->ip_address)) {
                    $subscriber->ip_address = Yii::app()->request->getUserHostAddress();
                }
                if (isset($attributes['status']) && in_array($attributes['status'], array_keys($subscriber->getStatusesList()))) {
                    $subscriber->status = $attributes['status'];
                } else {
                    $subscriber->status = ListSubscriber::STATUS_UNCONFIRMED;
                }

                // since 1.3.5
                if ($subscriber->status == ListSubscriber::STATUS_CONFIRMED) {

                    if (Yii::app()->customer->getModel()->getGroupOption('lists.can_mark_blacklisted_as_confirmed', 'yes') === 'yes') {

                        // global blacklist and customer blacklist
                    	$subscriber->removeFromBlacklistByEmail();

                    } else {

                        // only customer blacklist
                        CustomerEmailBlacklist::model()->deleteAllByAttributes(array(
                            'customer_id' => $subscriber->list->customer_id,
                            'email'       => $subscriber->email,
                        ));
                    }
                }

                if (!$subscriber->save()) {
                    if ($subscriber->hasErrors()) {
                        throw new Exception($subscriber->shortErrors->getAllAsString());
                    }
                    throw new Exception(Yii::t('app', 'Temporary error, please contact us if this happens too often!'));
                }

                // raise event
                $this->callbacks->onSubscriberSave(new CEvent($this->callbacks, array(
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
                $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                )));

                $transaction->commit();

            } catch (Exception $e) {

                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());

                // bind default save error event handler
                $this->callbacks->onSubscriberSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onSubscriberSaveError(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list
                )));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'   => $this,
                'success'      => Yii::app()->notify->hasSuccess,
                'subscriber'   => $subscriber,
            )));

            if ($collection->success) {
                if ($request->getPost('next_action') && $request->getPost('next_action') == 'create-new') {
                    $this->redirect(array('list_subscribers/create', 'list_uid' => $subscriber->list->list_uid));
                }
            }
        }

        // raise event. simply the fields are shown
        $this->callbacks->onSubscriberFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // add the default sorting of fields actions and raise the event
        $this->callbacks->onSubscriberFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onSubscriberFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('list_subscribers', 'Update existing list subscriber.'),
            'pageHeading'       => Yii::t('list_subscribers', 'Update existing list subscriber.'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Subscribers') => $this->createUrl('list_subscribers/index', array('list_uid' => $list->list_uid)),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('fieldsHtml', 'list', 'subscriber'));
    }

    /**
     * Campaigns sent to this subscriber
     */
    public function actionCampaigns($list_uid, $subscriber_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $request    = Yii::app()->request;

        $model = new CampaignDeliveryLog('search');
        $model->campaign_id   = -1;
        $model->subscriber_id = (int)$subscriber->subscriber_id;
        $model->status        = null;

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('list_subscribers', 'Subscriber campaigns'),
            'pageHeading'     => Yii::t('list_subscribers', 'Subscriber campaigns'),
            'pageBreadcrumbs' => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Subscribers') => $this->createUrl('list_subscribers/index', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Campaigns') => $this->createUrl('list_subscribers/campaigns', array('list_uid' => $list_uid, 'subscriber_uid' => $subscriber_uid)),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('campaigns', compact('model', 'list', 'subscriber'));
    }

    /**
     * Campaigns sent to this subscriber, export
     */
    public function actionCampaigns_export($list_uid, $subscriber_uid)
    {
        set_time_limit(0);
        
        $notify      = Yii::app()->notify;
        $list        = $this->loadListModel($list_uid);
        $subscriber  = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $inCampaigns = array();
        
        $logs = CampaignDeliveryLog::model()->findAllByAttributes(array(
            'subscriber_id' => $subscriber->subscriber_id,
        ));
        foreach ($logs as $log) {
            $inCampaigns[] = $log->campaign_id;
        }

        $logs = CampaignDeliveryLogArchive::model()->findAllByAttributes(array(
            'subscriber_id' => $subscriber->subscriber_id,
        ));
        foreach ($logs as $log) {
            $inCampaigns[] = $log->campaign_id;
        }

        $inCampaigns = array_unique($inCampaigns);
        if (empty($inCampaigns)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('campaign_id', $inCampaigns);
        
        $models = Campaign::model()->findAll($criteria);
        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('campaigns.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes);
        $columns    = array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes));
        $columns    = CMap::mergeArray($columns, array(
            'group'   => $models[0]->getAttributeLabel('group_id'),
            'list'    => $models[0]->getAttributeLabel('list_id'),
            'segment' => $models[0]->getAttributeLabel('segment_id'),
        ));
        @fputcsv($fp, $columns, ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->attributes);
            $attributes = CMap::mergeArray($attributes, array(
                'group'   => $model->group_id   ? $model->group->name   : '',
                'list'    => $model->list_id    ? $model->list->name    : '',
                'segment' => $model->segment_id ? $model->segment->name : '',
            ));
            @fputcsv($fp, $attributes, ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * Create a campaign for this subscriber only
     * 
     * @param $list_uid
     * @param $subscriber_uid
     * @throws CHttpException
     */
    public function actionCampaign_for_subscriber($list_uid, $subscriber_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $notify     = Yii::app()->notify;

        if (!(Yii::app()->customer->getModel()->getGroupOption('lists.can_segment_lists', 'yes') == 'yes')) {
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        
        $segment = new ListSegment();
        $segment->list_id        = $list->list_id;
        $segment->name           = $subscriber->email . ' @ ' . Yii::app()->dateFormatter->formatDateTime(time());
        $segment->operator_match = ListSegment::OPERATOR_MATCH_ALL;
        if (!$segment->save()) {
            $notify->addError(Yii::t('list_subscribers', 'Unable to create campaign for subscriber!'));    
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        
        $operator = ListSegmentOperator::model()->findByAttributes(array(
            'slug' => ListSegmentOperator::IS,
        ));
        if (empty($operator)) {
            $notify->addError(Yii::t('list_subscribers', 'Unable to create campaign for subscriber!'));
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        
        $field = ListField::model()->findByAttributes(array(
            'list_id' => $list->list_id,
            'tag'     => 'EMAIL',
        ));
        if (empty($field)) {
            $notify->addError(Yii::t('list_subscribers', 'Unable to create campaign for subscriber!'));
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        
        $condition = new ListSegmentCondition();
        $condition->segment_id  = $segment->segment_id;
        $condition->operator_id = $operator->operator_id; 
        $condition->field_id    = $field->field_id;
        $condition->value       = $subscriber->email;
        
        if (!$condition->save()) {
            $notify->addError(Yii::t('list_subscribers', 'Unable to create campaign for subscriber!'));
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        
        $campaign = new Campaign();
        $campaign->customer_id = $list->customer_id;
        $campaign->name        = Yii::t('campaigns', 'Send only to {name}', array('{name}' => $subscriber->email));
        $campaign->list_id     = $list->list_id;
        $campaign->segment_id  = $segment->segment_id;
        
        if (!$campaign->save(false)) {
            $notify->addError(Yii::t('list_subscribers', 'Unable to create campaign for subscriber!'));
            return $this->redirect(array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }

        return $this->redirect(array('campaigns/update', 'campaign_uid' => $campaign->campaign_uid));
    }
    
    /**
     * Delete existing list subscriber
     */
    public function actionDelete($list_uid, $subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);

        if ($subscriber->canBeDeleted) {
            $subscriber->delete();
            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                $logAction->subscriberDeleted($subscriber);
            }
        }

        $redirect = null;
        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'list'       => $list,
            'subscriber' => $subscriber,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Disable existing list subscriber
     */
    public function actionDisable($list_uid, $subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);

        if ($subscriber->getCanBeDisabled()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_DISABLED);
        }

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully disabled!'));
            $this->redirect($request->getPost('returnUrl', array('list_subscribers/index', 'list_uid' => $list->list_uid)));
        }
    }
    
    /**
     * Unsubscribe existing list subscriber
     */
    public function actionUnsubscribe($list_uid, $subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);

        if ($subscriber->getCanBeUnsubscribed()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
        }

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully unsubscribed!'));
            $this->redirect($request->getPost('returnUrl', array('list_subscribers/index', 'list_uid' => $list->list_uid)));
        }
    }

    /**
     * Subscribe existing list subscriber
     */
    public function actionSubscribe($list_uid, $subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $oldStatus  = $subscriber->status;

        if ($subscriber->getCanBeApproved()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);
            $subscriber->handleApprove(true)->handleWelcome(true);
        } elseif ($subscriber->getCanBeConfirmed()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);
        }

        if (!$request->isAjaxRequest) {
            if ($oldStatus == ListSubscriber::STATUS_UNSUBSCRIBED) {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list unsubscriber was successfully subscribed back!'));
            } elseif ($oldStatus == ListSubscriber::STATUS_UNAPPROVED) {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber has been approved and notified!'));
            } else {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber has been confirmed!'));
            }
            $this->redirect($request->getPost('returnUrl', array('list_subscribers/index', 'list_uid' => $list->list_uid)));
        }
    }
    
    /**
     * Bulk actions
     */
    public function actionBulk_action($list_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $list       = $this->loadListModel($list_uid);
        $subscriber = new ListSubscriber();
        $action     = $request->getPost('action');

        if (!in_array($action, array_keys($subscriber->getBulkActionsList()))) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        set_time_limit(0);

        $customer = Yii::app()->customer->getModel();

        $selectedSubscribers = (array)$request->getPost('bulk_select', array());
        $selectedSubscribers = array_values($selectedSubscribers);
        $selectedSubscribers = array_map('intval', $selectedSubscribers);

        // since 1.3.5.9
        $redirect = null;
        if (!$request->isAjaxRequest) {
            $redirect = $request->getPost('returnUrl', array('list_subscribers/index', 'list_uid' => $list->list_uid));
        }
        Yii::app()->hooks->doAction('controller_action_bulk_action', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'redirect'   => $redirect,
            'list'       => $list,
            'action'     => $action,
            'data'       => $selectedSubscribers,
        )));
        $selectedSubscribers = $collection->data;
        //

        if (!empty($selectedSubscribers)) {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$list->list_id);
            $criteria->addInCondition('subscriber_id', $selectedSubscribers);
            
            if ($action == ListSubscriber::BULK_SUBSCRIBE) {

                $statusNotIn          = array(ListSubscriber::STATUS_CONFIRMED);
                $canMarkBlAsConfirmed = $customer->getGroupOption('lists.can_mark_blacklisted_as_confirmed', 'no') === 'yes';

                $criteria->addNotInCondition('status', $statusNotIn);
                $subscribers = ListSubscriber::model()->findAll($criteria);
                
                foreach ($subscribers as $subscriber) {
                    
                    // save the flag here
                    $approve    = $subscriber->getIsUnapproved();
                    $initStatus = $subscriber->status;
                    
                    // confirm the subscriber
                    $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);
                    
                    // and if the above flag is bool, proceed with approval stuff
                    if ($approve) {
                        $subscriber->handleApprove(true)->handleWelcome(true);
                    }
                    
                    // finally remove from blacklist
                    if ($initStatus == ListSubscriber::STATUS_BLACKLISTED) {

                        if ($canMarkBlAsConfirmed) {

                            // global blacklist and customer blacklist
                            $subscriber->removeFromBlacklistByEmail();

                        } else {

                            // only customer blacklist
                            CustomerEmailBlacklist::model()->deleteAllByAttributes(array(
                                'customer_id' => $subscriber->list->customer_id,
                                'email'       => $subscriber->email,
                            ));
                        }
                    }
                    
                    // 1.3.8.8 - remove from moved table
                    ListSubscriberListMove::model()->deleteAllByAttributes(array(
                        'source_subscriber_id' => $subscriber->subscriber_id,
                    ));
                    
                }

            } elseif ($action == ListSubscriber::BULK_UNSUBSCRIBE) {

                $criteria->addNotInCondition('status', array(ListSubscriber::STATUS_BLACKLISTED, ListSubscriber::STATUS_MOVED));

                ListSubscriber::model()->updateAll(array(
                    'status'        => ListSubscriber::STATUS_UNSUBSCRIBED,
                    'last_updated'  => new CDbExpression('NOW()'),
                ), $criteria);

            } elseif ($action == ListSubscriber::BULK_DISABLE) {
                
                $criteria->addInCondition('status', array(ListSubscriber::STATUS_CONFIRMED));

                ListSubscriber::model()->updateAll(array(
                    'status'        => ListSubscriber::STATUS_DISABLED,
                    'last_updated'  => new CDbExpression('NOW()'),
                ), $criteria);
           
            } elseif ($action == ListSubscriber::BULK_UNCONFIRM) {

                $criteria->addInCondition('status', array(ListSubscriber::STATUS_CONFIRMED));

                ListSubscriber::model()->updateAll(array(
                    'status'        => ListSubscriber::STATUS_UNCONFIRMED,
                    'last_updated'  => new CDbExpression('NOW()'),
                ), $criteria);
                
            } elseif ($action == ListSubscriber::BULK_RESEND_CONFIRMATION_EMAIL) {

                $criteria->addInCondition('status', array(ListSubscriber::STATUS_UNCONFIRMED));
                $subscribers = ListSubscriber::model()->findAll($criteria);
                $options     = Yii::app()->options;
                
                
                foreach ($subscribers as $subscriber) {

                    $pageType = ListPageType::model()->findBySlug('subscribe-confirm-email');
                    if (empty($pageType)) {
                        continue;
                    }

                    $page = ListPage::model()->findByAttributes(array(
                        'list_id' => $subscriber->list_id,
                        'type_id' => $pageType->type_id
                    ));

                    $content = !empty($page->content) ? $page->content : $pageType->content;
                    $subject = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
                    $list    = $subscriber->list;

                    $subscribeUrl = $options->get('system.urls.frontend_absolute_url');
                    $subscribeUrl .= 'lists/' . $list->list_uid . '/confirm-subscribe/' . $subscriber->subscriber_uid;

                    // 1.5.3
                    $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
                    $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid;

                    $searchReplace = array(
                        '[LIST_NAME]'     => $list->display_name,
                        '[COMPANY_NAME]'  => !empty($list->company) ? $list->company->name : null,
                        '[SUBSCRIBE_URL]' => $subscribeUrl,
                        '[CURRENT_YEAR]'  => date('Y'),

                        // 1.5.3
                        '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
                        '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
                        '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
                    );

                    // since 1.5.2
                    $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
                    foreach ($subscriberCustomFields as $field => $value) {
                        $searchReplace[$field] = $value;
                    }
                    //
                    
                    $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
                    $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);

                    // 1.5.3
                    if (CampaignHelper::isTemplateEngineEnabled()) {
                        $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
                        $subject = CampaignHelper::parseByTemplateEngine($subject, $searchReplace);
                    }
                    
                    $email = new TransactionalEmail();
                    $email->to_name   = $subscriber->email;
                    $email->to_email  = $subscriber->email;
                    $email->from_name = $list->default->from_name;
                    $email->subject   = $subject;
                    $email->body      = $content;
                    $email->save();
                }

            } elseif ($action == ListSubscriber::BULK_DELETE) {

               ListSubscriber::model()->deleteAll($criteria);

            }

	        // since 1.6.4
	        if (!empty($selectedSubscribers)) {
		        $list->flushSubscribersCountCache();
	        }
        }

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('app', 'Bulk action completed successfully!'));
        }

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Bulk action from source
     */
    public function actionBulk_from_source($list_uid)
    {
        set_time_limit(0);

        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $ioFilter   = Yii::app()->ioFilter;
        $list       = $this->loadListModel($list_uid);
        $model      = new ListSubscriberBulkFromSource();
        $redirect   = array('list_subscribers/index', 'list_uid' => $list_uid);
        $customer   = Yii::app()->customer->getModel();
        
        $emailAddresses    = array();
        $model->attributes = (array)$request->getPost($model->modelName, array());

        if (!in_array($model->status, array_keys($model->getBulkActionsList()))) {
            $this->redirect($redirect);
        }

        if (!empty($model->bulk_from_text)) {
            $lines = explode("\n", $model->bulk_from_text);
            foreach ($lines as $line) {
                $emails = explode(',', $line);
                $emails = array_map('trim', $emails);
                foreach ($emails as $email) {
                    if (FilterVarHelper::email($email)) {
                        $emailAddresses[] = $email;
                    }
                }
            }
        }
        $emailAddresses = array_unique($emailAddresses);

        $model->bulk_from_file = CUploadedFile::getInstance($model, 'bulk_from_file');
        if (!empty($model->bulk_from_file)) {
            if (!$model->validate()) {
                $notify->addError($model->shortErrors->getAllAsString());
            } else {
                $file = new SplFileObject($model->bulk_from_file->tempName);
                $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
                while (!$file->eof()) {
                    $row = $file->fgetcsv();
                    if (empty($row)) {
                        continue;
                    }
                    $row = $ioFilter->stripPurify($row);
                    foreach ($row as $value) {
                        if (empty($value)) {
                            continue;
                        }
                        $emails = explode(',', $value);
                        $emails = array_map('trim', $emails);
                        foreach ($emails as $email) {
                            if (FilterVarHelper::email($email)) {
                                $emailAddresses[] = $email;
                            }
                        }
                    }
                }
            }
        }
        $emailAddresses = array_unique($emailAddresses);

        $total = 0;
        while (!empty($emailAddresses)) {
            $emails = array_splice($emailAddresses, 0, 10);

            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$list->list_id);
            $criteria->addInCondition('email', $emails);

            if ($model->status == ListSubscriber::BULK_SUBSCRIBE) {

                $statusNotIn          = array(ListSubscriber::STATUS_CONFIRMED);
                $canMarkBlAsConfirmed = $customer->getGroupOption('lists.can_mark_blacklisted_as_confirmed', 'no') === 'yes';

                $criteria->addNotInCondition('status', $statusNotIn);
                $subscribers = ListSubscriber::model()->findAll($criteria);

                foreach ($subscribers as $subscriber) {

                    // save the flag here
                    $approve    = $subscriber->getIsUnapproved();
                    $initStatus = $subscriber->status;

                    // confirm the subscriber
                    $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);

                    // and if the above flag is bool, proceed with approval stuff
                    if ($approve) {
                        $subscriber->handleApprove(true)->handleWelcome(true);
                    }

                    // finally remove from blacklist
                    if ($initStatus == ListSubscriber::STATUS_BLACKLISTED) {

                        if ($canMarkBlAsConfirmed) {

                            // global blacklist and customer blacklist
                            $subscriber->removeFromBlacklistByEmail();

                        } else {

                            // only customer blacklist
                            CustomerEmailBlacklist::model()->deleteAllByAttributes(array(
                                'customer_id' => $subscriber->list->customer_id,
                                'email'       => $subscriber->email,
                            ));
                        }
                    }

                    // 1.3.8.8 - remove from moved table
                    ListSubscriberListMove::model()->deleteAllByAttributes(array(
                        'source_subscriber_id' => $subscriber->subscriber_id,
                    ));
                }

            } elseif ($model->status == ListSubscriber::BULK_UNSUBSCRIBE) {

                $criteria->addNotInCondition('status', array(ListSubscriber::STATUS_BLACKLISTED, ListSubscriber::STATUS_MOVED));

                ListSubscriber::model()->updateAll(array(
                    'status'        => ListSubscriber::STATUS_UNSUBSCRIBED,
                    'last_updated'  => new CDbExpression('NOW()'),
                ), $criteria);
          
            } elseif ($model->status == ListSubscriber::BULK_DISABLE) {

                $criteria->addInCondition('status', array(ListSubscriber::STATUS_CONFIRMED));

                ListSubscriber::model()->updateAll(array(
                    'status' => ListSubscriber::STATUS_DISABLED,
                    'last_updated' => new CDbExpression('NOW()'),
                ), $criteria);

            } elseif ($model->status == ListSubscriber::BULK_RESEND_CONFIRMATION_EMAIL) {

                $criteria->addInCondition('status', array(ListSubscriber::STATUS_UNCONFIRMED));
                $subscribers = ListSubscriber::model()->findAll($criteria);
                $options     = Yii::app()->options;
                
                foreach ($subscribers as $subscriber) {

                    $pageType = ListPageType::model()->findBySlug('subscribe-confirm-email');
                    if (empty($pageType)) {
                        continue;
                    }

                    $page = ListPage::model()->findByAttributes(array(
                        'list_id' => $subscriber->list_id,
                        'type_id' => $pageType->type_id
                    ));

                    $content = !empty($page->content) ? $page->content : $pageType->content;
                    $subject = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
                    $list    = $subscriber->list;

                    $subscribeUrl = $options->get('system.urls.frontend_absolute_url');
                    $subscribeUrl .= 'lists/' . $list->list_uid . '/confirm-subscribe/' . $subscriber->subscriber_uid;

                    // 1.5.3
                    $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
                    $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid;

                    $searchReplace = array(
                        '[LIST_NAME]'     => $list->display_name,
                        '[COMPANY_NAME]'  => !empty($list->company) ? $list->company->name : null,
                        '[SUBSCRIBE_URL]' => $subscribeUrl,
                        '[CURRENT_YEAR]'  => date('Y'),

                        // 1.5.3
                        '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
                        '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
                        '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
                    );

                    // since 1.5.2
                    $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
                    foreach ($subscriberCustomFields as $field => $value) {
                        $searchReplace[$field] = $value;
                    }
                    //

                    $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
                    $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);

                    // 1.5.3
                    if (CampaignHelper::isTemplateEngineEnabled()) {
                        $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
                        $subject = CampaignHelper::parseByTemplateEngine($subject, $searchReplace);
                    }

                    $email = new TransactionalEmail();
                    $email->to_name   = $subscriber->email;
                    $email->to_email  = $subscriber->email;
                    $email->from_name = $list->default->from_name;
                    $email->subject   = $subject;
                    $email->body      = $content;
                    $email->save();
                }
                
            } elseif ($model->status == ListSubscriber::BULK_UNCONFIRM) {

                $criteria->addInCondition('status', array(ListSubscriber::STATUS_CONFIRMED));

                ListSubscriber::model()->updateAll(array(
                    'status'        => ListSubscriber::STATUS_UNCONFIRMED,
                    'last_updated'  => new CDbExpression('NOW()'),
                ), $criteria);

            } elseif ($model->status == ListSubscriber::BULK_DELETE) {

               ListSubscriber::model()->deleteAll($criteria);

            }

            $total += count($emails);
        }
        $notify->addSuccess(Yii::t('list_subscribers', 'Action completed, {count} subscribers were affected!', array(
            '{count}'   => $total,
        )));

        // since 1.6.4
        $list->flushSubscribersCountCache();

        $this->redirect($redirect);
    }

    /**
     * Return profile info
     */
    public function actionProfile($list_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('lists/all_subscribers'));
        }
        
        $list = Lists::model()->findByAttributes(array(
            'list_uid' => $list_uid,
        ));
        
        if (empty($list)) {
            return '';
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'        => $list->list_id,
            'subscriber_uid' => $subscriber_uid,
        ));
        
        if (empty($subscriber)) {
            return '';
        }
        
        return $this->renderPartial('_profile-in-modal', array(
            'list'          => $list,
            'subscriber'    => $subscriber,
            'subscriberName'=> $subscriber->getFullName(),
            'optinHistory'  => !empty($subscriber->optinHistory) ? $subscriber->optinHistory : null,
            'optoutHistory' => $subscriber->status == ListSubscriber::STATUS_UNSUBSCRIBED && !empty($subscriber->optoutHistory) ? $subscriber->optoutHistory : null,
        ));
    }

    /**
     * Export profile info
     */
    public function actionProfile_export($list_uid, $subscriber_uid)
    {
        $notify      = Yii::app()->notify;
        $list        = $this->loadListModel($list_uid);
        $subscriber  = $this->loadSubscriberModel($list->list_id, $subscriber_uid);
        $data        = $subscriber->getFullData();
        
        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('subscriber-profile.csv');

        @fputcsv($fp, array_keys($data), ',', '"');

        @fputcsv($fp, array_values($data), ',', '"');

        @fclose($fp);
        
        Yii::app()->end();
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

    /**
     * Helper method to load the list subscriber AR model
     */
    public function loadSubscriberModel($list_id, $subscriber_uid)
    {
        $model = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid'    => $subscriber_uid,
            'list_id'           => (int)$list_id,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
