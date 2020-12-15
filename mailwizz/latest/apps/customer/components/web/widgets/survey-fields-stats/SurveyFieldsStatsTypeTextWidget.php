<?php defined('MW_PATH') || exit('No direct script access allowed');

if (!class_exists('SurveyFieldsStatsTypeBaseWidget', false)) {
    require_once dirname(__FILE__) . '/SurveyFieldsStatsTypeBaseWidget.php';
}
/**
 * SurveyFieldsStatsTypeTextWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class SurveyFieldsStatsTypeTextWidget extends SurveyFieldsStatsTypeBaseWidget
{
    /**
     * @return array
     */
    protected function getData()
    {
        $data = array();

        $survey = $this->survey;
        $field  = $this->field;

        $respondersCount = SurveyResponder::model()->countByAttributes(array(
            'survey_id' => $survey->survey_id
        ));

        if (empty($respondersCount)) {
            return $data;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'COUNT(value) AS counter';
        $criteria->compare('field_id', $field->field_id);
        $criteria->addCondition('value != ""');
        $resultsCount = SurveyFieldValue::model()->count($criteria);

        $data[] = array(
            'label'           => Yii::t('surveys', 'With response'),
            'data'            => $resultsCount,
            'count'           => $resultsCount,
            'count_formatted' => $resultsCount,
        );

        $emptyResponsesCount = $respondersCount - $resultsCount;

        $data[] = array(
            'label'           => Yii::t('surveys', 'Without response'),
            'data'            => $emptyResponsesCount,
            'count'           => $emptyResponsesCount,
            'count_formatted' => $emptyResponsesCount,
        );

        return $data;
    }
}