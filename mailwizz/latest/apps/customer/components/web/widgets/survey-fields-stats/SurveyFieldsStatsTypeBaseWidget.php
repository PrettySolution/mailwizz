<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldsStatsTypeBaseWidget
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

abstract class SurveyFieldsStatsTypeBaseWidget extends CWidget
{
    /**
     * @var Survey
     */
    public $survey;

    /**
     * @var SurveyField
     */
    public $field;

    /**
     * @return string|void
     * @throws CException
     */
    public function run()
    {
        if (empty($this->survey) || empty($this->field)) {
            return '';
        }

        $field     = $this->field;
        $chartData = $this->getData();

        if (empty($chartData)) {
            return '';
        }

        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.pie.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/survey-fields-stats.js'));

        $viewName = 'field-type';

        if (is_file(dirname(__FILE__) . '/views/field-type-' . $field->type->identifier . '.php')) {
            $viewName = 'field-type-' . $field->type->identifier;
        }

        $this->render($viewName, compact('field', 'chartData'));
    }

    /**
     * @return array
     */
    abstract protected function getData();
}