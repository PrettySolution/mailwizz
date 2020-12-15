<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false 
     * in order to stop rendering the default content.
     * @since 1.3.3.1
     */
    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller'    => $this,
        'renderForm'    => true,
    )));
    
    // and render if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo IconHelper::make('fa-cog') . Yii::t('settings', 'Email blacklist settings')?></h3>
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
                            <?php echo $form->labelEx($blacklistModel, 'local_check');?>
                            <?php echo $form->dropDownList($blacklistModel, 'local_check', $blacklistModel->getCheckOptions(), $blacklistModel->getHtmlOptions('local_check')); ?>
                            <?php echo $form->error($blacklistModel, 'local_check');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($blacklistModel, 'allow_new_records');?>
                            <?php echo $form->dropDownList($blacklistModel, 'allow_new_records', $blacklistModel->getYesNoOptions(), $blacklistModel->getHtmlOptions('allow_new_records')); ?>
                            <?php echo $form->error($blacklistModel, 'allow_new_records');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($blacklistModel, 'allow_md5');?>
                            <?php echo $form->dropDownList($blacklistModel, 'allow_md5', $blacklistModel->getYesNoOptions(), $blacklistModel->getHtmlOptions('allow_md5')); ?>
                            <?php echo $form->error($blacklistModel, 'allow_md5');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($blacklistModel, 'reconfirm_blacklisted_subscribers_on_blacklist_delete');?>
                            <?php echo $form->dropDownList($blacklistModel, 'reconfirm_blacklisted_subscribers_on_blacklist_delete', $blacklistModel->getYesNoOptions(), $blacklistModel->getHtmlOptions('reconfirm_blacklisted_subscribers_on_blacklist_delete')); ?>
                            <?php echo $form->error($blacklistModel, 'reconfirm_blacklisted_subscribers_on_blacklist_delete');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-regex', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                            <?php echo $form->labelEx($blacklistModel, 'regular_expressions');?>
                            <?php echo $form->textArea($blacklistModel, 'regular_expressions', $blacklistModel->getHtmlOptions('regular_expressions', array('rows' => 10))); ?>
                            <?php echo $form->error($blacklistModel, 'regular_expressions');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
				            <?php echo $form->labelEx($blacklistModel, 'regex_test_email');?>
                            <div class="input-group">
	                            <?php echo $form->textField($blacklistModel, 'regex_test_email', $blacklistModel->getHtmlOptions('regex_test_email', array(
	                                    'name' => 'regex_test_email',
                                ))); ?>
                                <span class="input-group-btn">
                                    <button type="submit" href="javascript:;" class="btn btn-primary btn-flat"><?php echo Yii::t('settings', 'Test email(s)');?></button>
                                </span>
                            </div>
	                        <?php echo $form->error($blacklistModel, 'regex_test_email');?>
                        </div>
                    </div>
                </div>
                <hr />
                <!-- modals -->
                <div class="modal modal-info fade" id="page-info-dnsbl" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                            </div>
                            <div class="modal-body">
                                <?php echo Yii::t('settings', 'You can see a list of available DNSBL services by clicking {here}.', array(
                                    '{here}' => '<a href="http://www.dnsbl.info/dnsbl-list.php" target="_blank">'.Yii::t('app', 'here').'</a>'
                                ));?>
                                <br />
                                <?php echo Yii::t('settings', 'Please note, remote checks are usually slow and they will be even slower if you add many remote DNSBL services to check against.');?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal modal-info fade" id="page-info-regex" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                            </div>
                            <div class="modal-body">
                                - <?php echo Yii::t('settings', 'All expressions will be passed as first parameter to PHP\'s preg_match function for which you can find documentation here: {url}.', array(
                                    '{url}' => CHtml::link('http://php.net/preg_match', 'http://php.net/preg_match', array('target' => '_blank')),
                                ));?>
                                <br /><br />
                                - <?php echo Yii::t('settings', 'Make sure you enter a single expression per line. Wrongly formatted expressions might generate runtime errors in your PHP environment that can lead to application malfunction. You can use {url} for testing your regular expressions.', array(
                                    '{url}' => CHtml::link('https://regex101.com/', 'https://regex101.com/', array('target' => '_blank'))
                                ));?>
                            </div>
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
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <div class="box box-primary borderless">
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <?php 
        $this->endWidget(); 
    }
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.3.1
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
    ?>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));