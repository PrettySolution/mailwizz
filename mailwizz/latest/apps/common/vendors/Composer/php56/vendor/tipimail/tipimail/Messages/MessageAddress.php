<?php
namespace Tipimail\Messages;

class MessageAddress {
	
	private $address;
	private $personalName;
	
	public function __construct($address = null, $personalName = null) {
		$this->address = $address;
		$this->personalName = $personalName;
	}
	
	public function getAddress() {
		return $this->address;
	}
	
	public function getPersonalName() {
		return $this->personalName;
	}
	
}