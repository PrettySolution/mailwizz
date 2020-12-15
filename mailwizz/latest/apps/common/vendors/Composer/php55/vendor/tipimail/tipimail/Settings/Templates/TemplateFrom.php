<?php
namespace Tipimail\Settings\Templates;

class TemplateFrom {
	
	private $address;
	private $personalName;
	
	public function __construct($data = null) {
		if (isset($data->address)) {
			$this->address = $data->address;
		}
		if (isset($data->personalName)) {
			$this->personalName = $data->personalName;
		}
	}
	
	public function getAddress() {
		return $this->address;
	}
	
	public function getPersonalName() {
		return $this->personalName;
	}
	
}