<?php
namespace Tipimail\Statistics;

class StatisticsBounces {
	
	private $requested;
	private $hardbounced;
	private $softbounced;
	
	public function __construct($data = null) {
		if (isset($data->requested)) {
			$this->requested = $data->requested;
		}
		if (isset($data->hardbounced)) {
			$this->hardbounced = $data->hardbounced;
		}
		if (isset($data->softbounced)) {
			$this->softbounced = $data->softbounced;
		}
	}
	
	public function getRequested() {
		return $this->requested;
	}
	
	public function getHardbounced() {
		return $this->hardbounced;
	}
	
	public function getSoftbounced() {
		return $this->softbounced;
	}
	
}