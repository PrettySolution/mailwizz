<?php defined('MW_PATH') || exit('No direct script access allowed');

if (!class_exists('SurveyFieldsStatsTypeBaseWidget', false)) {
    require_once dirname(__FILE__) . '/SurveyFieldsStatsTypeBaseWidget.php';
}

/**
 * SurveyFieldsStatsTypeCountryWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class SurveyFieldsStatsTypeCountryWidget extends SurveyFieldsStatsTypeBaseWidget
{
    /**
     * @return array
     */
    protected function getData()
    {
        $data = array();

        $field  = $this->field;
        $survey = $this->survey;

        $respondersCount = SurveyResponder::model()->countByAttributes(array(
            'survey_id' => $survey->survey_id
        ));

        if (empty($respondersCount)) {
            return $data;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'value, COUNT(value) AS counter';
        $criteria->compare('field_id', $field->field_id);
        $criteria->addCondition('value != ""');
        $criteria->group = 'value';
        $results = SurveyFieldValue::model()->findAll($criteria);

        $responsesCount = 0;
        foreach ($results as $result) {
            $data[] = array(
                'label'           => $result->value,
                'data'            => $result->counter,
                'count'           => $result->counter,
                'count_formatted' => $result->counter,
            );

            $responsesCount += $result->counter;
        }

        $emptyResponsesCount = $respondersCount - $responsesCount;

        $data[] = array(
            'label'           => Yii::t('surveys', 'Without response'),
            'data'            => $emptyResponsesCount,
            'count'           => $emptyResponsesCount,
            'count_formatted' => $emptyResponsesCount,
        );

        return $data;
    }
}