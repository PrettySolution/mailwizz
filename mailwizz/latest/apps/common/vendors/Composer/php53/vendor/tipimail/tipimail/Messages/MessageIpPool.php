<?php
namespace Tipimail\Messages;

class MessageIpPool {
	
	private $ip;
	
	public function getIp() {
		return $this->ip;
	}
	
	public function setIp($ip) {
		$this->ip = $ip;
	}
	
}