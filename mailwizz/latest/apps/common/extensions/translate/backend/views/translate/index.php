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
                <?php echo IconHelper::make('glyphicon-plus-sign') . Yii::t('ext_translate', 'Translation extension');?>
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="row">
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'enabled');?>
                     <?php echo $form->dropDownList($model, 'enabled', $model->getOptionsDropDown(), $model->getHtmlOptions('enable')); ?>
                     <?php echo $form->error($model, 'enabled');?>
                 </div>
             </div>
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'translate_extensions');?>
                     <?php echo $form->dropDownList($model, 'translate_extensions', $model->getOptionsDropDown(), $model->getHtmlOptions('translate_extensions')); ?>
                     <?php echo $form->error($model, 'translate_extensions');?>
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
                <?php echo Yii::t('ext_translate', 'Once enabled, the translate extension will start collecting messages from the application and write them in files if the message is missing from file and the application language is other than english.');?><br />
            </div>
        </div>
    </div>
</div>
