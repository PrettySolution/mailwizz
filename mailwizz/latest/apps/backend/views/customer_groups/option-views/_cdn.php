<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.4
 */
 
 ?>
<div class="box box-primary borderless">
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'enabled');?>
                    <?php echo $form->dropDownList($model, 'enabled', $model->getYesNoOptions(), $model->getHtmlOptions('enabled')); ?>
                    <?php echo $form->error($model, 'enabled');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'subdomain');?>
                    <?php echo $form->textField($model, 'subdomain', $model->getHtmlOptions('subdomain')); ?>
                    <?php echo $form->error($model, 'subdomain');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'use_for_email_assets');?>
                    <?php echo $form->dropDownList($model, 'use_for_email_assets', $model->getYesNoOptions(), $model->getHtmlOptions('use_for_email_assets')); ?>
                    <?php echo $form->error($model, 'use_for_email_assets');?>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"><!-- --></div>
</div>