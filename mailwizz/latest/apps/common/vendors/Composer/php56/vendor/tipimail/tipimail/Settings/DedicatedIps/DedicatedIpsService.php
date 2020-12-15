<?php
namespace Tipimail\Settings\DedicatedIps;

class DedicatedIpsService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/ips';
	}
	
	/**
	 * Get all dedicated Ips informations
	 * @return \Tipimail\Settings\DedicatedIp[]
	 * 	id
	 * 	type
	 * 	ip
	 * 	expirationDate
	 * 	subscriptionDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll() {
		$result = $this->tipimail->getData($this->url);
		$dedicatedIps = array();
		foreach ($result as $value) {
			$dedicatedIps[] = new DedicatedIp($value);
		}
		return $dedicatedIps;
	}
	
	/**
	 * Get dedicated Ip informations
	 * @param string $id
	 * @return \Tipimail\Settings\DedicatedIp
	 * 	id
	 * 	type
	 * 	ip
	 * 	expirationDate
	 * 	subscriptionDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($id) {
		$result = $this->tipimail->getData($this->url . '/' . $id);
		return new DedicatedIp($result);
	}
	
	/**
	 * Update dedicated Ip type
	 * @param string $type
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function updateToType($type, $id) {
		$data = array('type' => $type);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Update dedicated Ip to bulk
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateToBulk($id) {
		$this->updateToType(1, $id);
	}
	
	/**
	 * Update dedicated Ip to transactional
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function updateToTransactional($id) {
		$this->updateToType(0, $id);
	}
	
	/**
	 * Delete dedicated Ip
	 * @param string $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function delete($id) {
		$this->tipimail->deleteData($this->url . '/' . $id);
	}
	
}