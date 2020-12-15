<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */
 
 ?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo Yii::t('settings', 'Tracking domains')?> </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-tracking-domains', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_manage_tracking_domains');?>
                    <?php echo $form->dropDownList($model, 'can_manage_tracking_domains', $model->getYesNoOptions(), $model->getHtmlOptions('can_manage_tracking_domains')); ?>
                    <?php echo $form->error($model, 'can_manage_tracking_domains');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_select_for_delivery_servers');?>
                    <?php echo $form->dropDownList($model, 'can_select_for_delivery_servers', $model->getYesNoOptions(), $model->getHtmlOptions('can_select_for_delivery_servers')); ?>
                    <?php echo $form->error($model, 'can_select_for_delivery_servers');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_select_for_campaigns');?>
                    <?php echo $form->dropDownList($model, 'can_select_for_campaigns', $model->getYesNoOptions(), $model->getHtmlOptions('can_select_for_campaigns')); ?>
                    <?php echo $form->error($model, 'can_select_for_campaigns');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>
<!-- modals -->
<div class="modal modal-info fade" id="page-info-tracking-domains" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('settings', 'Please note, in order for this feature to work this (sub)domain needs a dedicated IP address, otherwise all defined CNAMES for it will point to the default domain on this server.');?>
                <br />
                <strong><?php echo Yii::t('settings', 'If you do not use a dedicated IP address for this domain only or you are not sure you do so, do not enable this feature!');?></strong>
            </div>
        </div>
    </div>
</div>