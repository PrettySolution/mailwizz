<?php
namespace Tipimail\Settings\Webhooks;

class WebhookLog {
	
	private $recipient;
	private $subject;
	private $eventDate;
	private $descriptionError;
	private $eventType;
	private $typeOfStatus;
	
	public function __construct($data = null) {
		if (isset($data->recipient)) {
			$this->recipient = $data->recipient;
		}
		if (isset($data->subject)) {
			$this->subject = $data->subject;
		}
		if (isset($data->eventDate)) {
			$this->eventDate = $data->eventDate;
		}
		if (isset($data->descriptionError)) {
			$this->descriptionError = $data->descriptionError;
		}
		if (isset($data->eventType)) {
			$this->eventType = $data->eventType;
		}
		if (isset($data->typeOfStatus)) {
			$this->typeOfStatus = $data->typeOfStatus;
		}
	}
	
	public function getRecipient() {
		return $this->recipient;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function getEventDate() {
		return $this->eventDate;
	}
	
	public function getDescriptionError() {
		return $this->descriptionError;
	}
	
	public function getEventType() {
		return $this->eventType;
	}
	
	public function getTypeOfStatus() {
		return $this->recipient;
	}
	
}