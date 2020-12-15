<?php
namespace Tipimail\Messages;

class Message {
	
	private $to;
	private $cc;
	private $bcc;
	private $from;
	private $subject;
	private $replyTo;
	private $html;
	private $text;
	private $attachments;
	private $images;
	private $apiKey;
	private $domain;
	private $tracking;
	private $googleAnalytics;
	private $tags;
	private $meta;
	private $bulk;
	private $ipPool;
	private $textVersion;
	private $blacklist;
	private $template;
	private $subs;
	
	public function __construct() {
		$this->to = array();
		$this->cc = array();
		$this->bcc = array();
		$this->from = new MessageAddress();
		$this->subject = null;
		$this->replyTo = new MessageAddress();
		$this->html = null;
		$this->text = null;
		$this->attachments = array();
		$this->images = array();
		$this->apiKey = null;
		$this->domain = new MessageDomain();
		$this->tracking = new MessageTracking();
		$this->googleAnalytics = new MessageGoogleAnalytics();
		$this->tags = array();
		$this->meta = new MessageMeta();
		$this->bulk = new MessageBulk();
		$this->ipPool = new MessageIpPool();
		$this->textVersion = new MessageTextVersion();
		$this->blacklist = new MessageBlacklist();
		$this->template = new MessageTemplate();
		$this->subs = array();
	}
	
	public function getTo() {
		return $this->to;
	}
	
	public function getCc() {
		return $this->cc;
	}
	
	public function getBcc() {
		return $this->bcc;
	}
	
	public function getFrom() {
		return $this->from;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function getReplyTo() {
		return $this->replyTo;
	}
	
	public function getHtml() {
		return $this->html;
	}
	
	public function getText() {
		return $this->text;
	}
	
	public function getAttachments() {
		return $this->attachments;
	}
	
	public function getImages() {
		return $this->images;
	}
	
	public function getApiKey() {
		return $this->apiKey;
	}
	
	public function getDomain() {
		return $this->domain;
	}
	
	public function getTracking() {
		return $this->tracking;
	}
	
	public function getGoogleAnalytics() {
		return $this->googleAnalytics;
	}
	
	public function getTags() {
		return $this->tags;
	}
	
	public function getMeta() {
		return $this->meta;
	}
	
	public function getBulk() {
		return $this->bulk;
	}
	
	public function getIpPool() {
		return $this->ipPool;
	}
	
	public function getTextVersion() {
		return $this->textVersion;
	}
	
	public function getBlacklist() {
		return $this->blacklist;
	}
	
	public function getTemplate() {
		return $this->template;
	}
	
	public function getSubs() {
		return $this->subs;
	}
	
	private function deleteFromEmailArray(Array $array, $address) {
		$newArray = array();
		foreach ($array as $val) {
			if ($val->getAddress() != $address) {
				$newArray[] = new MessageAddress($val->getAddress(), $val->getPersonalName());
			}
		}
		return $newArray;
	}
	
	public function addTo($address, $name) {
		$this->to[] = new MessageAddress($address, $name);
	}
	
	public function deleteTo($address) {
		$this->to = $this->deleteFromEmailArray($this->to, $address);
	}
	
	public function addCc($address, $name) {
		$this->cc[] = new MessageAddress($address, $name);
	}
	
	public function deleteCc($address) {
		$this->cc = $this->deleteFromEmailArray($this->cc, $address);
	}
	
	public function addBcc($address, $name) {
		$this->bcc[] = new MessageAddress($address, $name);
	}
	
	public function deleteBcc($address) {
		$this->bcc = $this->deleteFromEmailArray($this->bcc, $address);
	}
	
	public function setFrom($address, $name) {
		$this->from = new MessageAddress($address, $name);
	}
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function setReplyTo($address, $name) {
		$this->replyTo = new MessageAddress($address, $name);
	}
	
	public function setHtml($html) {
		$this->html = $html;
	}
	
	public function setText($text) {
		$this->text = $text;
	}
	
	public function addAttachmentFromBase64($base64EncodedContent, $name, $contentType) {
		$this->attachments[] = new MessageAttachment($base64EncodedContent, $name, $contentType);
	}
	
	public function addAttachmentFromText($content, $name) {
		$this->attachments[] = new MessageAttachment(base64_encode($content), $name, 'text/plain');
	}
	
	public function deleteAttachment($name) {
		$attachments = array();
		foreach ($this->attachments as $val) {
			if ($val->getName() != $name) {
				$attachments[] = new MessageAttachment($val->getContent(), $val->getName(), $val->getContentType());
			}
		}
		$this->attachments = $attachments;
	}
	
	public function addImageFromBase64($base64EncodedContent, $name, $contentType, $contentId) {
		$this->images[] = new MessageImage($base64EncodedContent, $name, $contentType, $contentId);
	}
	
	public function deleteImage($name) {
		$images = array();
		foreach ($this->images as $val) {
			if ($val->getName() != $name) {
				$images[] = new MessageImage($val->getContent(), $val->getName(), $val->getContentType(), $val->getContentId());
			}
		}
		$this->images = $images;
	}
	
	public function addAttachmentFromFile($filePath, $name) {
		if (file_exists($filePath)) {
			$content = file_get_contents($filePath);
			if ($content !== false) {
				$base64EncodedContent = base64_encode($content);
				$contentType = mime_content_type($filePath);
				if ($contentType !== false) {
					$this->addAttachmentFromBase64($base64EncodedContent, $name, $contentType);
				}
			}
		}
	}
	
	public function addImageFromFile($filePath, $name, $contentId) {
		if (file_exists($filePath)) {
			$content = file_get_contents($filePath);
			if ($content !== false) {
				$base64EncodedContent = base64_encode($content);
				$contentType = mime_content_type($filePath);
				if ($contentType !== false) {
					$this->addImageFromBase64($base64EncodedContent, $name, $contentType, $contentId);
				}
			}
		}
	}
	
	public function setApiKey($apiKey) {
		$this->apiKey = $apiKey;
	}
	
	public function setDomain($domain) {
		$this->domain->setDomain($domain);
	}
	
	public function enableTrackingOpen() {
		$this->tracking->setOpen(1);
	}
	
	public function disableTrackingOpen() {
		$this->tracking->setOpen(0);
	}
	
	public function enableTrackingClick() {
		$this->tracking->setClick(1);
	}
	
	public function disableTrackingClick() {
		$this->tracking->setClick(0);
	}
	
	public function enableGoogleAnalytics($utmSource, $utmMedium, $utmContent, $utmCampaign) {
		$this->googleAnalytics->setEnable(1);
		$this->googleAnalytics->setUtmSource($utmSource);
		$this->googleAnalytics->setUtmMedium($utmMedium);
		$this->googleAnalytics->setUtmContent($utmContent);
		$this->googleAnalytics->setUtmCampaign($utmCampaign);
	}
	
	public function disableGoogleAnalytics() {
		$this->googleAnalytics->setEnable(0);
		$this->googleAnalytics->setUtmSource(null);
		$this->googleAnalytics->setUtmMedium(null);
		$this->googleAnalytics->setUtmContent(null);
		$this->googleAnalytics->setUtmCampaign(null);
	}
	
	public function addTag($tag) {
		$this->tags[] = new MessageTag($tag);
	}
	
	public function deleteTag($tag) {
		$tags = array();
		foreach ($this->tags as $val) {
			if ($val->getTag() != $tag) {
				$tags[] = new MessageTag($val->getTag());
			}
		}
		$this->tags = $tags;
	}
	
	public function setMeta(Array $meta) {
		$this->meta->setMeta($meta);
	}
	
	public function enableBulk() {
		$this->bulk->setBulkChoice(1);
	}
	
	public function disableBulk() {
		$this->bulk->setBulkChoice(0);
	}
	
	public function setIpPool($ip) {
		$this->ipPool->setIp($ip);
	}
	
	public function enableTextVersion() {
		$this->textVersion->setTextVersionChoice(1);
	}
	
	public function disableTextVersion() {
		$this->textVersion->setTextVersionChoice(0);
	}
	
	public function setBlacklist($blacklistName) {
		$this->blacklist->setBlacklistName($blacklistName);
	}
	
	public function setTemplate($templateName) {
		$this->template->setTemplateName($templateName);
	}
	
	public function addSub($email, Array $values, Array $meta) {
		$this->subs[] = new MessageSub($email, $values, $meta);
	}
	
	public function deleteSub($email) {
		$subs = array();
		foreach ($this->subs as $val) {
			if ($val->getEmail() != $email) {
				$subs[] = new MessageSub($val->getEmail(), $val->getValues(), $val->getMeta());
			}
		}
		$this->subs = $subs;
	}
	
}