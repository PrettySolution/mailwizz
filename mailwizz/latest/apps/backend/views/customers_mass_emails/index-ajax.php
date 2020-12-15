<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
?>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-send') .  $pageHeading;?> 
            </h3>
        </div>
        <div class="pull-right">
            <?php echo HtmlHelper::accessLink(Yii::t('app', 'Back'), array('customers_mass_emails/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back')));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body" id="customers-mass-email-box" data-attrs='<?php echo $jsonAttributes;?>'>
        <span class="counters">
            <?php echo Yii::t('customers', 'From a total of {total} customers, so far {processed} have been processed. {percentage} completed.', array(
                '{total}'       => '<span class="total" data-bind="text: total">0</span>',
                '{processed}'   => '<span class="total-processed" data-bind="text: processed">0</span>',
                '{percentage}'  => '<span class="percentage" data-bind="text: percentage">0</span>%',
            ));?>
        </span>
        <div class="progress progress-striped active">
            <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%" data-bind="style: {width: widthPercentage()}">
                <span class="sr-only"><span data-bind="text: percentage">0</span>% <?php echo Yii::t('app', 'Complete');?></span>
            </div>
        </div>
        <div class="alert alert-info log-info" data-bind="html: progressText"><?php echo Yii::t('customers', 'Please wait, queueing messages...');?></div>
        <div class="log-errors"></div>
    </div>
    <div class="box-footer"></div>
</div>