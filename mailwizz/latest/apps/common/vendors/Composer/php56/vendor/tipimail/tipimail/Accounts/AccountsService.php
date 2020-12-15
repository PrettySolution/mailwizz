<?php
namespace Tipimail\Accounts;

class AccountsService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'account';
	}
	
	/**
	 * Get account informations
	 * @return \Tipimail\Accounts\Account
	 * 	credits
	 * 	creditsSubscription
	 * 	quotaReachedRenew
	 * 	quotaReachedPercent
	 * 	automaticSuperiorPlan
	 * 	tacitAgreement
	 * 	displayName
	 * 	formulaTypeName
	 * 	subscriptionDate
	 * 	expirationDate
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAccountData() {
		$result = $this->tipimail->getData($this->url);
		return new Account($result);
	}
	
	/**
	 * Get account remaining credits
	 * @return \Tipimail\Accounts\AccountCredits
	 * 	credits
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getRemainingCredits() {
		$result = $this->tipimail->getData($this->url . '/credits');
		return new AccountCredits($result);
	}
	
}
