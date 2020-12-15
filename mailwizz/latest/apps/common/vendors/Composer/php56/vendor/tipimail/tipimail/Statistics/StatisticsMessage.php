<?php
namespace Tipimail\Statistics;

class StatisticsMessage {
	
	private $id;
	private $apiKey;
	private $createdDate;
	private $lastStateDate;
	private $msg;
	private $lastState;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->apiKey)) {
			$this->apiKey = $data->apiKey;
		}
		if (isset($data->createdDate)) {
			$this->createdDate = $data->createdDate;
		}
		if (isset($data->lastStateDate)) {
			$this->lastStateDate = $data->lastStateDate;
		}
		if (isset($data->msg)) {
			$this->msg = new StatisticsMessageInfo($data->msg);
		}
		else {
			$this->msg = new StatisticsMessageInfo();
		}
		if (isset($data->lastState)) {
			$this->lastState = $data->lastState;
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getApiKey() {
		return $this->apiKey;
	}
	
	public function getCreatedDate() {
		return $this->createdDate;
	}
	
	public function getLastStateDate() {
		return $this->lastStateDate;
	}
	
	public function getMsg() {
		return $this->msg;
	}
	
	public function getLastState() {
		return $this->lastState;
	}
	
}
