<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignsController
 *
 * Handles the actions for campaigns related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class CampaignsController extends Controller
{
	/**
	 * @inheritdoc
	 */
    public function init()
    {
        parent::init();
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('campaigns.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete, pause_unpause, resume_sending',
        ), parent::filters());
    }

    /**
     * List available campaigns
     */
    public function actionIndex()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();

        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns'),
            'pageHeading'       => Yii::t('campaigns', 'Campaigns'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('index', compact('campaign'));
    }

    /**
     * @since 1.5.5
     * List available regular campaigns
     */
    public function actionRegular()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();

        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();
        $campaign->type = Campaign::TYPE_REGULAR;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns') . ' | ' . Yii::t('campaigns', 'Regular campaigns'),
            'pageHeading'       => Yii::t('campaigns', 'Regular campaigns'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Regular campaigns') => $this->createUrl('campaigns/regular'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render($campaign->type, compact('campaign'));
    }

    /**
     * @since 1.5.5
     * List available autoresponder campaigns
     */
    public function actionAutoresponder()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();
        $campaign->addRelatedRecord('option', new CampaignOption(), false);
        
        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();
        $campaign->type = Campaign::TYPE_AUTORESPONDER;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns') . ' | ' . Yii::t('campaigns', 'Autoresponders'),
            'pageHeading'       => Yii::t('campaigns', 'Autoresponders'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Autoresponders') => $this->createUrl('campaigns/autoresponder'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render($campaign->type, compact('campaign'));
    }

	/**
	 * Show the overview for a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionOverview($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request = Yii::app()->request;

        if (!$campaign->accessOverview) {
            $this->redirect(array('campaigns/' . $campaign->type));
        }
        
        if ($recurring = $campaign->isRecurring) {
            Yii::import('common.vendors.JQCron.*');
            $cron = new JQCron($recurring);
            $this->setData('recurringInfo', $cron->getText(LanguageHelper::getAppLanguageCode()));
        }

        // since 1.3.5.9
        if ($campaign->isBlocked && !empty($campaign->option->blocked_reason)) {
            $message = array();
            $message[] = Yii::t('campaigns', 'This campaign is blocked because following reasons:');
            $reasons = explode("|", $campaign->option->blocked_reason);
            foreach ($reasons as $reason) {
                $message[] = Yii::t('campaigns', $reason);
            }
            $message[] = CHtml::link(Yii::t('campaigns', 'Click here to unblock it!'), $this->createUrl('campaigns/block_unblock', array('campaign_uid' => $campaign_uid)));
            Yii::app()->notify->addInfo($message);
        }
        //

        $options        = Yii::app()->options;
        $webVersionUrl  = $options->get('system.urls.frontend_absolute_url');
        $webVersionUrl .= 'campaigns/' . $campaign->campaign_uid;

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaign overview'),
            'pageHeading'       => Yii::t('campaigns', 'Campaign overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                $campaign->name . ' ' => $this->createUrl('campaigns/overview', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Overview')
            )
        ));
        
        $this->render('overview', compact('campaign', 'webVersionUrl'));
    }

	/**
	 * Delete campaign, will remove all campaign related data
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CDbException
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionDelete($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->removable) {
            $campaign->delete();
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('campaigns/' . $campaign->type));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $campaign,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

	/**
	 * Allows to approve a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionApprove($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->getCanBeApproved()) {
            $campaign->saveStatus(Campaign::STATUS_PENDING_SENDING);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to block/unblock a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionBlock_unblock($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        $campaign->blockUnblock();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to pause/unpause the sending of a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionPause_unpause($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        $campaign->pauseUnpause();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to resume sending of a stuck campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionResume_sending($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->isProcessing) {
            $campaign->status = Campaign::STATUS_SENDING;
            $campaign->save(false);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to mark a campaign as sent
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionMarksent($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $campaign->markAsSent();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to resend the giveups for a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @return BaseController|void
	 * @throws CHttpException
	 */
	public function actionResend_giveups($campaign_uid)
	{
		$campaign = $this->loadCampaignModel($campaign_uid);
		$request  = Yii::app()->request;
		if (!$request->isAjaxRequest || !$request->isPostRequest) {
			return $this->redirect(array('campaigns/' . $campaign->type));
		}

		if (!$campaign->getIsSent()) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'Resending to giveups only works for sent campaigns!'),
			));
		}

		if (empty($campaign->option->giveup_count)) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'It seems this campaign has no giveups!'),
			));
		}

		$queued = CampaignResendGiveupQueue::model()->countByAttributes(array(
			'campaign_id' => $campaign->campaign_id
		));

		if ($queued) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'It seems this campaign has already been queued to resend to giveups!'),
			));
		}

		$queue = new CampaignResendGiveupQueue();
		$queue->campaign_id = $campaign->campaign_id;
		$queue->save(false);

		return $this->renderJson(array(
			'result'  => 'success',
			'message' =>  Yii::t('campaigns', 'The campaigns has been queued successfully, it will start sending in a few minutes!'),
		));
	}

	/**
	 * Run a bulk action against the campaigns
	 * 
	 * @param string $type
	 *
	 * @throws CDbException
	 * @throws CException
	 */
    public function actionBulk_action($type = '')
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $action = $request->getPost('bulk_action');
        $items  = array_unique((array)$request->getPost('bulk_item', array()));

        $returnRoute = array('campaigns/index');
        $campaign = new Campaign();
        if (in_array($type, $campaign->getTypesList())) {
            $returnRoute = array('campaigns/' . $type);
        }
        
        if ($action == Campaign::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                if (!$campaign->removable) {
                    continue;
                }
                $campaign->delete();
                $affected++;
                if ($logAction = $campaign->customer->asa('logAction')) {
                    $logAction->campaignDeleted($campaign);
                }
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        } elseif ($action == Campaign::BULK_ACTION_COPY && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                $customer = $campaign->customer;
                if (($maxCampaigns = (int)$customer->getGroupOption('campaigns.max_campaigns', -1)) > -1) {
                    $criteria = new CDbCriteria();
                    $criteria->compare('customer_id', (int)$customer->customer_id);
                    $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
                    $campaignsCount = Campaign::model()->count($criteria);
                    if ($campaignsCount >= $maxCampaigns) {
                        continue;
                    }
                }
                if (!$campaign->copy()) {
                    continue;
                }
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        } elseif ($action == Campaign::BULK_ACTION_PAUSE_UNPAUSE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                $campaign->pauseUnpause();
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        } elseif ($action == Campaign::BULK_ACTION_MARK_SENT && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                if (!$campaign->markAsSent()) {
                    continue;
                }
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        } elseif ($action == Campaign::BULK_ACTION_SHARE_CAMPAIGN_CODE && count($items)) {
            $affected     = 0;
            $success      = false;
            $campaignsIds = array();

            /* Collect the campaign ids */
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                $campaignsIds[] = $campaign->campaign_id;
            }

            if (!empty($campaignsIds)) {
                $campaignShareCode = new CampaignShareCode();

                $transaction = Yii::app()->db->beginTransaction();

                try {
                    if (!$campaignShareCode->save()) {
                        throw new Exception(Yii::t('campaigns', 'Could not save the sharing code'));
                    }

                    foreach ($campaignsIds as $campaignId) {
                        $campaignShareCodeToCampaign              = new CampaignShareCodeToCampaign();
                        $campaignShareCodeToCampaign->code_id     = $campaignShareCode->code_id;
                        $campaignShareCodeToCampaign->campaign_id = (int)$campaignId;

                        if (!$campaignShareCodeToCampaign->save()) {
                            throw new Exception(Yii::t('campaigns', 'Could not save the sharing code to campaign'));
                        }

                        $affected++;
                    }

                    $transaction->commit();
                    $success = true;
                } catch (Exception $e) {
                    Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
                    $transaction->rollback();
                }
            }

            if ($success) {
                $notify->addSuccess(Yii::t('campaigns', 'The sharing code is: {code}', array(
                    '{code}' => sprintf('<strong>%s</strong>', $campaignShareCode->code_uid)
                )));
            }
        }
        
        $defaultReturn = $request->getServer('HTTP_REFERER', $returnRoute);
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

	/**
	 * Helper method to load the campaign AR model
	 * 
	 * @param $campaign_uid
	 *
	 * @return Campaign|null
	 * @throws CHttpException
	 */
    public function loadCampaignModel($campaign_uid)
    {
        $model = $this->loadCampaignByUid($campaign_uid);

        if($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($model->isPendingDelete) {
            $this->redirect(array('campaigns/' . $model->type));
        }

        return $model;
    }

	/**
	 * @param $campaign_uid
	 *
	 * @return Campaign|null
	 */
	public function loadCampaignByUid($campaign_uid)
	{
		$criteria = new CDbCriteria();
		$criteria->compare('campaign_uid', $campaign_uid);
		$criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));

		return Campaign::model()->find($criteria);
	}

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('index'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
