<?php
namespace Tipimail\Messages;

class MessageTextVersion {
	
	private $textVersionChoice;
	
	public function getTextVersionChoice() {
		return $this->textVersionChoice;
	}
	
	public function setTextVersionChoice($textVersionChoice) {
		$this->textVersionChoice = $textVersionChoice;
	}
	
}