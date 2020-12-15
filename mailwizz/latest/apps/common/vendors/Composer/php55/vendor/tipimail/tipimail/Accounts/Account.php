<?php
namespace Tipimail\Accounts;

class Account {
	
	private $credits;
	private $creditsSubscription;
	private $quotaReachedRenew;
	private $quotaReachedPercent;
	private $automaticSuperiorPlan;
	private $tacitAgreement;
	private $displayName;
	private $formulaTypeName;
	private $creationDate;
	private $subscriptionDate;
	private $expirationDate;
	
	public function __construct($data = null) {
		if (isset($data->credits)) {
			$this->credits = $data->credits;
		}
		if (isset($data->creditsSubscription)) {
			$this->creditsSubscription = $data->creditsSubscription;
		}
		if (isset($data->quotaReachedRenew)) {
			$this->quotaReachedRenew = $data->quotaReachedRenew;
		}
		if (isset($data->quotaReachedPercent)) {
			$this->quotaReachedPercent = $data->quotaReachedPercent;
		}
		if (isset($data->automaticSuperiorPlan)) {
			$this->automaticSuperiorPlan = $data->automaticSuperiorPlan;
		}
		if (isset($data->tacitAgreement)) {
			$this->tacitAgreement = $data->tacitAgreement;
		}
		if (isset($data->displayName)) {
			$this->displayName = $data->displayName;
		}
		if (isset($data->formulaTypeName)) {
			$this->formulaTypeName = $data->formulaTypeName;
		}
		if (isset($data->creationDate)) {
			$this->creationDate = $data->creationDate;
		}
		if (isset($data->subscriptionDate)) {
			$this->subscriptionDate = $data->subscriptionDate;
		}
		if (isset($data->expirationDate)) {
			$this->expirationDate = $data->expirationDate;
		}
	}
	
	public function getCredits() {
		return $this->credits;
	}
	
	public function getCreditsSubscription() {
		return $this->creditsSubscription;
	}
	
	public function getQuotaReachedRenew() {
		return $this->quotaReachedRenew;
	}
	
	public function getQuotaReachedPercent() {
		return $this->quotaReachedPercent;
	}
	
	public function getAutomaticSuperiorPlan() {
		return $this->automaticSuperiorPlan;
	}
	
	public function getTacitAgreement() {
		return $this->tacitAgreement;
	}
	
	public function getDisplayName() {
		return $this->displayName;
	}
	
	public function getFormulaTypeName() {
		return $this->formulaTypeName;
	}
	
	public function getCreationDate() {
		return $this->creationDate;
	}
	
	public function getExpirationDate() {
		return $this->expirationDate;
	}
	
	public function getSubscriptionDate() {
		return $this->subscriptionDate;
	}
	
}