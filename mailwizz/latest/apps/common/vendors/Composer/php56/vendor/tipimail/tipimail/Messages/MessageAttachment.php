<?php
namespace Tipimail\Messages;

class MessageAttachment {
	
	private $content;
	private $name;
	private $contentType;
	
	public function __construct($content, $name, $contentType) {
		$this->content = $content;
		$this->name = $name;
		$this->contentType = $contentType;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getContentType() {
		return $this->contentType;
	}
	
}