<?php
namespace Tipimail\Settings\SendingConfiguration;

class SendingConfigurationService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/sendingconfiguration';
	}
	
	/**
	 * Get sending configuration informations
	 * @return \Tipimail\Settings\SendingConfiguration
	 * 	trackOpens
	 * 	trackClicks
	 * 	googleAnalytics
	 * 		enable
	 * 		utmSource
	 * 		utmMedia
	 * 		utmContent
	 * 		utmCampaign
	 * 	unsubscribe
	 * 		enable
	 * 		content
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get() {
		$result = $this->tipimail->getData($this->url);
		return new SendingConfiguration($result);
	}
	
	/**
	 * Update sending configuration
	 * @param Array $data
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	private function update(Array $data) {
		 $this->tipimail->postData($this->url, $data);
	}
	
	/**
	 * Enable track opens
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function enableTrackOpens() {
		$data = array('trackOpens' => true);
		$this->update($data);
	}
	
	/**
	 * Disable track opens
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function disableTrackOpens() {
		$data = array('trackOpens' => false);
		$this->update($data);
	}
	
	/**
	 * Enable track clicks
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function enableTrackClicks() {
		$data = array('trackClicks' => true);
		$this->update($data);
	}
	
	/**
	 * Disable track clicks
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function disableTrackClicks() {
		$data = array('trackClicks' => false);
		$this->update($data);
	}
	
	/**
	 * Enable track Google analytics
	 * @param string $utmSource
	 * @param string $utmMedia
	 * @param string $utmContent
	 * @param string $utmCampaign
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function enableGoogleAnalytics($utmSource, $utmMedia, $utmContent, $utmCampaign) {
		$googleAnalytics = array(
			'enable' => true,
			'utmSource' => $utmSource,
			'utmMedia' => $utmMedia,
			'utmContent' => $utmContent,
			'utmCampaign' => $utmCampaign
		);
		$data = array(
			'trackClicks' => true,
			'googleAnalytics' => $googleAnalytics
		);
		$this->update($data);
	}
	
	/**
	 * Disable track Google analytics
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function disableGoogleAnalytics() {
		$googleAnalytics = array('enable' => false);
		$data = array('googleAnalytics' => $googleAnalytics);
		$this->update($data);
	}
	
	/**
	 * Enable customized unsubscribe link
	 * @param string $content
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function enableCustomizedUnsubscribeLink($content) {
		$unsubscribe = array(
			'enable' => true,
			'content' => $content
		);
		$data = array('unsubscribe' => $unsubscribe);
		$this->update($data);
	}
	
	/**
	 * Disable customized unsubscribe link
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function disableCustomizedUnsubscribeLink() {
		$unsubscribe = array('enable' => false);
		$data = array('unsubscribe' => $unsubscribe);
		$this->update($data);
	}
	
	/**
	 * Enable track mailto links
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function enableTrackMailtoLinks() {
		$data = array('trackMailTo' => true);
		$this->update($data);
	}
	
	/**
	 * Disable track mailto links
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function disableTrackMailtoLinks() {
		$data = array('trackMailTo' => false);
		$this->update($data);
	}
	
}
