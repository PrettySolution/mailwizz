<?php
namespace Tipimail\Accounts;

class AccountCredits {
	
	private $credits;
	
	public function __construct($data = null) {
		if (isset($data->credits)) {
			$this->credits = $data->credits;
		}
	}
	
	public function getCredits() {
		return $this->credits;
	}
	
}