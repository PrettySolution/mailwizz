<?php
namespace Tipimail\Settings\Domains;

class DomainSettings {
	
	private $dkim;
	private $spf;
	private $tracking;
	
	public function __construct($data = null) {
		if (isset($data->dkim)) {
			$this->dkim = new DomainSettingsDkim($data->dkim);
		}
		else {
			$this->dkim = new DomainSettingsDkim();
		}
		if (isset($data->spf)) {
			$this->spf = new DomainSettingsSpf($data->spf);
		}
		else {
			$this->spf = new DomainSettingsSpf();
		}
		if (isset($data->tracking)) {
			$this->tracking = new DomainSettingsTracking($data->tracking);
		}
		else {
			$this->tracking = new DomainSettingsTracking();
		}
	}
	
	public function getDkim() {
		return $this->dkim;
	}
	
	public function getSpf() {
		return $this->spf;
	}
	
	public function getTracking() {
		return $this->tracking;
	}
}