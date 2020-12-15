<?php
namespace Tipimail\Blacklists;

class BlacklistEmails {
	
	private $total;
	private $emails;
	
	public function __construct($data = null) {
		if (isset($data->total)) {
			$this->total = $data->total;
		}
		$this->emails = array();
		if (isset($data->bounces) && is_array($data->bounces)) {
			foreach ($data->bounces as $email) {
				$this->emails[] = new BlacklistEmail($email);
			}
		}
		else if (isset($data->complaints) && is_array($data->complaints)) {
			foreach ($data->complaints as $email) {
				$this->emails[] = new BlacklistEmail($email);
			}
		}
		else if (isset($data->unsubscribers) && is_array($data->unsubscribers)) {
			foreach ($data->unsubscribers as $email) {
				$this->emails[] = new BlacklistEmail($email);
			}
		}
	}
	
	public function getTotal() {
		return $this->total;
	}
	
	public function getEmails() {
		return $this->emails;
	}
	
}