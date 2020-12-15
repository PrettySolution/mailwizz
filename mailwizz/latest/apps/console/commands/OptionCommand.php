<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
class OptionCommand extends ConsoleCommand 
{
    /**
     * @param $name
     * @param null $default
     */
    public function actionGet_option($name, $default = null)
    {
        exit((string)Yii::app()->options->get($name, $default));
    }

    /**
     * @param $name
     * @param $value
     */
    public function actionSet_option($name, $value)
    {
        Yii::app()->options->set($name, $value);
    }
}