<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_6_4
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.4
 */

class UpdateWorkerFor_1_6_4 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('1.6.4');
        
        $options = Yii::app()->options;
        if ((int)$options->get('system.exporter.process_at_once', 500) < 500) {
	        $options->set('system.exporter.process_at_once', 500);
        }
    }
}
