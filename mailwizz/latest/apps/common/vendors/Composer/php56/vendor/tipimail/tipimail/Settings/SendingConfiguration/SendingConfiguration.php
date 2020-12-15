<?php
namespace Tipimail\Settings\SendingConfiguration;

class SendingConfiguration {
	
	private $trackOpens;
	private $trackClicks;
	private $googleAnalytics;
	private $trackMailTo;
	private $unsubscribe;
	
	public function __construct($data = null) {
		if (isset($data->trackOpens)) {
			$this->trackOpens = $data->trackOpens;
		}
		if (isset($data->trackClicks)) {
			$this->trackClicks = $data->trackClicks;
		}
		if (isset($data->googleAnalytics)) {
			$this->googleAnalytics = new SendingConfigurationGoogleAnalytics($data->googleAnalytics);
		}
		else {
			$this->googleAnalytics = new SendingConfigurationGoogleAnalytics();
		}
		if (isset($data->trackMailTo)) {
			$this->trackMailTo = $data->trackMailTo;
		}
		if (isset($data->unsubscribe)) {
			$this->unsubscribe =  new SendingConfigurationUnsubscribe($data->unsubscribe);
		}
		else {
			$this->unsubscribe =  new SendingConfigurationUnsubscribe();
		}
	}
	
	public function getTrackOpens() {
		return $this->trackOpens;
	}
	
	public function getTrackClicks() {
		return $this->trackClicks;
	}
	
	public function getGoogleAnalytics() {
		return $this->googleAnalytics;
	}
	
	public function getTrackMailTo() {
		return $this->trackMailTo;
	}
	
	public function getUnsubscribe() {
		return $this->unsubscribe;
	}
	
}