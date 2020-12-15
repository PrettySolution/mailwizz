<?php
namespace Tipimail\Settings\SendingConfiguration;

class SendingConfigurationGoogleAnalytics {
	
	private $enable;
	private $utmSource;
	private $utmMedia;
	private $utmContent;
	private $utmCampaign;
	
	public function __construct($data = null) {
		if (isset($data->enable)) {
			$this->enable = $data->enable;
		}
		if (isset($data->utmSource)) {
			$this->utmSource = $data->utmSource;
		}
		if (isset($data->utmMedia)) {
			$this->utmMedia = $data->utmMedia;
		}
		if (isset($data->utmContent)) {
			$this->utmContent = $data->utmContent;
		}
		if (isset($data->utmCampaign)) {
			$this->utmCampaign = $data->utmCampaign;
		}
	}
	
	public function getEnable() {
		return $this->enable;
	}
	
	public function getUtmSource() {
		return $this->utmSource;
	}
	
	public function getUtmMedia() {
		return $this->utmMedia;
	}
	
	public function getUtmContent() {
		return $this->utmContent;
	}
	
	public function getUtmCampaign() {
		return $this->utmCampaign;
	}
	
}