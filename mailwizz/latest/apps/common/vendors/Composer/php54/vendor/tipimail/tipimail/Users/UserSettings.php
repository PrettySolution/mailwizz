<?php
namespace Tipimail\Users;

class UserSettings {
	
	private $newsletter;
	private $language;
	private $dateFormat;
	private $timeFormat;
	private $timezone;
	
	public function __construct($data = null) {
		if (isset($data->newsletter)) {
			$this->newsletter = $data->newsletter;
		}
		if (isset($data->language)) {
			$this->language = $data->language;
		}
		if (isset($data->dateFormat)) {
			$this->dateFormat = $data->dateFormat;
		}
		if (isset($data->timeFormat)) {
			$this->timeFormat = $data->timeFormat;
		}
		if (isset($data->timezone)) {
			$this->timezone = $data->timezone;
		}
	}
	
	public function getNewsletter() {
		return $this->newsletter;
	}
	
	public function getLanguage() {
		return $this->language;
	}
	
	public function getDateFormat() {
		return $this->dateFormat;
	}
	
	public function getTimeFormat() {
		return $this->timeFormat;
	}
	
	public function getTimezone() {
		return $this->timezone;
	}
	
}