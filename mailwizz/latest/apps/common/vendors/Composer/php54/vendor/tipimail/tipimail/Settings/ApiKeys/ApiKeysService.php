<?php
namespace Tipimail\Settings\ApiKeys;

class ApiKeysService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/apikeys';
	}
	
	/**
	 * Get all Api keys informations
	 * @return \Tipimail\Settings\ApiKey[]
	 * 	id
	 * 	hash
	 * 	key
	 * 	allowedIPs
	 * 	iPFilterEnabled
	 * 	description
	 * 	createdAt
	 * 	updatedAt
	 * 	enabled
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll() {
		$result = $this->tipimail->getData($this->url);
		$apiKeys = array();
		foreach ($result as $value) {
			$apiKeys[] = new ApiKey($value);
		}
		return $apiKeys;
	}
	
	/**
	 * Get api key informations
	 * @param string $id
	 * @return \Tipimail\Settings\ApiKey
	 * 	id
	 * 	hash
	 * 	key
	 * 	allowedIPs
	 * 	iPFilterEnabled
	 * 	description
	 * 	createdAt
	 * 	updatedAt
	 * 	enabled
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($id) {
		$result = $this->tipimail->getData($this->url . '/' . $id);
		return new ApiKey($result);
	}
	
	/**
	 * Test api key (send an email to user)
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function test($id) {
		$this->tipimail->getData($this->url . '/' . $id . '/test');
	}
	
	/**
	 * Regenerate api key
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function regenerate($id) {
		$this->tipimail->getData($this->url . '/' . $id . '/regenerate');
	}
	
	/**
	 * Add new Api key
	 * @param string $description
	 * @param array $allowedIPs
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function add($description = '', Array $allowedIPs = array()) {
		$data = array(
			'description' => $description,
			'iPFilterEnabled' => (bool)count($allowedIPs),
			'allowedIPs' => $allowedIPs
		);
		$result = $this->tipimail->postData($this->url, $data);
		return new ApiKey($result);
	}
	
	/**
	 * Update Api key description
	 * @param string $id
	 * @param string $description
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateDescription($id, $description = '') {
		$data = array('description' => $description);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update Api key allowed Ips
	 * @param string $id
	 * @param array $allowedIPs
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateAllowedIps($id, Array $allowedIPs = array()) {
		$data = array(
			'iPFilterEnabled' => (bool)count($allowedIPs),
			'allowedIPs' => $allowedIPs
		);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Delete Api key
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function delete($id) {
		$this->tipimail->deleteData($this->url . '/' . $id);
	}
	
}
