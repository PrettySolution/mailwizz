<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorMessagesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords' => array('messages list', 'notifications', 'view messages'),
				'skip'     => array($this, '_indexSkip'),
			),
		);
	}

	/**
	 * @param SearchExtSearchItem $item
	 *
	 * @return bool
	 */
	public function _indexSkip(SearchExtSearchItem $item)
	{
		if (MW_APP_NAME == 'customer') {
			return false;
		}
		return !Yii::app()->user->getModel()->hasRouteAccess($item->route);
	}
}
	