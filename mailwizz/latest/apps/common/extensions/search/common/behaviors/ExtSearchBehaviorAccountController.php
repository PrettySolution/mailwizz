<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorAccountController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'  => array('account', 'my account', 'update account'),
			),
			'2fa' => array(
				'keywords'  => array('2fa', 'two factor auth', '2 factor auth'),
				'skip'      => array($this, '_2faSkip')
			),
		);
	}

	/**
	 * @return bool
	 */
	public function _2faSkip()
	{
        $twoFaSettings = new OptionTwoFactorAuth();
        return !$twoFaSettings->isEnabled;
	}
}
	