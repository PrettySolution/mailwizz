<?php
namespace Tipimail\Settings\Templates;

class TemplatesService {
	
	private $tipimail;
	private $url;
	
	public function __construct($tipimail) {
		$this->tipimail = $tipimail;
		$this->url = 'settings/templates';
	}
	
	/**
	 * Get all templates
	 * @param int $page
	 * @param int $pageSize
	 * @return \Tipimail\Settings\Template[]
	 * 	id
	 * 	templateName
	 * 	descriptionfrom
	 * 		address
	 * 		personalName
	 * 	subject
	 * 	htmlContent
	 * 	textContent
	 * 	createdAt
	 * 	updatedAt
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function getAll($page = null, $pageSize = null) {
		$data = array(
			'page' =>  $page,
			'pageSize' => $pageSize
		);
		$result = $this->tipimail->postData($this->url . '/list', $data);
		$templates = array();
		foreach ($result as $value) {
			$templates[] =  new Template($value);
		}
		return $templates;
	}
	
	/**
	 * Get template
	 * @param string $id
	 * @return \Tipimail\Settings\Template
	 * 	id
	 * 	templateName
	 * 	descriptionfrom
	 * 		address
	 * 		personalName
	 * 	subject
	 * 	htmlContent
	 * 	textContent
	 * 	createdAt
	 * 	updatedAt
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function get($id) {
		$result = $this->tipimail->getData($this->url . '/' . $id);
		return new Template($result);
	}
	
	/**
	 * Test template
	 * @param string $id
	 * @param string $recipient
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function test($id, $recipient) {
		$data = array(
			'recipient' => $recipient
		);
		$result = $this->tipimail->postData($this->url . '/' . $id . '/test', $data);
	}
	
	/**
	 * Add template
	 * @param string $description
	 * @param string $fromAddress
	 * @param string $fromPersonalName
	 * @param string $subject
	 * @param string $htmlContent
	 * @param string $textContent
	 * @return \Tipimail\Settings\Template
	 * 	id
	 * 	templateName
	 * 	descriptionfrom
	 * 		address
	 * 		personalName
	 * 	subject
	 * 	htmlContent
	 * 	textContent
	 * 	createdAt
	 * 	updatedAt
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function add($description, $fromAddress, $fromPersonalName, $subject, $htmlContent, $textContent) {
		$from = array(
			'address' => $fromAddress,
			'personalName' => $fromPersonalName
		);
		$data = array(
			'description' => $description,
			'from' => $from,
			'subject' => $subject,
			'htmlContent' => $htmlContent,
			'textContent' => $textContent
		);
		$result = $this->tipimail->postData($this->url, $data);
		return new Template($result);
	}
	
	/**
	 * Update template
	 * @param string $id
	 * @param string $description
	 * @param string $fromAddress
	 * @param string $fromPersonalName
	 * @param string $subject
	 * @param string $htmlContent
	 * @param string $textContent
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function update($id, $description, $fromAddress, $fromPersonalName, $subject, $htmlContent, $textContent) {
		$from = array(
			'address' => $fromAddress,
			'personalName' => $fromPersonalName
		);
		$data = array(
			'description' => $description,
			'from' => $from,
			'subject' => $subject,
			'htmlContent' => $htmlContent,
			'textContent' => $textContent
		);
		$this->tipimail->putData($this->url . '/' . $id, $data);
	}
	
	/**
	 * Delete template
	 * @param $id
	 * @throws \Tipimail\Exceptions\TipimailException
	 */
	public function delete($id) {
		$this->tipimail->deleteData($this->url . '/' . $id);
	}
	
}
