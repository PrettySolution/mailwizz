<?php
namespace Tipimail\Settings\Domains;

class Domain {
	
	private $sending;
	private $tracking;
	private $id;
	private $createdAt;
	private $updatedAt;
	private $verifiedDkim;
	private $verifiedSpf;
	private $verifiedTracking;
	private $verifiedMx;
	private $verifiedA;
	private $verifiedDomain;
	private $default;
	
	public function __construct($data = null) {
		if (isset($data->sending)) {
			$this->sending = $data->sending;
		}
		if (isset($data->tracking)) {
			$this->tracking = $data->tracking;
		}
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->createdAt)) {
			$this->createdAt = $data->createdAt;
		}
		if (isset($data->updatedAt)) {
			$this->updatedAt = $data->updatedAt;
		}
		if (isset($data->verifiedDkim)) {
			$this->verifiedDkim = $data->verifiedDkim;
		}
		if (isset($data->verifiedSpf)) {
			$this->verifiedSpf = $data->verifiedSpf;
		}
		if (isset($data->verifiedTracking)) {
			$this->verifiedTracking = $data->verifiedTracking;
		}
		if (isset($data->verifiedMx)) {
			$this->verifiedMx = $data->verifiedMx;
		}
		if (isset($data->verifiedA)) {
			$this->verifiedA = $data->verifiedA;
		}
		if (isset($data->verifiedDomain)) {
			$this->verifiedDomain = $data->verifiedDomain;
		}
		if (isset($data->default)) {
			$this->default = $data->default;
		}
	}
	
	public function getSending() {
		return $this->sending;
	}
	
	public function getTracking() {
		return $this->tracking;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
	public function getVerifiedDkim() {
		return $this->verifiedDkim;
	}
	
	public function getVerifiedSpf() {
		return $this->verifiedSpf;
	}
	
	public function getVerifiedTracking() {
		return $this->verifiedTracking;
	}
	
	public function getVerifiedMx() {
		return $this->verifiedMx;
	}
	
	public function getVerifiedA() {
		return $this->verifiedA;
	}
	
	public function getVerifiedDomain() {
		return $this->verifiedDomain;
	}
	
	public function getDefault() {
		return $this->default;
	}
	
}