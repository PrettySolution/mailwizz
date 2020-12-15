<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
?>

<div class="callout callout-info">
    <?php
    $text = 'The import process will start shortly. <br />
    While the import is running it is recommended you leave this page as it is and wait for the import to finish.<br />
    The importer runs in batches of {subscribersPerBatch} subscribers with a pause of {pause} seconds between the batches, therefore 
    the import process might take a while depending on your file size and number of subscribers to import.<br />
    Please note, the subscribers number is aproximate if your text file has empty lines or ends with an empty line or it has duplicate emails(case in which the subscriber will be updated).<br />
    This is a tedious process, so sit tight and wait for it to finish.';
    echo Yii::t('list_import', StringHelper::normalizeTranslationString($text), array(
        '{subscribersPerBatch}' => $importAtOnce,
        '{pause}' => $pause,
    ));
    ?>
</div>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('import') . Yii::t('list_import', 'Text file import progress');?> 
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('back') . Yii::t('list_import', 'Back to import options'), array('list_import/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back')));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body" id="text-import" data-model="<?php echo $import->modelName;?>" data-pause="<?php echo (int)$pause;?>" data-iframe="<?php echo $this->createUrl('list_import/ping');?>" data-attributes='<?php echo CJSON::encode($import->attributes);?>'>
        <span class="counters">
            <?php echo Yii::t('list_import', 'From a total of {total} subscribers, so far {totalProcessed} have been processed, {successfullyProcessed} successfully and {errorProcessing} with errors. {percentage} completed.', array(
                '{total}'                   => '<span class="total">0</span>',
                '{totalProcessed}'          => '<span class="total-processed">0</span>',
                '{successfullyProcessed}'   => '<span class="success">0</span>',
                '{errorProcessing}'         => '<span class="error">0</span>',
                '{percentage}'              => '<span class="percentage">0%</span>',
            ));?>
        </span>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                <span class="sr-only">0% <?php echo Yii::t('app', 'Complete');?></span>
            </div>
        </div>
        <div class="alert alert-info log-info">
             <?php echo Yii::t('list_import', 'The import process is starting, please wait...');?>
        </div>
        <div class="log-errors"></div>
    </div>
    <div class="box-footer"></div>
</div>