<?php
namespace Tipimail\Settings\DedicatedIps;

class DedicatedIp {
	
	private $id;
	private $type;
	private $ip;
	private $expirationDate;
	private $subscriptionDate;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->type)) {
			$this->type = $data->type;
		}
		if (isset($data->ip)) {
			$this->ip = $data->ip;
		}
		if (isset($data->expirationDate)) {
			$this->expirationDate = $data->expirationDate;
		}
		if (isset($data->subscriptionDate)) {
			$this->subscriptionDate = $data->subscriptionDate;
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getIp() {
		return $this->ip;
	}
	
	public function getExpirationDate() {
		return $this->expirationDate;
	}
	
	public function getSubscriptionDate() {
		return $this->subscriptionDate;
	}
	
}