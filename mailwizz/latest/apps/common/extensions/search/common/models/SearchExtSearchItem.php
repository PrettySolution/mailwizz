<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SearchExtSearchItem
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class SearchExtSearchItem extends FormModel
{
	/**
	 * @var string 
	 */
	public $title = '';

	/**
	 * @var string 
	 */
	public $url = '';

	/**
	 * @var string
	 */
	public $route = '';
	
	/**
	 * @var array 
	 */
	public $keywords = array();

	/**
	 * @var array 
	 */
	public $children = array();

	/**
	 * @var int 
	 */
	public $score = 0;

	/**
	 * @var callable
	 */
	public $skip;

	/**
	 * @var array 
	 */
	public $buttons = array();
	
	/**
	 * @var callable
	 */
	public $childrenGenerator;
	
	/**
	 * @var callable
	 */
	public $keywordsGenerator;
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return array(
			array('title, url, route, skip, keywords, buttons, keywordsGenerator, childrenGenerator', 'safe'),
		);
	}

	/**
	 * @param $newAttributes
	 *
	 * @return $this
	 */
	public function mergeAttributes($newAttributes)
	{
		if (empty($newAttributes)) {
			return $this;
		}
		$this->setAttributes(CMap::mergeArray($this->attributes, $newAttributes));
		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields()
	{
		return array(
			'title'     => $this->title,
			'url'       => $this->url,
			'score'     => $this->score,
			'keywords'  => $this->keywords,
            'buttons'   => $this->buttons,
            'children'  => $this->children
		);
	}
}
