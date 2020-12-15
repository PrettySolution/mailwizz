<?php
namespace Tipimail\Statistics;

class StatisticsEmailPlaformOpen {
	
	private $open;
	
	public function __construct($data = null) {
		if (isset($data->open)) {
			$this->open = $data->open;
		}
	}
	
	public function getOpen() {
		return $this->open;
	}
	
}