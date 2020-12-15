<?php
namespace Tipimail\Messages;

class MessageBulk {
	
	private $bulkChoice;
	
	public function getBulkChoice() {
		return $this->bulkChoice;
	}
	
	public function setBulkChoice($bulkChoice) {
		$this->bulkChoice = $bulkChoice;
	}
	
}