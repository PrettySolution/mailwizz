<?php
namespace Tipimail\Statistics;

class StatisticsActivity {
	
	private $delivered;
	private $open;
	private $click;
	private $unsubscribed;
	private $complaint;
	private $opener;
	private $clicker;
	
	public function __construct($data = null) {
		if (isset($data->delivered)) {
			$this->delivered = $data->delivered;
		}
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->unsubscribed)) {
			$this->unsubscribed = $data->unsubscribed;
		}
		if (isset($data->complaint)) {
			$this->complaint = $data->complaint;
		}
		if (isset($data->opener)) {
			$this->opener = $data->opener;
		}
		if (isset($data->clicker)) {
			$this->clicker = $data->clicker;
		}
	}
	
	public function getDelivered() {
		return $this->delivered;
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function getUnsubscribed() {
		return $this->unsubscribed;
	}
	
	public function getComplaint() {
		return $this->complaint;
	}
	
	public function getOpener() {
		return $this->opener;
	}
	
	public function getClicker() {
		return $this->clicker;
	}
	
}