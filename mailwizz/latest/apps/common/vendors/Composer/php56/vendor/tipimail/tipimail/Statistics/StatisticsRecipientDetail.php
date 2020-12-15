<?php
namespace Tipimail\Statistics;

class StatisticsRecipientDetail {
	
	private $requested;
	private $delivered;
	private $hardbounced;
	private $softbounced;
	private $open;
	private $click;
	private $opener;
	
	public function __construct($data = null) {
		if (isset($data->requested)) {
			$this->requested = $data->requested;
		}
		if (isset($data->delivered)) {
			$this->delivered = $data->delivered;
		}
		if (isset($data->hardbounced)) {
			$this->hardbounced = $data->hardbounced;
		}
		if (isset($data->softbounced)) {
			$this->softbounced = $data->softbounced;
		}
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->opener)) {
			$this->opener = $data->opener;
		}
	}
	
	public function getRequested() {
		return $this->requested;
	}
	
	public  function getDelivered() {
		return $this->delivered;
	}
	
	public function getHardbounced() {
		return $this->hardbounced;
	}
	
	public function getSoftbounced() {
		return $this->softbounced;
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function getOpener() {
		return $this->opener;
	}
	
}