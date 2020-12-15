<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * GridViewDropDownLinksSelector
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class GridViewDropDownLinksSelector extends CWidget
{
	/**
	 * @var string 
	 */
	public $heading = '';
	
	/**
	 * @var array
	 */
	public $links = array();
	
	/**
	 * @inheritdoc
	 */
	public function run()
	{
		if (!in_array(Yii::app()->apps->getCurrentAppName(), array('customer', 'backend'))) {
			return;
		}

		$this->render('grid-view-drop-down-links-selector', array(
			'heading'   => $this->heading,
			'links'     => $this->links,
		));
	}
}