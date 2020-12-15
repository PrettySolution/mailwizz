<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCampaigns_geo_opensController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords' => array('campaigns geo localization'),
                'skip'     => array($this, '_skip'),
            ),
		);
	}

    /**
     * @return bool
     */
    public function _skip()
    {
        if (Yii::app()->customer->getModel()->getGroupOption('campaigns.show_geo_opens', 'no') != 'yes') {
            return true;
        }
        return false;
    }
}
	