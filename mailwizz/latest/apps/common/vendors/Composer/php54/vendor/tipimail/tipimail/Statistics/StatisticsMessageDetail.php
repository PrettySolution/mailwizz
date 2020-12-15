<?php
namespace Tipimail\Statistics;

class StatisticsMessageDetail {

	private $id;
	private $apiKey;
	private $createdDate;
	private $lastStateDate;
	private $msg;
	private $lastState;
	private $open;
	private $click;
	private $openDetails;
	private $clickDetails;
	private $meta;
	private $tags;

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
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->openDetails)) {
			$this->openDetails = $data->openDetails;
		}
		if (isset($data->clickDetails)) {
			$this->clickDetails = $data->clickDetails;
		}
		if (isset($data->tags)) {
			$this->tags = $data->tags;
		}
		if (isset($data->meta)) {
			$this->meta = $data->meta;
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

	public function getOpen() {
		return $this->open;
	}

	public function getClick() {
		return $this->click;
	}

	public function getOpenDetails() {
		return $this->openDetails;
	}

	public function getClickDetails() {
		return $this->clickDetails;
	}

	public function getTags() {
		return $this->tags;
	}

	public function getMeta() {
		return $this->meta;
	}
}
