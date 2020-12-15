<?php
namespace Tipimail\Statistics;

class StatisticsSends {
	
	private $error;
	private $rejected;
	private $requested;
	private $deferred;
	private $scheduled;
	private $filtered;
	private $delivered;
	private $hardbounced;
	private $softbounced;
	private $open;
	private $click;
	private $read;
	private $unsubscribed;
	private $complaint;
	private $opener;
	private $clicker;
	
	public function __construct($data = null) {
		if (isset($data->error)) {
			$this->error = $data->error;
		}
		if (isset($data->rejected)) {
			$this->rejected = $data->rejected;
		}
		if (isset($data->requested)) {
			$this->requested = $data->requested;
		}
		if (isset($data->deferred)) {
			$this->deferred = $data->deferred;
		}
		if (isset($data->scheduled)) {
			$this->scheduled = $data->scheduled;
		}
		if (isset($data->filtered)) {
			$this->filtered = $data->filtered;
		}
		if (isset($data->delivered)) {
			$this->delivered = $data->delivered;
		}
		if (isset($data->hardbounced)) {
			$this->hardbounced = $data->hardbounced;
		}
		if (isset($data->softbounced)) {
			$this->softbounced = $data->softbounced;
		}
		if (isset($data->open)) {
			$this->open = $data->open;
		}
		if (isset($data->click)) {
			$this->click = $data->click;
		}
		if (isset($data->read)) {
			$this->read = $data->read;
		}
		if (isset($data->unsubscribed)) {
			$this->unsubscribed = $data->unsubscribed;
		}
		if (isset($data->complaint)) {
			$this->complaint = $data->complaint;
		}
		if (isset($data->opener)) {
			$this->opener = $data->opener;
		}
		if (isset($data->clicker)) {
			$this->clicker = $data->clicker;
		}
	}
	
	public function getError() {
		return $this->error;
	}
	
	public function getRejected() {
		return $this->rejected;
	}
	
	public function getRequested() {
		return $this->requested;
	}
	
	public function getDeferred() {
		return $this->deferred;
	}
	
	public function getScheduled() {
		return $this->scheduled;
	}
	
	public function getFiltered() {
		return $this->filtered;
	}
	
	public function getDelivered() {
		return $this->delivered;
	}
	
	public function getHardbounced() {
		return $this->hardbounced;
	}
	
	public function getSoftbounced() {
		return $this->softbounced;
	}
	
	public function getOpen() {
		return $this->open;
	}
	
	public function getClick() {
		return $this->click;
	}
	
	public function getRead() {
		return $this->read;
	}
	
	public function getUnsubscribed() {
		return $this->unsubscribed;
	}
	
	public function getComplaint() {
		return $this->complaint;
	}
	
	public function getOpener() {
		return $this->opener;
	}
	
	public function getClicker() {
		return $this->clicker;
	}
	
}