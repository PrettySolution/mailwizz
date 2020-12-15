<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DashboardController
 *
 * Handles the actions for dashboard related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class DashboardController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => AssetsUrl::js('dashboard.js'))
        ));
        parent::init();
    }

    /**
     * Display dashboard informations
     */
    public function actionIndex()
    {
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('dashboard', 'Dashboard'),
            'pageHeading'       => Yii::t('dashboard', 'Dashboard'),
            'pageBreadcrumbs'   => array(
                Yii::t('dashboard', 'Dashboard'),
            ),
        ));

        // stats
        $timelineItems = $this->getTimelineItems();
        
        // 1.4.5
        $appName     = Yii::app()->apps->getCurrentAppName();
        $glanceStats = Yii::app()->hooks->applyFilters($appName . '_dashboard_glance_stats_list', array(), $this);
        if (empty($glanceStats)) {
            $glanceStats = $this->getGlanceStats();
        }
        $keys = array('count', 'heading', 'icon', 'url');
        foreach ($glanceStats as $index => $stat) {
            foreach ($keys as $key) {
                if (!array_key_exists($key, $stat)) {
                    unset($glanceStats[$index]);
                }
            }    
        }
        //
        
        $renderItems = false;
        foreach ($glanceStats as $stat) {
            if (!empty($stat['count'])) {
                $renderItems = true;
                break;
            }
        }
        
        $this->render('index', compact('timelineItems', 'glanceStats', 'renderItems'));
    }

    /**
     * @return BaseController|void
     */
    public function actionCampaigns()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('dashboard/index'));
        }

        $listId     = (int)$request->getPost('list_id');
        $campaignId = (int)$request->getPost('campaign_id');    
        
        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id, name';
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->compare('status', Campaign::STATUS_SENT);
        if (!empty($listId)) {
            $criteria->compare('list_id', $listId);
        }
        $criteria->order = 'campaign_id DESC';
        $criteria->limit = 30;
        
        $latestCampaigns = Campaign::model()->findAll($criteria);
        $campaignsList   = array();
        foreach ($latestCampaigns as $cmp) {
            $campaignsList[$cmp->campaign_id] = $cmp->name;
        }
        
        if (empty($campaignId) && !empty($latestCampaigns)) {
            $campaignId = $latestCampaigns[0]->campaign_id;
        }

        $campaign = Campaign::model()->findByAttributes(array(
            'customer_id' => (int)Yii::app()->customer->getId(),
            'campaign_id' => $campaignId,
            'status'      => Campaign::STATUS_SENT,
        ));

        if (empty($campaign)) {
            return $this->renderJson(array(
                'html'  => '',
            ));
        }
        
        return $this->renderJson(array(
            'html'  => $this->renderPartial('_campaigns', compact('campaign', 'campaignsList'), true),
        ));
    }

    /**
     * Export
     */
    public function actionExport_recent_activity()
    {
        $notify = Yii::app()->notify;

        $models = CustomerActionLog::model()->findAllByAttributes(array(
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
        HeaderHelper::setDownloadHeaders('recent-activity.csv');

        $attrsList  = array('category', 'message', 'date_added');
        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->getAttributes($attrsList));
        $columns    = array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes));
        @fputcsv($fp, $columns, ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->getAttributes($attrsList));
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * @return array
     */
    public function getGlanceStats()
    {
        $customer    = Yii::app()->customer->getModel();
        $customer_id = (int)$customer->customer_id;
        $languageId  = (int)$customer->language_id;
        $cacheKey    = sha1('customer.'.$customer_id.'.dashboard.glanceStats.'.$languageId);
        $cache       = Yii::app()->cache;

        if (($items = $cache->get($cacheKey))) {
            return $items;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('t.customer_id', $customer_id);
        $criteria->addNotInCondition('t.status', array(Lists::STATUS_PENDING_DELETE));

        $subsCriteria = new CDbCriteria();
        $subsCriteria->addInCondition('t.list_id', $customer->getAllListsIdsNotMerged());
 
        $items = array(
            array(
                'count'     => Yii::app()->format->formatNumber(Campaign::model()->count($criteria)),
                'heading'   => Yii::t('dashboard', 'Campaigns'),
                'icon'      => IconHelper::make('ion-ios-email-outline'),
                'url'       => $this->createUrl('campaigns/index'),
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(Lists::model()->count($criteria)),
                'heading'   => Yii::t('dashboard', 'Lists'),
                'icon'      => IconHelper::make('ion ion-clipboard'),
                'url'       => $this->createUrl('lists/index'),
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(ListSubscriber::model()->count($subsCriteria)),
                'heading'   => Yii::t('dashboard', 'Subscribers'),
                'icon'      => IconHelper::make('ion-ios-people'),
                'url'       => $this->createUrl('lists/all_subscribers'),
            ),
            array(
                'count'     => Yii::app()->format->formatNumber(CustomerEmailTemplate::model()->countByAttributes(array('customer_id' => $customer_id))),
                'heading'   => Yii::t('dashboard', 'Templates'),
                'icon'      => IconHelper::make('ion-ios-albums'),
                'url'       => $this->createUrl('templates/index'),
            ),
        );

        $cache->set($cacheKey, $items, 600);

        return $items;
    }
    
    /**
     * @return array
     */
    public function getTimelineItems()
    {
        $customer    = Yii::app()->customer->getModel();
        $customer_id = (int)$customer->customer_id;
        $languageId  = (int)$customer->language_id;
        $cacheKey    = sha1('customer.'.$customer_id.'.dashboard.timelineItems.'.$languageId);
        $cache       = Yii::app()->cache;

        if (($items = $cache->get($cacheKey))) {
            return $items;
        }
        
        $criteria = new CDbCriteria();
        $criteria->select    = 'DISTINCT(DATE(t.date_added)) as date_added';
        $criteria->condition = 't.customer_id = :customer_id AND DATE(t.date_added) >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        $criteria->group     = 'DATE(t.date_added)';
        $criteria->order     = 't.date_added DESC';
        $criteria->limit     = 3;
        $criteria->params    = array(':customer_id' => $customer_id);
        $models = CustomerActionLog::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $_item = array(
                'date'  => $model->dateTimeFormatter->formatLocalizedDate($model->date_added),
                'items' => array(),
            );
            $criteria = new CDbCriteria();
            $criteria->select    = 't.log_id, t.customer_id, t.message, t.date_added';
            $criteria->condition = 't.customer_id = :customer_id AND DATE(t.date_added) = :date';
            $criteria->params    = array(':customer_id' => $customer_id, ':date' => $model->date_added);
            $criteria->limit     = 5;
            $criteria->order     = 't.date_added DESC';
            $criteria->with      = array(
                'customer' => array(
                    'select'   => 'customer.customer_id, customer.first_name, customer.last_name',
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                ),
            );
            $records = CustomerActionLog::model()->findAll($criteria);
            foreach ($records as $record) {
                $customer = $record->customer;
                $time     = $record->dateTimeFormatter->formatLocalizedTime($record->date_added);
                $_item['items'][] = array(
                    'time'         => $time,
                    'customerName' => $customer->getFullName(),
                    'customerUrl'  => $this->createUrl('account/index'),
                    'message'      => $record->message,
                );
            }
            $items[] = $_item;
        }

        $cache->set($cacheKey, $items, 600);

        return $items;
    }
}
