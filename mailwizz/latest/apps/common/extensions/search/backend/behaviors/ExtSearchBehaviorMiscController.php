<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorMiscController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
	    $defaultKeywords = array('miscellaneous', 'utils');
		return array(
			'index' => array(
				'keywords'  => array_merge($defaultKeywords, array()),
                'skip'      => array($this, '_indexSkip')
			),
            'emergency_actions' => array(
                'keywords'  => array_merge($defaultKeywords, array()),
            ),
            'application_log' => array(
                'keywords'  => array_merge($defaultKeywords, array('application logs', 'logging')),
            ),
            'campaigns_delivery_logs' => array(
                'keywords'  => array_merge($defaultKeywords, array('logging', 'campaigns logs', 'campaign logs', 'campaign delivery')),
            ),
            'campaigns_bounce_logs' => array(
                'keywords'  => array_merge($defaultKeywords, array('logging', 'campaigns logs', 'campaign logs', 'campaign bounce', 'bounce logs')),
            ),
            'campaigns_stats' => array(
                'keywords'  => array_merge($defaultKeywords, array('logging', 'campaigns logs', 'campaign logs', 'campaign stat', 'stats logs')),
            ),
            'delivery_servers_usage_logs' => array(
                'keywords'  => array_merge($defaultKeywords, array('logging')),
            ),
            'guest_fail_attempts' => array(
                'keywords'  => array_merge($defaultKeywords, array('logging', 'login failed attempts')),
            ),
            'cron_jobs_list' => array(
                'keywords'  => array_merge($defaultKeywords, array('cron list')),
            ),
            'cron_jobs_history' => array(
                'keywords'  => array_merge($defaultKeywords, array('cron history')),
            ),
            'phpinfo' => array(
                'keywords'  => array_merge($defaultKeywords, array('system info', 'php info')),
            ),
            'changelog' => array(
                'keywords'  => array_merge($defaultKeywords, array('system info', 'change log', 'version info', 'updates')),
            ),
        );
	}

    /**
     * @return bool
     */
	public function _indexSkip()
    {
        return true;
    }
}
	