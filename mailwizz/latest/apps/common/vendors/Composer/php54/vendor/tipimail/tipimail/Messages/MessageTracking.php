<?php
namespace Tipimail\Messages;

class MessageTracking {
	
	private $open;
	private $click;
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function setOpen($open) {
		$this->open = $open;
	}
	
	public function setClick($click) {
		$this->click = $click;
	}
	
}