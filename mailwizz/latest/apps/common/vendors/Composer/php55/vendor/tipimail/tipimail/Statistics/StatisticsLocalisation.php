<?php
namespace Tipimail\Statistics;

class StatisticsLocalisation {
	
	private $open;
	private $click;
	private $clicker;
	private $opener;
	private $country;
	private $city;
	private $latitude;
	private $longitude;
	
	public function __construct($data = null) {
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->clicker)) {
			$this->clicker = $data->clicker;
		}
		if (isset($data->opener)) {
			$this->opener = $data->opener;
		}
		if (isset($data->country)) {
			$this->country = $data->country;
		}
		if (isset($data->city)) {
			$this->city = $data->city;
		}
		if (isset($data->latitude)) {
			$this->latitude = $data->latitude;
		}
		if (isset($data->longitude)) {
			$this->longitude = $data->longitude;
		}
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function getClicker() {
		return $this->clicker;
	}
	
	public function getOpener() {
		return $this->opener;
	}
	
	public function getCountry() {
		return $this->country;
	}
	
	public function getCity() {
		return $this->city;
	}
	
	public function getLatitude() {
		return $this->latitude;
	}
	
	public function getLongitude() {
		return $this->longitude;
	}
	
}