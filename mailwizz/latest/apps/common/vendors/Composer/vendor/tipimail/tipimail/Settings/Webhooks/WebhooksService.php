<?php
namespace Tipimail\Settings\Webhooks;

class WebhooksService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/webhooks';
	}
	
	/**
	 * Get all webhooks informations
	 * @return \Tipimail\Settings\Webhook[]
	 * 	id
	 * 	url
	 * 	description
	 * 	createdAt
	 * 	updatedAt
	 * 	lastCall
	 * 	success
	 * 	errors
	 * 	events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll() {
		$result = $this->tipimail->getData($this->url);
		$webhooks = array();
		foreach ($result as $value) {
			$webhooks[] = new Webhook($value);
		}
		return $webhooks;
	}
	
	/**
	 * Get webhook informations
	 * @param string $id
	 * @return \Tipimail\Settings\Webhook
	 * 	id
	 * 	url
	 * 	description
	 * 	createdAt
	 * 	updatedAt
	 * 	lastCall
	 * 	success
	 * 	errors
	 * 	events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($id) {
		$result = $this->tipimail->getData($this->url . '/' . $id);
		return new Webhook($result);
	}
	
	/**
	 * Get webhook logs
	 * @param string $id
	 * @param int $pageSize
	 * @param int $page
	 * @return \Tipimail\Settings\WebhookLog[]
	 * 	recipient
	 * 	subject
	 * 	eventDate
	 * 	descriptionError
	 * 	eventType
	 * 	typeOfStatus
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getLogs($id, $pageSize, $page) {
		$data = array(
			'pageSize' => $pageSize,
			'page' => $page
		);
		$result = $this->tipimail->postData($this->url . '/' . $id . '/logs', $data);
		$webhooksLogs = array();
		foreach ($result as $value) {
			$webhooksLogs[] = new WebhookLog($value);
		}
		return $webhooksLogs;
	}
	
	/**
	 * Test webhook
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function test($id) {
		$this->tipimail->getData($this->url . '/' . $id . '/test');
	}
	
	/**
	 * Add webhook
	 * @param string $url
	 * @param string $description
	 * @param WebhookEvents $events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function add($url, $description, WebhookEvents $events) {
		$data = array(
			'url' => $url,
			'description' => $description,
			'events' => $events->getEnabledWebhookEvents()
		);
		$result = $this->tipimail->postData($this->url, $data);
		return new Webhook($result);
	}
	
	/**
	 * Add webhook with all events
	 * @param string $url
	 * @param string $description
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function addWithAllEvents($url, $description) {
		$events = new WebhookEvents();
		$events->enableAll();
		return $this->add($url, $description, $events);
	}
	
	/**
	 * Add webhook with selected events
	 * @param string $url
	 * @param string $description
	 * @param WebhookEvents $events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function addWithSelectedEvents($url, $description, WebhookEvents $events) {
		return $this->add($url, $description, $events);
	}
	
	/**
	 * Update webhook url
	 * @param string $id
	 * @param string $url
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateUrl($id, $url) {
		$data = array('url' => $url);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update webhook description
	 * @param string $id
	 * @param string $description
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateDescription($id, $description) {
		$data = array('description' => $description);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update webhook events
	 * @param string $id
	 * @param WebhookEvents $events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function updateEvents($id, WebhookEvents $events) {
		$data = array('events' => $events->getEnabledWebhookEvents());
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update webhook with all events
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateEventsWithAll($id) {
		$events = new WebhookEvents();
		$events->enableAll();
		$this->updateEvents($id, $events);
	}
	
	/**
	 * Update webhook with selected events
	 * @param string $id
	 * @param WebhookEvents $events
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateEventsWithSelected($id, WebhookEvents $events) {
		$this->updateEvents($id, $events);
	}
	
	/**
	 * Delete webhook
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function delete($id) {
		$this->tipimail->deleteData($this->url . '/' . $id);
	}
	
}
