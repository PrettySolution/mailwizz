<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorEmail_blacklistController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'  => array('black lists'),
				'skip'      => array($this, '_skip'),
			),
            'create' => array(
                'keywords'  => array('create black lists'),
                'skip'      => array($this, '_skip'),
            ),
		);
	}

	/**
	 * @param SearchExtSearchItem $item
	 *
	 * @return bool
	 */
	public function _skip(SearchExtSearchItem $item)
	{
		if (MW_APP_NAME == 'customer') {
			$customer = Yii::app()->customer->getModel();
			return $customer->getGroupOption('lists.can_use_own_blacklist', 'no') != 'yes';
		}
		return !Yii::app()->user->getModel()->hasRouteAccess($item->route);
	}
}
	