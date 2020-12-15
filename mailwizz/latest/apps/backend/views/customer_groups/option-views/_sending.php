<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
 ?>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-sending', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota');?>
                    <?php echo $form->numberField($model, 'quota', $model->getHtmlOptions('quota')); ?>
                    <?php echo $form->error($model, 'quota');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota_time_value');?>
                    <?php echo $form->numberField($model, 'quota_time_value', $model->getHtmlOptions('quota_time_value')); ?>
                    <?php echo $form->error($model, 'quota_time_value');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota_time_unit');?>
                    <?php echo $form->dropDownList($model, 'quota_time_unit', $model->getTimeUnits(), $model->getHtmlOptions('quota_time_unit')); ?>
                    <?php echo $form->error($model, 'quota_time_unit');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota_wait_expire');?>
                    <?php echo $form->dropDownList($model, 'quota_wait_expire', $model->getYesNoOptions(), $model->getHtmlOptions('quota_wait_expire')); ?>
                    <?php echo $form->error($model, 'quota_wait_expire');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'action_quota_reached');?>
                            <?php echo $form->dropDownList($model, 'action_quota_reached', $model->getActionsQuotaReached(), $model->getHtmlOptions('action_quota_reached')); ?>
                            <?php echo $form->error($model, 'action_quota_reached');?>
                        </div>
                    </div>
                    <div class="col-lg-6 move-to-group-id" style="display: <?php echo $model->action_quota_reached == 'move-in-group' ? 'block' : 'none';?>;">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'move_to_group_id');?>
                            <?php echo $form->dropDownList($model, 'move_to_group_id', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $model->getGroupsList()), $model->getHtmlOptions('move_to_group_id')); ?>
                            <?php echo $form->error($model, 'move_to_group_id');?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'hourly_quota');?>
                    <?php echo $form->numberField($model, 'hourly_quota', $model->getHtmlOptions('hourly_quota')); ?>
                    <?php echo $form->error($model, 'hourly_quota');?>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota_notify_enabled');?>
                    <?php echo $form->dropDownList($model, 'quota_notify_enabled', $model->getYesNoOptions(), $model->getHtmlOptions('quota_notify_enabled')); ?>
                    <?php echo $form->error($model, 'quota_notify_enabled');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'quota_notify_percent');?>
                    <?php echo $form->numberField($model, 'quota_notify_percent', $model->getHtmlOptions('quota_notify_percent')); ?>
                    <?php echo $form->error($model, 'quota_notify_percent');?>
                </div>
            </div>
            <div class="clearfix"><!-- --></div>
            <div class="col-lg-12">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-quota-email-template', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($model, 'quota_notify_email_content');?>
                    <?php echo $form->textArea($model, 'quota_notify_email_content', $model->getHtmlOptions('quota_notify_email_content')); ?>
                    <?php echo $form->error($model, 'quota_notify_email_content');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>
<!-- modals -->
<div class="modal modal-info fade" id="page-info-sending" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('settings', 'A sending quota of 1000 with a time value of 1 and a time unit of Day means the customer is able to send 1000 emails during 1 day.');?>
                <br />
                <?php echo Yii::t('settings', 'If waiting is enabled and the customer sends all emails in an hour, he will wait 23 more hours until the specified action is taken.');?>
                <br />
                <?php echo Yii::t('settings', 'However, if the waiting is disabled, the action will be taken immediatly.');?>
                <br />
                <?php echo Yii::t('settings', 'You can find a more detailed explanation for these settings {here}.', array(
                    '{here}' => CHtml::link(Yii::t('settings', 'here'), Yii::app()->hooks->applyFilters('customer_sending_explanation_url', 'https://kb.mailwizz.com/articles/understanding-sending-quota-limits-work/') , array('target' => '_blank')),
                ));?>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-info fade" id="page-info-quota-email-template" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('settings', 'Following placeholders are available:');?>
                <div style="width:100%; max-height: 100px; overflow:scroll">
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[FIRST_NAME]</a>
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[LAST_NAME]</a>
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[FULL_NAME]</a>
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[QUOTA_TOTAL]</a>
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[QUOTA_USAGE]</a>
                    <a href="javascript:;" class="btn btn-primary btn-xs btn-flat">[QUOTA_USAGE_PERCENT]</a>
                </div>
            </div>
        </div>
    </div>
</div>