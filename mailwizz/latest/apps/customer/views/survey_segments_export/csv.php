<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('export') . Yii::t('survey_export', 'CSV export progress');?>
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(Yii::t('survey_export', 'Back to export options'), array('survey_segments_export/index', 'survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back')));?>
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body" id="csv-export" data-pause="<?php echo (int)$pause;?>" data-iframe="<?php echo $this->createUrl('survey_import/ping');?>" data-attributes='<?php echo CJSON::encode($export->attributes);?>'>
        <span class="counters">
            <?php echo Yii::t('survey_export', 'From a total of {total} responders, so far {totalProcessed} have been processed, {successfullyProcessed} successfully and {errorProcessing} with errors. {percentage} completed.', array(
                '{total}' => '<span class="total">0</span>',
                '{totalProcessed}' => '<span class="total-processed">0</span>',
                '{successfullyProcessed}' => '<span class="success">0</span>',
                '{errorProcessing}' => '<span class="error">0</span>',
                '{percentage}'  => '<span class="percentage">0%</span>',
            ));?>
        </span>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                <span class="sr-only">0% <?php echo Yii::t('app', 'Complete');?></span>
            </div>
        </div>
        <div class="alert alert-info log-info">
             <?php echo Yii::t('survey_export', 'The export process is starting, please wait...');?>
        </div>
        <div class="log-errors"></div>
    </div>
    <div class="box-footer"></div>
</div>

<!-- modals -->
<div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php
                $text = 'The export process will start shortly. <br />
                While the export is running it is recommended you leave this page as it is and wait for the export to finish.<br />
                The exporter runs in batches of {respondersPerBatch} responders per file with a pause of {pause} seconds between the batches, therefore 
                the export process might take a while depending on your survey size and number of responders to export.<br />
                This is a tedious process, so sit tight and wait for it to finish.';
                echo Yii::t('survey_export', StringHelper::normalizeTranslationString($text), array(
                    '{respondersPerBatch}' => $processAtOnce,
                    '{pause}'               => $pause,
                ));
                ?>
            </div>
        </div>
    </div>
</div>