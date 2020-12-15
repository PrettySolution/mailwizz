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
    <div class="box-body">
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'campaign_emails');?>
                    <?php echo $form->dropDownList($model, 'campaign_emails', $model->getYesNoOptions(), $model->getHtmlOptions('campaign_emails')); ?>
                    <?php echo $form->error($model, 'campaign_emails');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'campaign_test_emails');?>
                    <?php echo $form->dropDownList($model, 'campaign_test_emails', $model->getYesNoOptions(), $model->getHtmlOptions('campaign_test_emails')); ?>
                    <?php echo $form->error($model, 'campaign_test_emails');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'template_test_emails');?>
                    <?php echo $form->dropDownList($model, 'template_test_emails', $model->getYesNoOptions(), $model->getHtmlOptions('template_test_emails')); ?>
                    <?php echo $form->error($model, 'template_test_emails');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'list_emails');?>
                    <?php echo $form->dropDownList($model, 'list_emails', $model->getYesNoOptions(), $model->getHtmlOptions('list_emails')); ?>
                    <?php echo $form->error($model, 'list_emails');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'transactional_emails');?>
                    <?php echo $form->dropDownList($model, 'transactional_emails', $model->getYesNoOptions(), $model->getHtmlOptions('transactional_emails')); ?>
                    <?php echo $form->error($model, 'transactional_emails');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'campaign_giveup_emails');?>
                    <?php echo $form->dropDownList($model, 'campaign_giveup_emails', $model->getYesNoOptions(), $model->getHtmlOptions('campaign_giveup_emails')); ?>
                    <?php echo $form->error($model, 'campaign_giveup_emails');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>