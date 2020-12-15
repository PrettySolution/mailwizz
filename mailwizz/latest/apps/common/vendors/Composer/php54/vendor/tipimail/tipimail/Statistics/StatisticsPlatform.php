<?php
namespace Tipimail\Statistics;

class StatisticsPlatform {
	
	private $open;
	private $operatingSystem;
	private $deviceType;
	
	public function __construct($data = null) {
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->operatingSystem)) {
			$this->operatingSystem = $data->operatingSystem;
		}
		if (isset($data->deviceType)) {
			$this->deviceType = $data->deviceType;
		}
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getOperatingSystem() {
		return $this->operatingSystem;
	}
	
	public function getDeviceType() {
		return $this->deviceType;
	}
	
}