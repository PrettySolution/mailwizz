<?php defined('MW_PATH') || exit('No direct script access allowed');

if (!class_exists('SurveyFieldsStatsTypeTextWidget', false)) {
    require_once dirname(__FILE__) . '/SurveyFieldsStatsTypeTextWidget.php';
}

/**
 * SurveyFieldsStatsTypeUrlWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class SurveyFieldsStatsTypeUrlWidget extends SurveyFieldsStatsTypeTextWidget
{
}