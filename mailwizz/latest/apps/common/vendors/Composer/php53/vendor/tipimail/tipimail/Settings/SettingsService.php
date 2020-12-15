<?php
namespace Tipimail\Settings;

class SettingsService {
	
	private $apiKeysService;
	private $dedicatedIpsService;
	private $domainsService;
	private $webhooksService;
	private $sendingConfigurationService;
	private $templatesService;
	
	public function __construct($tipimail) {
		$this->apiKeysService = new ApiKeys\ApiKeysService($tipimail);
		$this->dedicatedIpsService = new DedicatedIps\DedicatedIpsService($tipimail);
		$this->domainsService = new Domains\DomainsService($tipimail);
		$this->webhooksService = new Webhooks\WebhooksService($tipimail);
		$this->sendingConfigurationService = new SendingConfiguration\SendingConfigurationService($tipimail);
		$this->templatesService = new Templates\TemplatesService($tipimail);
	}
	
	/**
	 * To use ApiKeyService functions
	 * @return \Tipimail\Settings\ApiKeysService
	 */
	public function getApiKeysService() {
		return $this->apiKeysService;
	}
	
	/**
	 * To use DedicatedIpsService functions
	 * @return \Tipimail\Settings\DedicatedIpsService
	 */
	public function getDedicatedIpsService() {
		return $this->dedicatedIpsService;
	}
	
	/**
	 * To use DomainsService functions
	 * @return \Tipimail\Settings\DomainsService
	 */
	public function getDomainsService() {
		return $this->domainsService;
	}
	
	/**
	 * To use WebhooksService functions
	 * @return \Tipimail\Settings\WebhooksService
	 */
	public function getWebhooksService() {
		return $this->webhooksService;
	}
	
	/**
	 * To use SendingConfigurationService functions
	 * @return \Tipimail\Settings\SendingConfigurationService
	 */
	public function getSendingConfigurationService() {
		return $this->sendingConfigurationService;
	}
	
	/**
	 * To use TemplatesService functions
	 * @return \Tipimail\Settings\TemplatesService
	 */
	public function getTemplatesService() {
		return $this->templatesService;
	}
	
}