<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.4.3
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    $this->renderPartial('_customers_tabs');
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false 
     * in order to stop rendering the default content.
     * @since 1.3.4.3
     */
    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller'    => $this,
        'renderForm'    => true,
    )));
    
    // and render if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm'); 
        ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Customer registration')?></h3>
            </div>
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
                            <?php echo $form->labelEx($model, 'default_group');?>
                            <?php echo $form->dropDownList($model, 'default_group', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $model->getGroupsList()), $model->getHtmlOptions('default_group')); ?>
                            <?php echo $form->error($model, 'default_group');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'unconfirm_days_removal');?>
                            <?php echo $form->numberField($model, 'unconfirm_days_removal', $model->getHtmlOptions('unconfirm_days_removal')); ?>
                            <?php echo $form->error($model, 'unconfirm_days_removal');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'require_approval');?>
                            <?php echo $form->dropDownList($model, 'require_approval', $model->getYesNoOptions(), $model->getHtmlOptions('require_approval')); ?>
                            <?php echo $form->error($model, 'require_approval');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'require_email_confirmation');?>
                            <?php echo $form->dropDownList($model, 'require_email_confirmation', $model->getYesNoOptions(), $model->getHtmlOptions('require_email_confirmation')); ?>
                            <?php echo $form->error($model, 'require_email_confirmation');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'company_required');?>
                            <?php echo $form->dropDownList($model, 'company_required', $model->getYesNoOptions(), $model->getHtmlOptions('company_required')); ?>
                            <?php echo $form->error($model, 'company_required');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                       <div class="form-group">
                           <?php echo $form->labelEx($model, 'tc_url');?>
                           <?php echo $form->textField($model, 'tc_url', $model->getHtmlOptions('tc_url')); ?>
                           <?php echo $form->error($model, 'tc_url');?>
                       </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'send_email_method');?>
                            <?php echo $form->dropDownList($model, 'send_email_method', $model->getSendEmailMethods(), $model->getHtmlOptions('send_email_method')); ?>
                            <?php echo $form->error($model, 'send_email_method');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'new_customer_registration_notification_to');?>
                            <?php echo $form->textField($model, 'new_customer_registration_notification_to', $model->getHtmlOptions('new_customer_registration_notification_to')); ?>
                            <?php echo $form->error($model, 'new_customer_registration_notification_to');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'default_country');?>
                            <?php echo $form->dropDownList($model, 'default_country', CMap::mergeArray(array('' => ''), Country::getAsDropdownOptions()), $model->getHtmlOptions('default_country')); ?>
                            <?php echo $form->error($model, 'default_country');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'default_timezone');?>
                            <?php echo $form->dropDownList($model, 'default_timezone', CMap::mergeArray(array('' => ''), DateTimeHelper::getTimeZones()), $model->getHtmlOptions('default_timezone')); ?>
                            <?php echo $form->error($model, 'default_timezone');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'minimum_age');?>
                            <?php echo $form->textField($model, 'minimum_age', $model->getHtmlOptions('minimum_age')); ?>
                            <?php echo $form->error($model, 'minimum_age');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'forbidden_domains');?>
                            <?php echo $form->textArea($model, 'forbidden_domains', $model->getHtmlOptions('forbidden_domains')); ?>
                            <?php echo $form->error($model, 'forbidden_domains');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Send customer to email list')?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_enabled');?>
                            <?php echo $form->dropDownList($model, 'api_enabled', $model->getYesNoOptions(), $model->getHtmlOptions('api_enabled')); ?>
                            <?php echo $form->error($model, 'api_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_url');?>
                            <?php echo $form->textField($model, 'api_url', $model->getHtmlOptions('api_url')); ?>
                            <?php echo $form->error($model, 'api_url');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_public_key');?>
                            <?php echo $form->textField($model, 'api_public_key', $model->getHtmlOptions('api_public_key')); ?>
                            <?php echo $form->error($model, 'api_public_key');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_private_key');?>
                            <?php echo $form->textField($model, 'api_private_key', $model->getHtmlOptions('api_private_key')); ?>
                            <?php echo $form->error($model, 'api_private_key');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_list_uid');?>
                            <?php echo $form->textField($model, 'api_list_uid', $model->getHtmlOptions('api_list_uid')); ?>
                            <?php echo $form->error($model, 'api_list_uid');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'api_consent_text');?>
                            <?php echo $form->textField($model, 'api_consent_text', $model->getHtmlOptions('api_consent_text')); ?>
                            <?php echo $form->error($model, 'api_consent_text');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Facebook integration')?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'facebook_enabled');?>
                            <?php echo $form->dropDownList($model, 'facebook_enabled', $model->getYesNoOptions(), $model->getHtmlOptions('facebook_enabled')); ?>
                            <?php echo $form->error($model, 'facebook_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'facebook_app_id');?>
                            <?php echo $form->textField($model, 'facebook_app_id', $model->getHtmlOptions('facebook_app_id')); ?>
                            <?php echo $form->error($model, 'facebook_app_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'facebook_app_secret');?>
                            <?php echo $form->textField($model, 'facebook_app_secret', $model->getHtmlOptions('facebook_app_secret')); ?>
                            <?php echo $form->error($model, 'facebook_app_secret');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Twitter integration')?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'twitter_enabled');?>
                            <?php echo $form->dropDownList($model, 'twitter_enabled', $model->getYesNoOptions(), $model->getHtmlOptions('twitter_enabled')); ?>
                            <?php echo $form->error($model, 'twitter_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'twitter_app_consumer_key');?>
                            <?php echo $form->textField($model, 'twitter_app_consumer_key', $model->getHtmlOptions('twitter_app_consumer_key')); ?>
                            <?php echo $form->error($model, 'twitter_app_consumer_key');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'twitter_app_consumer_secret');?>
                            <?php echo $form->textField($model, 'twitter_app_consumer_secret', $model->getHtmlOptions('twitter_app_consumer_secret')); ?>
                            <?php echo $form->error($model, 'twitter_app_consumer_secret');?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Welcome email')?></h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'welcome_email');?>
                            <?php echo $form->dropDownList($model, 'welcome_email', $model->getYesNoOptions(), $model->getHtmlOptions('welcome_email')); ?>
                            <?php echo $form->error($model, 'welcome_email');?>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="form">
                            <?php echo $form->labelEx($model, 'welcome_email_subject');?>
                            <?php echo $form->textField($model, 'welcome_email_subject', $model->getHtmlOptions('welcome_email_subject')); ?>
                            <?php echo $form->error($model, 'welcome_email_subject');?>
                        </div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-welcome-email-content', array('class' => 'btn btn-primary btn-xs btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                            <?php echo $form->labelEx($model, 'welcome_email_content');?>
                            <?php echo $form->textArea($model, 'welcome_email_content', $model->getHtmlOptions('welcome_email_content', array('rows' => 20))); ?>
                            <?php echo $form->error($model, 'welcome_email_content');?>
                        </div>
                    </div>
                </div>
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
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info-welcome-email-content" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo $model->getAttributeHelpText('welcome_email_content');?>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        $this->endWidget(); 
    }
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.4.3
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.4.3
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));