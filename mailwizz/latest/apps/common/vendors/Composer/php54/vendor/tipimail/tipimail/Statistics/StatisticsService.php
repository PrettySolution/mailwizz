<?php
namespace Tipimail\Statistics;

class StatisticsService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'statistics';
	}
	
	/**
	 * Get sends
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsSends
	 * 	error
	 * 	rejected
	 * 	requested
	 * 	deferred
	 * 	scheduled
	 * 	filtered
	 * 	delivered
	 * 	hardbounced
	 * 	softbounced
	 * 	open
	 * 	click
	 * 	read
	 * 	unsubscribed
	 * 	complaint
	 * 	opener
	 * 	clicker
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getSends($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/sends', $data);
		return new StatisticsSends($result);
	}
	
	/**
	 * Get sends by domain
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsSends[]
	 * 	error
	 * 	rejected
	 * 	requested
	 * 	deferred
	 * 	scheduled
	 * 	filtered
	 * 	delivered
	 * 	hardbounced
	 * 	softbounced
	 * 	open
	 * 	click
	 * 	read
	 * 	unsubscribed
	 * 	complaint
	 * 	opener
	 * 	clicker
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getSendsByDomain($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/domains', $data);
		$sendsByDomain = array();
		foreach ($result as $key => $value) {
			$sendsByDomain[$key] = new StatisticsSends($value);
		}
		return $sendsByDomain;
	}
	
	/**
	 * Get sends by tag
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsSends[]
	 * 	error
	 * 	rejected
	 * 	requested
	 * 	deferred
	 * 	scheduled
	 * 	filtered
	 * 	delivered
	 * 	hardbounced
	 * 	softbounced
	 * 	open
	 * 	click
	 * 	read
	 * 	unsubscribed
	 * 	complaint
	 * 	opener
	 * 	clicker
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getSendsByTag($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/tags', $data);
		$sendsByTag = array();
		foreach ($result as $key => $value) {
			$sendsByTag[$key] = new StatisticsSends($value);
		}
		return $sendsByTag;
	}
	
	/**
	 * Get sends by link
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsLink[]
	 * 	click
	 * 	clicker
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getSendsByLink($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/links', $data);
		$sendsByLink = array();
		foreach ($result as $key => $value) {
			$sendsByLink[$key] = new StatisticsLink($value);
		}
		return $sendsByLink;
	}
	
	/**
	 * Get activity by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsActivity[]
	 * 	delivered
	 * 	open
	 * 	click
	 * 	unsubscribed
	 * 	complaint
	 * 	opener
	 * 	clicker
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getActivityByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/activities', $data);
		$activityByDate = array();
		foreach ($result as $key => $value) {
			$activityByDate[$key] = new StatisticsActivity($value);
		}
		return $activityByDate;
	}
	
	/**
	 * Get bounces by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsBounces[]
	 * 	requested
	 * 	hardbounced
	 * 	softbounced
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getBouncesByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/bounces', $data);
		$bouncesByDate = array();
		foreach ($result as $key => $value) {
			$bouncesByDate[$key] = new StatisticsBounces($value);
		}
		return $bouncesByDate;
	}
	
	/**
	 * Get platforms by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsPlatform[]
	 * 	open
	 * 	operatingSystem
	 * 	deviceType
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getPlatformsByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/platforms', $data);
		$platformsByDate = array();
		foreach ($result as $key => $value) {
			foreach ($value as $subvalue) {
				$platformsByDate[$key][] = new StatisticsPlatform($subvalue);
			}
		}
		return $platformsByDate;
	}
	
	/**
	 * Get computers by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsPlatform[]
	 * 	open
	 * 	operatingSystem
	 * 	deviceType
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getComputersByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/platforms/computers', $data);
		$computerByDate = array();
		foreach ($result as $key => $value) {
			foreach ($value as $subvalue) {
				$computerByDate[$key][] = new StatisticsPlatform($subvalue);
			}
		}
		return $computerByDate;
	}
	
	/**
	 * Get computers
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsPlatform[]
	 * 	open
	 * 	operatingSystem
	 * 	deviceType
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getComputers($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/platforms/computers/total', $data);
		$computer = array();
		foreach ($result as $value) {
			$computer[] = new StatisticsPlatform($value);
		}
		return $computer;
	}
	
	/**
	 * Get mobiles by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsPlatform[]
	 * 	open
	 * 	operatingSystem
	 * 	deviceType
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getMobilesByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/platforms/mobiles', $data);
		$mobileByDate = array();
		foreach ($result as $key => $value) {
			$mobileByDate[$key] = new StatisticsPlatform($value);
		}
		return $mobileByDate;
	}
	
	/**
	 * Get mobiles
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsPlatform[]
	 * 	open
	 * 	operatingSystem
	 * 	deviceType
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getMobiles($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/platforms/mobiles/total', $data);
		$mobile = array();
		foreach ($result as $value) {
			$mobile[] = new StatisticsPlatform($value);
		}
		return $mobile;
	}
	
	/**
	 * Get email platforms by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsEmailPlaform
	 * 	open
	 * 	name
	 * 	type
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getEmailPlatformsByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/emailplatforms', $data);
		$emailplatformsByDate = array();
		foreach ($result as $key => $value) {
			foreach ($value as $subvalue) {
				$emailplatformsByDate[$key][] = new StatisticsEmailPlaform($subvalue);
			}
		}
		return $emailplatformsByDate;
	}
	
	/**
	 * Get webmails by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsEmailPlaform[]
	 * 	open
	 * 	name
	 * 	type
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getWebmailsByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/emailplatforms/webmails', $data);
		$webmailsByDate = array();
		foreach ($result as $key => $value) {
			foreach ($value as $subvalue) {
				$webmailsByDate[$key][] = new StatisticsEmailPlaform($subvalue);
			}
		}
		return $webmailsByDate;
	}
	
	/**
	 * Get webmails
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsEmailPlaformOpen
	 * 	open
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getWebmails($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/emailplatforms/webmails/total', $data);
		return new StatisticsEmailPlaformOpen($result);
	}
	
	/**
	 * Get applications by date
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsEmailPlaform[]
	 * 	open
	 * 	name
	 * 	type
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getApplicationsByDate($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/emailplatforms/applications', $data);
		$applicationsByDate = array();
		foreach ($result as $key => $value) {
			foreach ($value as $subvalue) {
				$applicationsByDate[$key][] = new StatisticsEmailPlaform($subvalue);
			}
		}
		return $applicationsByDate;
	}
	
	/**
	 * Get applications
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsEmailPlaformOpen
	 * 	open
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getApplications($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/emailplatforms/applications/total', $data);
		return new StatisticsEmailPlaformOpen($result);
	}
	
	/**
	 * Get localisations
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsLocalisation[]
	 * 	open
	 * 	click
	 * 	clicker
	 * 	opener
	 * 	country
	 * 	city
	 * 	latitude
	 * 	longitude
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getLocalisations($dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/localisations', $data);
		$localisations = array();
		foreach ($result as $value) {
			$localisations[] = new StatisticsLocalisation($value);
		}
		return $localisations;
	}
	
	/**
	 * Get messages
	 * @param int $page
	 * @param int $pageSize
	 * @param timestamp $dateBegin
	 * @param timestamp $dateEnd
	 * @param array $froms
	 * @param array $tags
	 * @param array $apiKeys
	 * @return \Tipimail\StatisticsMessages
	 * 	total
	 * 	messages
	 * 		id
	 *	 	apiKey
	 * 		createdDate
	 * 		lastStateDate
	 * 			msg
	 * 			from
	 * 			email
	 * 			subject
	 * 			size
	 * 		lastState
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getMessages($page = null, $pageSize = null, $dateBegin = null, $dateEnd = null, Array $froms = null, Array $tags = null, Array $apiKeys = null) {
		$data = array(
			'page' => $page,
			'pageSize' => $pageSize,
			'dateBegin' => $dateBegin,
			'dateEnd' => $dateEnd,
			'froms' => $froms,
			'tags' => $tags,
			'apiKeys' => $apiKeys
		);
		$result = $this->tipimail->postData($this->url . '/messages', $data);
		return new StatisticsMessages($result);
	}
	
	/**
	 * Get message detail
	 * @param string $id
	 * @return \Tipimail\StatisticsMessageDetail
	 * 	id
	 * 	apiKey
	 * 	createdDate
	 * 	lastStateDate
	 * 	msg
	 * 		from
	 * 		email
	 * 		subject
	 * 		size
	 * 	lastState
	 * 	open
	 * 	click
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getMessageDetail($id) {
		$result = $this->tipimail->getData($this->url . '/message/' . $id);
		return new StatisticsMessageDetail($result);
	}
	
	/**
	 * get recipient detail
	 * @param string $email
	 * @return \Tipimail\StatisticsRecipientDetail
	 * 	requested
	 * 	delivered
	 * 	hardbounced
	 * 	softbounced
	 * 	open
	 * 	click
	 * 	opener
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getRecipientDetail($email) {
		$result = $this->tipimail->getData($this->url . '/recipient/' . $email);
		return new StatisticsRecipientDetail($result);
	}
	
}