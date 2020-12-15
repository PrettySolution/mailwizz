<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DailyCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class DailyCommand extends ConsoleCommand 
{
    /**
     * @return int
     */
    public function actionIndex() 
    {
        $this
            ->deleteSubscribers()
            ->deleteDeliveryServersUsageLogs()
	        ->deleteDeliveryServers()
            ->deleteCustomerOldActionLogs()
            ->deleteUnconfirmedCustomers()
            ->deleteUncompleteOrders()
            ->deleteGuestFailedAttempts()
            ->deleteCampaigns()
            ->deleteSegments()
            ->deleteLists()
            ->deleteSurveys()
            ->syncListsCustomFields()
            ->syncSurveysCustomFields()
            ->deleteCampaignsQueueTables()
            ->deleteCustomers()
            ->deleteDisabledCustomers()
            ->deleteDisabledCustomersData()
            ->deleteMutexes()
            ->deleteCampaignDeliveryLogs()
	        ->deleteCampaignBounceLogs()
	        ->deleteCampaignOpenLogs()
	        ->deleteCampaignClickLogs()
            ->deleteTransactionalEmails()
            ->deleteUnusedCampaignShareCodes()
            ->sendCampaignStatsEmail()
            ->handleScheduledInactiveCustomers()
            ->writePhpInfo()
            ->verifyLicense();
        
        Yii::app()->hooks->doAction('console_command_daily', $this);

        /**
         * Run the auto-updater at the end of everything.
         */
        $this->runAutoUpdater();
        
        return 0;
    }

    /**
     * @return $this
     */
    protected function deleteSubscribers()
    {
        $options         = Yii::app()->options;
        $unsubscribeDays = (int)$options->get('system.cron.process_subscribers.unsubscribe_days', 0);
        $unconfirmDays   = (int)$options->get('system.cron.process_subscribers.unconfirm_days', 3);
        $blacklistedDays = (int)$options->get('system.cron.process_subscribers.blacklisted_days', 0);
        
        if ($memoryLimit = $options->get('system.cron.process_subscribers.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        
        try {
            $connection = Yii::app()->getDb();
            
            if ($unsubscribeDays > 0) {
                $interval = 60 * 60 * 24 * $unsubscribeDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_UNSUBSCRIBED,
                ));
            }
            
            if ($unconfirmDays > 0) {
                $interval = 60 * 60 * 24 * $unconfirmDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_UNCONFIRMED,
                ));
            }
            
            if ($blacklistedDays > 0) {
                $interval = 60 * 60 * 24 * $blacklistedDays;
                $sql = 'DELETE FROM `{{list_subscriber}}` WHERE `status` = :st AND last_updated < DATE_SUB(NOW(), INTERVAL '.(int)$interval.' SECOND)';
                $connection->createCommand($sql)->execute(array(
                    ':st' => ListSubscriber::STATUS_BLACKLISTED,
                ));
            }
        } catch(Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

	/**
	 * @return $this
	 */
	protected function deleteDeliveryServersUsageLogs()
	{
		try {
			$options      = Yii::app()->options;
			$daysRemoval  = (int)$options->get('system.cron.process_delivery_bounce.delivery_servers_usage_logs_removal_days', 90);

			$connection = Yii::app()->getDb();
			$connection->createCommand(sprintf('DELETE FROM `{{delivery_server_usage_log}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL %d DAY)', $daysRemoval))->execute();
		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}
		return $this;
	}
	
	/**
	 * @return $this
	 */
	protected function deleteDeliveryServers()
	{
		$servers = DeliveryServer::model()->findAllByAttributes(array(
			'status' => DeliveryServer::STATUS_PENDING_DELETE,
		));
		foreach ($servers as $server) {
			try {
				$mapping = DeliveryServer::getTypesMapping();
				$type = isset($mapping[$server->type]) ? $mapping[$server->type] : null;
				if (empty($type)) {
					continue;
				}
				$server = DeliveryServer::model($type)->findByPk((int)$server->server_id);
				$server->delete();
			} catch (Exception $e) {
				
				$this->stdout(__LINE__ . ': ' . $e->getMessage());
				Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
			}
		}
		return $this;
	}

    /**
     * @return $this
     */
    protected function deleteCustomerOldActionLogs()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{customer_action_log}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 MONTH)')->execute();    
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteUnconfirmedCustomers()
    {
        $options        = Yii::app()->options;
        $unconfirmDays  = (int)$options->get('system.customer_registration.unconfirm_days_removal', 7);
        
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand(sprintf('DELETE FROM `{{customer}}` WHERE `status` = :st AND date_added < DATE_SUB(NOW(), INTERVAL %d DAY)', (int)$unconfirmDays))->execute(array(
                ':st' => Customer::STATUS_PENDING_CONFIRM,
            ));    
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteUncompleteOrders()
    {
        $options        = Yii::app()->options;
        $unconfirmDays  = (int)$options->get('system.monetization.orders.uncomplete_days_removal', 7);
        
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand(sprintf('DELETE FROM `{{price_plan_order}}` WHERE `status` != :st AND `status` != :st2 AND date_added < DATE_SUB(NOW(), INTERVAL %d DAY)', (int)$unconfirmDays))->execute(array(
                ':st'   => PricePlanOrder::STATUS_COMPLETE,
                ':st2'  => PricePlanOrder::STATUS_REFUNDED,
            ));    
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteCampaigns()
    {
        $campaigns = Campaign::model()->findAllByAttributes(array(
            'status' => Campaign::STATUS_PENDING_DELETE,
        ));
        foreach ($campaigns as $campaign) {
            try {
                $campaign->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteLists()
    {
        $lists = Lists::model()->findAllByAttributes(array(
            'status' => Lists::STATUS_PENDING_DELETE,
        ));
        foreach ($lists as $list) {
            try {
                $list->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteSegments()
    {
        $segments = ListSegment::model()->findAllByAttributes(array(
            'status' => ListSegment::STATUS_PENDING_DELETE,
        ));
        foreach ($segments as $segment) {
            try {
                $segment->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteSurveys()
    {
        $surveys = Survey::model()->findAllByAttributes(array(
            'status' => Survey::STATUS_PENDING_DELETE,
        ));
        foreach ($surveys as $survey) {
            try {
                $survey->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteGuestFailedAttempts()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{guest_fail_attempt}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 HOUR)')->execute();    
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function syncListsCustomFields()
    {
        if (Yii::app()->options->get('system.cron.process_subscribers.sync_custom_fields_values', 'no') != 'yes') {
            return $this;
        }
        
        $argv = array(
            $_SERVER['argv'][0],
            'sync-lists-custom-fields',
        );
        
        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }

        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    protected function syncSurveysCustomFields()
    {
        if (Yii::app()->options->get('system.cron.process_responders.sync_custom_fields_values', 'no') != 'yes') {
            return $this;
        }

        $argv = array(
            $_SERVER['argv'][0],
            'sync-surveys-custom-fields',
        );

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }

        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteCampaignsQueueTables()
    {
        if (empty(Yii::app()->params['send.campaigns.command.useTempQueueTables'])) {
            return $this;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('status', Campaign::STATUS_SENT);
        $criteria->addCondition('date_added > DATE_SUB(NOW(), INTERVAL 7 DAY)');
        
        $campaigns = Campaign::model()->findAll($criteria);
        foreach ($campaigns as $campaign) {
            try {
                $campaign->queueTable->dropTable();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
            
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteCustomers()
    {
        $customers = Customer::model()->findAllByAttributes(array(
            'status' => Customer::STATUS_PENDING_DELETE,
        ));
        foreach ($customers as $customer) {
            try {
                $customer->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteDisabledCustomers()
    {
        $days = (int)Yii::app()->options->get('system.customer_common.days_to_keep_disabled_account', 30);
        if ($days < 0) {
            return $this;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('status', Customer::STATUS_DISABLED);
        $criteria->addCondition(sprintf('DATE_SUB(NOW(), INTERVAL %d DAY) > last_login', $days));
        
        $customers = Customer::model()->findAll($criteria);

        foreach ($customers as $customer) {
            try {
                $customer->status = Customer::STATUS_PENDING_DELETE;
                $customer->delete();
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteDisabledCustomersData()
    {
        $customers = Customer::model()->findAllByAttributes(array(
            'status' => Customer::STATUS_PENDING_DISABLE,
        ));
        
        foreach ($customers as $customer) {
            
            try {

                $attributes = $customer->attributes;
                
                $customer->status = Customer::STATUS_PENDING_DELETE;
                $customer->delete();
                
                $newCustomer = new Customer();
                foreach ($attributes as $key => $value) {
                    $newCustomer->$key = $value;
                }
                $newCustomer->status = Customer::STATUS_DISABLED;
                $newCustomer->save(false);
                
            } catch (Exception $e) {

                $this->stdout(__LINE__ . ': ' . $e->getMessage());
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function deleteMutexes() 
    {
        $argv = array(
            $_SERVER['argv'][0],
            'delete-mutexes',
        );

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }

        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteCampaignDeliveryLogs()
    {
        $deleteCampaignDeliveryLogs = Yii::app()->options->get('system.cron.delete_logs.delete_campaign_delivery_logs', 'no') === 'yes';
        if (!$deleteCampaignDeliveryLogs) {
            return $this;
        }
        
        $argv = array(
            $_SERVER['argv'][0],
            'delete-campaign-delivery-logs',
        );

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }

        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

	/**
	 * @return $this
	 */
	public function deleteCampaignBounceLogs()
	{
		$deleteCampaignBounceLogs = Yii::app()->options->get('system.cron.delete_logs.delete_campaign_bounce_logs', 'no') === 'yes';
		if (!$deleteCampaignBounceLogs) {
			return $this;
		}

		$argv = array(
			$_SERVER['argv'][0],
			'delete-campaign-bounce-logs',
		);

		foreach ($_SERVER['argv'] as $arg) {
			if ($arg == '--verbose=1') {
				$argv[] = $arg;
				break;
			}
		}

		try {
			$runner = clone Yii::app()->getCommandRunner();
			$runner->run($argv);
		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function deleteCampaignOpenLogs()
	{
		$deleteCampaignOpenLogs = Yii::app()->options->get('system.cron.delete_logs.delete_campaign_open_logs', 'no') === 'yes';
		if (!$deleteCampaignOpenLogs) {
			return $this;
		}

		$argv = array(
			$_SERVER['argv'][0],
			'delete-campaign-open-logs',
		);

		foreach ($_SERVER['argv'] as $arg) {
			if ($arg == '--verbose=1') {
				$argv[] = $arg;
				break;
			}
		}

		try {
			$runner = clone Yii::app()->getCommandRunner();
			$runner->run($argv);
		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function deleteCampaignClickLogs()
	{
		$deleteCampaignClickLogs = Yii::app()->options->get('system.cron.delete_logs.delete_campaign_click_logs', 'no') === 'yes';
		if (!$deleteCampaignClickLogs) {
			return $this;
		}

		$argv = array(
			$_SERVER['argv'][0],
			'delete-campaign-click-logs',
		);

		foreach ($_SERVER['argv'] as $arg) {
			if ($arg == '--verbose=1') {
				$argv[] = $arg;
				break;
			}
		}

		try {
			$runner = clone Yii::app()->getCommandRunner();
			$runner->run($argv);
		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		return $this;
	}

    /**
     * @return $this
     */
    public function deleteTransactionalEmails()
    {
        $daysBack = (int)Yii::app()->options->get('system.cron.transactional_emails.delete_days_back', -1);
        if ($daysBack < 0) {
            return $this;
        }
        
        $argv = array(
            $_SERVER['argv'][0],
            'delete-transactional-emails',
            sprintf("--time=-%d days", $daysBack)
        );
        
        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }
        
        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function deleteUnusedCampaignShareCodes()
    {
        try {
            $connection = Yii::app()->getDb();
            $connection->createCommand('DELETE FROM `{{campaign_share_code}}` WHERE date_added < DATE_SUB(NOW(), INTERVAL 1 WEEK)')->execute();
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function sendCampaignStatsEmail()
    {
        try {

            while (true) {
                $criteria = new CDbCriteria();
                $criteria->compare('t.status', Campaign::STATUS_SENT);
                $criteria->addCondition('DATE(t.finished_at) < DATE_SUB(NOW(), INTERVAL 24 HOUR)');
                $criteria->with['option'] = array(
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => 'LENGTH(`option`.`email_stats`) > 0 AND `option`.`email_stats_sent` = 0',
                );
                $criteria->limit = 100;

                /** @var Campaign[] $campaigns */
                $campaigns = Campaign::model()->findAll($criteria);
                if (empty($campaigns)) {
                    break;
                }
                
                foreach ($campaigns as $campaign) {
                    $campaign->option->updateCounters(array('email_stats_sent' => 1), 'campaign_id = :cid', array(
                        ':cid' => $campaign->campaign_id
                    ));
                    $campaign->sendStatsEmail();
                }
            }

        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function writePhpInfo()
    {
        if (!CommonHelper::functionExists('phpinfo')) {
            return $this;
        }
        
        ob_start();
        ob_implicit_flush(false);
        phpinfo();
        $phpInfo = ob_get_clean();
        
        @file_put_contents(Yii::getPathOfAlias('common.runtime') . '/php-info-cli.txt', $phpInfo);
        
        return $this;
    }
    
    /**
     * @return $this
     */
    protected function verifyLicense()
    {
        try {   

            $request = LicenseHelper::verifyLicense();

            if ($request["status"] == "error" || empty($request["message"])) {
                return $this;
            }

            $response = CJSON::decode($request["message"], true);
            if (empty($response) || empty($response['status'])) {
                return $this;
            }
            
            if ($response["status"] == "success") {
                return $this;
            }

            Yii::app()->options->set("system.common.site_status", "offline");
            Yii::app()->options->set("system.common.api_status", "offline");
            Yii::app()->options->set("system.license.error_message", $response["message"]);

        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runAutoUpdater()
    {
        $enabled = Yii::app()->options->get('system.common.auto_update', 'no') == 'yes';
        if (!$enabled) {
            return $this;
        }

        $argv = array(
            $_SERVER['argv'][0],
            'auto-update',
        );

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }

        try {
            $runner = clone Yii::app()->getCommandRunner();
            $runner->run($argv);
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' . $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function handleScheduledInactiveCustomers()
    {
        try {

	        /** @var OptionsManager $options */
	        $options = Yii::app()->options;
	        
	        $criteria = new CDbCriteria();
            $criteria->compare('status', Customer::STATUS_ACTIVE);
            $criteria->addCondition('inactive_at IS NOT NULL AND inactive_at < NOW()');
            
            /** @var Customer[] $customers */
            $customers = Customer::model()->findAll($criteria);
            
            if (empty($customers)) {
            	return $this;
            }
            
            $customersBaseUrl = $options->get('system.urls.backend_absolute_url') . '/customers/update/id/';

	        $customersList = array();
            foreach ($customers as $customer) {
                $customer->saveStatus(Customer::STATUS_INACTIVE);
                $customersList[] = CHtml::link($customer->getFullName(), $customersBaseUrl . $customer->customer_id);
            }
            $customersList = implode('<br/>', $customersList);

            $users = User::model()->findAllByAttributes(array(
                'status'    => User::STATUS_ACTIVE,
                'removable' => User::TEXT_NO,
            ));

            $params  = CommonEmailTemplate::getAsParamsArrayBySlug('scheduled-inactive-customers',
                array(
                    'subject' => Yii::t('customers', 'Scheduled inactive customers'),
                ), array(
                    '[CUSTOMERS_LIST]'  => $customersList,
                )
            );

            foreach ($users as $user) {
                $email = new TransactionalEmail();
                $email->to_name   = $user->getFullName();
                $email->to_email  = $user->email;
                $email->from_name = $options->get('system.common.site_name', 'Marketing website');
                $email->subject   = $params['subject'];
                $email->body      = $params['body'];
                $email->save();

                // add a notification message too
                $message = new UserMessage();
                $message->title   = 'Scheduled inactive customers';
                $message->message = $customersList;
                $message->user_id = $user->user_id;
                $message->save();
            }

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

}
