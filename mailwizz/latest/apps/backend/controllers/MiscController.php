<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * MiscController
 * 
 * Handles the actions for miscellaneous tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3
 */
 
class MiscController extends Controller
{
    
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('misc.js')));
        parent::init();
    }
    
    public function actionIndex()
    {
        $this->redirect(array('misc/application_log'));
    }
    
    /**
     * Emergency actions
     */
    public function actionEmergency_actions()
    {
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('app', 'Emergency actions'), 
            'pageHeading'       => Yii::t('app', 'Emergency actions'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Emergency actions'),
            ),
        ));
        
        $this->render('emergency-actions');
    }
    
    /**
     * Remove sending pid
     */
    public function actionRemove_sending_pid()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        Yii::app()->options->remove('system.cron.send_campaigns.lock');
        Yii::app()->options->set('system.cron.send_campaigns.campaigns_offset', 0);
        return $this->renderJson();
    }
    
    /**
     * Remove bounces pid
     */
    public function actionRemove_bounce_pid()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        Yii::app()->options->remove('system.cron.process_bounce_servers.pid');
        return $this->renderJson();
    }
    
    /**
     * Remove fbl pid
     */
    public function actionRemove_fbl_pid()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        Yii::app()->options->remove('system.cron.process_feedback_loop_servers.pid');
        return $this->renderJson();
    }
    
    /**
     * Reset campaigns
     */
    public function actionReset_campaigns()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        Campaign::model()->updateAll(array('status' => Campaign::STATUS_SENDING), 'status = :status', array(':status' => Campaign::STATUS_PROCESSING));
        return $this->renderJson();
    }
    
    /**
     * Reset bounce servers
     */
    public function actionReset_bounce_servers()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        BounceServer::model()->updateAll(array('status' => BounceServer::STATUS_ACTIVE), 'status = :status', array(':status' => BounceServer::STATUS_CRON_RUNNING));
        return $this->renderJson();
    }
    
    /**
     * Reset fbl servers
     */
    public function actionReset_fbl_servers()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        FeedbackLoopServer::model()->updateAll(array('status' => FeedbackLoopServer::STATUS_ACTIVE), 'status = :status', array(':status' => FeedbackLoopServer::STATUS_CRON_RUNNING));
        return $this->renderJson();
    }

    /**
     * Reset email box monitors
     */
    public function actionReset_email_box_monitors()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('misc/emergency_actions'));
        }
        EmailBoxMonitor::model()->updateAll(array('status' => EmailBoxMonitor::STATUS_ACTIVE), 'status = :status', array(':status' => EmailBoxMonitor::STATUS_CRON_RUNNING));
        return $this->renderJson();
    }
    
    /**
     * Application log
     */
    public function actionApplication_log()
    {
        $request = Yii::app()->request;
        
        if ($request->isPostRequest && $request->getPost('delete') == 1) {
            if (is_file($file = Yii::app()->runtimePath . '/application.log')) {
                @unlink($file);
                Yii::app()->notify->addSuccess(Yii::t('app', 'The application log file has been successfully deleted!'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('app', 'Application log'), 
            'pageHeading'       => Yii::t('app', 'Application log'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Application log'),
            ),
        ));
        
        $applicationLog = FileSystemHelper::getFileContents(Yii::app()->runtimePath . '/application.log');
        $this->render('application-log', compact('applicationLog'));
    }
    
    /**
     * Campaign delivery logs
     */
    public function actionCampaigns_delivery_logs($archive = null)
    {
        $request   = Yii::app()->request;
        $className = $archive ? 'CampaignDeliveryLogArchive' : 'CampaignDeliveryLog';
        $log       = new $className('search');
        $log->unsetAttributes();
        
        $log->attributes = (array)$request->getQuery($log->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('misc', 'View campaigns delivery logs'),
            'pageHeading'       => Yii::t('misc', 'View campaigns delivery logs'),
            'pageBreadcrumbs'   => array(
                Yii::t('misc', 'Campaigns delivery logs'),
            )
        ));
        
        $this->render('campaigns-delivery-logs', compact('log', 'archive'));
    }
    
    /**
     * Campaign bounce logs
     */
    public function actionCampaigns_bounce_logs()
    {
        $request = Yii::app()->request;
        $log     = new CampaignBounceLog('search');
        $log->unsetAttributes();
        
        $log->attributes = (array)$request->getQuery($log->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('misc', 'View campaigns bounce logs'),
            'pageHeading'       => Yii::t('misc', 'View campaigns bounce logs'),
            'pageBreadcrumbs'   => array(
                Yii::t('misc', 'Campaigns bounce logs'),
            )
        ));
        
        $this->render('campaigns-bounce-logs', compact('log'));
    }

    /**
     * Campaigns stats
     */
    public function actionCampaigns_stats()
    {
        $request  = Yii::app()->request;
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();
        $campaign->attributes = (array)$request->getQuery($campaign->modelName, array());
        $campaign->status     = array(Campaign::STATUS_PENDING_SENDING, Campaign::STATUS_SENDING, Campaign::STATUS_SENT);
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('misc', 'View campaigns stats'),
            'pageHeading'       => Yii::t('misc', 'View campaigns stats'),
            'pageBreadcrumbs'   => array(
                Yii::t('misc', 'View campaigns stats'),
            )
        ));

        $this->render('campaigns-stats', compact('campaign'));
    }
    
    /**
     * Delivery servers usage logs
     */
    public function actionDelivery_servers_usage_logs()
    {
        $request = Yii::app()->request;
        $log     = new DeliveryServerUsageLog('search');
        $log->unsetAttributes();
        
        $log->attributes = (array)$request->getQuery($log->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('misc', 'View delivery servers usage logs'),
            'pageHeading'       => Yii::t('misc', 'View delivery servers usage logs'),
            'pageBreadcrumbs'   => array(
                Yii::t('misc', 'Delivery servers usage logs'),
            )
        ));
        
        $this->render('delivery-servers-usage-logs', compact('log'));
    }
    
    /**
     * Delete temporary errors from campaigns delivery logs
     */
    public function actionDelete_delivery_temporary_errors()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        
        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id';
        $criteria->compare('status', Campaign::STATUS_SENDING);
        $campaigns = Campaign::model()->findAll($criteria);
        
        foreach ($campaigns as $campaign) {
            CampaignDeliveryLog::model()->deleteAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id, 
                'status'      => CampaignDeliveryLog::STATUS_TEMPORARY_ERROR
            ));
        }

        Yii::app()->notify->addSuccess(Yii::t('misc', 'Delivery temporary errors were successfully deleted!'));
        $this->redirect(array('misc/campaigns_delivery_logs'));
    }
    
    /**
     * Guest fail attempts
     */
    public function actionGuest_fail_attempts()
    {
        $request = Yii::app()->request;
        $attempt = new GuestFailAttempt('search');
        $attempt->unsetAttributes();
        
        $attempt->attributes = (array)$request->getQuery($attempt->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('guest_fail_attempt', 'View guest fail attempts'),
            'pageHeading'       => Yii::t('guest_fail_attempt', 'View guest fail attempts'),
            'pageBreadcrumbs'   => array(
                Yii::t('guest_fail_attempt', 'Guest fail attempts'),
            )
        ));
        
        $this->render('guest-fail-attempts', compact('attempt'));
    }
    
    /**
     * Cron jobs display list
     */
    public function actionCron_jobs_list()
    {
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('cronjobs', 'View cron jobs list'),
            'pageHeading'       => Yii::t('cronjobs', 'View cron jobs list'),
            'pageBreadcrumbs'   => array(
                Yii::t('cronjobs', 'Cron jobs list'),
            )
        ));
        
        $this->render('cron-jobs-list');
    }

    /**
     * Cron jobs display list
     */
    public function actionCron_jobs_history()
    {
        $request = Yii::app()->request;
        $model   = new ConsoleCommandListHistory('search');
        $model->unsetAttributes();

        $model->attributes = (array)$request->getQuery($model->modelName, array());
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('cronjobs', 'View cron jobs history'),
            'pageHeading'       => Yii::t('cronjobs', 'View cron jobs history'),
            'pageBreadcrumbs'   => array(
                Yii::t('cronjobs', 'Cron jobs history'),
            )
        ));

        $this->render('cron-jobs-history', compact('model'));
    }
    
    /**
     * Display information about the current php version
     */
    public function actionPhpinfo()
    {
        if (Yii::app()->request->getQuery('show')) {
            if (CommonHelper::functionExists('phpinfo')) {
                phpinfo();
            }
            Yii::app()->end();
        }

        $phpInfoCli = Yii::t('settings', 'Please check back after the daily cron job runs and this area will contain updated info!');
        if (is_file($file = Yii::getPathOfAlias('common.runtime') . '/php-info-cli.txt')) {
            $phpInfoCli = file_get_contents($file);
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('misc', 'View PHP info'),
            'pageHeading'       => Yii::t('misc', 'View PHP info'),
            'pageBreadcrumbs'   => array(
                Yii::t('misc', 'PHP info'),
            )
        ));
        
        $this->render('php-info', compact('phpInfoCli'));
    }

    /**
     * Application log
     */
    public function actionChangelog()
    {
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | ' . Yii::t('app', 'Changelog'),
            'pageHeading'     => Yii::t('app', 'Changelog'),
            'pageBreadcrumbs' => array(
                Yii::t('app', 'Changelog'),
            ),
        ));

        $changeLog = FileSystemHelper::getFileContents(Yii::getPathOfAlias('root') . '/CHANGELOG');
        $this->render('changelog', compact('changeLog'));
    }

}