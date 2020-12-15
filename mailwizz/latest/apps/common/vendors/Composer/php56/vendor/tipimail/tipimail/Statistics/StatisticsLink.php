<?php
namespace Tipimail\Statistics;

class StatisticsLink {
	
	private $click;
	private $clicker;
	
	public function __construct($data = null) {
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->clicker)) {
			$this->clicker = $data->clicker;
		}
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function getClicker() {
		return $this->clicker;
	}
	
}