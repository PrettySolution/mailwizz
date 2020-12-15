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
        <h3 class="box-title"><?php echo IconHelper::make('fa-cog') . Yii::t('settings', 'Settings for processing Delivery and Bounce logs')?></h3>
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
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'memory_limit');?>
                    <?php echo $form->dropDownList($cronLogsModel, 'memory_limit', $cronLogsModel->getMemoryLimitOptions(), $cronLogsModel->getHtmlOptions('memory_limit', array('data-placement' => 'right'))); ?>
                    <?php echo $form->error($cronLogsModel, 'memory_limit');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'process_at_once');?>
                    <?php echo $form->numberField($cronLogsModel, 'process_at_once', $cronLogsModel->getHtmlOptions('process_at_once')); ?>
                    <?php echo $form->error($cronLogsModel, 'process_at_once');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'max_fatal_errors');?>
                    <?php echo $form->numberField($cronLogsModel, 'max_fatal_errors', $cronLogsModel->getHtmlOptions('max_fatal_errors')); ?>
                    <?php echo $form->error($cronLogsModel, 'max_fatal_errors');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'max_soft_errors');?>
                    <?php echo $form->numberField($cronLogsModel, 'max_soft_errors', $cronLogsModel->getHtmlOptions('max_soft_errors')); ?>
                    <?php echo $form->error($cronLogsModel, 'max_soft_errors');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3" style="display: none">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'max_hard_bounce');?>
                    <?php echo $form->numberField($cronLogsModel, 'max_hard_bounce', $cronLogsModel->getHtmlOptions('max_hard_bounce')); ?>
                    <?php echo $form->error($cronLogsModel, 'max_hard_bounce');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'max_soft_bounce');?>
                    <?php echo $form->numberField($cronLogsModel, 'max_soft_bounce', $cronLogsModel->getHtmlOptions('max_soft_bounce')); ?>
                    <?php echo $form->error($cronLogsModel, 'max_soft_bounce');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($cronLogsModel, 'delivery_servers_usage_logs_removal_days');?>
                    <?php echo $form->numberField($cronLogsModel, 'delivery_servers_usage_logs_removal_days', $cronLogsModel->getHtmlOptions('delivery_servers_usage_logs_removal_days')); ?>
                    <?php echo $form->error($cronLogsModel, 'delivery_servers_usage_logs_removal_days');?>
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
        <div class="row">
            <div class="col-lg-12">
                <div class="pull-right">
                    <a href="#errors-explained-modal" data-toggle="modal" class="btn btn-primary btn-flat"><?php echo Yii::t('app', 'Errors explained');?></a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="errors-explained-modal" tabindex="-1" role="dialog" aria-labelledby="errors-explained-modal-label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title"><?php echo Yii::t('settings', 'Errors explained');?></h4>
        </div>
        <div class="modal-body">
             <div>
                <strong><span class="badge"><?php echo Yii::t('settings', 'Fatal error');?></span></strong><br />
                <?php
                $text = '- a fatal error means a SMTP error that has a return code higher or equal to 450.<br />
                - this happens when for example the account we try to send the email does not exist on the target server, or it has been closed.<br />
                - please note there are cases when the smtp server will directly return a fatal error if your server is blacklisted there so you might want to set a higher number here to avoid this.';
                echo Yii::t('settings', StringHelper::normalizeTranslationString($text));
                ?>
                <br /><br />

                <strong><span class="badge"><?php echo Yii::t('settings', 'Soft error');?></span></strong><br />
                <?php
                $text = '- a soft error means a SMTP error that has a return code lower than 450 but higher than 2xx(which is a success code).<br />
                - this might happen if the subscriber account is temporarly disabled, or the receiving server is too busy, etc. Usually you\'ll want this option set to a high number.';
                echo Yii::t('settings', StringHelper::normalizeTranslationString($text));
                ?>
                 <br /><br />

                <strong><span class="badge"><?php echo Yii::t('settings', 'Hard bounce');?></span></strong><br />
                <?php
                $text = '- after the email has been delivered, there are chances the target server will bounce it back for several reasons.<br />
                - a hard bounce means the email does not exist anymore on the target server, or it has been blacklisted, disabled, etc.<br />
                - you should keep this option set to a lower number.';
                echo Yii::t('settings', StringHelper::normalizeTranslationString($text));
                ?>
                <br /><br />

                <strong><span class="badge"><?php echo Yii::t('settings', 'Soft bounce');?></span></strong><br />
                <?php
                $text = 'Unlike hard bounces, soft bounces can happen for reasons like server/account temporarily unavailable, not enough disk space to store the email on the server, or even the response of an autoresponder.<br />
                - you should keep this option set to a high number.';
                echo Yii::t('settings', StringHelper::normalizeTranslationString($text));
                ?>
                <br />
            </div>
        </div>
      </div>
    </div>
</div>
<hr />