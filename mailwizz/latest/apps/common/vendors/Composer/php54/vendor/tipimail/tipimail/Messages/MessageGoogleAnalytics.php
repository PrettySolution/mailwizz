<?php
namespace Tipimail\Messages;

class MessageGoogleAnalytics {
	
	private $enable;
	private $utmSource;
	private $utmMedium;
	private $utmContent;
	private $utmCampaign;
	
	public function getEnable() {
		return $this->enable;
	}
	
	public function getUtmSource() {
		return $this->utmSource;
	}
	
	public function getUtmMedium() {
		return $this->utmMedium;
	}
	
	public function getUtmContent() {
		return $this->utmContent;
	}
	
	public function getUtmCampaign() {
		return $this->utmCampaign;
	}
	
	public function setEnable($enable) {
		$this->enable = $enable;
	}
	
	public function setUtmSource($utmSource) {
		$this->utmSource = $utmSource;
	}
	
	public function setUtmMedium($utmMedium) {
		$this->utmMedium = $utmMedium;
	}
	
	public function setUtmContent($utmContent) {
		$this->utmContent = $utmContent;
	}
	
	public function setUtmCampaign($utmCampaign) {
		$this->utmCampaign = $utmCampaign;
	}
	
}