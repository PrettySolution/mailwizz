<?php
namespace Tipimail\Settings\Webhooks;

class Webhook {
	
	private $id;
	private $url;
	private $description;
	private $createdAt;
	private $updatedAt;
	private $lastCall;
	private $success;
	private $errors;
	private $events;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->url)) {
			$this->url = $data->url;
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
		if (isset($data->lastCall)) {
			$this->lastCall = $data->lastCall;
		}
		if (isset($data->success)) {
			$this->success = $data->success;
		}
		if (isset($data->errors)) {
			$this->errors = $data->errors;
		}
		if (isset($data->events)) {
			$this->events = $data->events;
		}
		else {
			$this->events = array();
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUrl() {
		return $this->url;
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
	
	public function getLastCall() {
		return $this->lastCall;
	}
	
	public function getSuccess() {
		return $this->success;
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function getEvents() {
		return $this->events;
	}
	
}