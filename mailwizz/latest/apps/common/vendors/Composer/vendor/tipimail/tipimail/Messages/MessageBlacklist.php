<?php
namespace Tipimail\Messages;

class MessageBlacklist {
	
	private $blacklistName;
	
	public function getBlacklistName() {
		return $this->blacklistName;
	}
	
	public function setBlacklistName($blacklistName) {
		$this->blacklistName = $blacklistName;
	}
	
}