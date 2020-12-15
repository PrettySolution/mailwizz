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
                <?php echo IconHelper::make('glyphicon-plus-sign') . Yii::t('ext_campaign_activity_map', 'Campaign activity map');?>
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="row">
             <div class="col-lg-4">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'show_opens_map');?>
                     <?php echo $form->dropDownList($model, 'show_opens_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_opens_map')); ?>
                     <?php echo $form->error($model, 'show_opens_map');?>
                 </div>
             </div>
             <div class="col-lg-4">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'show_clicks_map');?>
                     <?php echo $form->dropDownList($model, 'show_clicks_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_clicks_map')); ?>
                     <?php echo $form->error($model, 'show_clicks_map');?>
                 </div>
             </div>
             <div class="col-lg-4">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'show_unsubscribes_map');?>
                     <?php echo $form->dropDownList($model, 'show_unsubscribes_map', $model->getOptionsDropDown(), $model->getHtmlOptions('show_unsubscribes_map')); ?>
                     <?php echo $form->error($model, 'show_unsubscribes_map');?>
                 </div>
             </div>
         </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'opens_at_once');?>
                    <?php echo $form->textField($model, 'opens_at_once', $model->getHtmlOptions('opens_at_once')); ?>
                    <?php echo $form->error($model, 'opens_at_once');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'clicks_at_once');?>
                    <?php echo $form->textField($model, 'clicks_at_once', $model->getHtmlOptions('clicks_at_once')); ?>
                    <?php echo $form->error($model, 'clicks_at_once');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'unsubscribes_at_once');?>
                    <?php echo $form->textField($model, 'unsubscribes_at_once', $model->getHtmlOptions('unsubscribes_at_once')); ?>
                    <?php echo $form->error($model, 'unsubscribes_at_once');?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'google_maps_api_key');?>
                    <?php echo $form->textField($model, 'google_maps_api_key', $model->getHtmlOptions('google_maps_api_key')); ?>
                    <?php echo $form->error($model, 'google_maps_api_key');?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>

<!-- modals -->
<div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('ext_campaign_activity_map', 'Decide whether to show various maps in the campaign overview area.');?><br />
            </div>
        </div>
    </div>
</div>
