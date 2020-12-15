<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldsStatsWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class SurveyFieldsStatsWidget extends CWidget
{
    /**
     * @var Survey
     */
    public $survey;
    
    public function run() 
    {
        $survey = $this->survey;
        $criteria = new CDbCriteria();
        $criteria->compare('survey_id', $survey->survey_id);
        $criteria->order = 'sort_order ASC';
        $fields = SurveyField::model()->findAll($criteria);

        foreach ($fields as $field) {
            $className  = 'SurveyFieldsStatsType' . ucfirst($field->type->identifier);
            $classAlias = 'customer.components.web.widgets.survey-fields-stats.' . $className . 'Widget';

            if (!is_file(Yii::getPathOfAlias($classAlias) . '.php')) {
                continue;
            }

            $this->controller->widget($classAlias, array(
                'survey' => $survey,
                'field'  => $field
            ));
        }
    }
}