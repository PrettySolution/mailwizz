<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_3_4_6
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
class UpdateWorkerFor_1_3_4_6 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('1.3.4.6');
        
        // since the delivery servers were disabled, add the notice
        Yii::app()->notify->addWarning(Yii::t('update', 'This version adds new fields for delivery servers and some of them are required. Because of this, all delivery servers have been marked as inactive. Please review the settings and validate the servers once again.'));
    }
} 