<?php
namespace Tipimail\Messages;

class MessageSub {
	
	private $email;
	private $values;
	private $meta;
	
	public function __construct($email, Array $values, Array $meta) {
		$this->email = $email;
		$this->values = $values;
		$this->meta = $meta;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getValues() {
		return $this->values;
	}
	
	public function getMeta() {
		return $this->meta;
	}
	
}