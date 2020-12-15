<?php
namespace Tipimail\Settings\Domains;

class DomainSettingsTracking {
	
	private $value;
	private $record;
	
	public function __construct($data = null) {
		if (isset($data->value)) {
			$this->value = $data->value;
		}
		if (isset($data->record)) {
			$this->record = $data->record;
		}
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getRecord() {
		return $this->record;
	}
	
}