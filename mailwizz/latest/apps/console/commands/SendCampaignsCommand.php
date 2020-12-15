<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendCampaignsCommand
 *
 * Please do not alter/extend this file as it is subject to major changes always and future updates will break your app.
 * Since 1.3.5.9 this file has been changed drastically.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 *
 */

class SendCampaignsCommand extends ConsoleCommand
{
	/**
	 * @var string what type of campaigns this command is sending
	 */
	public $campaigns_type = '';

	/**
	 * @var int how many campaigns to process at once
	 */
	public $campaigns_limit = 0;

	/**
	 * @var int from where to start
	 */
	public $campaigns_offset = 0;

	/**
	 * @var Campaign
	 */
	protected $_campaign;

	/**
	 * @var bool
	 */
	protected $_restoreStates = true;

	/**
	 * @var bool
	 */
	protected $_improperShutDown = false;

	/**
	 * @since 1.3.7.3
	 * @var array
	 */
	protected $_customerData = array();

	/**
	 * @since 1.3.7.9
	 * @var bool
	 */
	protected $_useTempQueueTables = false;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		// this will catch exit signals and restore states
		if (CommonHelper::functionExists('pcntl_signal')) {
			declare(ticks = 1);
			pcntl_signal(SIGINT,  array($this, '_handleExternalSignal'));
			pcntl_signal(SIGTERM, array($this, '_handleExternalSignal'));
			pcntl_signal(SIGHUP,  array($this, '_handleExternalSignal'));
		}
		register_shutdown_function(array($this, '_restoreStates'));
		Yii::app()->attachEventHandler('onError', array($this, '_restoreStates'));
		Yii::app()->attachEventHandler('onException', array($this, '_restoreStates'));

		// if more than 6 hours then something is def. wrong?
		ini_set('max_execution_time', 6 * 3600);
		set_time_limit(6 * 3600);

		if ($memoryLimit = Yii::app()->options->get('system.cron.send_campaigns.memory_limit')) {
			ini_set('memory_limit', $memoryLimit);
		}

		if (!empty(Yii::app()->params['send.campaigns.command.useTempQueueTables'])) {
			$this->_useTempQueueTables = true;
		}

		// 1.5.3
		Yii::app()->mutex->shutdownCleanup = false;
	}

	/**
	 * @param $signalNumber
	 */
	public function _handleExternalSignal($signalNumber)
	{
		// this will trigger all the handlers attached via register_shutdown_function
		$this->_improperShutDown = true;
		exit;
	}

	/**
	 * @param null $event
	 */
	public function _restoreStates($event = null)
	{
		if (!$this->_restoreStates) {
			return;
		}
		$this->_restoreStates = false;

		// called as a callback from register_shutdown_function
		// must pass only if improper shutdown in this case
		if ($event === null && !$this->_improperShutDown) {
			return;
		}

		if (!empty($this->_campaign) && $this->_campaign instanceof Campaign) {
			if ($this->_campaign->isProcessing) {
				$this->_campaign->saveStatus(Campaign::STATUS_SENDING);
				$this->stdout('Campaign status has been restored to sending!');
			}
		}

		$this->stdout('Shutting down!');
	}

	/**
	 * @return int
	 */
	public function actionIndex()
	{
		// 1.5.3
		$this->stdout('Starting the work for this batch...');

		// set the lock name
		$lockName = sha1(__METHOD__ . $this->campaigns_type);

		// 1.3.7.3 - mutex
		if ($this->getCanUsePcntl() && !Yii::app()->mutex->acquire($lockName)) {
			$this->stdout('PCNTL processes running already, locks acquired previously!');
			return 0;
		}

		$result = 0;

		try {

			// since 1.5.3 - whether we should automatically adjust the number of campaigns at once
			if (Yii::app()->options->get('system.cron.send_campaigns.auto_adjust_campaigns_at_once', 'no') == 'yes') {
				$criteria = new CDbCriteria();
				$criteria->compare('type', Campaign::TYPE_AUTORESPONDER);
				$criteria->addInCondition('status', array(Campaign::STATUS_SENDING, Campaign::STATUS_PROCESSING));
				$newCount = (int)Campaign::model()->count($criteria) + 10;
				Yii::app()->options->set('system.cron.send_campaigns.campaigns_at_once', $newCount);
			}

			// since 1.5.0 
			// we can do this because we are under a lock now if pcntl is used
			// the master lock is per campaign type, which means two processes can get same handler 
			if ($this->getCanUsePcntl()) {

				// since 1.5.3 make sure we lock to avoid deadlock when processing regular and ar separately.
				$mutexKey = __METHOD__ . ':update-other-campaigns-status';
				if (Yii::app()->mutex->acquire($mutexKey, 5)) {

					try {

						if ($this->campaigns_type) {
							Campaign::model()->updateAll(array('status' => Campaign::STATUS_SENDING), '`type` = :tp AND `status` = :st', array(':tp' => $this->campaigns_type, ':st' => Campaign::STATUS_PROCESSING));
						} else {
							Campaign::model()->updateAll(array('status' => Campaign::STATUS_SENDING), '`status` = :st', array(':st' => Campaign::STATUS_PROCESSING));
						}

					} catch (Exception $e) {

						$this->stdout(__LINE__ . ': ' . $e->getMessage());
						Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
					}

					Yii::app()->mutex->release($mutexKey);
				}
			}
			//

			$timeStart        = microtime(true);
			$memoryUsageStart = memory_get_peak_usage(true);

			// added in 1.3.4.7
			Yii::app()->hooks->doAction('console_command_send_campaigns_before_process', $this);

			$result = $this->process();

			// 1.3.7.5 - do we need to send notifications for reaching the quota?
			// we do this after processing to not send notifications before the sending actually ends...
			if ($result === 0) {
				$this->checkCustomersQuotaLimits();
			}

			// added in 1.3.4.7
			Yii::app()->hooks->doAction('console_command_send_campaigns_after_process', $this);

			$timeEnd        = microtime(true);
			$memoryUsageEnd = memory_get_peak_usage(true);

			$time        = round($timeEnd - $timeStart, 2);
			$memoryUsage = CommonHelper::formatBytes($memoryUsageEnd - $memoryUsageStart);
			$this->stdout(sprintf('This cycle completed in %s and used %s of memory!', $time . ' seconds', $memoryUsage));

			if (CommonHelper::functionExists('sys_getloadavg')) {
				list($_1, $_5, $_15) = sys_getloadavg();
				$this->stdout(sprintf('CPU usage in last minute: %.2f, in last 5 minutes: %.2f, in last 15 minutes: %.2f!', $_1, $_5, $_15));
			}

		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		// remove the lock
		if ($this->getCanUsePcntl()) {
			Yii::app()->mutex->release($lockName);
		}

		return $result;
	}

	/**
	 * @return int
	 * @throws CDbException
	 * @throws CException
	 */
	protected function process()
	{
		$options  = Yii::app()->options;
		$statuses = array(Campaign::STATUS_SENDING, Campaign::STATUS_PENDING_SENDING);
		$types    = array(Campaign::TYPE_REGULAR, Campaign::TYPE_AUTORESPONDER);
		$limit    = (int)$options->get('system.cron.send_campaigns.campaigns_at_once', 10);

		if ($this->campaigns_type !== null && !in_array($this->campaigns_type, $types)) {
			$this->campaigns_type = null;
		}

		if ((int)$this->campaigns_limit > 0) {
			$limit = (int)$this->campaigns_limit;
		}

		$criteria = new CDbCriteria();
		$criteria->addInCondition('t.status', $statuses);
		$criteria->addCondition('t.send_at <= NOW()');
		if (!empty($this->campaigns_type)) {
			$criteria->addCondition('t.type = :type');
			$criteria->params[':type'] = $this->campaigns_type;
		}
		$criteria->order  = 't.priority ASC, t.campaign_id ASC';
		$criteria->limit  = $limit;
		$criteria->offset = (int)$this->campaigns_offset;

		// offer a chance to alter this criteria.
		$criteria = Yii::app()->hooks->applyFilters('console_send_campaigns_command_find_campaigns_criteria', $criteria, $this);

		// in case it has been changed in hook
		$criteria->limit = $limit;

		$this->stdout(sprintf("Loading %d campaigns, starting with offset %d...", $criteria->limit, $criteria->offset));

		// and find all campaigns matching the criteria
		$campaigns = Campaign::model()->findAll($criteria);

		if (empty($campaigns)) {

			$this->stdout("No campaign found, stopping.");

			return 0;
		}

		$this->stdout(sprintf("Found %d campaigns and now starting processing them...", count($campaigns)));
		if ($this->getCanUsePcntl()) {
			$this->stdout(sprintf(
				'Since PCNTL is active, we will send %d campaigns in parallel and for each campaign, %d batches of subscribers in parallel.',
				$this->getCampaignsInParallel(),
				$this->getSubscriberBatchesInParallel()
			));
		}

		$customersFail = array();
		$campaignIds   = array();
		$options       = Yii::app()->options;

		foreach ($campaigns as $campaign) {

			// reference
			$customer = $campaign->customer;

			// already processed but failed
			if (in_array($customer->customer_id, $customersFail)) {
				continue;
			}

			if (!$customer->getIsActive()) {
				Yii::log(Yii::t('campaigns', 'This customer is inactive!'), CLogger::LEVEL_ERROR);
				$campaign->saveStatus(Campaign::STATUS_PAUSED);
				$this->stdout("This customer is inactive!");
				$customersFail[] = $customer->customer_id;
				continue;
			}

			// since 1.3.9.7
			if ($customer->getCanHaveHourlyQuota() && !$customer->getHourlyQuotaLeft()) {
				$campaign->incrementPriority(); // move at the end of the processing queue
				$this->stdout("This customer reached the hourly assigned quota!");
				$customersFail[] = $customer->customer_id;
				continue;
			}

			if ($customer->getIsOverQuota()) {
				Yii::log(Yii::t('campaigns', 'This customer(ID:{cid}) reached the assigned quota!', array('{cid}' => $customer->customer_id)), CLogger::LEVEL_ERROR);
				$campaign->saveStatus(Campaign::STATUS_PAUSED);
				$this->stdout("This customer reached the assigned quota!");
				$customersFail[] = $customer->customer_id;
				continue;
			}

			// 1.3.7.9 - create the queue table and populate it...
			if ($this->_useTempQueueTables) {

				// put proper status
				$this->stdout('Temporary changing the campaign status into PROCESSING!');
				$campaign->saveStatus(Campaign::STATUS_PROCESSING);

				// 1.5.8
				$mutexKey = sprintf('send-campaigns:campaign:%d:populateTempTable:date:%s', $campaign->campaign_id, date('Ymd'));

				try {

					// 1.5.8 - mutex protection
					if (!Yii::app()->mutex->acquire($mutexKey)) {
						throw new Exception('Unable to acquire the mutex for table population!');
					}

					// populate table
					$campaign->queueTable->populateTable();

					// release the mutex
					Yii::app()->mutex->release($mutexKey);

				} catch (Exception $e) {

					// release the mutex
					Yii::app()->mutex->release($mutexKey);

					$campaign->saveStatus(Campaign::STATUS_SENDING);
					continue;
				}

				$this->stdout('Restoring the campaign status to SENDING!');
				$campaign->saveStatus(Campaign::STATUS_SENDING);
			}

			// populate the campaigns array
			$campaignIds[] = $campaign->campaign_id;

			// counter
			$subscribersAtOnce = (int)$customer->getGroupOption('campaigns.subscribers_at_once', (int)$options->get('system.cron.send_campaigns.subscribers_at_once', 300));
			if ($this->getCanUsePcntl()) {
				$subscribersAtOnce *= $this->getSubscriberBatchesInParallel();
			}

			// 1.3.7.3 - precheck and allow because pcntl mainly
			if (!isset($this->_customerData[$campaign->customer_id])) {
				$quotaTotal  = (int)$customer->getGroupOption('sending.quota', -1);

				$quotaUsage = 0;
				$quotaLeft  = PHP_INT_MAX;
				if ($quotaTotal > -1) {
					$quotaUsage = (int)$customer->countUsageFromQuotaMark();
					$quotaLeft  = $quotaTotal - $quotaUsage;
					$quotaLeft  = $quotaLeft >= 0 ? $quotaLeft : 0;
				}

				// 1.3.9.7
				if ($customer->getCanHaveHourlyQuota()) {
					$hourlyQuotaLeft = $customer->getHourlyQuotaLeft();
					if ($hourlyQuotaLeft <= $quotaLeft) {
						$quotaLeft = $hourlyQuotaLeft;
						$quotaLeft = $quotaLeft >= 0 ? $quotaLeft : 0;
					}
				}

				$this->_customerData[$campaign->customer_id] = array(
					'customer'          => $customer,
					'campaigns'         => array(),
					'quotaTotal'        => $quotaTotal,
					'quotaUsage'        => $quotaUsage,
					'quotaLeft'         => $quotaLeft,
					'subscribersAtOnce' => $subscribersAtOnce,
					'subscribersCount'  => $this->countSubscribers($campaign),
				);
			}

			$campaignMaxSubscribers = 0;
			if ($this->_customerData[$campaign->customer_id]['quotaLeft'] > 0) {
				if ($this->_customerData[$campaign->customer_id]['quotaLeft'] >= $subscribersAtOnce) {
					$campaignMaxSubscribers = $subscribersAtOnce;
				} else {
					$campaignMaxSubscribers = $this->_customerData[$campaign->customer_id]['quotaLeft'];
				}

				/**
				 * @since 1.5.8
				 * In case we have AR's but they don't have subscribers or they have a few,
				 * we will want to deduct just the right number, we can't round anymore.
				 */
				$_subscribersAtOnce = $subscribersAtOnce;
				if ($campaign->getIsAutoresponder()) {
					$_cnt = count($this->findSubscribers(0, $subscribersAtOnce, $campaign));
					if ($_cnt < $subscribersAtOnce) {
						$_subscribersAtOnce = $_cnt;
					}
				}
				//

				$this->_customerData[$campaign->customer_id]['quotaLeft'] -= $_subscribersAtOnce;
				if ($this->_customerData[$campaign->customer_id]['quotaLeft'] < 0) {
					$this->_customerData[$campaign->customer_id]['quotaLeft'] = 0;
				}
			}

			// how much each campaign is allowed to send
			$this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id] = $campaignMaxSubscribers;
		}

		// 1.3.7.5
		foreach ($campaigns as $campaign) {
			if (!$campaign->option->canSetMaxSendCount) {
				continue;
			}

			$campaignDeliveryLogSuccessCount = CampaignDeliveryLog::model()->countByAttributes(array(
				'campaign_id' => $campaign->campaign_id,
				'status'      => CampaignDeliveryLog::STATUS_SUCCESS,
			));

			$sendingsLeft = $campaign->option->max_send_count - $campaignDeliveryLogSuccessCount;
			$sendingsLeft = $sendingsLeft >= 0 ? $sendingsLeft : 0;

			if (!$sendingsLeft) {

				unset($this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id]);
				if (($idx = array_search($campaign->campaign_id, $campaignIds)) !== false) {
					unset($campaignIds[$idx]);
				}

				if ($this->markCampaignSent($campaign)) {
					$this->stdout('Campaign has been marked as sent because of MaxSendCount settings!');
				}

				continue;
			}

			$campaignMaxSubscribers = $this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id];
			if ($sendingsLeft < $campaignMaxSubscribers) {
				$this->_customerData[$campaign->customer_id]['campaigns'][$campaign->campaign_id] = $sendingsLeft;
				continue;
			}
		}
		unset($campaigns);
		//

		$this->sendCampaignStep0($campaignIds);

		return 0;
	}

	/**
	 * @param array $campaignIds
	 * @throws CException
	 */
	protected function sendCampaignStep0(array $campaignIds = array())
	{
		$handled = false;

		if ($this->getCanUsePcntl() && ($campaignsInParallel = $this->getCampaignsInParallel()) > 1) {
			$handled = true;

			// make sure we close the external connections
			$this->setExternalConnectionsActive(false);

			$campaignChunks = array_chunk($campaignIds, $campaignsInParallel);
			foreach ($campaignChunks as $index => $cids) {
				$childs = array();
				foreach ($cids as $cid) {
					$pid = pcntl_fork();
					if($pid == -1) {
						continue;
					}

					// Parent
					if ($pid) {
						$childs[] = $pid;
					}

					// Child
					if (!$pid) {

						$mutexKey = sprintf('send-campaigns:campaign:%d:date:%s', $cid, date('Ymd'));
						if (Yii::app()->mutex->acquire($mutexKey)) {

							// 1.5.3
							try {
								
								$this->sendCampaignStep1($cid, $index+1);

							} catch (Exception $e) {

								$this->stdout(__LINE__ . ': ' . $e->getMessage());
								Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

								try {

									if ($campaign = Campaign::model()->findByPk($cid)) {
										$campaign->saveStatus(Campaign::STATUS_SENDING);
									}

								} catch (Exception $e) {

									$this->stdout(__LINE__ . ': ' . $e->getMessage());
									Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
								}
							}

							Yii::app()->mutex->release($mutexKey);
						}
						Yii::app()->end();
					}
				}

				while (count($childs) > 0) {
					foreach ($childs as $key => $pid) {
						$res = pcntl_waitpid($pid, $status, WNOHANG);
						if($res == -1 || $res > 0) {
							unset($childs[$key]);
						}
					}
					sleep(1);
				}
			}
		}

		if (!$handled) {
			foreach ($campaignIds as $campaignId) {
				$mutexKey = sprintf('send-campaigns:campaign:%d:date:%s', $campaignId, date('Ymd'));
				if (Yii::app()->mutex->acquire($mutexKey)) {

					// 1.5.3
					try {

						$this->sendCampaignStep1($campaignId, 0);

					} catch (Exception $e) {

						$this->stdout(__LINE__ . ': ' . $e->getMessage());
						Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

						try {

							if ($campaign = Campaign::model()->findByPk($campaignId)) {
								$campaign->saveStatus(Campaign::STATUS_SENDING);
							}

						} catch (Exception $e) {

							$this->stdout(__LINE__ . ': ' . $e->getMessage());
							Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
						}
					}

					Yii::app()->mutex->release($mutexKey);
				}
			}
		}
	}

	/**
	 * @param $campaignId
	 * @param int $workerNumber
	 * @return int
	 * @throws CDbException
	 * @throws CException
	 */
	protected function sendCampaignStep1($campaignId, $workerNumber = 0)
	{
		$this->stdout(sprintf("Campaign Worker #%d looking into the campaign with ID: %d", $workerNumber, $campaignId));

		$statuses = array(Campaign::STATUS_SENDING, Campaign::STATUS_PENDING_SENDING);
		$campaign = Campaign::model()->findByPk((int)$campaignId);

		// since 1.3.7.3
		Yii::app()->hooks->doAction('console_command_send_campaigns_send_campaign_step1_start', $campaign);

		if (empty($campaign) || !in_array($campaign->status, $statuses)) {
			$this->stdout(sprintf("The campaign with ID: %d is not ready for processing.", $campaignId));
			return 1;
		}

		// this should never happen unless the list is removed while sending
		if (empty($campaign->list_id)) {
			$this->stdout(sprintf("The campaign with ID: %d is not ready for processing.", $campaignId));
			return 1;
		}

		// Make this available in entire class
		$this->_campaign = $campaign;

		$options  = Yii::app()->options;
		$list     = $campaign->list;
		$customer = $list->customer;

		$this->stdout(sprintf("This campaign belongs to %s(uid: %s).", $customer->getFullName(), $customer->customer_uid));

		// put proper status and priority
		$this->stdout('Changing the campaign status into PROCESSING!');
		$campaign->saveStatus(Campaign::STATUS_PROCESSING); // because we need the extra checks we can't get in saveAttributes
		$campaign->saveAttributes(array('priority' => 0));

		$dsParams = array('customerCheckQuota' => false, 'useFor' => array(DeliveryServer::USE_FOR_CAMPAIGNS));
		$server   = DeliveryServer::pickServer(0, $campaign, $dsParams);

		if (empty($server)) {
			$message  = 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!';
			$message .= 'Campaign UID: ' . $campaign->campaign_uid;
			Yii::log($message, CLogger::LEVEL_ERROR);
			$this->stdout($message);
			$campaign->saveStatus(Campaign::STATUS_SENDING);
			return 1;
		}

		if (!empty($customer->language_id)) {
			$language = Language::model()->findByPk((int)$customer->language_id);
			if (!empty($language)) {
				Yii::app()->setLanguage($language->getLanguageAndLocaleCode());
			}
		}

		// find the subscribers limit
		$limit = (int)$customer->getGroupOption('campaigns.subscribers_at_once', (int)Yii::app()->options->get('system.cron.send_campaigns.subscribers_at_once', 300));

		$mailerPlugins = array(
			'loggerPlugin' => true,
		);

		$sendAtOnce = (int)$customer->getGroupOption('campaigns.send_at_once', (int)$options->get('system.cron.send_campaigns.send_at_once', 0));
		if (!empty($sendAtOnce)) {
			$mailerPlugins['antiFloodPlugin'] = array(
				'sendAtOnce' => $sendAtOnce,
				'pause'      => (int)$customer->getGroupOption('campaigns.pause', (int)$options->get('system.cron.send_campaigns.pause', 0)),
			);
		}

		$perMinute = (int)$customer->getGroupOption('campaigns.emails_per_minute', (int)$options->get('system.cron.send_campaigns.emails_per_minute', 0));
		if (!empty($perMinute)) {
			$mailerPlugins['throttlePlugin'] = array(
				'perMinute' => $perMinute,
			);
		}

		$attachments = CampaignAttachment::model()->findAll(array(
			'select'    => 'file',
			'condition' => 'campaign_id = :cid',
			'params'    => array(':cid' => $campaign->campaign_id),
		));

		$changeServerAt    = (int)$customer->getGroupOption('campaigns.change_server_at', (int)$options->get('system.cron.send_campaigns.change_server_at', 0));
		$maxBounceRate     = (float)$customer->getGroupOption('campaigns.max_bounce_rate', (float)$options->get('system.cron.send_campaigns.max_bounce_rate', -1));
		$maxComplaintRate  = (float)$customer->getGroupOption('campaigns.max_complaint_rate', (float)$options->get('system.cron.send_campaigns.max_complaint_rate', -1));

		$this->sendCampaignStep2(array(
			'campaign'                => $campaign,
			'customer'                => $customer,
			'list'                    => $list,
			'server'                  => $server,
			'mailerPlugins'           => $mailerPlugins,
			'limit'                   => $limit,
			'offset'                  => 0,
			'changeServerAt'          => $changeServerAt,
			'maxBounceRate'           => $maxBounceRate,
			'maxComplaintRate'        => $maxComplaintRate,
			'options'                 => $options,
			'canChangeCampaignStatus' => true,
			'attachments'             => $attachments,
			'workerNumber'            => 0,
		));

		// since 1.3.9.7
		Yii::app()->hooks->doAction('console_command_send_campaigns_send_campaign_step1_end', $campaign);
	}

	/**
	 * @param array $params
	 * @return int
	 * @throws CException
	 */
	protected function sendCampaignStep2(array $params = array())
	{
		// max number of subs allowed to send this time
		$maxSubscribers = $this->_customerData[$params['customer']->customer_id]['campaigns'][$params['campaign']->campaign_id];

		$handled = false;
		if ($this->getCanUsePcntl() && ($subscriberBatchesInParallel = $this->getSubscriberBatchesInParallel()) > 1) {
			$handled = true;

			// 1.6.8 - this counter will be decremented under a mutex to allow sync for sendCampaignStep3 method
			// to avoid sending duplicates when under load and some processes start sending while others just load
			// data from database. When this counter is zero, we assume all processes loaded data in memory from database
			// and we can move on with sending 
			$fsCounterKey = __CLASS__ . '::findSubscribersSyncLock::' . $params['campaign']->campaign_id;
			Yii::app()->cache->set($fsCounterKey, $subscriberBatchesInParallel);
			//
			
			// make sure we deny this for all right now.
			$params['canChangeCampaignStatus'] = false;

			// make sure we close the external connections
			$this->setExternalConnectionsActive(false);

			$childs = array();
			$subscriberBatchesInParallelCounter = $subscriberBatchesInParallel;
			
			// 1.8.0 #pNa2E
			// offset must be kept separately because $params['limit'] might change from 
			// iteration to iteration which might lead to subscribers overalapping 
			// when calculating the offset
			$offset = 0;
			
			for($i = 0; $i < $subscriberBatchesInParallel; ++$i) {

				// 1.3.5.7
				if ($maxSubscribers <= $params['limit']) {
					$params['limit'] = $maxSubscribers;
				}
				$maxSubscribers -= $params['limit'];
				$maxSubscribers  = $maxSubscribers > 0 ? $maxSubscribers : 0;
				$params['limit'] = $params['limit'] > 0 ? $params['limit'] : 0;
				$subscriberBatchesInParallelCounter--;
				//

				// 1.8.0 #pNa2E
				$params['workerNumber']            = $i + 1;
				$params['offset']                  = $offset;
				$params['canChangeCampaignStatus'] = ($i == 0); // keep an eye on this.
				$offset                            = $params['offset'] + $params['limit'];
				//

				$pid = pcntl_fork();
				if($pid == -1) {
					continue;
				}

				// Parent
				if ($pid) {
					$childs[] = $pid;
				}

				// Child
				if (!$pid) {
					
					// TODO: Remove me if 1.8.0 #pNa2E proves correct
					// $params['workerNumber'] = $i + 1;
					// $params['offset'] = ($i * $params['limit']);
					// $params['canChangeCampaignStatus'] = ($i == 0); // keep an eye on this.

					$mutexKey = sprintf('send-campaigns:campaign:%s:date:%s:offset:%d:limit:%d',
						$params['campaign']->campaign_uid,
						date('Ymd'),
						$params['offset'],
						$params['limit']
					);

					if (Yii::app()->mutex->acquire($mutexKey)) {

						// 1.5.3
						try {
							
							$this->sendCampaignStep3($params);

						} catch (Exception $e) {

							$this->stdout(__LINE__ . ': ' . $e->getMessage());
							Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

							try {

								$params['campaign']->saveStatus(Campaign::STATUS_SENDING);

							} catch (Exception $e) {

								$this->stdout(__LINE__ . ': ' . $e->getMessage());
								Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
							}
						}

						Yii::app()->mutex->release($mutexKey);
					}
					Yii::app()->end();
				}
			}

			if (count($childs) == 0) {
				$handled = false;
			}

			while (count($childs) > 0) {
				foreach ($childs as $key => $pid) {
					$res = pcntl_waitpid($pid, $status, WNOHANG);
					if($res == -1 || $res > 0) {
						unset($childs[$key]);
					}
				}
				sleep(1);
			}
		}

		if (!$handled) {

			// 1.3.5.7
			if ($maxSubscribers > $params['limit']) {
				$maxSubscribers -= $params['limit'];
			} else {
				$params['limit'] = $maxSubscribers;
			}
			$params['limit'] = $params['limit'] > 0 ? $params['limit'] : 0;
			//

			$mutexKey = sprintf('send-campaigns:campaign:%s:date:%s:offset:%d:limit:%d',
				$params['campaign']->campaign_uid,
				date('Ymd'),
				$params['offset'],
				$params['limit']
			);

			if (Yii::app()->mutex->acquire($mutexKey)) {

				// 1.5.3
				try {

					$this->sendCampaignStep3($params);

				} catch (Exception $e) {

					$this->stdout(__LINE__ . ': ' . $e->getMessage());
					Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

					try {

						$params['campaign']->saveStatus(Campaign::STATUS_SENDING);

					} catch (Exception $e) {

						$this->stdout(__LINE__ . ': ' . $e->getMessage());
						Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
					}
				}

				Yii::app()->mutex->release($mutexKey);
			}
		}

		return 0;
	}

	/**
	 * @param array $params
	 * @return int
	 * @throws CException
	 */
	protected function sendCampaignStep3(array $params = array())
	{
		extract($params, EXTR_SKIP);

		$this->stdout(sprintf("Looking for subscribers for campaign with uid %s...(This is subscribers worker #%d)", $campaign->campaign_uid, $workerNumber));
		$this->stdout(sprintf("For campaign with uid %s and worker %d the offset is %d and the limit is %d", $campaign->campaign_uid, $workerNumber, $offset, $limit));
		
		$subscribers = $this->findSubscribers($offset, $limit, $campaign);

		// 1.6.8 - this will force all parallel processes for this campaign to wait until all the other processes have
		// loaded the data from database. This avoids sending duplicates when some processes started sending while others 
		// are still at the loading data from database step.
		if ($this->getCanUsePcntl() && ($subscriberBatchesInParallel = $this->getSubscriberBatchesInParallel()) > 1) {
			$fsCounterKey = __CLASS__ . '::findSubscribersSyncLock::' . $campaign->campaign_id;
			for ($i = 0; $i < $subscriberBatchesInParallel; $i++) {
				if (Yii::app()->mutex->acquire($fsCounterKey, $subscriberBatchesInParallel)) {
					if (($fsCounter = (int)Yii::app()->cache->get($fsCounterKey)) === 0) {
						Yii::app()->mutex->release($fsCounterKey);
						break;
					}
					$fsCounter--;
					Yii::app()->cache->set($fsCounterKey, $fsCounter);
					Yii::app()->mutex->release($fsCounterKey);
					if ($fsCounter === 0) {
						break;
					}
				}
				sleep(1);
			}
		}
		//

		$this->stdout(sprintf("This subscribers worker(#%d) will process %d subscribers for this campaign...", $workerNumber, count($subscribers)));

		// run some cleanup on subscribers
		$this->stdout("Running subscribers cleanup...");

		// since 1.3.6.2 - in some very rare conditions this happens!
		foreach ($subscribers as $index => $subscriber) {
			if (empty($subscriber->email)) {
				$subscriber->delete();
				unset($subscribers[$index]);
				continue;
			}

			// 1.3.7
			$separators = array(',', ';');
			foreach ($separators as $separator) {
				if (strpos($subscriber->email, $separator) === false) {
					continue;
				}

				$emails = explode($separator, $subscriber->email);
				$emails = array_map('trim', $emails);

				while (!empty($emails)) {
					$email = array_shift($emails);
					if (!FilterVarHelper::email($email)) {
						continue;
					}
					$exists = ListSubscriber::model()->findByAttributes(array(
						'list_id' => $subscriber->list_id,
						'email'   => $email,
					));
					if (!empty($exists)) {
						continue;
					}
					$subscriber->email = $email;
					$subscriber->save(false);
					break;
				}

				foreach ($emails as $index => $email) {
					if (!FilterVarHelper::email($email)) {
						continue;
					}
					$exists = ListSubscriber::model()->findByAttributes(array(
						'list_id' => $subscriber->list_id,
						'email'   => $email,
					));
					if (!empty($exists)) {
						continue;
					}
					$sub = new ListSubscriber();
					$sub->list_id = $subscriber->list_id;
					$sub->email   = $email;
					$sub->save();
				}
				break;
			}
			//

			if (!FilterVarHelper::email($subscriber->email)) {
				$subscriber->delete();
				unset($subscribers[$index]);
				continue;
			}
		}

		// reset the keys
		$subscribers      = array_values($subscribers);
		$subscribersCount = count($subscribers);

		$this->stdout(sprintf("Checking subscribers count after cleanup: %d", $subscribersCount));

		try {

			$params['subscribers'] = &$subscribers;

			$this->processSubscribersLoop($params);

			// free mem
			unset($params);

		} catch (Exception $e) {

			// free mem
			unset($params);

			$this->stdout(sprintf('Exception thrown: %s', $e->getMessage()));

			// exception code to be returned later
			$code = (int)$e->getCode();

			// make sure sending is resumed next time.
			$campaign->status = Campaign::STATUS_SENDING;

			// pause the campaigns of customers that reached the quota
			// they will only delay processing of other campaigns otherwise.
			if ($code == 98) {
				$campaign->status = Campaign::STATUS_PAUSED;
			}

			if ($canChangeCampaignStatus) {

				// save the changes, but no validation
				$campaign->saveStatus();

				// since 1.3.5.9
				$this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);

				// since 1.6.1
				$this->checkCampaignOverMaxComplaintRate($campaign, $maxComplaintRate);
			}

			// log the error so we can reference it
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

			// return the exception code
			return $code;
		}

		$this->stdout("", false);
		$this->stdout(sprintf('Done processing %d subscribers!', $subscribersCount));

		if ($canChangeCampaignStatus) {

			// do a final check for this campaign to see if it still exists or has been somehow changed from web interface.
			// this used to exist in the foreach loop but would cause so much overhead that i think is better to move it here
			// since if a campaign is paused from web interface it will keep that status anyway so it won't affect customers and will improve performance
			$_campaign = Yii::app()->getDb()->createCommand()
			                ->select('status')
			                ->from($campaign->tableName())
			                ->where('campaign_id = :cid', array(':cid' => (int)$campaign->campaign_id))
			                ->queryRow();

			if (empty($_campaign) || $_campaign['status'] != Campaign::STATUS_PROCESSING) {
				if (!empty($_campaign)) {
					$campaign->saveStatus($_campaign['status']);
					$this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);
					$this->checkCampaignOverMaxComplaintRate($campaign, $maxComplaintRate);
					$this->stdout('Campaign status has been changed successfully!');
				}
				return 0;
			}

			// the sending batch is over.
			// if we don't have enough subscribers for next batch, we stop.
			$subscribers = $this->findSubscribers(0, 1, $campaign);

			/**
			 * @since 1.5.8
			 * If TW is enabled and we don't have any subscribers to send for this hour, at this moment,
			 * we cannot just mark the campaign as sent, instead, we should postpone the campaign for the next minute.
			 * We do this with a limit, since we can't keep up forever.
			 * Limit is 1440 retries, which means each minute for 24 hours, which might be a bit overzealous...
			 */
			if ($campaign->isRegular && $campaign->option->timewarpEnabled) {
				$cacheKey       = sprintf('campaign:%d:timewarp:retries:counter', $campaign->campaign_id);
				$retriesCounter = (int)Yii::app()->cache->get($cacheKey, 0);
				$retriesCounter++;
				Yii::app()->cache->set($cacheKey, $retriesCounter);

				if (empty($subscribers) && $retriesCounter <= 1440) {
					$campaign->incrementPriority(1);
					$campaign->saveStatus(Campaign::STATUS_SENDING);
					$this->stdout('Campaign status has been changed successfully, but postponed!');
					return 0;
				}
			}
			//

			if (empty($subscribers)) {
				if ($this->markCampaignSent($campaign)) {
					$this->stdout('Campaign has been marked as sent!');
				}
				return 0;
			}

			// make sure sending is resumed next time
			$campaign->saveStatus(Campaign::STATUS_SENDING);
			$this->checkCampaignOverMaxBounceRate($campaign, $maxBounceRate);
			$this->checkCampaignOverMaxComplaintRate($campaign, $maxComplaintRate);
			$this->stdout('Campaign status has been changed successfully!');
		}

		$this->stdout('Done processing the campaign.');

		return 0;
	}

	/**
	 * @param array $params
	 * @return mixed
	 * @throws CException
	 * @throws Exception
	 */
	protected function processSubscribersLoop(array $params = array())
	{
		extract($params, EXTR_SKIP);

		// 1.4.5
		if ($campaign->getIsPaused()) {
			throw new Exception('Campaign has been paused!', 98);
		}

		$subscribersCount = count($subscribers);
		$processedCounter = 0;
		$failuresCount    = 0;
		$serverHasChanged = false;

		$dsParams = empty($dsParams) || !is_array($dsParams) ? array() : $dsParams;
		$dsParams = CMap::mergeArray(array(
			'customerCheckQuota' => false,
			'serverCheckQuota'   => false,
			'useFor'             => array(DeliveryServer::USE_FOR_CAMPAIGNS),
			'excludeServers'     => array(),
		), $dsParams);
		$domainPolicySubscribers = array();

		if (empty($server) && !($server = DeliveryServer::pickServer(0, $campaign, $dsParams))) {
			if (empty($serverNotFoundMessage)) {
				$serverNotFoundMessage  = 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!';
				$serverNotFoundMessage .= 'Campaign UID: ' . $campaign->campaign_uid;
			}
			throw new Exception($serverNotFoundMessage, 99);
		}

		$this->stdout('Sorting the subscribers...');
		$subscribers = $this->sortSubscribers($subscribers);

		// 1.8.2 - preload the list of campaign group block subscribers. if any...
		$campaignGroupBlockSubscribersList = array();
		if (!empty($campaign->group_id) && CampaignGroupBlockSubscriber::model()->countByAttributes(array('group_id' => (int)$campaign->group_id))) {
			$subscribersIds = array();
			foreach ($subscribers as $index => $subscriber) {
				$subscribersIds[] = $subscriber->subscriber_id;
			}
			$subscribersIdsChunks = array_chunk($subscribersIds, 100);
			$models = array();
			foreach ($subscribersIdsChunks as $subscribersIdsChunk) {
				$criteria = new CDbCriteria();
				$criteria->select = 'subscriber_id';
				$criteria->compare('group_id', $campaign->group_id);
				$criteria->addInCondition('subscriber_id', $subscribersIdsChunk);
				$models = CampaignGroupBlockSubscriber::model()->findAll($criteria);
				foreach ($models as $model) {
					$campaignGroupBlockSubscribersList[$model->subscriber_id] = true;
				}
			}
			unset($subscribersIds, $subscribersIdsChunks, $subscribersIdsChunk, $models);
		}
		//
		
		$this->stdout(sprintf('Entering the foreach processing loop for all %d subscribers...', $subscribersCount));

		foreach ($subscribers as $index => $subscriber) {

			// 1.4.5
			if (rand(0, 10) <= 5 && $campaign->getIsPaused()) {
				throw new Exception('Campaign has been paused!', 98);
			}

			$this->stdout("", false);
			$this->stdout(sprintf("%s - %d/%d", $subscriber->email, ($index+1), $subscribersCount));
			$this->stdout(sprintf('Checking if we can send to domain of %s...', $subscriber->email));

			// if this server is not allowed to send to this email domain, then just skip it.
			if (!$server->canSendToDomainOf($subscriber->email)) {
				$domainPolicySubscribers[] = $subscriber;
				unset($subscribers[$index]);
				continue;
			}

			// 1.4.5
			Yii::app()->hooks->doAction('console_send_campaigns_command_process_subscribers_loop_in_loop_start', $collection = new CAttributeCollection(array(
				'campaign'                => $campaign,
				'subscriber'              => $subscriber,
				'server'                  => $server,
				'domainPolicySubscribers' => $domainPolicySubscribers,
				'subscribers'             => $subscribers,
				'index'                   => $index,
				'continueProcessing'      => true,
			)));
			if (!$collection->continueProcessing) {
				continue;
			}
			$domainPolicySubscribers = $collection->domainPolicySubscribers;
			$subscribers             = $collection->subscribers;
			//

			// 1.4.4 - because of the temp queue campaigns
			$this->stdout(sprintf('Checking if %s is still confirmed...', $subscriber->email));
			if (!$subscriber->getIsConfirmed()) {
				$this->logDelivery($subscriber, Yii::t('campaigns', 'Subscriber not confirmed anymore!'), CampaignDeliveryLog::STATUS_ERROR, null, $server, $campaign);
				continue;
			}

			// if blacklisted, goodbye.
			$this->stdout(sprintf('Checking if %s is blacklisted...', $subscriber->email));
			if ($blCheckInfo = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_CAMPAIGN))) {
				if ($blCheckInfo->customerBlacklist) {
					$this->logDelivery($subscriber, $blCheckInfo->reason, CampaignDeliveryLog::STATUS_BLACKLISTED, null, $server, $campaign);
				} else {
					$this->logDelivery($subscriber, Yii::t('campaigns', 'This email is blacklisted. Sending is denied!'), CampaignDeliveryLog::STATUS_BLACKLISTED, null, $server, $campaign);
				}
				continue;
			}

			// if in a campaign suppression list, goodbye.
			$this->stdout(sprintf('Checking if %s is listed in a campaign suppression list...', $subscriber->email));
			if (CustomerSuppressionListEmail::isSubscriberListedByCampaign($subscriber, $campaign)) {
				$this->logDelivery($subscriber, Yii::t('campaigns', 'This email is listed in a suppression list. Sending is denied!'), CampaignDeliveryLog::STATUS_SUPPRESSED, null, $server, $campaign);
				continue;
			}
			
			// 1.8.2 - if listed in a campaign group block, stop
			$this->stdout(sprintf('Checking if %s is blocked in the campaign group...', $subscriber->email));
			if (isset($campaignGroupBlockSubscribersList[$subscriber->subscriber_id])) {
				unset($campaignGroupBlockSubscribersList[$subscriber->subscriber_id]);
				$this->logDelivery($subscriber, Yii::t('campaigns', 'This email is blocked for the current campaign group. Sending is denied!'), CampaignDeliveryLog::STATUS_BLOCKED, null, $server, $campaign);
				continue;
			}
			//

			// in case the server is over quota
			$this->stdout('Checking if the server is over quota...');
			if ($server->getIsOverQuota()) {
				$this->stdout('Server is over quota, choosing another one.');
				$currentServerId = $server->server_id;
				if (!($server = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams))) {
					$message  = 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!';
					$message .= 'Campaign UID: ' . $campaign->campaign_uid;
					throw new Exception($message, 99);
				}
			}

			$this->stdout('Preparing the entire email...');
			$emailParams = $this->prepareEmail($subscriber, $server, $campaign);

			if (empty($emailParams) || !is_array($emailParams)) {
				$this->logDelivery($subscriber, Yii::t('campaigns', 'Unable to prepare the email content!'), CampaignDeliveryLog::STATUS_ERROR, null, $server, $campaign);
				continue;
			}

			// since 1.5.2
			if (empty($emailParams['subject']) || (empty($emailParams['body']) && empty($emailParams['plainText']))) {
				$this->logDelivery($subscriber, Yii::t('campaigns', 'Unable to prepare the email content!'), CampaignDeliveryLog::STATUS_ERROR, null, $server, $campaign);
				continue;
			}

			if ($failuresCount >= 5 || ($changeServerAt > 0 && $processedCounter >= $changeServerAt && !$serverHasChanged)) {
				$this->stdout('Try to change the delivery server...');
				$currentServerId = $server->server_id;
				if ($newServer = DeliveryServer::pickServer($currentServerId, $campaign, $dsParams)) {
					$server = clone $newServer;
					unset($newServer);
					$this->stdout('Delivery server has been changed.');
				} else {
					$this->stdout('Delivery server cannot be changed.');
				}

				$failuresCount    = 0;
				$processedCounter = 0;
				$serverHasChanged = true;
			}

			$listUnsubscribeHeaderValue = $options->get('system.urls.frontend_absolute_url');
			$listUnsubscribeHeaderValue .= 'lists/'.$list->list_uid.'/unsubscribe/'.$subscriber->subscriber_uid . '/' . $campaign->campaign_uid . '/unsubscribe-direct?source=email-client-unsubscribe-button';
			$listUnsubscribeHeaderValue = '<'.$listUnsubscribeHeaderValue.'>';

			$reportAbuseUrl  = $options->get('system.urls.frontend_absolute_url');
			$reportAbuseUrl .= 'campaigns/'. $campaign->campaign_uid . '/report-abuse/' . $list->list_uid . '/' . $subscriber->subscriber_uid;

			// since 1.3.4.9
			$listUnsubscribeHeaderEmail = '';
			if (!empty($campaign->reply_to)) {
				$listUnsubscribeHeaderEmail = $campaign->reply_to;
			}
			if ($_email = $customer->getGroupOption('campaigns.list_unsubscribe_header_email', '')) {
				$listUnsubscribeHeaderEmail = $_email;
			}
			if (!empty($listUnsubscribeHeaderEmail)) {
				$_subject = sprintf('Campaign-Uid:%s / Subscriber-Uid:%s - Unsubscribe request', $campaign->campaign_uid, $subscriber->subscriber_uid);
				$_body    = 'Please unsubscribe me!';
				$mailToUnsubscribeHeader    = sprintf(', <mailto:%s?subject=%s&body=%s>', $listUnsubscribeHeaderEmail, $_subject, $_body);
				$listUnsubscribeHeaderValue .= $mailToUnsubscribeHeader;
			}
			//

			$headerPrefix = Yii::app()->params['email.custom.header.prefix'];
			$emailParams['headers'] = array(
				array('name' => $headerPrefix . 'Campaign-Uid',   'value' => $campaign->campaign_uid),
				array('name' => $headerPrefix . 'Subscriber-Uid', 'value' => $subscriber->subscriber_uid),
				array('name' => $headerPrefix . 'Customer-Uid',   'value' => $customer->customer_uid),
				array('name' => $headerPrefix . 'Customer-Gid',   'value' => (string)intval($customer->group_id)), // because of sendgrid
				array('name' => $headerPrefix . 'Delivery-Sid',   'value' => (string)intval($server->server_id)), // because of sendgrid
				array('name' => $headerPrefix . 'Tracking-Did',   'value' => (string)intval($server->tracking_domain_id)), // because of sendgrid
				array('name' => 'List-Unsubscribe',               'value' => $listUnsubscribeHeaderValue),
				array('name' => 'List-Id',                        'value' => $list->list_uid . ' <' . $list->display_name . '>'),
				array('name' => 'X-Report-Abuse',                 'value' => 'Please report abuse for this campaign here: ' . $reportAbuseUrl),
				array('name' => 'Feedback-ID',                    'value' => $this->getFeedbackIdHeaderValue($campaign, $subscriber, $list, $customer)),
				// https://support.google.com/a/answer/81126?hl=en#unsub
				array('name' => 'Precedence',                     'value' => 'bulk'),

				// since 1.3.7.3
				array('name' => $headerPrefix . 'EBS',   'value' => $options->get('system.urls.frontend_absolute_url') . 'lists/block-address'),
			);

			// since 1.3.4.6
			$headers = !empty($server->additional_headers) && is_array($server->additional_headers) ? $server->additional_headers : array();
			$headers = (array)Yii::app()->hooks->applyFilters('console_command_send_campaigns_campaign_custom_headers', $headers, $campaign, $subscriber, $customer, $server, $emailParams);
			$headers = $server->parseHeadersFormat($headers);

			// since 1.3.9.8
			$defaultHeaders = $customer->getGroupOption('servers.custom_headers', '');
			if (!empty($defaultHeaders)) {
				$defaultHeaders = DeliveryServerHelper::getOptionCustomerCustomHeadersArrayFromString($defaultHeaders);
				$headers = CMap::mergeArray($defaultHeaders, $headers);
			}
			
			if (!empty($headers)) {
				$headersNames = array();
				foreach ($headers as $header) {
					if (!is_array($header) || !isset($header['name'], $header['value']) || isset($headersNames[$header['name']])) {
						continue;
					}

					// 1.7.6
					if (strtolower($header['name']) == 'x-force-return-path') {
						$header['value'] = str_replace('@', '{{at}}', $header['value']);
					}
					// 
					
					$headersNames[$header['name']] = true;
					$headerSearchReplace           = CampaignHelper::getCommonTagsSearchReplace($header['value'], $campaign, $subscriber, $server);
					$header['value']               = str_replace(array_keys($headerSearchReplace), array_values($headerSearchReplace), $header['value']);

					// since 1.7.6
					if (strtolower($header['name']) == 'x-force-return-path') {
						$header['value']           = str_replace('@', '=', $header['value']);
						$header['value']           = str_replace('{{at}}', '@', $header['value']);
						$emailParams['returnPath'] = $header['value'];
					}
					//
					
					$emailParams['headers'][] = $header;
				}
				unset($headers, $headersNames);
			}
			
			$emailParams['mailerPlugins'] = $mailerPlugins;

			if (!empty($attachments)) {
				$emailParams['attachments'] = array();
				foreach ($attachments as $attachment) {
					$emailParams['attachments'][] = Yii::getPathOfAlias('root') . $attachment->file;
				}
			}

			$processedCounter++;
			if ($processedCounter >= $changeServerAt) {
				$serverHasChanged = false;
			}

			// since 1.3.6.6
			if (!empty($campaign->option->tracking_domain_id) && !empty($campaign->option->trackingDomain)) {
				$emailParams['trackingEnabled']     = true;
				$emailParams['trackingDomainModel'] = $campaign->option->trackingDomain;
			}
			//

			// since 1.3.4.6 (will be removed, don't hook into it)
			Yii::app()->hooks->doAction('console_command_send_campaigns_before_send_to_subscriber', $campaign, $subscriber, $customer, $server, $emailParams);

			// since 1.3.5.9
			$emailParams = Yii::app()->hooks->applyFilters('console_command_send_campaigns_before_send_to_subscriber', $emailParams, $campaign, $subscriber, $customer, $server);

			// set delivery object
			$server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign);

			// default status
			$status = CampaignDeliveryLog::STATUS_SUCCESS;

			$this->stdout(sprintf('Using delivery server: %s (ID: %d).', $server->hostname, $server->server_id));

			/**
			 * @since 1.5.3
			 * Put a final check on the quota and put the sending under the mutex
			 * to avoid concurrent access at incrementing the quota.
			 * Keep in mind that the below is the best we can get because
			 * we check the quota and increment it under a unique lock.
			 */
			$canHaveQuota = $server->getCanHaveQuota();
			if ($canHaveQuota) {
				$mutexKey = sha1(__METHOD__ . '-delivery-server-usage-' . (int)$server->server_id . '-' . date('Ymd'));
				$try = 0;
				$locked = false;
				while ($try <= 300) {
					$try++;
					if (Yii::app()->mutex->acquire($mutexKey)) {
						$locked = true;
						break;
					}
					$this->stdout('Long attempt to acquire the lock: #' . $try);
					sleep(1);
				}
				if (!$locked) {
					$message = 'Cannot acquire the mutex for delivery server to send the email!';
					$message .= 'Campaign UID: ' . $campaign->campaign_uid;
					throw new Exception($message, 99);
				}
			}

			try {

				/**
				 * We cannot swap the server anymore here because all the
				 * above information has been set for this server, that is headers, tags, etc.
				 * Therefore, we just give up and try again in the next run.
				 */
				if ($server->getIsOverQuota()) {
					$message  = 'Cannot find a valid server to send the campaign email, aborting until a delivery server is available!';
					$message .= 'Campaign UID: ' . $campaign->campaign_uid;
					throw new Exception($message, 99);
				}

				// since 1.5.8 - log before to avoid mutex contention
				$server->enableLogUsage()->logUsage()->disableLogUsage();

				// since 1.5.8, release the lock
				if ($canHaveQuota) {
					Yii::app()->mutex->release($mutexKey);
				}

				$start = microtime(true);

				try {
					$sent     = $server->sendEmail($emailParams);
					$response = $server->getMailer()->getLog();
				} catch (Exception $e) {
					$sent     = false;
					$response = $e->getMessage();
				}

				$this->stdout('Communication with the delivery server took: ' . (round(microtime(true) - $start, 5)));

			} catch (Exception $e) {

				if ($canHaveQuota) {
					Yii::app()->mutex->release($mutexKey);
				}

				Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
				$this->stdout($e->getMessage());

				throw new Exception($e->getMessage(), 99);
			}
			// end 1.5.3 

			// free mem
			unset($emailParams);

			$messageId = null;

			// 1.7.6
			$stdoutData = array(
				'category'  => 'email.send.subscriber',
				'sent'      => $sent ? true : false,
				'campaign'  => array(
					'uid'  => $campaign->campaign_uid,
					'name' => $campaign->name,
				),
				'subscriber' => array(
					'uid'   => $subscriber->subscriber_uid,
					'email' => $subscriber->email,
				),
				'customer' => array(
					'uid'   => $customer->customer_uid,
					'name'  => $customer->getFullName(),
				),
				'server' => array(
					'id'   => $server->server_id,
					'name' => $server->name,
				),
			);
			//
			
			if (!$sent) {
				$failuresCount++;
				$status = CampaignDeliveryLog::STATUS_GIVEUP;
				$this->stdout(CMap::mergeArray($stdoutData, array(
					'message'   => sprintf('Sending failed with: %s', $response),
				)));
			} else {
				$failuresCount = 0;
				$this->stdout(CMap::mergeArray($stdoutData, array(
					'message' => sprintf('Sending response is: %s', (!empty($response) ? $response : 'OK')),
				)));
			}

			if ($sent && is_array($sent) && !empty($sent['message_id'])) {
				$messageId = $sent['message_id'];
			}

			if ($sent) {
				$this->stdout('Sending OK.');
			}

			$start = microtime(true);
			$this->stdout(sprintf('Done for %s, logging delivery...', $subscriber->email));
			$this->logDelivery($subscriber, $response, $status, $messageId, $server, $campaign);
			$this->stdout('Logging delivery took: ' . (round(microtime(true) - $start, 5)));

			// since 1.4.2
			if ($sent) {
				$this->handleCampaignSentActionSubscriberField($campaign, $subscriber);
				$this->handleCampaignSentActionSubscriber($campaign, $subscriber);
			}

			// since 1.4.4
			if (!$sent) {
				if ($status == CampaignDeliveryLog::STATUS_GIVEUP && $campaign->customer->getGroupOption('quota_counters.campaign_giveup_emails', 'no') == 'yes') {
					$server->logUsage(array('force_customer_countable' => true));
				}
			}

			// since 1.3.4.6
			Yii::app()->hooks->doAction('console_command_send_campaigns_after_send_to_subscriber', $campaign, $subscriber, $customer, $server, $sent, $response, $status);

			// since 1.3.8.8
			if (!empty($server->pause_after_send)) {
				$sleepSeconds = round($server->pause_after_send / 1000000, 6);
				$this->stdout(sprintf('According to server settings, sleeping for %d seconds.', $sleepSeconds));

				// if set to sleep for too much,
				// close the external connections otherwise will timeout.
				if ($sleepSeconds >= 30) {
					$this->setExternalConnectionsActive(false);
				}

				// take a break
				usleep((int)$server->pause_after_send);

				// if set to sleep for too much, open the connections again
				if ($sleepSeconds >= 30) {
					$this->setExternalConnectionsActive(true);
				}
			}
		}

		// free mem
		unset($subscribers);

		// since 1.3.6.3 - it's not 100% bullet proof but should be fine
		// for most of the use cases
		if (!isset($params['domainPolicySubscribersCounter'])) {
			$params['domainPolicySubscribersCounter'] = 0;
		}
		$params['domainPolicySubscribersCounter']++;
		if (!empty($domainPolicySubscribers)) {
			if (empty($params['domainPolicySubscribersMaxRounds'])) {
				$params['domainPolicySubscribersMaxRounds'] = DeliveryServer::model()->countByAttributes(array(
					'status' => DeliveryServer::STATUS_ACTIVE
				));
			}
			if ($params['domainPolicySubscribersCounter'] <= $params['domainPolicySubscribersMaxRounds']) {
				$params['subscribers'] = &$domainPolicySubscribers;
				$params['changeServerAt'] = 0;
				$params['dsParams']['excludeServers'][] = $server->server_id;
				$params['server'] = null;
				$this->stdout("", false);
				$this->stdout(sprintf('Processing the rest of %d subscribers because of delivery server domain policies...', count($domainPolicySubscribers)));
				$this->stdout("", false);
				return $this->processSubscribersLoop($params);
			}
		}

		// free mem
		unset($params);
	}

	/**
	 * @since 1.3.6.6
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * @param Lists $list
	 * @param Customer $customer
	 * @return mixed|string
	 */
	public function getFeedbackIdHeaderValue(Campaign $campaign, ListSubscriber $subscriber, Lists $list, Customer $customer)
	{
		$format = $customer->getGroupOption('campaigns.feedback_id_header_format', '');
		if (empty($format)) {
			return sprintf('%s:%s:%s:%s', $campaign->campaign_uid, $subscriber->subscriber_uid, $list->list_uid, $customer->customer_uid);
		}

		$searchReplace = array(
			'[CAMPAIGN_UID]'    => $campaign->campaign_uid,
			'[SUBSCRIBER_UID]'  => $subscriber->subscriber_uid,
			'[LIST_UID]'        => $list->list_uid,
			'[CUSTOMER_UID]'    => $customer->customer_uid,
			'[CUSTOMER_NAME]'   => StringHelper::truncateLength(URLify::filter($customer->getFullName()), 15, ''),
		);
		$searchReplace = Yii::app()->hooks->applyFilters('feedback_id_header_format_tags_search_replace', $searchReplace);

		return str_replace(array_keys($searchReplace), array_values($searchReplace), $format);
	}

	/**
	 * @since 1.3.5.9
	 * @param Campaign $campaign
	 * @param $maxBounceRate
	 */
	protected function checkCampaignOverMaxBounceRate(Campaign $campaign, $maxBounceRate)
	{
		if ((int)$maxBounceRate < 0 || $campaign->getIsBlocked()) {
			return;
		}

		$bouncesRate = $campaign->getStats()->getBouncesRate() - $campaign->getStats()->getInternalBouncesRate();
		if ((float)$bouncesRate > (float)$maxBounceRate) {
			$campaign->block("Campaign bounce rate is higher than allowed!");
		}
	}

	/**
	 * @since 1.6.1
	 * @param Campaign $campaign
	 * @param $maxComplaintRate
	 */
	protected function checkCampaignOverMaxComplaintRate(Campaign $campaign, $maxComplaintRate)
	{
		if ((int)$maxComplaintRate < 0 || $campaign->getIsBlocked()) {
			return;
		}

		if ((float)$campaign->getStats()->getComplaintsRate() > (float)$maxComplaintRate) {
			$campaign->block("Campaign complaint rate is higher than allowed!");
		}
	}

	/**
	 * @since 1.3.5.9
	 * @return bool
	 */
	protected function getCanUsePcntl()
	{
		static $canUsePcntl;
		if ($canUsePcntl !== null) {
			return $canUsePcntl;
		}
		if (Yii::app()->options->get('system.cron.send_campaigns.use_pcntl', 'no') != 'yes') {
			return $canUsePcntl = false;
		}
		if (!CommonHelper::functionExists('pcntl_fork') || !CommonHelper::functionExists('pcntl_waitpid')) {
			return $canUsePcntl = false;
		}
		return $canUsePcntl = true;
	}

	/**
	 * @since 1.3.5.9
	 * @return int
	 */
	protected function getCampaignsInParallel()
	{
		return (int)Yii::app()->options->get('system.cron.send_campaigns.campaigns_in_parallel', 5);
	}

	/**
	 * @since 1.3.5.9
	 * @return int
	 */
	protected function getSubscriberBatchesInParallel()
	{
		return (int)Yii::app()->options->get('system.cron.send_campaigns.subscriber_batches_in_parallel', 5);
	}

	/**
	 * @param ListSubscriber $subscriber
	 * @param $message
	 * @param $status
	 * @param $messageId
	 * @param DeliveryServer $server
	 * @param Campaign $campaign
	 * @return bool
	 */
	protected function logDelivery(ListSubscriber $subscriber, $message, $status, $messageId, DeliveryServer $server, Campaign $campaign)
	{
		// 1.3.7.9
		if ($this->_useTempQueueTables) {
			$campaign->queueTable->deleteSubscriber($subscriber->subscriber_id);
		}

		$deliveryLog = new CampaignDeliveryLog();
		$deliveryLog->campaign_id      = $campaign->campaign_id;
		$deliveryLog->subscriber_id    = $subscriber->subscriber_id;
		$deliveryLog->email_message_id = (string)$messageId;
		$deliveryLog->message          = str_replace("\n\n", "\n", $message);
		$deliveryLog->status           = $status;

		// since 1.3.6.1
		$deliveryLog->delivery_confirmed = CampaignDeliveryLog::TEXT_YES;
		if ($server) {
			$deliveryLog->server_id = $server->server_id;
			if ($server->canConfirmDelivery && $server->must_confirm_delivery == DeliveryServer::TEXT_YES) {
				$deliveryLog->delivery_confirmed = CampaignDeliveryLog::TEXT_NO;
			}
		}

		$deliveryLog->addRelatedRecord('campaign', $campaign, false);
		$deliveryLog->addRelatedRecord('subscriber', $subscriber, false);
		$deliveryLog->addRelatedRecord('server', $server, false);
		
		return $deliveryLog->save(false);
	}

	/**
	 * @param Campaign $campaign
	 * @return int
	 */
	protected function countSubscribers(Campaign $campaign)
	{
		// 1.3.7.9
		if ($this->_useTempQueueTables) {
			return $campaign->queueTable->countSubscribers();
		}

		$criteria = new CDbCriteria();
		$criteria->with['deliveryLogs'] = array(
			'select'    => false,
			'together'  => true,
			'joinType'  => 'LEFT OUTER JOIN',
			'on'        => 'deliveryLogs.campaign_id = :cid',
			'condition' => 'deliveryLogs.subscriber_id IS NULL',
			'params'    => array(':cid' => $campaign->campaign_id),
		);

		return $campaign->countSubscribers($criteria);
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 * @param Campaign $campaign
	 * @return array
	 */
	protected function findSubscribers($offset = 0, $limit = 300, Campaign $campaign)
	{
		// 1.3.7.3
		if (empty($limit) || $limit <= 0) {
			return array();
		}

		// 1.3.7.9
		if ($this->_useTempQueueTables) {
			$subscribers = $campaign->queueTable->findSubscribers($offset, $limit);
			return $subscribers;
		}

		$criteria = new CDbCriteria();
		$criteria->with['deliveryLogs'] = array(
			'select'    => false,
			'together'  => true,
			'joinType'  => 'LEFT OUTER JOIN',
			'on'        => 'deliveryLogs.campaign_id = :cid',
			'condition' => 'deliveryLogs.subscriber_id IS NULL',
			'params'    => array(':cid' => $campaign->campaign_id),
		);

		// since 1.5.2
		if ($campaign->isRegular && $campaign->option->getTimewarpEnabled()) {
			if ($_criteria = CampaignHelper::getTimewarpCriteria($campaign)) {
				$criteria->mergeWith($_criteria);
			}
		}

		// since 1.3.6.3 - because in pcntl mode we send dupes, we don't want this
		if (!$this->getCanUsePcntl() && $campaign->option->canSetMaxSendCountRandom) {
			$criteria->order = 'RAND()';
		}

		// and find them
		$subscribers = $campaign->findSubscribers($offset, $limit, $criteria);
		return $subscribers;
	}

	/**
	 * @param $subscribers
	 * @return array
	 */
	protected function sortSubscribers(array $subscribers = array())
	{
		$subscribersCount = count($subscribers);
		$_subscribers = array();
		foreach ($subscribers as $index => $subscriber) {
			$emailParts = explode('@', $subscriber->email);
			$domainName = $emailParts[1];
			if (!isset($_subscribers[$domainName])) {
				$_subscribers[$domainName] = array();
			}
			$_subscribers[$domainName][] = $subscriber;
			unset($subscribers[$index]);
		}

		$subscribers = array();
		while ($subscribersCount > 0) {
			foreach ($_subscribers as $domainName => $subs) {
				foreach ($subs as $index => $sub) {
					$subscribers[] = $sub;
					unset($_subscribers[$domainName][$index]);
					break;
				}
			}
			$subscribersCount--;
		}

		// free mem
		unset($_subscribers);

		return $subscribers;
	}

	/**
	 * @param ListSubscriber $subscriber
	 * @param DeliveryServer $server
	 * @param Campaign $campaign
	 *
	 * @return array
	 * @throws CException
	 * @throws Throwable
	 */
	protected function prepareEmail(ListSubscriber $subscriber, DeliveryServer $server, Campaign $campaign)
	{
		// how come ?
		if (empty($campaign->template)) {
			return array();
		}

		// since 1.3.9.3
		Yii::app()->hooks->applyFilters('console_command_send_campaigns_before_prepare_email', null, $campaign, $subscriber, $server);

		$list           = $campaign->list;
		$customer       = $list->customer;
		$emailContent   = $campaign->template->content;
		$emailSubject   = $campaign->subject;
		$embedImages    = array();
		$emailFooter    = null;
		$onlyPlainText  = !empty($campaign->template->only_plain_text) && $campaign->template->only_plain_text === CampaignTemplate::TEXT_YES;
		$emailAddress   = $subscriber->email;
		$toName         = $subscriber->email;

		// since 1.3.5.9
		$fromEmailCustom= null;
		$fromNameCustom = null;
		$replyToCustom  = null;

		// really blind check to see if it contains a tag
		if (strpos($campaign->from_email, '[') !== false || strpos($campaign->from_name, '[') !== false || strpos($campaign->reply_to, '[') !== false) {
			if (strpos($campaign->from_email, '[') !== false) {
				$searchReplace   = CampaignHelper::getCommonTagsSearchReplace($campaign->from_email, $campaign, $subscriber);
				$fromEmailCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_email);
				$fromEmailCustom = CampaignHelper::applyRandomContentTag($fromEmailCustom);
				if (!FilterVarHelper::email($fromEmailCustom)) {
					$fromEmailCustom = null;
					$campaign->from_email = $server->from_email;
				}
			}
			if (strpos($campaign->from_name, '[') !== false) {
				$searchReplace  = CampaignHelper::getCommonTagsSearchReplace($campaign->from_name, $campaign, $subscriber);
				$fromNameCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_name);
				$fromNameCustom = CampaignHelper::applyRandomContentTag($fromNameCustom);
			}
			if (strpos($campaign->reply_to, '[') !== false) {
				$searchReplace  = CampaignHelper::getCommonTagsSearchReplace($campaign->reply_to, $campaign, $subscriber);
				$replyToCustom  = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->reply_to);
				$replyToCustom  = CampaignHelper::applyRandomContentTag($replyToCustom);
				if (!FilterVarHelper::email($replyToCustom)) {
					$replyToCustom = null;
					$campaign->reply_to = $server->from_email;
				}
			}
		}

		if (!$onlyPlainText) {

			if (!empty($campaign->option->preheader)) {
				$emailContent = CampaignHelper::injectPreheader($emailContent, $campaign->option->preheader, $campaign);
			}

			if (($emailHeader = $customer->getGroupOption('campaigns.email_header')) && strlen(trim($emailHeader)) > 5) {
				$emailContent = CampaignHelper::injectEmailHeader($emailContent, $emailHeader, $campaign);
			}

			if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
				$emailContent = CampaignHelper::injectEmailFooter($emailContent, $emailFooter, $campaign);
			}

			if ($server->canEmbedImages && !empty($campaign->option) && !empty($campaign->option->embed_images) && $campaign->option->embed_images == CampaignOption::TEXT_YES) {
				list($emailContent, $embedImages) = CampaignHelper::embedContentImages($emailContent, $campaign);
			}

			if (CampaignHelper::contentHasXmlFeed($emailContent)) {
				$emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, true, null, $server);
			}

			if (CampaignHelper::contentHasJsonFeed($emailContent)) {
				$emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, true, null, $server);
			}

			// 1.5.3
			if (CampaignHelper::hasRemoteContentTag($emailContent)) {
				$emailContent = CampaignHelper::fetchContentForRemoteContentTag($emailContent, $campaign, $subscriber);
			}
			//

			if (!empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
				$emailContent = CampaignHelper::transformLinksForTracking($emailContent, $campaign, $subscriber, true);
			}

			// since 1.3.5.9 - optional open tracking.
			$trackOpen = $campaign->option->open_tracking == CampaignOption::TEXT_YES;
			//
			$emailData = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, $trackOpen, $server);
			list($toName, $emailSubject, $emailContent) = $emailData;
		}

		// Plain TEXT only supports basic tags transform, no xml/json feeds.
		$emailPlainText = null;
		if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) {
			if ($campaign->template->auto_plain_text === CampaignTemplate::TEXT_YES /* && empty($campaign->template->plain_text)*/) {
				$emailPlainText = CampaignHelper::htmlToText($emailContent);
			}
			if (empty($emailPlainText) && !empty($campaign->template->plain_text) && !$onlyPlainText) {
				$emailPlainText = $campaign->template->plain_text;
				if (($emailHeader = $customer->getGroupOption('campaigns.email_header')) && strlen(trim($emailHeader)) > 5) {
					$emailHeader  = strip_tags($emailHeader);
					$emailHeader .= "\n\n\n";
					$emailPlainText = $emailHeader . $emailPlainText;
				}
				if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
					$emailPlainText .= "\n\n\n";
					$emailPlainText .= strip_tags($emailFooter);
				}
				if (!empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
					$emailPlainText = CampaignHelper::transformLinksForTracking($emailPlainText, $campaign, $subscriber, true, true);
				}
				$_emailData = CampaignHelper::parseContent($emailPlainText, $campaign, $subscriber, false, $server);
				list(, , $emailPlainText) = $_emailData;
				$emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
			}
		}

		if ($onlyPlainText) {
			$emailPlainText = $campaign->template->plain_text;
			if (($emailHeader = $customer->getGroupOption('campaigns.email_header')) && strlen(trim($emailHeader)) > 5) {
				$emailHeader  = strip_tags($emailHeader);
				$emailHeader .= "\n\n\n";
				$emailPlainText = $emailHeader . $emailPlainText;
			}
			if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
				$emailPlainText .= "\n\n\n";
				$emailPlainText .= strip_tags($emailFooter);
			}
			if (!empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
				$emailPlainText = CampaignHelper::transformLinksForTracking($emailPlainText, $campaign, $subscriber, true, true);
			}
			$_emailData = CampaignHelper::parseContent($emailPlainText, $campaign, $subscriber, false, $server);
			list($toName, $emailSubject, $emailPlainText) = $_emailData;
			$emailPlainText = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailPlainText);
		}

		// since 1.3.5.3
		if (CampaignHelper::contentHasXmlFeed($emailSubject)) {
			$emailSubject = CampaignXmlFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject, $server);
		}

		if (CampaignHelper::contentHasJsonFeed($emailSubject)) {
			$emailSubject = CampaignJsonFeedParser::parseContent($emailSubject, $campaign, $subscriber, true, $campaign->subject, $server);
		}

		// 1.5.3
		if (CampaignHelper::hasRemoteContentTag($emailSubject)) {
			$emailSubject = CampaignHelper::fetchContentForRemoteContentTag($emailSubject, $campaign, $subscriber);
		}
		//
		
		if (CampaignHelper::isTemplateEngineEnabled()) {
			if (!$onlyPlainText && !empty($emailContent)) {
				$searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailContent, $campaign, $subscriber, $server);
				$emailContent  = CampaignHelper::parseByTemplateEngine($emailContent, $searchReplace);
			}
			if (!empty($emailSubject)) {
				$searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailSubject, $campaign, $subscriber, $server);
				$emailSubject  = CampaignHelper::parseByTemplateEngine($emailSubject, $searchReplace);
			}
			if (!empty($emailPlainText)) {
				$searchReplace  = CampaignHelper::getCommonTagsSearchReplace($emailPlainText, $campaign, $subscriber, $server);
				$emailPlainText = CampaignHelper::parseByTemplateEngine($emailPlainText, $searchReplace);
			}
		}
		
		$emailParams = array(
			'to'              => array($emailAddress => $toName),
			'subject'         => trim(!empty($emailSubject) ? $emailSubject : $campaign->subject),
			'body'            => trim($emailContent),
			'plainText'       => trim($emailPlainText),
			'embedImages'     => $embedImages,
			'onlyPlainText'   => $onlyPlainText,

			// since 1.3.5.9
			'fromEmailCustom' => $fromEmailCustom,
			'fromNameCustom'  => $fromNameCustom,
			'replyToCustom'   => $replyToCustom,
		);

		// since 1.3.9.3
		$emailParams = Yii::app()->hooks->applyFilters('console_command_send_campaigns_after_prepare_email', $emailParams, $campaign, $subscriber, $server);

		return $emailParams;
	}

	/**
	 * @param Campaign $campaign
	 * @return bool
	 * @throws CDbException
	 * @throws CException
	 */
	protected function markCampaignSent(Campaign $campaign)
	{
		// 1.4.4 this might take a while...
		if ($this->campaignMustHandleGiveups($campaign)) {
			$campaign->saveStatus(Campaign::STATUS_SENDING);
			return true;
		}

		if ($campaign->isAutoresponder) {
			$campaign->saveStatus(Campaign::STATUS_SENDING);
			return true;
		}

		$campaign->saveStatus(Campaign::STATUS_SENT);

		if (Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
			$list = $campaign->list;
			$customer = $list->customer;
			if (!($logAction = $customer->asa('logAction'))) {
				$customer->attachBehavior('logAction', array(
					'class' => 'customer.components.behaviors.CustomerActionLogBehavior',
				));
				$logAction = $customer->asa('logAction');
			}
			$logAction->campaignSent($campaign);
		}

		// since 1.3.4.6
		Yii::app()->hooks->doAction('console_command_send_campaigns_campaign_sent', $campaign);

		$campaign->sendStatsEmail();

		// since 1.3.5.3
		$campaign->tryReschedule();
		
		// since 1.7.6
		if (($count = (int)$campaign->getSendingGiveupsCount()) > 0) {
			$campaign->updateSendingGiveupCount($count);
		}

		return true;
	}

	/**
	 * Check customers quota limits
	 */
	protected function checkCustomersQuotaLimits()
	{
		if (empty($this->_customerData) || !is_array($this->_customerData)) {
			return;
		}

		foreach ($this->_customerData as $customerId => $cdata) {

			$customer = $cdata['customer'];
			$enabled  = $customer->getGroupOption('sending.quota_notify_enabled', 'no') == 'yes';

			if (!$enabled) {
				continue;
			}

			if ($this->getCanUsePcntl()) {
				sleep(rand(1, 3));
			}

			$counter = 0;
			foreach ($cdata['campaigns'] as $campaignId => $campaignMaxSubscribers) {
				if ($cdata['subscribersCount'] > $campaignMaxSubscribers) {
					$counter += $campaignMaxSubscribers;
				} else {
					$counter += $cdata['subscribersCount'];
				}
			}

			$timeNow    = time();
			$lastNotify = (int)$customer->getOption('sending_quota.last_notification', 0);
			$notifyTs   = 6 * 3600; // no less than 6 hours.

			$quotaTotal = $cdata['quotaTotal'];
			$quotaUsage = $cdata['quotaUsage'] + $counter; // current usage + future usage

			if ($quotaTotal <= 0 || ($lastNotify + $notifyTs) > $timeNow) {
				continue;
			}

			$quotaNotifyPercent = (int)$customer->getGroupOption('sending.quota_notify_percent', 95);
			$quotaUsagePercent  = ($quotaUsage / $quotaTotal) * 100;

			if ($quotaUsagePercent < $quotaNotifyPercent) {
				continue;
			}

			$customer->setOption('sending_quota.last_notification', $timeNow);

			$this->notifyCustomerReachingQuota(array(
				'customer'           => $customer,
				'quotaTotal'         => $quotaTotal,
				'quotaLeft'          => $cdata['quotaLeft'],
				'quotaUsage'         => $quotaUsage,
				'quotaUsagePercent'  => $quotaUsagePercent,
				'quotaNotifyPercent' => $quotaNotifyPercent,
			));
		}
	}

	/**
	 * @param array $params
	 */
	protected function notifyCustomerReachingQuota(array $params = array())
	{
		$customer = $params['customer'];

		// create the message
		$_message  = 'Your maximum allowed sending quota is set to {max} emails and you currently have sent {current} emails, which means you have used {percent} of your allowed sending quota!<br />';
		$_message .= 'Once your sending quota is over, you will not be able to send any emails!<br /><br />';
		$_message .= 'Please make sure you renew your sending quota.<br /> Thank you!';

		$message = new CustomerMessage();
		$message->customer_id = $customer->customer_id;
		$message->title       = 'Your sending quota is close to the limit!';
		$message->message     = $_message;
		$message->message_translation_params = array(
			'{max}'     => $params['quotaTotal'],
			'{current}' => $params['quotaUsage'],
			'{percent}' => round($params['quotaUsagePercent'], 2) . '%',
		);
		$message->save();

		$dsParams = array('useFor' => array(DeliveryServer::USE_FOR_REPORTS));
		if (!($server = DeliveryServer::pickServer(0, null, $dsParams))) {
			return;
		}

		// prepare and send the email.
		$emailTemplate  = Yii::app()->options->get('system.email_templates.common');
		$emailBody      = $customer->getGroupOption('sending.quota_notify_email_content');
		$emailTemplate  = str_replace('[CONTENT]', $emailBody, $emailTemplate);

		$searchReplace  = array(
			'[FIRST_NAME]'          => $customer->first_name,
			'[LAST_NAME]'           => $customer->last_name,
			'[FULL_NAME]'           => $customer->fullName,
			'[QUOTA_TOTAL]'         => $params['quotaTotal'],
			'[QUOTA_USAGE]'         => $params['quotaUsage'],
			'[QUOTA_USAGE_PERCENT]' => round($params['quotaUsagePercent'], 2) . '%',

		);
		$emailTemplate = str_replace(array_keys($searchReplace), array_values($searchReplace), $emailTemplate);

		$emailParams            = array();
		$emailParams['subject'] = Yii::t('customers', 'Your sending quota is close to the limit!');
		$emailParams['body']    = $emailTemplate;
		$emailParams['to']      = $customer->email;

		$server->sendEmail($emailParams);

	}

	/**
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * @return $this
	 */
	protected function handleCampaignSentActionSubscriberField(Campaign $campaign, ListSubscriber $subscriber)
	{
		static $sentActionModels = array();

		try {

			if (!isset($sentActionModels[$campaign->campaign_id])) {
				$sentActionModels[$campaign->campaign_id] = CampaignSentActionListField::model()->findAllByAttributes(array(
					'campaign_id' => $campaign->campaign_id,
				));
			}

			if (empty($sentActionModels[$campaign->campaign_id])) {
				return $this;
			}

			foreach ($sentActionModels[$campaign->campaign_id] as $model) {
				$valueModel = ListFieldValue::model()->findByAttributes(array(
					'field_id'      => $model->field_id,
					'subscriber_id' => $subscriber->subscriber_id,
				));
				if (empty($valueModel)) {
					$valueModel = new ListFieldValue();
					$valueModel->field_id       = $model->field_id;
					$valueModel->subscriber_id  = $subscriber->subscriber_id;
				}

				$valueModel->value = $model->getParsedFieldValueByListFieldValue(new CAttributeCollection(array(
					'valueModel' => $valueModel,
					'campaign'   => $campaign,
					'subscriber' => $subscriber,
				)));
				$valueModel->save();
			}

		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		return $this;
	}

	/**
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * @return $this
	 */
	protected function handleCampaignSentActionSubscriber(Campaign $campaign, ListSubscriber $subscriber)
	{
		static $sentActionModels = array();

		try {

			if (!isset($sentActionModels[$campaign->campaign_id])) {
				$sentActionModels[$campaign->campaign_id] = CampaignSentActionSubscriber::model()->findAllByAttributes(array(
					'campaign_id' => $campaign->campaign_id,
				));
			}

			if (empty($sentActionModels[$campaign->campaign_id])) {
				return $this;
			}

			foreach ($sentActionModels[$campaign->campaign_id] as $model) {
				if ($model->action == CampaignSentActionSubscriber::ACTION_MOVE) {
					$subscriber->moveToList($model->list_id, false, false);
				} else {
					$subscriber->copyToList($model->list_id, false, false);
				}
			}

		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		return $this;
	}

	/**
	 * @since 1.4.4
	 *
	 * @param Campaign $campaign
	 * @return bool
	 */
	protected function campaignMustHandleGiveups(Campaign $campaign)
	{
		if ($campaign->customer->getGroupOption('campaigns.retry_failed_sending', 'no') != 'yes') {
			return false;
		}

		if (!($count = $campaign->getSendingGiveupsCount())) {
			return false;
		}

		// since 1.7.6
		$campaign->updateSendingGiveupCount($count);
		
		if ($campaign->isRegular && (int)$campaign->option->giveup_counter >= (int)Yii::app()->params['campaign.delivery.giveup.retries']) {
			return false;
		}

		$campaign->updateSendingGiveupCounter();

		if ($this->_useTempQueueTables) {

			$campaign->queueTable->handleSendingGiveups();

		} else {

			$campaign->resetSendingGiveups();
		}

		return true;
	}

	/**
	 * @param $message
	 * @param bool $timer
	 * @param string $separator
	 * @param bool $store
	 */
	public function stdout($message, $timer = true, $separator = "\n", $store = false)
	{
		if (!is_array($message)) {
			$message = array(
				'message' => $message
			);
		}

		if ($timer) {
			$message['timestamp'] = time();
		}
		
		if (empty($message['category'])) {
			$message['category'] = 'common';
		}

		$message = Yii::app()->hooks->applyFilters('console_command_send_campaigns_stdout_message', $message, $this);
		
		if (!empty($message['message'])) {
			parent::stdout($message['message'], $timer, $separator, $store);	
		}
	}
}