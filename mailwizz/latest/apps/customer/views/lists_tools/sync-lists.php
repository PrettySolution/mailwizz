<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */
 
?>

<div class="callout callout-info">
    <?php echo $fromText; ?>
</div>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('tools') .  $pageHeading;?> 
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('back') . Yii::t('tools', 'Back to tools'), array('lists_tools/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back')));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body" id="sync-lists-box" data-attrs='<?php echo $jsonAttributes;?>'>
        <span class="counters">
            <?php echo Yii::t('list_import', 'From a total of {total} subscribers, so far {totalProcessed} have been processed, {successfullyProcessed} successfully and {errorProcessing} with errors. {percentage} completed.', array(
                '{total}'                   => '<span class="total" data-bind="text: count">0</span>',
                '{totalProcessed}'          => '<span class="total-processed" data-bind="text: processedTotal">0</span>',
                '{successfullyProcessed}'   => '<span class="success" data-bind="text: processedSuccess">0</span>',
                '{errorProcessing}'         => '<span class="error" data-bind="text: processedError">0</span>',
                '{percentage}'              => '<span class="percentage" data-bind="text: percentage">0</span>%',
            ));?>
        </span>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-bind="style: {width: widthPercentage()}">
                <span class="sr-only"><span data-bind="text: percentage">0</span>% <?php echo Yii::t('app', 'Complete');?></span>
            </div>
        </div>
        <div class="alert alert-info log-info" data-bind="html: progressText">
        </div>
        <div class="log-errors"></div>
    </div>
    <div class="box-footer"></div>
</div>