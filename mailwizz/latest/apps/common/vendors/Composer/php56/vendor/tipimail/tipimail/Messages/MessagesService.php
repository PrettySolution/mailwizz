<?php
namespace Tipimail\Messages;

class MessagesService {

	private $tipimail;
	private $url;

	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'messages';
	}

	private function getTo(Message $messageData) {
		$to = array();
		foreach ($messageData->getTo() as $value) {
			$to[] = array(
				'address' => $value->getAddress(),
				'personalName' => $value->getPersonalName()
			);
		}
		return $to;
	}

	private function getCc(Message $messageData) {
		$cc = array();
		foreach ($messageData->getCc() as $value) {
			$cc[] = array(
				'address' => $value->getAddress(),
				'personalName' => $value->getPersonalName()
			);
		}
		return $cc;
	}

	private function getBcc(Message $messageData) {
		$bcc = array();
		foreach ($messageData->getBcc() as $value) {
			$bcc[] = array(
				'address' => $value->getAddress(),
				'personalName' => $value->getPersonalName()
			);
		}
		return $bcc;
	}

	private function getFrom(Message $messageData) {
		return array(
			'address' => $messageData->getFrom()->getAddress(),
			'personalName' => $messageData->getFrom()->getPersonalName()
		);
	}

	private function getReplyTo(Message $messageData) {
		$replyTo = array();
		if ($messageData->getReplyTo()->getAddress() !== null) {
			$replyTo = array(
				'address' => $messageData->getReplyTo()->getAddress(),
				'personalName' => $messageData->getReplyTo()->getPersonalName()
			);
		}
		return $replyTo;
	}

	private function getAttachments(Message $messageData) {
		$attachments = array();
		foreach ($messageData->getAttachments() as $value) {
			$attachments[] = array(
				'content' => $value->getContent(),
				'filename' => $value->getName(),
				'contentType' => $value->getContentType()
			);
		}
		return $attachments;
	}

	private function getImages(Message $messageData) {
		$images = array();
		foreach ($messageData->getImages() as $value) {
			$images[] = array(
				'content' => $value->getContent(),
				'filename' => $value->getName(),
				'contentType' => $value->getContentType(),
				'contentId' => $value->getContentId()
			);
		}
		return $images;
	}

	private function getHeaders(Message $messageData) {
		$headers = array();
		if ($messageData->getDomain()->getDomain() !== null) {
			$headers['X-TM-DOMAIN'] = $messageData->getDomain()->getDomain();
		}
		if ($messageData->getTracking()->getOpen() !== null || $messageData->getTracking()->getClick() !== null) {
			$headers['X-TM-TRACKING'] = array(
				'html' => array(
					'open' => $messageData->getTracking()->getOpen(),
					'click' => $messageData->getTracking()->getClick()
				),
				'text' => array('click' => $messageData->getTracking()->getClick())
			);
		}
		if ($messageData->getGoogleAnalytics()->getEnable() !== null) {
			$headers['X-TM-GOOGLENALYTICS'] = array(
				'enable' => $messageData->getGoogleAnalytics()->getEnable(),
				'utm_source' => $messageData->getGoogleAnalytics()->getUtmSource(),
				'utm_medium' => $messageData->getGoogleAnalytics()->getUtmMedium(),
				'utm_content' => $messageData->getGoogleAnalytics()->getUtmContent(),
				'utm_campaign' => $messageData->getGoogleAnalytics()->getUtmCampaign()
			);
		}
		foreach ($messageData->getTags() as $tag) {
			$headers['X-TM-TAGS'][] = $tag->getTag();
		}
		if ($messageData->getMeta()->getMeta() !== null) {
			$headers['X-TM-META'] = $messageData->getMeta()->getMeta();
		}
		if ($messageData->getBulk()->getBulkChoice() !== null) {
			$headers['X-TM-BULK'] = $messageData->getBulk()->getBulkChoice();
		}
		if ($messageData->getIpPool()->getIp() !== null) {
			$headers['X-TM-IPPOOL'] = $messageData->getIpPool()->getIp();
		}
		if ($messageData->getTextVersion()->getTextVersionChoice() !== null) {
			$headers['X-TM-TEXTVERSION'] = $messageData->getTextVersion()->getTextVersionChoice();
		}
		if ($messageData->getBlacklist()->getBlacklistName() !== null) {
			$headers['X-TM-BLACKLIST'] = $messageData->getBlacklist()->getBlacklistName();
		}
		if ($messageData->getTemplate()->getTemplateName() !== null) {
			$headers['X-TM-TEMPLATE'] = $messageData->getTemplate()->getTemplateName();
		}
		foreach ($messageData->getSubs() as $sub) {
			$headers['X-TM-SUB'][] = array(
				'email' => $sub->getEmail(),
				'values' => $sub->getValues(),
				'meta' => $sub->getMeta()
			);
		}
		return $headers;
	}

	private function getMsg(Message $messageData, Array $from, Array $replyTo, Array $attachments, Array $images) {
		$msg = array(
			'from' => $from,
			'subject' => $messageData->getSubject(),
			'html' => $messageData->getHtml(),
			'text' => $messageData->getText()
		);
		if (count($replyTo) > 0) {
			$msg['replyTo'] = $replyTo;
		}
		if (count($attachments) > 0) {
			$msg['attachments'] = $attachments;
		}
		if (count($images) > 0) {
			$msg['images'] = $images;
		}
		return $msg;
	}

	private function getData(Message $messageData, Array $msg, Array $to, Array $cc, Array $bcc, Array $headers) {
		$data = array(
			'msg' => $msg,
			'to' => $to
		);
		if ($messageData->getApiKey() != null) {
			$data['apiKey'] = $messageData->getApiKey();
		}
		if (count($cc) > 0) {
			$data['cc'] = $cc;
		}
		if (count($bcc) > 0) {
			$data['bcc'] = $bcc;
		}
		if (count($headers) > 0) {
			$data['headers'] = $headers;
		}
		return $data;
	}

	/**
	 * Send message
	 * @param Message $messageData
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function send(Message $messageData) {
		$to = $this->getTo($messageData);
		$cc = $this->getCc($messageData);
		$bcc = $this->getBcc($messageData);
		$from = $this->getFrom($messageData);
		$replyTo = $this->getReplyTo($messageData);
		$attachments = $this->getAttachments($messageData);
		$images = $this->getImages($messageData);
		$headers = $this->getHeaders($messageData);
		$msg = $this->getMsg($messageData, $from, $replyTo, $attachments, $images);
		$data = $this->getData($messageData, $msg, $to, $cc, $bcc, $headers);
		$this->tipimail->postData($this->url . '/send', $data);
	}
}
