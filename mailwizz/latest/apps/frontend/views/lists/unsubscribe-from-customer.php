<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.4
 */

?>

<div class="row">
    <div class="col-lg-6 col-lg-push-3 col-md-6 col-md-push-3 col-sm-12">
        <?php $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('lists', 'Unsubscribe'); ?></h3>
            </div>
            <div class="box-body">
                <div class="callout callout-info">
                    <?php echo Yii::t('lists', 'This action will unsubscribe you from all the email lists belonging to this customer!');?><br />
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($subscriber, 'email');?>
                            <?php echo $form->textField($subscriber, 'email', $subscriber->getHtmlOptions('email')); ?>
                            <?php echo $form->error($subscriber, 'email');?>
                        </div>
                    </div>
                </div>
                <?php echo $reasonField; ?>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <?php echo CHtml::submitButton(Yii::t('lists', 'Unsubscribe'), array('class' => 'btn btn-primary btn-flat')); ?>
                </div>
                <div class="clearfix"> </div>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>


