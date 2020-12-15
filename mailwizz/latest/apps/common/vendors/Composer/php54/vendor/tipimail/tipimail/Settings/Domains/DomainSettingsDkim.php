<?php
namespace Tipimail\Settings\Domains;

class DomainSettingsDkim {
	
	private $value;
	private $record;
	private $prefix;
	
	public function __construct($data = null) {
		if (isset($data->value)) {
			$this->value = $data->value;
		}
		if (isset($data->record)) {
			$this->record = $data->record;
		}
		if (isset($data->prefix)) {
			$this->prefix = $data->prefix;
		}
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function getRecord() {
		return $this->record;
	}
	
	public function getPrefix() {
		return $this->prefix;
	}
	
}