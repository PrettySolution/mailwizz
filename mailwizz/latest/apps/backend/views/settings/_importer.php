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
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('fa-cog') . Yii::t('settings', 'Importer settings')?></h3>
        </div>
        <div class="pull-right">
            <?php echo HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('settings/import_export'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
        </div>
        <div class="clearfix"><!-- --></div>
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
            'controller'    => $this,
            'form'          => $form
        )));
        ?>
        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'enabled');?>
                    <?php echo $form->dropDownList($importModel, 'enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('enabled')); ?>
                    <?php echo $form->error($importModel, 'enabled');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'web_enabled');?>
                    <?php echo $form->dropDownList($importModel, 'web_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('web_enabled')); ?>
                    <?php echo $form->error($importModel, 'web_enabled');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'file_size_limit');?>
                    <?php echo $form->dropDownList($importModel, 'file_size_limit', $importModel->getFileSizeOptions(), $importModel->getHtmlOptions('file_size_limit')); ?>
                    <?php echo $form->error($importModel, 'file_size_limit');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'memory_limit');?>
                    <?php echo $form->dropDownList($importModel, 'memory_limit', $importModel->getMemoryLimitOptions(), $importModel->getHtmlOptions('memory_limit')); ?>
                    <?php echo $form->error($importModel, 'memory_limit');?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'import_at_once');?>
                    <?php echo $form->textField($importModel, 'import_at_once', $importModel->getHtmlOptions('import_at_once')); ?>
                    <?php echo $form->error($importModel, 'import_at_once');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'pause');?>
                    <?php echo $form->numberField($importModel, 'pause', $importModel->getHtmlOptions('pause')); ?>
                    <?php echo $form->error($importModel, 'pause');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($importModel, 'check_mime_type');?>
                    <?php echo $form->dropDownList($importModel, 'check_mime_type', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('check_mime_type')); ?>
                    <?php echo $form->error($importModel, 'check_mime_type');?>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-import', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($importModel, 'cli_enabled');?>
                    <?php echo $form->dropDownList($importModel, 'cli_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('cli_enabled')); ?>
                    <?php echo $form->error($importModel, 'cli_enabled');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-url-import', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($importModel, 'url_enabled');?>
                    <?php echo $form->dropDownList($importModel, 'url_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('url_enabled')); ?>
                    <?php echo $form->error($importModel, 'url_enabled');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-suppression-list-cli-import', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($importModel, 'suppression_list_cli_enabled');?>
                    <?php echo $form->dropDownList($importModel, 'suppression_list_cli_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('suppression_list_cli_enabled')); ?>
                    <?php echo $form->error($importModel, 'suppression_list_cli_enabled');?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="form-group">
                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-email-blacklist-cli-import', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                    <?php echo $form->labelEx($importModel, 'email_blacklist_cli_enabled');?>
                    <?php echo $form->dropDownList($importModel, 'email_blacklist_cli_enabled', $importModel->getYesNoOptions(), $importModel->getHtmlOptions('email_blacklist_cli_enabled')); ?>
                    <?php echo $form->error($importModel, 'email_blacklist_cli_enabled');?>
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
            'controller'    => $this,
            'form'          => $form
        )));
        ?>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-import" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('settings', 'The command line importer(CLI) is used to queue import files to be processed from the command line instead of having customers wait for the import to finish in the browser.');?><br />
                        <?php echo Yii::t('settings', 'Please note that in order for the command line importer to work, after you enable it, you need to add the following cron job, which runs once at 5 minutes:');?><br />
                        <b>*/5 * * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php list-import folder >/dev/null 2>&1 </b>
                    </div>
                </div>
            </div>
        </div>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-suppression-list-cli-import" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('settings', 'The command line importer(CLI) is used to queue import files to be processed from the command line instead of having customers wait for the import to finish in the browser.');?><br />
                        <?php echo Yii::t('settings', 'Please note that in order for the command line importer to work, after you enable it, you need to add the following cron job, which runs once at 5 minutes:');?><br />
                        <b>*/5 * * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php suppression-list-import folder >/dev/null 2>&1 </b>
                    </div>
                </div>
            </div>
        </div>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-email-blacklist-cli-import" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('settings', 'The command line importer(CLI) is used to queue import files to be processed from the command line instead of having users wait for the import to finish in the browser.');?><br />
                        <?php echo Yii::t('settings', 'Please note that in order for the command line importer to work, after you enable it, you need to add the following cron job, which runs once at 5 minutes:');?><br />
                        <b>*/5 * * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php email-blacklist-import folder >/dev/null 2>&1 </b>
                    </div>
                </div>
            </div>
        </div>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-url-import" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('settings', 'The url importer is used to import subscribers in lists from remote urls, on a recurring basis, i.e: once a day.');?><br />
                        <?php echo Yii::t('settings', 'Please note that in order for this to work, the CLI importer has to be enabled as well and you need to add the following cron job, which runs once a day:');?><br />
                        <b>0 0 * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php list-import url >/dev/null 2>&1 </b>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<hr />
