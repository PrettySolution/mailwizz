<?php
namespace Tipimail\Settings\ApiKeys;

class ApiKey {
	
	private $id;
	private $hash;
	private $key;
	private $iPFilterEnabled;
	private $allowedIPs;
	private $description;
	private $createdAt;
	private $updatedAt;
	private $enabled;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->hash)) {
			$this->hash = $data->hash;
		}
		if (isset($data->key)) {
			$this->key = $data->key;
		}
		if (isset($data->iPFilterEnabled)) {
			$this->iPFilterEnabled = $data->iPFilterEnabled;
		}
		if (isset($data->allowedIPs)) {
			$this->allowedIPs = $data->allowedIPs;
		}
		else {
			$this->allowedIPs = array();
		}
		if (isset($data->description)) {
			$this->description = $data->description;
		}
		if (isset($data->createdAt)) {
			$this->createdAt = $data->createdAt;
		}
		if (isset($data->updatedAt)) {
			$this->updatedAt = $data->updatedAt;
		}
		if (isset($data->enabled)) {
			$this->enabled = $data->enabled;
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getHash() {
		return $this->hash;
	}
	
	public function getKey() {
		return $this->key;
	}
	
	public function getAllowedIPs() {
		return $this->allowedIPs;
	}
	
	public function getIPFilterEnabled() {
		return $this->iPFilterEnabled;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
	public function getEnabled() {
		return $this->enabled;
	}
	
}