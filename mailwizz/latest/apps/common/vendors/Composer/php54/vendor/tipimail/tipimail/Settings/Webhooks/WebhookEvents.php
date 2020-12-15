<?php
namespace Tipimail\Settings\Webhooks;

class WebhookEvents {
	
	private $delivered;
	private $rejected;
	private $error;
	private $hardbounced;
	private $softbounced;
	private $opened;
	private $clicked;
	private $unsubscribed;
	private $complaint;
	
	public function enableAll() {
		$this->delivered = true;
		$this->rejected = true;
		$this->error = true;
		$this->hardbounced = true;
		$this->softbounced = true;
		$this->opened = true;
		$this->clicked = true;
		$this->unsubscribed = true;
		$this->complaint = true;
	}
	
	public function enableDelivered() {
		$this->delivered = true;
	}
	
	public function enableRejected() {
		$this->rejected = true;
	}
	
	public function enableError() {
		$this->error = true;
	}
	
	public function enableHardbounced() {
		$this->hardbounced = true;
	}
	
	public function enableSoftbounced() {
		$this->softbounced = true;
	}
	
	public function enableOpened() {
		$this->opened = true;
	}
	
	public function enableClicked() {
		$this->clicked = true;
	}
	
	public function enableUnsubscribed() {
		$this->unsubscribed = true;
	}
	
	public function enableComplaint() {
		$this->complaint = true;
	}
	
	public function getEnabledWebhookEvents() {
		$events = array();
		if ($this->delivered) {
			$events[] = 'delivered';
		}
		if ($this->rejected) {
			$events[] = 'rejected';
		}
		if ($this->error) {
			$events[] = 'error';
		}
		if ($this->hardbounced) {
			$events[] = 'hardbounced';
		}
		if ($this->softbounced) {
			$events[] = 'softbounced';
		}
		if ($this->opened) {
			$events[] = 'opened';
		}
		if ($this->clicked) {
			$events[] = 'clicked';
		}
		if ($this->unsubscribed) {
			$events[] = 'unsubscribed';
		}
		if ($this->complaint) {
			$events[] = 'complaint';
		}
		return $events;
	}
	
}