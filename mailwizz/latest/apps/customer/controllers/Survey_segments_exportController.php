<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Survey_segments_exportController
 *
 * Handles the actions for list segments export related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class Survey_segments_exportController extends Controller
{
    public function init()
    {
        parent::init();

        if (Yii::app()->options->get('system.exporter.enabled', 'yes') != 'yes') {
            $this->redirect(array('surveys/index'));
        }

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('survey-segments-export.js')));
    }

    /**
     * Display the export options
     */
    public function actionIndex($survey_uid, $segment_uid)
    {
        $survey  = $this->loadSurveyModel($survey_uid);
        $segment = $this->loadSegmentModel($survey->survey_id, $segment_uid);

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('survey_export', 'Export responders from your survey segment'),
            'pageHeading'       => Yii::t('survey_export', 'Export responders'),
            'pageBreadcrumbs'   => array(
                Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                Yii::t('surveys', 'Segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                $segment->name . ' ' => $this->createUrl('survey_segments/update', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid)),
                Yii::t('survey_export', 'Export responders')
            )
        ));

        $this->render('list', compact('survey', 'segment'));
    }

    /**
     * Handle the CSV export option
     */
    public function actionCsv($survey_uid, $segment_uid)
    {
        $survey     = $this->loadSurveyModel($survey_uid);
        $segment    = $this->loadSegmentModel($survey->survey_id, $segment_uid);
        $request    = Yii::app()->request;
        $options    = Yii::app()->options;

        $export = new SurveySegmentCsvExport();
        $export->survey_id  = $survey->survey_id; // should not be assigned in attributes
        $export->segment_id = $segment->segment_id; // should not be assigned in attributes
	    
        $processAtOnce  = (int)$options->get('system.exporter.process_at_once', 500);
        $pause          = (int)$options->get('system.exporter.pause', 1);

        set_time_limit(0);
        if ($memoryLimit = $options->get('system.exporter.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        ini_set("auto_detect_line_endings", true);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($export->modelName, array()))) {
            $export->attributes = $attributes;
        }

        if (!$export->count) {
            $export->count = $export->countResponders();
        }

        if (!$request->isPostRequest || !$request->isAjaxRequest) {
            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('survey_export', 'Export responders'),
                'pageHeading'       => Yii::t('survey_export', 'Export responders'),
                'pageBreadcrumbs'   => array(
                    Yii::t('surveys', 'Surveys') => $this->createUrl('surveys/index'),
                    $survey->name . ' ' => $this->createUrl('surveys/overview', array('survey_uid' => $survey->survey_uid)),
                    Yii::t('surveys', 'Segments') => $this->createUrl('survey_segments/index', array('survey_uid' => $survey->survey_uid)),
                    $segment->name . ' ' => $this->createUrl('survey_segments/update', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid)),
                    Yii::t('survey_export', 'Export responders') => $this->createUrl('survey_segments_export/index', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid)),
                    Yii::t('survey_export', 'CSV Export')
                )
            ));
            return $this->render('csv', compact('survey', 'segment', 'export', 'processAtOnce', 'pause'));
        }
        
        if ($export->count == 0) {
            return $this->renderJson(array(
                'result'    => 'error',
                'message'   => Yii::t('survey_export', 'Your survey has no responders to export!'),
            ));
        }

	    $storageDir     = Yii::getPathOfAlias('common.runtime.survey-segment-export');
	    $prefix         = strtolower(preg_replace('/[^a-z0-9]/i', '-', $segment->name));
	    $csvFile        = $prefix . '-responders-' . $segment->segment_uid . '.csv';
	    $isFirstBatch   = $export->is_first_batch;

        if ($export->is_first_batch) {

	        // old csv
	        if (is_file($oldCsvFile = $storageDir . '/' . $csvFile)) {
		        @unlink($oldCsvFile);
	        }
	        
            // new ones
            if (!file_exists($storageDir) && !is_dir($storageDir) && !@mkdir($storageDir, 0777, true)) {
                return $this->renderJson(array(
                    'result'    => 'error',
                    'message'   => Yii::t('survey_export', 'Cannot create the storage directory for your export!'),
                ));
            }

            $export->is_first_batch = 0;
        }

        if (!($fp = @fopen($storageDir . '/' . $csvFile, 'a'))) {
            return $this->renderJson(array(
                'result'    => 'error',
                'message'   => Yii::t('survey_export', 'Cannot open the storage file for your export!'),
            ));
        }
        
	    $exportLog       = array();
	    $hasData         = false;
	    $counter         = 0;
	    $startFromOffset = ($export->current_page - 1) * $processAtOnce;
	    $responders      = $export->findResponders($processAtOnce, $startFromOffset);
	    $responder       = new SurveyResponder();

	    if (!empty($responders)) {
	    	
		    if ($isFirstBatch) {
			    fputcsv($fp, array_keys($responders[0]), ',', '"');
		    }

		    foreach ($responders as $responderData) {
			    fputcsv($fp, array_values($responderData), ',', '"');
			    $exportLog[] = array(
				    'type'      => 'success',
				    'message'   => Yii::t('survey_export', 'Successfully added the IP "{ip}" to the export survey.', array(
					    '{ip}' => $responderData[$responder->getAttributeLabel('ip_address')],
				    )),
				    'counter'   => true,
			    );
		    }
	    }

	    if (!$hasData && !empty($responders)) {
		    $hasData = true;
	    }
	    
	    $counter += count($responders);
        
        fclose($fp);

        if ($counter > 0) {
            $exportLog[] = array(
                'type'      => 'info',
                'message'   => Yii::t('survey_export', 'Exported {count} responders, from {start} to {end}.', array(
                    '{count}'   => $counter,
                    '{start}'   => ($export->current_page - 1) * $processAtOnce,
                    '{end}'     => (($export->current_page - 1) * $processAtOnce) + $processAtOnce,
                )),
            );
        }

        // is it done ?
        if (!$hasData || ($export->current_page * $processAtOnce >= $export->count)) {

            $exportLog[] = array(
                'type'      => 'success',
                'message'   => Yii::t('survey_export', 'The export is now complete, starting the packing process...')
            );
            
            $downloadUrl = $this->createUrl('survey_segments_export/csv_download', array('survey_uid' => $survey_uid, 'segment_uid' => $segment_uid));

            return $this->renderJson(array(
                'result'        => 'success',
                'message'       => Yii::t('survey_export', 'Packing done, your file will be downloaded now, please wait...'),
                'download'      => $downloadUrl,
                'export_log'    => $exportLog,
                'recordsCount'  => $export->count,
            ));
        }

        $export->current_page++;
        return $this->renderJson(array(
            'result'        => 'success',
            'message'       => Yii::t('survey_export', 'Please wait, starting another batch...'),
            'attributes'    => $export->attributes,
            'export_log'    => $exportLog,
            'recordsCount'  => $export->count,
        ));
    }

    /**
     * Download the csv created from export
     */
    public function actionCsv_download($survey_uid, $segment_uid)
    {
        $survey     = $this->loadSurveyModel($survey_uid);
        $segment    = $this->loadSegmentModel($survey->survey_id, $segment_uid);
	    $storageDir = Yii::getPathOfAlias('common.runtime.survey-segment-export');
	    $prefix     = strtolower(preg_replace('/[^a-z0-9]/i', '-', $segment->name));
	    $csvName    = $prefix . '-responders-' . $segment->segment_uid . '.csv';
	    $csvPath    = $storageDir . '/' . $csvName;
	    
        if (!is_file($csvPath)) {
            Yii::app()->notify->addError(Yii::t('survey_export', 'The export file has been deleted.'));
            $this->createUrl('survey_segments_export/index', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid));
        }

        if (!($fp = @fopen($csvPath, "rb"))) {
            @unlink($csvPath);
            Yii::app()->notify->addError(Yii::t('survey_export', 'The export file has been deleted.'));
            $this->createUrl('survey_segments_export/index', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders($csvName, filesize($csvPath));

        while(!feof($fp)) {
            echo fread($fp, 8192);
            flush();
            if (connection_status() != 0) {
                @fclose($fp);
                @unlink($csvPath);
                die();
            }
        }
        @fclose($fp);
        @unlink($csvPath);
    }

    /**
     * Helper method to load the survey AR model
     */
    public function loadSurveyModel($survey_uid)
    {
        $model = Survey::model()->findByAttributes(array(
            'survey_uid'    => $survey_uid,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to load the segment AR model
     */
    public function loadSegmentModel($survey_id, $segment_uid)
    {
        $model = SurveySegment::model()->findByAttributes(array(
            'survey_id'   => $survey_id,
            'segment_uid' => $segment_uid,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
