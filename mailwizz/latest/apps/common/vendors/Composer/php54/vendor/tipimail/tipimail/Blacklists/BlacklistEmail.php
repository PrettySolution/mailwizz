<?php
namespace Tipimail\Blacklists;

class BlacklistEmail {
	
	private $email;
	private $blacklist;
	private $listName;
	private $createdDate;
	private $lastModifiedDate;
	
	public function __construct($data = null) {
		if (isset($data->email)) {
			$this->email = $data->email;
		}
		if (isset($data->blacklist)) {
			$this->blacklist = $data->blacklist;
		}
		if (isset($data->listName)) {
			$this->listName = $data->listName;
		}
		if (isset($data->createdDate)) {
			$this->createdDate = $data->createdDate;
		}
		if (isset($data->lastModifiedDate)) {
			$this->lastModifiedDate = $data->lastModifiedDate;
		}
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getBlacklist() {
		return $this->blacklist;
	}
	
	public function getListName() {
		return $this->listName;
	}
	
	public function getCreatedDate() {
		return $this->createdDate;
	}
	
	public function getLastModifiedDate() {
		return $this->lastModifiedDate;
	}
	
}