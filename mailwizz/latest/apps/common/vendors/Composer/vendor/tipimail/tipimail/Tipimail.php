<?php
namespace Tipimail;

class Tipimail {
	
	private $apiUsername;
	private $apiKey;
	
	private $settingsService;
	private $accountsService;
	private $statisticsService;
	private $blacklistsService;
	private $messagesService;
	private $usersService;
	
	public function __construct($apiUsername, $apiKey) {
		$this->apiUsername = $apiUsername;
		$this->apiKey = $apiKey;
		$this->settingsService = new Settings\SettingsService($this);
		$this->accountsService = new Accounts\AccountsService($this);
		$this->statisticsService = new Statistics\StatisticsService($this);
		$this->blacklistsService = new Blacklists\BlacklistsService($this);
		$this->messagesService = new Messages\MessagesService($this);
		$this->usersService = new Users\UsersService($this);
	}
	
	/**
	 * To use SettingsService functions
	 * @return \Tipimail\Settings\SettingsService
	 */
	public function getSettingsService() {
		return $this->settingsService;
	}
	
	/**
	 * To use AccountsService functions
	 * @return \Tipimail\Accounts\AccountsService
	 */
	public function getAccountsService() {
		return $this->accountsService;
	}
	
	/**
	 * To use StatisticsService functions
	 * @return \Tipimail\Statistics\StatisticsService
	 */
	public function getStatisticsService() {
		return $this->statisticsService;
	}
	
	/**
	 * To use BlacklistsService functions
	 * @return \Tipimail\Blacklists\BlacklistsService
	 */
	public function getBlacklistsService() {
		return $this->blacklistsService;
	}
	
	/**
	 * To use MessagesService functions
	 * @return \Tipimail\Messages\MessagesService
	 */
	public function getMessagesService() {
		return $this->messagesService;
	}
	
	/**
	 * To use UsersService functions
	 * @return \Tipimail\Users\UsersService
	 */
	public function getUsersService() {
		return $this->usersService;
	}
	
	public function getData($url) {
		return $this->requestData($url, 'GET', array());
	}
	
	public function postData($url, $data) {
		return $this->requestData($url, 'POST', $data);
	}
	
	public function putData($url, $data) {
		return $this->requestData($url, 'PUT', $data);
	}
	
	public function deleteData($url) {
		return $this->requestData($url, 'DELETE', array());
	}
	
	private function requestData($url, $requestType, $data) {
		$response = array();
		if (function_exists('curl_init')) {
			if ($requestType != 'GET') {
				$headers = array(
					'Cache-control: no-cache',
					'Content-Type: application/json',
					'X-Tipimail-ApiUser: ' . $this->apiUsername,
					'X-Tipimail-ApiKey: ' . $this->apiKey
				);
			}
			else {
				$headers = array(
					'Cache-control: no-cache',
					'X-Tipimail-ApiUser: ' . $this->apiUsername,
					'X-Tipimail-ApiKey: ' . $this->apiKey
				);
			}
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, 'https://api.tipimail.com/v1/' . $url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			if ($requestType != 'GET') {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestType);
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode((object)$data));
			}
			$response = curl_exec($curl);
			$response = (object)json_decode($response, false);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$error = '';
			if (curl_errno($curl)) {
				$error = curl_error($curl);
			}
			curl_close($curl);
			if ($httpcode < 200 || $httpcode >= 300) {
				if (isset($response->error)) {
					$error = $response->error;
				}
				else {
					$httpcodeDescriptions = array(
						400 => 'Bad request',
						401 => 'Unauthorized',
						404 => 'Not found',
						409 => 'Conflict',
					);
					if (isset($httpcodeDescriptions[$httpcode])) {
						$error = $httpcodeDescriptions[$httpcode];
					}
					if ($error == '') {
						$error = 'Error';
					}
				}
				if ($httpcode == 400) {
					throw new Exceptions\TipimailBadRequestException($error, $httpcode);
				}
				else if ($httpcode == 401) {
					throw new Exceptions\TipimailUnauthorizedException($error, $httpcode);
				}
				else if ($httpcode == 404) {
					throw new Exceptions\TipimailNotFoundException($error, $httpcode);
				}
				else if ($httpcode == 409) {
					throw new Exceptions\TipimailConflictException($error, $httpcode);
				}
				else {
					throw new Exceptions\TipimailException($error, $httpcode);
				}
			}
		}
		else {
			throw new Exceptions\TipimailException('Curl extension not found in your server', 0);
		}
		return $response;
	}
	
}
