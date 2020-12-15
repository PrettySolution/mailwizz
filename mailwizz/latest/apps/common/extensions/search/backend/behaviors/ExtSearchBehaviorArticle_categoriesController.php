<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorArticle_categoriesController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords' => array('category', 'article category'),
			),
		);
	}
}
	