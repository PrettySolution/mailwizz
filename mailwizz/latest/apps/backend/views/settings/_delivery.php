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
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('fa-cog') . Yii::t('settings', 'Delivery settings')?></h3>
        </div>
        <div class="pull-right">
            
        </div>
    </div>
    <div class="box-body">
        <?php
        /**
         * This hook gives a chance to prepend content before the active form fields.
         * Please note that from inside the action callback you can access all the controller view variables
         * via {@CAttributeCollection $collection->controller->data}
         * @since 1.3.3.1
         */
        $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
            'controller'        => $this,
            'form'              => $form
        )));
        ?>
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'memory_limit');?>
                    <?php echo $form->dropDownList($cronDeliveryModel, 'memory_limit', $cronDeliveryModel->getMemoryLimitOptions(), $cronDeliveryModel->getHtmlOptions('memory_limit', array('data-placement' => 'right'))); ?>
                    <?php echo $form->error($cronDeliveryModel, 'memory_limit');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'auto_adjust_campaigns_at_once');?>
                    <?php echo $form->dropDownList($cronDeliveryModel, 'auto_adjust_campaigns_at_once', $cronDeliveryModel->getYesNoOptions(), $cronDeliveryModel->getHtmlOptions('auto_adjust_campaigns_at_once')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'auto_adjust_campaigns_at_once');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'campaigns_at_once');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'campaigns_at_once', $cronDeliveryModel->getHtmlOptions('campaigns_at_once')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'campaigns_at_once');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'subscribers_at_once');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'subscribers_at_once', $cronDeliveryModel->getHtmlOptions('subscribers_at_once')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'subscribers_at_once');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'send_at_once');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'send_at_once', $cronDeliveryModel->getHtmlOptions('send_at_once')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'send_at_once');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'pause');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'pause', $cronDeliveryModel->getHtmlOptions('pause')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'pause');?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'emails_per_minute');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'emails_per_minute', $cronDeliveryModel->getHtmlOptions('emails_per_minute')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'emails_per_minute');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'change_server_at');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'change_server_at', $cronDeliveryModel->getHtmlOptions('change_server_at')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'change_server_at');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'max_bounce_rate');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'max_bounce_rate', $cronDeliveryModel->getHtmlOptions('max_bounce_rate', array(
	                    'step' => '0.01',
	                    'min'  => '-1',
	                    'max'  => '100',
                    ))); ?>
                    <?php echo $form->error($cronDeliveryModel, 'max_bounce_rate');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
			        <?php echo $form->labelEx($cronDeliveryModel, 'max_complaint_rate');?>
			        <?php echo $form->numberField($cronDeliveryModel, 'max_complaint_rate', $cronDeliveryModel->getHtmlOptions('max_complaint_rate', array(
				        'step' => '0.01',
				        'min'  => '-1',
				        'max'  => '100',
			        ))); ?>
			        <?php echo $form->error($cronDeliveryModel, 'max_complaint_rate');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'retry_failed_sending');?>
                    <?php echo $form->dropDownList($cronDeliveryModel, 'retry_failed_sending', $cronDeliveryModel->getYesNoOptions(), $cronDeliveryModel->getHtmlOptions('retry_failed_sending')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'retry_failed_sending');?>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-pcntl', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($cronDeliveryModel, 'use_pcntl');?>
                    <?php echo $form->dropDownList($cronDeliveryModel, 'use_pcntl', $cronDeliveryModel->getYesNoOptions(), $cronDeliveryModel->getHtmlOptions('use_pcntl')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'use_pcntl');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'campaigns_in_parallel');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'campaigns_in_parallel', $cronDeliveryModel->getHtmlOptions('campaigns_in_parallel')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'campaigns_in_parallel');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($cronDeliveryModel, 'subscriber_batches_in_parallel');?>
                    <?php echo $form->numberField($cronDeliveryModel, 'subscriber_batches_in_parallel', $cronDeliveryModel->getHtmlOptions('subscriber_batches_in_parallel')); ?>
                    <?php echo $form->error($cronDeliveryModel, 'subscriber_batches_in_parallel');?>
                </div>
            </div>
        </div>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-pcntl" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('settings', 'You can use below settings to increase the delivery speed. Please be aware that wrong changes might have undesired results.');?>
                        <br />
                        <strong><?php echo Yii::t('settings', 'Also note that below will apply only if you have installed and enabled PHP\'s PCNTL extension on your server. If you are not sure if your server has the extension, ask your hosting.');?></strong>
                    </div>
                </div>
            </div>
        </div>
        <?php
        /**
         * This hook gives a chance to append content after the active form fields.
         * Please note that from inside the action callback you can access all the controller view variables
         * via {@CAttributeCollection $collection->controller->data}
         * @since 1.3.3.1
         */
        $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
            'controller'        => $this,
            'form'              => $form
        )));
        ?>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<hr />
