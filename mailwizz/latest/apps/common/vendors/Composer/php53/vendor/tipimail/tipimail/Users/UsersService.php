<?php
namespace Tipimail\Users;

class UsersService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'users';
	}

	/**
	 * Get users informations
	 * @return \Tipimail\Users\User[]
	 * 	id
	 * 	username
	 * 	civility
	 * 	firstName
	 * 	lastName
	 * 	customerType
	 * 	company
	 * 	activity
	 * 	job
	 * 	address
	 * 	address2
	 * 	zipCode
	 * 	city
	 * 	country
	 * 	phone
	 * 	mobile
	 * 	newsletter
	 * 	language
	 * 	dateFormat
	 * 	timeFormat
	 * 	timezone
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll() {
		$result = $this->tipimail->getData($this->url);
		$users = array();
		foreach ($result as $value) {
			$users[] = new User($value);
		}
		return $users;
	}
	
	/**
	 * Get user informations
	 * @param string $id
	 * @return \Tipimail\Users\User
	 * 	id
	 * 	username
	 * 	civility
	 * 	firstName
	 * 	lastName
	 * 	customerType
	 * 	company
	 * 	activity
	 * 	job
	 * 	address
	 * 	address2
	 * 	zipCode
	 * 	city
	 * 	country
	 * 	phone
	 * 	mobile
	 * 	newsletter
	 * 	language
	 * 	dateFormat
	 * 	timeFormat
	 * 	timezone
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($id) {
		$result = $this->tipimail->getData($this->url . '/' . $id);
		return new User($result);
	}
	
	/**
	 * Update username
	 * @param string $id
	 * @param string $username
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateUsername($id, $username) {
		$data = array('username' => $username);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update first name
	 * @param string $id
	 * @param string $firstName
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateFirstName($id, $firstName) {
		$data = array('firstName' => $firstName);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update last name
	 * @param string $id
	 * @param string $lastName
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateLastName($id, $lastName) {
		$data = array('lastName' => $lastName);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Send an email to user to change password
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function resetPassword($id) {
		$this->tipimail->getData($this->url . '/' . $id . '/reset');
	}
	
}