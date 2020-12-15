<?php
namespace Tipimail\Statistics;

class StatisticsMessageInfo {
	
	private $from;
	private $email;
	private $subject;
	private $size;
	
	public function __construct($data = null) {
		if (isset($data->from)) {
			$this->from = $data->from;
		}
		if (isset($data->email)) {
			$this->email = $data->email;
		}
		if (isset($data->subject)) {
			$this->subject = $data->subject;
		}
		if (isset($data->size)) {
			$this->size = $data->size;
		}
	}
	
	public function getFrom() {
		return $this->from;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function getSize() {
		return $this->size;
	}
	
}