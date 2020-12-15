<?php
namespace Tipimail\Statistics;

class StatisticsMessages {
	
	private $total;
	private $messages;
	
	public function __construct($data = null) {
		if (isset($data->total)) {
			$this->total = $data->total;
		}
		$this->messages = array();
		if (isset($data->messages) && is_array($data->messages)) {
			foreach ($data->messages as $message) {
				$this->messages[] = new StatisticsMessage($message);
			}
		}
	}
	
	public function getTotal() {
		return $this->total;
	}
	
	public function getMessages() {
		return $this->messages;
	}
	
}
