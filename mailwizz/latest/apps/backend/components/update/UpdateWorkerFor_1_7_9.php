<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_7_9
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.9
 */

class UpdateWorkerFor_1_7_9 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('1.7.9');
        
        // make sure user settings survive
        $options = Yii::app()->options;
        $newKey  = 'system.cron.delete_logs.delete_campaign_delivery_logs';
	    $oldKey  = 'system.cron.send_campaigns.delete_campaign_delivery_logs';
        $options->set($newKey, $options->get($oldKey, 'no'));
    }
}
