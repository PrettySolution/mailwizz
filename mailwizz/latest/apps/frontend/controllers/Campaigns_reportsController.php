<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaigns_reportsController
 *
 * Handles the actions for campaign reports related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

require_once Yii::getPathOfAlias('customer.controllers.Campaign_reportsController') . '.php';

class Campaigns_reportsController extends Campaign_reportsController
{
    /**
     * @var array
     */
    public $localPageScripts = array();
    
    /**
     * @var string
     */
    public $campaignReportsController = 'campaigns_reports';

    /**
     * @var string
     */
    public $campaignReportsExportController = 'campaigns_reports_export';
    
    /**
     * @var string
     */
    public $campaignsListRoute = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $campaign_uid = Yii::app()->request->getQuery('campaign_uid');
        $campaign     = $this->loadCampaignModel($campaign_uid);
        
        if ($campaign->shareReports->share_reports_enabled != CampaignOptionShareReports::TEXT_YES) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        $session = Yii::app()->session;
        $public  = array('login');
        if (!in_array($action->id, $public) && !isset($session['campaign_reports_access_' . $campaign_uid])) {
            return $this->redirect(array('campaigns_reports/login'));
        }

        if (!empty($campaign->customer->language)) {
            Yii::app()->setLanguage($campaign->customer->language->getLanguageAndLocaleCode());
        }
        
        return parent::beforeAction($action);
    }

    /**
     * Login to view campaign stats
     */
    public function actionLogin($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->shareReports->share_reports_enabled != CampaignOptionShareReports::TEXT_YES) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $session = Yii::app()->session;
        $request = Yii::app()->request;
        if ($request->isPostRequest) {
            if ($request->getPost('password') == $campaign->shareReports->share_reports_password) {
                $session['campaign_reports_access_' . $campaign_uid] = true;
                return $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign_uid));
            }
            Yii::app()->notify->addError(Yii::t('campaign_reports', 'Invalid credentials!'));
        }
        
        $this->render('login');
    }

    /**
     * Show delivery report for campaign
     */
    public function actionDelivery($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;

        // 1.4.9
        if ($campaign->option->processed_count >= 0) {
            Yii::app()->notify->addInfo(Yii::t('campaigns', 'Delivery report stats are not available for this campaign!'));
            return $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }
        
        $className = $campaign->getDeliveryLogsArchived() ? 'CampaignDeliveryLogArchive' : 'CampaignDeliveryLog';
        $deliveryLogs = new $className('customer-search');
        $deliveryLogs->unsetAttributes();
        $deliveryLogs->attributes   = (array)$request->getQuery($deliveryLogs->modelName, array());
        $deliveryLogs->campaign_id  = (int)$campaign->campaign_id;

        $subscriber  = new ListSubscriber();
        $bulkActions = $subscriber->getBulkActionsList();
        foreach ($bulkActions as $value => $name) {
            if (!empty($value) && $value != ListSubscriber::BULK_DELETE) {
                unset($bulkActions[$value]);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Sent emails report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Sent emails report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Sent emails report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['delivery'], compact('campaign', 'deliveryLogs', 'bulkActions'));
    }

    /**
     * Show bounce report for a campaign
     */
    public function actionBounce($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;

        $bounceLogs = new CampaignBounceLog('customer-search');
        $bounceLogs->unsetAttributes();
        $bounceLogs->attributes     = (array)$request->getQuery($bounceLogs->modelName, array());
        $bounceLogs->campaign_id    = (int)$campaign->campaign_id;

        $subscriber  = new ListSubscriber();
        $bulkActions = $subscriber->getBulkActionsList();
        foreach ($bulkActions as $value => $name) {
            if (!empty($value) && $value != ListSubscriber::BULK_DELETE) {
                unset($bulkActions[$value]);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Bounce report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Bounce report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Bounce report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['bounce'], compact('campaign', 'bounceLogs', 'bulkActions'));
    }

    /**
     * Show campaign opens
     */
    public function actionOpen($campaign_uid)
    {
        $request  = Yii::app()->request;
        $campaign = $this->loadCampaignModel($campaign_uid);
   
        $model = new CampaignTrackOpen();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->compare('t.campaign_id', (int)$campaign->campaign_id);
        $criteria->order = 't.id DESC';

        if ($countryCode = $request->getQuery('country_code')) {
            $criteria->with['ipLocation'] = array(
                'together' => true,
                'joinType' => 'INNER JOIN',
            );
            $criteria->compare('ipLocation.country_code', $countryCode);
        }
        
        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Opens report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Opens report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Opens report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['open'], compact('campaign', 'model', 'dataProvider'));
    }

    /**
     * Show campaign unique opens
     */
    public function actionOpen_unique($campaign_uid)
    {
        $request  = Yii::app()->request;
        $campaign = $this->loadCampaignModel($campaign_uid);
  
        $model = new CampaignTrackOpen();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->select = 't.*, COUNT(*) AS counter';
        $criteria->compare('t.campaign_id', (int)$campaign->campaign_id);
        $criteria->group = 't.subscriber_id';
        $criteria->order = 'counter DESC';

        if ($countryCode = $request->getQuery('country_code')) {
            $criteria->with['ipLocation'] = array(
                'together' => true,
                'joinType' => 'INNER JOIN',
            );
            $criteria->compare('ipLocation.country_code', $countryCode);
        }
        
        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Unique opens report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Unique opens report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Unique opens report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['open_unique'], compact('campaign', 'model', 'dataProvider'));
    }

    /**
     * Show campaign opens by subscriber
     */
    public function actionOpen_by_subscriber($campaign_uid, $subscriber_uid)
    {
        $campaign   = $this->loadCampaignModel($campaign_uid);
        $subscriber = $this->loadSubscriberModel($campaign->list->list_id, $subscriber_uid);

        $model = new CampaignTrackOpen();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->compare('campaign_id', (int)$campaign->campaign_id);
        $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
        $criteria->order = 'id DESC';

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Opens report by subscriber'),
            'pageHeading'       => Yii::t('campaign_reports', 'Opens report by subscriber'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Opens report by subscriber'),
            ),
        ));

        $this->render($this->actionView['open_by_subscriber'], compact('campaign', 'subscriber', 'model', 'dataProvider'));
    }

    /**
     * Show clicked url from within the campaign email
     */
    public function actionClick($campaign_uid, $show = null)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        
        if ($campaign->option->url_tracking != CampaignOption::TEXT_YES) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $showActions = array('latest', 'top');
        if (!empty($show) && !in_array($show, $showActions)) {
            $show = null;
        }

        $model = new CampaignUrl();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->select = 't.*, (SELECT COUNT(*) FROM {{campaign_track_url}} WHERE url_id = t.url_id) AS counter';
        $criteria->compare('t.campaign_id', (int)$campaign->campaign_id);

        if ($show == 'latest' || $show == 'top') {
            $criteria->addCondition('(SELECT COUNT(*) FROM {{campaign_track_url}} WHERE url_id = t.url_id) > 0');
        }

        if ($show == 'latest') {
            $criteria->order = 't.date_added DESC';
        } else {
            $criteria->order = 'counter DESC';
        }

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $heading = Yii::t('campaign_reports', 'Clicks report');
        if ($show == 'top') {
            $heading = Yii::t('campaign_reports', 'Top clicks report');
        } elseif ($show == 'latest') {
            $heading = Yii::t('campaign_reports', 'Latest clicks report');
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . $heading,
            'pageHeading'       => $heading,
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                $heading,
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['click'], compact('campaign', 'model', 'dataProvider', 'show'));
    }

    /**
     * Show only stats about a certain url from campaign
     */
    public function actionClick_url($campaign_uid, $url_id)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        
        if ($campaign->option->url_tracking != CampaignOption::TEXT_YES) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $url = $this->loadUrlModel($campaign->campaign_id, $url_id);


        $model = new CampaignTrackUrl();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->select = 't.*, COUNT(*) AS counter';
        $criteria->compare('t.url_id', (int)$url->url_id);
        $criteria->with = array(
            'subscriber' => array(
                'together' => true,
                'joinType' => 'INNER JOIN',
            ),
        );
        $criteria->order = 'counter DESC';
        $criteria->group = 't.subscriber_id';

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Url clicks report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Url clicks report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Url clicks report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['click_url'], compact('campaign', 'url', 'model', 'dataProvider'));
    }

    /**
     * Show what links a certain subscriber clicked
     */
    public function actionClick_by_subscriber($campaign_uid, $subscriber_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->option->url_tracking != CampaignOption::TEXT_YES) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $subscriber = $this->loadSubscriberModel($campaign->list->list_id, $subscriber_uid);

        $model = new CampaignTrackUrl();
        $model->unsetAttributes();

        $criteria = new CDbCriteria();
        $criteria->compare('t.subscriber_id', (int)$subscriber->subscriber_id);

        $criteria->with = array(
            'url' => array(
                'select'    => 'url.url_id, url.destination',
                'together'    => true,
                'joinType'    => 'INNER JOIN',
                'condition'    => 'url.campaign_id = :cid',
                'params'    => array(':cid' => $campaign->campaign_id),
            )
        );
        $criteria->order = 't.id DESC';

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Clicks report by subscriber'),
            'pageHeading'       => Yii::t('campaign_reports', 'Clicks report by subscriber'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Clicks report by subscriber'),
            ),
        ));
        
        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['click_by_subscriber'], compact('campaign', 'subscriber', 'model', 'dataProvider'));
    }

    /**
     * Show only unique click for a certain subscriber
     */
    public function actionClick_by_subscriber_unique($campaign_uid, $subscriber_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->option->url_tracking != CampaignOption::TEXT_YES) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $subscriber = $this->loadSubscriberModel($campaign->list->list_id, $subscriber_uid);
        $model = new CampaignTrackUrl();
        $model->unsetAttributes();

        $criteria = new CDbCriteria();
        $criteria->select = 't.*, COUNT(*) AS counter';
        $criteria->compare('t.subscriber_id', (int)$subscriber->subscriber_id);

        $criteria->with = array(
            'url' => array(
                'select'    => 'url.url_id, url.destination',
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'url.campaign_id = :cid',
                'params'    => array(':cid' => $campaign->campaign_id),
            )
        );
        $criteria->group = 't.url_id';
        $criteria->order = 'counter DESC';

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Clicks report by subscriber'),
            'pageHeading'       => Yii::t('campaign_reports', 'Clicks report by subscriber'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Clicks report by subscriber'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['click_by_subscriber_unique'], compact('campaign', 'subscriber', 'model', 'dataProvider'));
    }

    /**
     * Show campaign unsubscribes
     */
    public function actionUnsubscribe($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $model = new CampaignTrackUnsubscribe();
        $model->unsetAttributes();

        $criteria = new CDbCriteria;
        $criteria->compare('campaign_id', (int)$campaign->campaign_id);
        $criteria->order = 'id DESC';

        $dataProvider = new CActiveDataProvider($model->modelName, array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$model->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
        ));

        $subscriber  = new ListSubscriber();
        $bulkActions = $subscriber->getBulkActionsList();
        foreach ($bulkActions as $value => $name) {
            if (!empty($value) && $value != ListSubscriber::BULK_DELETE) {
                unset($bulkActions[$value]);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Unsubscribes report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Unsubscribes report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Unsubscribes report'),
            ),
        ));

        // 1.3.5.9
        $this->setData('canExportStats', ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes'));

        $this->render($this->actionView['unsubscribe'], compact('campaign', 'model', 'dataProvider', 'bulkActions'));
    }

    /**
     * Show campaign forward to a friend
     */
    public function actionForward_friend($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;

        $forward = new CampaignForwardFriend('search');
        $forward->attributes  = (array)$request->getQuery($forward->modelName, array());
        $forward->campaign_id = $campaign->campaign_id;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Forward to a friend report'),
            'pageHeading'       => Yii::t('campaign_reports', 'Forward to a friend report'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Forward to a friend report'),
            ),
        ));

        $this->render($this->actionView['forward_friend'], compact('campaign', 'forward'));
    }

    /**
     * Show abuse reports for this campaign
     */
    public function actionAbuse_reports($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;

        $reports = new CampaignAbuseReport('search');
        $reports->attributes  = (array)$request->getQuery($reports->modelName, array());
        $reports->campaign_id = $campaign->campaign_id;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('campaign_reports', 'Abuse reports'),
            'pageHeading'       => Yii::t('campaign_reports', 'Abuse reports'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->campaignsListUrl,
                $campaign->name . ' ' => $this->createUrl($this->campaignOverviewRoute, array('campaign_uid' => $campaign_uid)),
                Yii::t('campaign_reports', 'Abuse reports'),
            ),
        ));

        $this->render($this->actionView['abuse_reports'], compact('campaign', 'reports'));
    }

    /**
     * Helper method to load the campaign AR model
     */
    public function loadCampaignModel($campaign_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->with = array(
            'customer' => array(
                'together'  => true,
                'joinType'  => 'INNER JOIN',
            ),
            'list' => array(
                'together'  => true,
                'joinType'  => 'INNER JOIN',
            )
        );
        $criteria->compare('t.campaign_uid', $campaign_uid);
        $statuses = array(
            Campaign::STATUS_DRAFT, Campaign::STATUS_PENDING_DELETE, Campaign::STATUS_PENDING_SENDING,
        );
        $criteria->addNotInCondition('t.status', $statuses);

        $model = Campaign::model()->find($criteria);

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
            'list_id'           => $list_id,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to load the campaign url AR model
     */
    public function loadUrlModel($campaign_id, $url_id)
    {
        $model = CampaignUrl::model()->findByAttributes(array(
            'url_id'        => $url_id,
            'campaign_id'   => $campaign_id,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

}
