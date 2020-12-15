<?php
namespace Tipimail\Users;

class User {
	
	private $id;
	private $username;
	private $civility;
	private $firstName;
	private $lastName;
	private $customerType;
	private $company;
	private $activity;
	private $job;
	private $address;
	private $address2;
	private $zipCode;
	private $city;
	private $country;
	private $phone;
	private $mobile;
	private $settings;
	
	public function __construct($data = null) {
		if (isset($data->id)) {
			$this->id = $data->id;
		}
		if (isset($data->username)) {
			$this->username = $data->username;
		}
		if (isset($data->civility)) {
			$this->civility = $data->civility;
		}
		if (isset($data->firstName)) {
			$this->firstName = $data->firstName;
		}
		if (isset($data->lastName)) {
			$this->lastName = $data->lastName;
		}
		if (isset($data->customerType)) {
			$this->customerType = $data->customerType;
		}
		if (isset($data->company)) {
			$this->company = $data->company;
		}
		if (isset($data->activity)) {
			$this->activity = $data->activity;
		}
		if (isset($data->job)) {
			$this->job = $data->job;
		}
		if (isset($data->address)) {
			$this->address = $data->address;
		}
		if (isset($data->address2)) {
			$this->address2 = $data->address2;
		}
		if (isset($data->zipCode)) {
			$this->zipCode = $data->zipCode;
		}
		if (isset($data->city)) {
			$this->city = $data->city;
		}
		if (isset($data->country)) {
			$this->country = $data->country;
		}
		if (isset($data->phone)) {
			$this->phone = $data->phone;
		}
		if (isset($data->mobile)) {
			$this->mobile = $data->mobile;
		}
		if (isset($data->settings)) {
			$this->settings = new UserSettings($data->settings);
		}
		else {
			$this->settings = new UserSettings();
		}
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getCivility() {
		return $this->civility;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function getCustomerType() {
		return $this->customerType;
	}
	
	public function getCompany() {
		return $this->company;
	}
	
	public function getActivity() {
		return $this->activity;
	}
	
	public function getJob() {
		return $this->job;
	}
	
	public function getAddress() {
		return $this->address;
	}
	
	public function getAddress2() {
		return $this->address2;
	}
	
	public function getZipCode() {
		return $this->zipCode;
	}
	
	public function getCity() {
		return $this->city;
	}
	
	public function getCountry() {
		return $this->country;
	}
	
	public function getPhone() {
		return $this->phone;
	}
	
	public function getMobile() {
		return $this->mobile;
	}
	
	public function getSettings() {
		return $this->settings;
	}
	
}