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
    <div class="box-body">
        <div class="clearfix"><!-- --></div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_surveys');?>
                    <?php echo $form->numberField($model, 'max_surveys', $model->getHtmlOptions('max_surveys')); ?>
                    <?php echo $form->error($model, 'max_surveys');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_responders');?>
                    <?php echo $form->numberField($model, 'max_responders', $model->getHtmlOptions('max_responders')); ?>
                    <?php echo $form->error($model, 'max_responders');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_responders_per_survey');?>
                    <?php echo $form->numberField($model, 'max_responders_per_survey', $model->getHtmlOptions('max_responders_per_survey')); ?>
                    <?php echo $form->error($model, 'max_responders_per_survey');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_delete_own_surveys');?>
                    <?php echo $form->dropDownList($model, 'can_delete_own_surveys', $model->getYesNoOptions(), $model->getHtmlOptions('can_delete_own_surveys')); ?>
                    <?php echo $form->error($model, 'can_delete_own_surveys');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_delete_own_responders');?>
                    <?php echo $form->dropDownList($model, 'can_delete_own_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_delete_own_responders')); ?>
                    <?php echo $form->error($model, 'can_delete_own_responders');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_edit_own_responders');?>
                    <?php echo $form->dropDownList($model, 'can_edit_own_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_edit_own_responders')); ?>
                    <?php echo $form->error($model, 'can_edit_own_responders');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_segment_surveys');?>
                    <?php echo $form->dropDownList($model, 'can_segment_surveys', $model->getYesNoOptions(), $model->getHtmlOptions('can_segment_surveys')); ?>
                    <?php echo $form->error($model, 'can_segment_surveys');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_segment_conditions');?>
                    <?php echo $form->numberField($model, 'max_segment_conditions', $model->getHtmlOptions('max_segment_conditions')); ?>
                    <?php echo $form->error($model, 'max_segment_conditions');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_segment_wait_timeout');?>
                    <?php echo $form->numberField($model, 'max_segment_wait_timeout', $model->getHtmlOptions('max_segment_wait_timeout')); ?>
                    <?php echo $form->error($model, 'max_segment_wait_timeout');?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_export_responders');?>
                    <?php echo $form->dropDownList($model, 'can_export_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_export_responders')); ?>
                    <?php echo $form->error($model, 'can_export_responders');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'show_7days_responders_activity_graph');?>
                    <?php echo $form->dropDownList($model, 'show_7days_responders_activity_graph', $model->getYesNoOptions(), $model->getHtmlOptions('show_7days_responders_activity_graph')); ?>
                    <?php echo $form->error($model, 'show_7days_responders_activity_graph');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>