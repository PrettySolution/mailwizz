<?php
namespace Tipimail\Settings\Domains;

class DomainTest {
	
	private $sending;
	private $verifiedDkim;
	private $verifiedSpf;
	private $verifiedTracking;
	private $verifiedMx;
	private $verifiedA;
	private $verifiedDomain;
	
	public function __construct($data = null) {
		if (isset($data->sending)) {
			$this->sending = $data->sending;
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
	}
	
	public function getSending() {
		return $this->sending;
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
	
}