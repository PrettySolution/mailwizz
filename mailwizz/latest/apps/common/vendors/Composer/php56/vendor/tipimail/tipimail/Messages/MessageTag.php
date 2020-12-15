<?php
namespace Tipimail\Messages;

class MessageTag {
	
	private $tag;
	
	public function __construct($tag) {
		$this->tag = $tag;
	}
	
	public function getTag() {
		return $this->tag;
	}
	
}