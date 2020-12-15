<?php
namespace Tipimail\Messages;

class MessageDomain {
	
	private $domain;
	
	public function getDomain() {
		return $this->domain;
	}
	
	public function setDomain($domain) {
		$this->domain = $domain;
	}
	
}