<?php
namespace Tipimail\Blacklists;

class BlacklistsService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'blacklists';
	}
	
	/**
	 * Get bounces
	 * @param int $pageSize
	 * @param int $page
	 * @return \Tipimail\Blacklists\BlacklistEmails
	 * total
	 * emails
	 * 		email
	 * 		blacklist
	 * 		listName
	 * 		createdDate
	 * 		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getBounces($pageSize = null, $page = null, $order = null) {
		$data = array(
			'type' => 'bounces',
			'pageSize' => $pageSize,
			'page' => $page,
			'order' => $order
		);
		$result = $this->tipimail->postData($this->url . '/list', $data);
		return new BlacklistEmails($result);
	}
	
	/**
	 * Get complaints
	 * @param string $list
	 * @param int $pageSize
	 * @param int $page
	 * @return \Tipimail\Blacklists\BlacklistEmails
	 * total
	 * emails
	 * 		email
	 * 		blacklist
	 * 		listName
	 * 		createdDate
	 * 		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getComplaints($list = null, $pageSize = null, $page = null, $order = null) {
		$data = array(
			'type' => 'complaints',
			'list' => $list,
			'pageSize' => $pageSize,
			'page' => $page,
			'order' => $order
		);
		$result = $this->tipimail->postData($this->url . '/list', $data);
		return new BlacklistEmails($result);
	}
	
	/**
	 * Get unsubscribers
	 * @param string $list
	 * @param int $pageSize
	 * @param int $page
	 * @return \Tipimail\Blacklists\BlacklistEmails
	 * 	total
	 * 	emails
	 * 		email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getUnsubscribers($list = null, $pageSize = null, $page = null, $order = null) {
		$data = array(
			'type' => 'unsubscribes',
			'list' => $list,
			'pageSize' => $pageSize,
			'page' => $page,
			'order' => $order
		);
		$result = $this->tipimail->postData($this->url . '/list', $data);
		return new BlacklistEmails($result);
	}
	
	/**
	 * Get email informations
	 * @param string $email
	 * @return Tipimail\Blacklists\BlacklistEmail[]
	 * 	email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($email) {
		$result = $this->tipimail->getData($this->url . '/' . $email);
		$emailInfo = array();
		foreach ($result as $value) {
			$emailInfo[] = new BlacklistEmail($value);
		}
		return $emailInfo;
	}
	
	/**
	 * Get email informations for type
	 * @param string $type
	 * @param string $email
	 * @return Tipimail\Blacklists\BlacklistEmail
	 * 	email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function getForType($type, $email) {
		$result = $this->tipimail->getData($this->url . '/' . $type . '/' . $email);
		return new BlacklistEmail($result);
	}
	
	/**
	 * Get bounce email informations
	 * @param string $email
	 * @return Tipimail\Blacklists\BlacklistEmail
	 * 	email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getBounce($email) {
		return $this->getForType('bounces', $email);
	}
	
	/**
	 * get complaint email informations
	 * @param string $email
	 * @return Tipimail\Blacklists\BlacklistEmail
	 * 	email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getComplaint($email) {
		return $this->getForType('complaints', $email);
	}
	
	/**
	 * Get unsubscriber email informations
	 * @param string $email
	 * @return Tipimail\Blacklists\BlacklistEmail
	 * 	email
	 *		blacklist
	 *		listName
	 *		createdDate
	 *		lastModifiedDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getUnsubscriber($email) {
		return $this->getForType('unsubscribes', $email);
	}
	
	/**
	 * Add email for type
	 * @param string $type
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function add($type, $email) {
		$data = array(
			'type' => $type,
			'email' => $email
		);
		$this->tipimail->postData($this->url, $data);
	}
	
	/**
	 * Add bounce email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function addBounce($email) {
		return $this->add('bounces', $email);
	}
	
	/**
	 * Add complaint email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function addComplaint($email) {
		return $this->add('complaints', $email);
	}
	
	/**
	 * Add unsubscriber email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function addUnsubscriber($email) {
		return $this->add('unsubscribes', $email);
	}
	
	/**
	 * Delete email for type
	 * @param string $type
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function delete($type, $email) {
		$this->tipimail->deleteData($this->url . '/' . $type . '/' . $email);
	}
	
	/**
	 * Delete bounce email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function deleteBounce($email) {
		return $this->delete('bounces', $email);
	}
	
	/**
	 * Delete complaint email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function deleteComplaint($email) {
		return $this->delete('complaints', $email);
	}
	
	/**
	 * Delete unsubscriber email
	 * @param string $email
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function deleteUnsubscriber($email) {
		return $this->delete('unsubscribers', $email);
	}
	
}