<?php
namespace Tipimail\Settings\SendingConfiguration;

class SendingConfigurationUnsubscribe {
	
	private $enable;
	private $content;
	
	public function __construct($data = null) {
		if (isset($data->enable)) {
			$this->enable = $data->enable;
		}
		if (isset($data->content)) {
			$this->content = $data->content;
		}
	}
	
	public function getEnable() {
		return $this->enable;
	}
	
	public function getContent() {
		return $this->content;
	}
	
}