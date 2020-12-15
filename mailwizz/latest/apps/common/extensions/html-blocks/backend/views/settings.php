<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
?>

<?php $form = $this->beginWidget('CActiveForm'); ?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-html5') .  $pageHeading;?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-customer-footer', array('class' => 'btn btn-primary btn-flat btn-xs', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($model, 'customer_footer');?>
                    <?php echo $form->textArea($model, 'customer_footer', $model->getHtmlOptions('customer_footer')); ?>
                    <?php echo $form->error($model, 'customer_footer');?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-submit"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>

<!-- modals -->
<div class="modal modal-info fade" id="page-info-customer-footer" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $model->getAttributeHelpText('customer_footer');?>
            </div>
        </div>
    </div>
</div>

