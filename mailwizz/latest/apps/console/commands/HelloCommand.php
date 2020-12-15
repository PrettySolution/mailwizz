<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HelloCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class HelloCommand extends ConsoleCommand 
{
    public function actionIndex() 
    {
        echo 'Hello World!' . "\n";
    }
}