<?php
namespace Tipimail\Messages;

class MessageImage {
	
	private $content;
	private $name;
	private $contentType;
	private $contentId;
	
	public function __construct($content, $name, $contentType, $contentId) {
		$this->content = $content;
		$this->name = $name;
		$this->contentType = $contentType;
		$this->contentId = $contentId;
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
	
	public function getContentId() {
		return $this->contentId;
	}
	
}