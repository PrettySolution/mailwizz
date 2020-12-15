<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyResponders7DaysActivityWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class SurveyResponders7DaysActivityWidget extends CWidget
{
    public $survey;
    
    public function run() 
    {
        $survey = $this->survey;
        
        if ($survey->customer->getGroupOption('surveys.show_7days_responders_activity_graph', 'yes') != 'yes') {
            return;
        }
        
        $cacheKey = sha1(__METHOD__ . $survey->survey_id . date('H') . 'v2');
        if (($chartData = Yii::app()->cache->get($cacheKey)) === false) {
           
            $chartData = array(
                'responders' => array(
                    'label' => '&nbsp;' . Yii::t('survey_responders', 'Responders'),
                    'data'  => array(),
                ),
            );
            
            for ($i = 0; $i < 7; $i++) {
                $timestamp = strtotime(sprintf('-%d days', $i));
                
                // responders
                $count = SurveyResponder::model()->count(array(
                    'condition' => 'survey_id = :lid AND status = :st AND DATE(date_added) = :date',
                    'params'    => array(
                        ':lid'  => $survey->survey_id,
                        ':st'   => SurveyResponder::STATUS_ACTIVE,
                        ':date' => date('Y-m-d', $timestamp)
                    ),
                ));
                $chartData['responders']['data'][] = array($timestamp * 1000, (int)$count);
            }

            $chartData = array_values($chartData);
            Yii::app()->cache->set($cacheKey, $chartData, 3600);
        }
        
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.crosshair.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.time.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/strftime/strftime-min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/survey-responders-7days-activity.js'));
        
        $this->render('7days-activity', compact('chartData'));
    }
}