<?php
namespace Tipimail\Settings\Domains;

class DomainsService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/domains';
	}
	
	/**
	 * Get all domains informations
	 * @return \Tipimail\Settings\Domain[]
	 * 	sending
	 * 	tracking
	 * 	id
	 * 	createdAt
	 * 	updatedAt
	 * 	verifiedDkim
	 * 	verifiedSpf
	 * 	verifiedTracking
	 * 	verifiedMx
	 * 	verifiedA
	 * 	verifiedDomain
	 * 	default
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll() {
		$result = $this->tipimail->getData($this->url);
		$domains = array();
		foreach ($result as $value) {
			$domains[] =  new Domain($value);
		}
		return $domains;
	}
	
	/**
	 * Get domain informations
	 * @param string $sending
	 * @return \Tipimail\Settings\Domain
	 * 	sending
	 * 	tracking
	 * 	id
	 * 	createdAt
	 * 	updatedAt
	 * 	verifiedDkim
	 * 	verifiedSpf
	 * 	verifiedTracking
	 * 	verifiedMx
	 * 	verifiedA
	 * 	verifiedDomain
	 * 	default
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($sending) {
		$result = $this->tipimail->getData($this->url . '/' . $sending);
		return new Domain($result);
	}
	
	/**
	 * Test domain
	 * @param string $sending
	 * @return \Tipimail\Settings\DomainTest
	 * 	sending
	 * 	verifiedDkim
	 * 	verifiedSpf
	 * 	verifiedTracking
	 * 	verifiedMx
	 * 	verifiedA
	 * 	verifiedDomain
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function test($sending) {
		$result = $this->tipimail->getData($this->url . '/' . $sending . '/test');
		return new DomainTest($result);
	}
	
	/**
	 * Get domain settings
	 * @return \Tipimail\Settings\DomainSettings
	 * 	dkim
	 * 		value
	 * 		record
	 * 		prefix
	 * 	spf
	 * 		value
	 * 		record
	 * 	tracking
	 * 		value
	 * 		record
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getSettings() {
		$result = $this->tipimail->getData($this->url . '/settings');
		return new DomainSettings($result);
	}
	
	/**
	 * Save domain
	 * @param string $sending
	 * @param string $tracking
	 * @return \Tipimail\Settings\DomainTest
	 * 	sending
	 * 	verifiedDkim
	 * 	verifiedSpf
	 * 	verifiedTracking
	 * 	verifiedMx
	 * 	verifiedA
	 * 	verifiedDomain
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function save($sending, $tracking, $email) {
		$data = array(
			'sending' => $sending,
			'tracking' => $tracking,
			'email' => $email
		);
		$result = $this->tipimail->postData($this->url, $data);
		return new DomainTest($result);
	}
	
	/**
	 * Delete domain
	 * @param string $sending
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function delete($sending) {
		$this->tipimail->deleteData($this->url . '/' . $sending);
	}
	
}
