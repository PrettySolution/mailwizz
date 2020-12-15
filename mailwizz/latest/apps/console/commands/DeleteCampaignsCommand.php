<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeleteCampaignsCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 */

class DeleteCampaignsCommand extends ConsoleCommand
{
    /**
     * @param $time
     * @param string $type
     * @return int
     * @throws CDbException
     */
    public function actionIndex($time, $type='')
    {
        if (empty($time)) {
            echo 'Please set the time option using the --time option, i.e: --time="-6 months"' . "\n";
            return 0;
        }
        
        $timestamp = strtotime($time);
        $date      = date('Y-m-d H:i:s', $timestamp);
        $confirm   = sprintf('Are you sure you want to delete the campaigns that are older than "%s" date?', $date);
        
        if (!$this->confirm($confirm)) {
            echo "Okay, aborting!\n";
            return 0;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('status', Campaign::STATUS_SENT);
        if (!empty($type)) {
            $criteria->compare('type', $type);
        }
        $criteria->addCondition('date_added < :dt');
        $criteria->params[':dt'] = $date;

        $count = Campaign::model()->count($criteria);
        
        if (empty($count)) {
            echo "Nothing to delete, aborting!\n";
            return 0;
        }
        
        if (!$this->confirm(sprintf('This action will delete %d campaigns. Proceed?', $count))) {
            echo "Okay, aborting!\n";
            return 0;
        }
        
        $start = microtime(true);
    
        $campaigns = Campaign::model()->findAll($criteria);
        foreach ($campaigns as $campaign) {
            $campaign->delete();
        }
        
        $timeTook  = round(microtime(true) - $start, 4);
        
        echo sprintf("DONE, took %s seconds!\n", $timeTook);
        return 0;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $cmd = $this->getCommandRunner()->getScriptName() .' '. $this->getName();
        
        $help  = sprintf('command: %s --time=EXPRESSION --type=TYPE', $cmd) . "\n";
        $help .= '--time=EXPRESSION where EXPRESSION can be any expression parsable by php\'s strtotime function. ie: --time="-6 months".' . "\n";
        $help .= '--type=TYPE where TYPE can be either regular or autoresponder.' . "\n";

        return $help;
    }
}