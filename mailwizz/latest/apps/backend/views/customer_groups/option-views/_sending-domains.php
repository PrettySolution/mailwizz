<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
 ?>
<div class="box box-primary borderless">
    <div class="box-header">
        <h3 class="box-title"><?php echo Yii::t('settings', 'Sending domains')?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'can_manage_sending_domains');?>
                    <?php echo $form->dropDownList($model, 'can_manage_sending_domains', $model->getYesNoOptions(), $model->getHtmlOptions('can_manage_sending_domains')); ?>
                    <?php echo $form->error($model, 'can_manage_sending_domains');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'max_sending_domains');?>
                    <?php echo $form->numberField($model, 'max_sending_domains', $model->getHtmlOptions('max_sending_domains')); ?>
                    <?php echo $form->error($model, 'max_sending_domains');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>