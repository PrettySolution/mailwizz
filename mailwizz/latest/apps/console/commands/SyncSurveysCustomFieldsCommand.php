<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SyncSurveysCustomFieldsCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class SyncSurveysCustomFieldsCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        try {

            $this->stdout('Loading all surveys...');

            // load all surveys at once
            $db      = Yii::app()->getDb();
            $sql     = 'SELECT survey_id FROM {{survey}} WHERE `status` = "active"';
            $surveys = $db->createCommand($sql)->queryAll();
            
            foreach ($surveys as $survey) {

                $this->stdout('Processing survey id: ' . $survey['survey_id']);
                
                $cacheKey = sha1('system.cron.process_responders.sync_custom_fields_values.survey_id.' . $survey['survey_id']);
                $cachedFieldsIds = Yii::app()->cache->get($cacheKey);
                $cachedFieldsIds = empty($cachedFieldsIds) || !is_array($cachedFieldsIds) ? array() : $cachedFieldsIds;
                
                // load all custom fields for the given survey
                $this->stdout('Loading all custom fields for this survey...');
                $sql    = 'SELECT field_id, default_value FROM {{survey_field}} WHERE survey_id = :lid';
                $fields = $db->createCommand($sql)->queryAll(true, array(':lid' => $survey['survey_id']));
                
                // for cache check
                $invalidateCache = false;
                $fieldsIds       = array();
                foreach ($fields as $field) {
                    $fieldsIds[] = $field['field_id'];
                    // new field added, invalidate everything
                    if (!in_array($field['field_id'], $cachedFieldsIds)) {
                        $invalidateCache = true;
                    }
                }
                
                // nothing has changed in the fields, we can stop
                if (!$invalidateCache) {
                    $this->stdout('No change detected in the custom fields for this survey, we can continue with next survey!');
                    continue;
                }
                
                // load 500 responders at once and find out if they have the right custom fields or not
                $this->stdout('Loading initial responders set for the survey...');
                $limit       = 1000;
                $offset      = 0;
                $sql         = 'SELECT responder_id, ip_address FROM {{survey_responder}} WHERE survey_id = :lid ORDER BY responder_id ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
                $responders = $db->createCommand($sql)->queryAll(true, array(':lid' => (int)$survey['survey_id']));

                $this->stdout('Entering responders loop...');
                while (!empty($responders)) {
                    
                    // keep a reference
                    $respondersList = array();
                    $rids           = array();
                    foreach ($responders as $res) {
                        $rids[]                               = $res['responder_id'];
                        $respondersList[$res['responder_id']] = $res;
                    }

                    // load all custom fields values for existing responders
                    $this->stdout('Selecting fields values for responders...');
                    $sql = 'SELECT field_id, responder_id FROM {{survey_field_value}} WHERE responder_id IN(' . implode(',', $rids) . ')';
                    $fieldsValues = $db->createCommand($sql)->queryAll();

                    // populate this to have the defaults set so we can diff them later
                    $fieldResponders = array();
                    foreach ($fields as $field) {
                        $fieldResponders[$field['field_id']] = array();
                    }

                    // we have set the defaults above, we now just have to add to the array
                    foreach ($fieldsValues as $fieldValue) {
                        $fieldResponders[$fieldValue['field_id']][] = $fieldValue['responder_id'];
                    }
                    $fieldsValues = null;

                    foreach ($fieldResponders as $fieldId => $_responders) {

                        // exclude $responders from $rids
                        $responders  = array_diff($rids, $_responders);

                        if (!count($responders)) {
                            $this->stdout('Nothing to do...');
                            continue;
                        }

                        $this->stdout('Field id ' . $fieldId . ' is missing ' . count($responders) . ' responders data, adding it...');

                        $fieldValues = array();
                        foreach ($fields as $field) {
                            if ($field['field_id'] == $fieldId) {
                                foreach ($responders as $responder) {
                                    $responderObject = null;
                                    if (isset($respondersList[$responder])) {
                                        $responderObject = new SurveyResponder();
                                        $responderObject->responder_id  = $responder;
                                        $responderObject->ip_address    = $respondersList[$responder]['ip_address'];
                                    }
                                    $fieldValues[$responder] = $field['default_value'];
                                }
                                break;
                            }
                        }
                        
                        $inserts = array();
                        foreach ($responders as $responderId) {
                            $fieldValue = isset($fieldValues[$responderId]) ? $fieldValues[$responderId] : '';
                            $inserts[]  = array(
                                'field_id'     => $fieldId,
                                'responder_id' => $responderId,
                                'value'        => $fieldValue,
                                'date_added'   => new CDbExpression('NOW()'),
                                'last_updated' => new CDbExpression('NOW()'),
                            );
                        }

                        $inserts = array_chunk($inserts, 100);
                        foreach ($inserts as $insert) {
                            $connection = $db->getSchema()->getCommandBuilder();
                            $command = $connection->createMultipleInsertCommand('{{survey_field_value}}', $insert);
                            $command->execute();

                            $this->stdout('Inserted ' . count($insert) . ' rows for the value.');
                        }
                        $inserts = null;
                    }

                    $this->stdout('Batch is done...');
                    $fieldResponders = null;

                    $offset      = $offset + $limit;
                    $sql         = 'SELECT responder_id, ip_address FROM {{survey_responder}} WHERE survey_id = :lid ORDER BY responder_id ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
                    $responders = $db->createCommand($sql)->queryAll(true, array(':lid' => (int)$survey['survey_id']));

                    if (!empty($responders)) {
                        $this->stdout('Processing ' . count($responders) . ' more responders...');
                    }
                }

                // set the new cached ids
                Yii::app()->cache->set($cacheKey, $fieldsIds);
                
                // and ... done
                $this->stdout('Done, no more responders for this survey!');
            }

            $this->stdout('Done!');
            
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' .  $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            
        }

        return 0;
    }
}
