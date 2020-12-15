<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeleteEmailBlacklistCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.2
 */

class DeleteEmailBlacklistCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $count = EmailBlacklist::model()->count();
        
        if (empty($count)) {
            $this->stdout('Nothing to delete, aborting!');
            return 0;
        }
        
        $this->stdout(sprintf('This action will delete %d blacklisted emails...', $count));
        
        $start = microtime(true);
        
        while (true) {
            
            $criteria = new CDbCriteria();
            $criteria->limit = 1000;
            $emails = EmailBlacklist::model()->findAll($criteria);
            
            if (empty($emails)) {
                break;
            }
            
            foreach ($emails as $email) {
                $this->stdout(sprintf('Deleting the email: %s', $email->email));
                $email->delete();
            }
        }
        
        $timeTook  = round(microtime(true) - $start, 4);
        
        $this->stdout(sprintf("DONE, took %s seconds!\n", $timeTook));
        return 0;
    }
}