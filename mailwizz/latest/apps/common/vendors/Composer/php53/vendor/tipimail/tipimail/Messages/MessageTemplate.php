<?php
namespace Tipimail\Messages;

class MessageTemplate {
	
	private $templateName;
	
	public function getTemplateName() {
		return $this->templateName;
	}
	
	public function setTemplateName($templateName) {
		$this->templateName = $templateName;
	}
	
}