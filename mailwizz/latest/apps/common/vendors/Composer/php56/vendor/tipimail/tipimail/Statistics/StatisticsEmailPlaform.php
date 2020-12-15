<?php
namespace Tipimail\Statistics;

class StatisticsEmailPlaform {
	
	private $open;
	private $name;
	private $type;
	
	public function __construct($data = null) {
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->name)) {
			$this->name = $data->name;
		}
		if (isset($data->type)) {
			$this->type = $data->type;
		}
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getType() {
		return $this->type;
	}
	
}