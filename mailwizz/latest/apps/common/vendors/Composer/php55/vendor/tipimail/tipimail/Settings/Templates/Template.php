<?php
namespace Tipimail\Settings\Templates;

class Template {
	
	private $id;
	private $templateName;
	private $description;
	private $from;
	private $subject;
	private $htmlContent;
	private $textContent;
	private $createdAt;
	private $updatedAt;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->templateName)) {
			$this->templateName = $data->templateName;
		}
		if (isset($data->description)) {
			$this->description = $data->description;
		}
		if (isset($data->from)) {
			$this->from = new TemplateFrom($data->from);
		}
		else {
			$this->from = new TemplateFrom();
		}
		if (isset($data->subject)) {
			$this->subject = $data->subject;
		}
		if (isset($data->htmlContent)) {
			$this->htmlContent = $data->htmlContent;
		}
		if (isset($data->textContent)) {
			$this->textContent = $data->textContent;
		}
		if (isset($data->createdAt)) {
			$this->createdAt = $data->createdAt;
		}
		if (isset($data->updatedAt)) {
			$this->updatedAt = $data->updatedAt;
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getTemplateName() {
		return $this->templateName;
	}
	
	public function getDescription() {
		return $this->description;
	}
	
	public function getFrom() {
		return $this->from;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function getHtmlContent() {
		return $this->htmlContent;
	}
	
	public function getTextContent() {
		return $this->textContent;
	}
	
	public function getCreatedAt() {
		return $this->createdAt;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
}