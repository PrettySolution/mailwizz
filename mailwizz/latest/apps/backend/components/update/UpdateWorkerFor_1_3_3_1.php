<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_3_3_1
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class UpdateWorkerFor_1_3_3_1 extends UpdateWorkerAbstract
{
    public function run()
    {
        $options = Yii::app()->options; 
        $options->set('system.customer_servers.can_select_delivery_servers_for_campaign', $options->get('system.campaign.campaign_options.customer_select_delivery_servers', 'no'));
        
        // run the sql from file
        $this->runQueriesFromSqlFile('1.3.3.1');
        
        // add a few notes
        $phpCli = CommonHelper::findPhpCliPath();
        $notify = Yii::app()->notify;
        $notify->addInfo(Yii::t('update', 'Version {version} brings a new cron job that you have to add to run once at 20 minutes. After addition, it must look like: {cron}', array(
            '{version}' => '1.3.3.1',
            '{cron}'    => sprintf('<br /><strong>*/20 * * * * %s -q ' . MW_ROOT_PATH . '/apps/console/console.php feedback-loop-handler > /dev/null 2>&1</strong>', $phpCli),
        )));
        $notify->addInfo(Yii::t('update', 'Version {version} brings a new cron job that you have to add to run once a day. After addition, it must look like: {cron}', array(
            '{version}' => '1.3.3.1',
            '{cron}'    => sprintf('<br /><strong>0 0 * * * %s -q ' . MW_ROOT_PATH . '/apps/console/console.php daily > /dev/null 2>&1</strong>', $phpCli),
        )));
        $notify->addWarning(Yii::t('update', 'Starting with version {version}, the "process-subscribers" command is no longer needed, please disable it from your crons!', array(
            '{version}' => '1.3.3.1',
        )));
    }
} 