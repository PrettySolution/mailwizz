<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendCampaignsWebhooksCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.8
 */
 
class SendCampaignsWebhooksCommand extends ConsoleCommand 
{
	/**
	 * @var int 
	 */
	protected $maxRetries = 5;
	
    /**
     * @return int
     */
    public function actionIndex() 
    {
    	$this->actionOpens();
    	
    	$this->actionClicks();
    	
        return 0;
    }

	/**
	 * @return int
	 */
    public function actionOpens()
    {
	    // set the lock name
	    $lockName = sha1(__METHOD__);

	    if (!Yii::app()->mutex->acquire($lockName, 5)) {
		    return 0;
	    }

	    $result = 0;

	    try {

		    Yii::app()->hooks->doAction('console_command_send_campaigns_webhooks_opens_before_process', $this);

		    $result = $this->processOpens();

		    Yii::app()->hooks->doAction('console_command_send_campaigns_webhooks_opens_after_process', $this);

	    } catch (Exception $e) {

		    $this->stdout(__LINE__ . ': ' . $e->getMessage());
		    Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
	    }

	    Yii::app()->mutex->release($lockName);

	    return $result;
    }

	/**
	 * @return int
	 */
	public function actionClicks()
	{
		// set the lock name
		$lockName = sha1(__METHOD__);

		if (!Yii::app()->mutex->acquire($lockName, 5)) {
			return 0;
		}

		$result = 0;

		try {

			Yii::app()->hooks->doAction('console_command_send_campaigns_webhooks_clicks_before_process', $this);

			$result = $this->processClicks();

			Yii::app()->hooks->doAction('console_command_send_campaigns_webhooks_clicks_after_process', $this);

		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' . $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		Yii::app()->mutex->release($lockName);

		return $result;
	}

	/**
	 * @return int
	 */
	protected function processOpens()
	{
		while (true) {
			
			$models = CampaignTrackOpenWebhookQueue::model()->findAll($this->getSearchCriteria());
			if (empty($models)) {
				break;
			}
			
			foreach ($models as $model) {
				
				try {
					
					$url        = $model->webhook->webhook_url;
					$list       = $model->trackOpen->subscriber->list;
					$subscriber = $model->trackOpen->subscriber;
					$campaign   = $model->webhook->campaign;
					$customData = array(
						'event' => array(
							'type'       => 'open',
							'ip_address' => $model->trackOpen->ip_address,
							'user_agent' => $model->trackOpen->user_agent,
							'date_added' => $model->trackOpen->date_added,
						)
					);
					
					if (!$this->sendData($url, $list, $subscriber, $campaign, $customData)) {
						$model->retry_count++;
						$model->next_retry = date('Y-m-d H:i:s', strtotime(sprintf('+%d hour', $model->retry_count)));
						$model->save(false);
						$this->stdout('Queue item id: ' . $model->id . ' will be retried at ' . $model->next_retry);
						continue;
					}
					
					$model->delete();
					$this->stdout('Queue item id: ' . $model->id . ' has been processed and deleted!');
					
				} catch (Exception $e) {

					$this->stdout('Opens exception:' . $e->getMessage());
				}
			}
		}
		
		CampaignTrackOpenWebhookQueue::model()->deleteAll($this->getDeleteCriteria());
		
		return 0;
	}

	/**
	 * @return int
	 */
	protected function processClicks()
	{
		while (true) {

			$models = CampaignTrackUrlWebhookQueue::model()->findAll($this->getSearchCriteria());
			if (empty($models)) {
				break;
			}

			foreach ($models as $model) {

				try {

					$url        = $model->webhook->webhook_url;
					$list       = $model->trackUrl->subscriber->list;
					$subscriber = $model->trackUrl->subscriber;
					$campaign   = $model->webhook->campaign;
					$customData = array(
						'event' => array(
							'type'        => 'click',
							'ip_address'  => $model->trackUrl->ip_address,
							'user_agent'  => $model->trackUrl->user_agent,
							'date_added'  => $model->trackUrl->date_added,
							'clicked_url' => $model->trackUrl->url->destination,
						)
					);
					
					if (!$this->sendData($url, $list, $subscriber, $campaign, $customData)) {
						$model->retry_count++;
						$model->next_retry = date('Y-m-d H:i:s', strtotime(sprintf('+%d hour', $model->retry_count)));
						$model->save(false);
						$this->stdout('Queue item id: ' . $model->id . ' will be retried at ' . $model->next_retry);
						continue;
					}

					$model->delete();
					$this->stdout('Queue item id: ' . $model->id . ' has been processed and deleted!');

				} catch (Exception $e) {
					$this->stdout('Clicks exception: ' . $e->getMessage());
				}
			}
		}

		CampaignTrackUrlWebhookQueue::model()->deleteAll($this->getDeleteCriteria());

		return 0;
	}

	/**
	 * @param $webhookUrl
	 * @param Lists $list
	 * @param ListSubscriber $subscriber
	 * @param Campaign $campaign
	 * @param array $customData
	 *
	 * @return bool
	 */
	protected function sendData($webhookUrl, Lists $list, ListSubscriber $subscriber, Campaign $campaign, array $customData = array())
	{
		$success = false; 
		
		try {
			
			$fieldsWithValues = array();
			$fieldTagValue    = $subscriber->getAllCustomFieldsWithValues();
			foreach ($fieldTagValue as $tag => $value) {
				$tagName = str_replace(array('[', ']'), '', $tag);
				$fieldsWithValues[$tagName] = $value;
			}

			$data = CMap::mergeArray(array(
				'event' => array(
					'type'          => 'n/a',
					'ip_address'    => '',
					'user_agent'    => '',
					'date_added'    => '',
				),
				'timestamp' => time(),
				'list'  => array(
					'attributes' => $list->getAttributes(array(
						'list_uid', 'name',
					)),
				),
				'subscriber' => array(
					'attributes' => $subscriber->getAttributes(array(
						'subscriber_uid', 'email', 'source', 'status', 'ip_address'
					)),
					'fields' => $fieldsWithValues,
				),
				'campaign' => array(
					'attributes' => $campaign->getAttributes(array(
						'campaign_uid', 'name',
					)),
				),
			), $customData);
			

			$data = json_encode($data);

			$this->stdout(sprintf('URL %s / DATA: %s', $webhookUrl, $data));
			
			$result = AppInitHelper::makeRemoteRequest($webhookUrl, array(
				CURLOPT_CONNECTTIMEOUT => 5,
				CURLOPT_TIMEOUT        => 5,
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $data,
				CURLINFO_HEADER_OUT    => true,
				CURLOPT_HTTPHEADER     => array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data)
				)
			));
			
			$success = $result['status'] === 'success' && $result['http_code'] >= 200 && $result['http_code'] < 300;
			
			$this->stdout('Sending result: ' . json_encode($result));
		
		} catch (Exception $e) {
			$this->stdout('Sending exception: ' . $e->getMessage());
		}
		
		return $success;
	}

	/**
	 * @return CDbCriteria
	 */
	protected function getSearchCriteria()
	{
		$criteria = new CDbCriteria();
		$criteria->addCondition('retry_count <= :max AND next_retry < NOW()');
		$criteria->order = 'retry_count ASC';
		$criteria->limit = 500;
		$criteria->params[':max'] = $this->maxRetries;
		
		return $criteria;
	}

	/**
	 * @return CDbCriteria
	 */
	protected function getDeleteCriteria()
	{
		$criteria = new CDbCriteria();
		$criteria->addCondition('retry_count > :max');
		$criteria->params[':max'] = $this->maxRetries;
		
		return $criteria;
	}
    
}
