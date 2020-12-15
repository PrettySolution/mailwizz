<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
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
        <div class="login-box-body">
            <p class="login-box-msg"><?php echo Yii::t('app', 'Register');?></p>
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
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'first_name');?>
                        <?php echo $form->textField($model, 'first_name', $model->getHtmlOptions('first_name')); ?>
                        <?php echo $form->error($model, 'first_name');?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'last_name');?>
                        <?php echo $form->textField($model, 'last_name', $model->getHtmlOptions('last_name')); ?>
                        <?php echo $form->error($model, 'last_name');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'email');?>
                        <?php echo $form->emailField($model, 'email', $model->getHtmlOptions('email')); ?>
                        <?php echo $form->error($model, 'email');?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'confirm_email');?>
                        <?php echo $form->emailField($model, 'confirm_email', $model->getHtmlOptions('confirm_email')); ?>
                        <?php echo $form->error($model, 'confirm_email');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'fake_password');?>
                        <?php echo $form->passwordField($model, 'fake_password', $model->getHtmlOptions('fake_password')); ?>
                        <?php echo $form->error($model, 'fake_password');?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'confirm_password');?>
                        <?php echo $form->passwordField($model, 'confirm_password', $model->getHtmlOptions('confirm_password')); ?>
                        <?php echo $form->error($model, 'confirm_password');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'birthDate');?>
                        <?php echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'     => $model,
                            'attribute' => 'birthDate',
                            'cssFile'   => null,
                            'language'  => $model->getDatePickerLanguage(),
                            'options'   => array(
                                'showAnim'    => 'fold',
                                'dateFormat'  => $model->getDatePickerFormat(),
                                'changeYear'  => true,
                                'changeMonth' => true,
                                'defaultDate' => sprintf('-%dy', (int)Yii::app()->options->get('system.customer_registration.minimum_age', 16)),
                                'maxDate'     => sprintf('-%dy', (int)Yii::app()->options->get('system.customer_registration.minimum_age', 16)),
                                'yearRange'   => '-100:+0',
                            ),
                            'htmlOptions' => $model->getHtmlOptions('birthDate'),
                        ), true); ?>
                        <?php echo $form->error($model, 'birthDate');?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($model, 'timezone');?>
                        <?php echo $form->dropDownList($model, 'timezone', $model->getTimeZonesArray(), $model->getHtmlOptions('timezone')); ?>
                        <?php echo $form->error($model, 'timezone');?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
			            <?php echo $form->labelEx($model, 'phone');?>
			            <?php echo $form->textField($model, 'phone', $model->getHtmlOptions('phone')); ?>
			            <?php echo $form->error($model, 'phone');?>
                    </div>
                </div>
            </div>
            <?php if($companyRequired) { ?>
                <hr />
                <h4><?php echo Yii::t('customers', 'Company info');?></h4>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'name');?>
                            <?php echo $form->textField($company, 'name', $company->getHtmlOptions('name')); ?>
                            <?php echo $form->error($company, 'name');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'country_id');?>
                            <?php echo $company->getCountriesDropDown(array(
                                'data-zones-by-country-url' => Yii::app()->createUrl('guest/zones_by_country'),
                            )); ?>
                            <?php echo $form->error($company, 'country_id');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'address_1');?>
                            <?php echo $form->textField($company, 'address_1', $company->getHtmlOptions('address_1')); ?>
                            <?php echo $form->error($company, 'address_1');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'zone_id');?>
                            <?php echo $company->getZonesDropDown(); ?>
                            <?php echo $form->error($company, 'zone_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'city');?>
                            <?php echo $form->textField($company, 'city', $company->getHtmlOptions('city')); ?>
                            <?php echo $form->error($company, 'city');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'zip_code');?>
                            <?php echo $form->textField($company, 'zip_code', $company->getHtmlOptions('zip_code')); ?>
                            <?php echo $form->error($company, 'zip_code');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'phone');?>
                            <?php echo $form->textField($company, 'phone', $company->getHtmlOptions('phone')); ?>
                            <?php echo $form->error($company, 'phone');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'vat_number');?>
                            <?php echo $form->textField($company, 'vat_number', $company->getHtmlOptions('vat_number')); ?>
                            <?php echo $form->error($company, 'vat_number');?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <hr />
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo $form->checkBox($model, 'tc_agree', $model->getHtmlOptions('tc_agree', array('class' => '', 'uncheckValue' => null))); ?>
                        <label>
                            <?php echo Yii::t('customers', 'I agree with the specified {terms}', array(
                                '{terms}' => CHtml::link(Yii::t('customers', 'Terms and conditions'), Yii::app()->options->get('system.customer_registration.tc_url', 'javascript:;'), array('target' => '_blank')),
                            ))?>
                        </label>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($model, 'tc_agree');?>
                    </div>
                    
                    <?php if (!empty($newsletterApiEnabled) && !empty($newsletterApiConsentText)) { ?>
                    <div class="form-group">
                        <?php echo $form->checkBox($model, 'newsletter_consent', $model->getHtmlOptions('newsletter_consent', array(
                            'class'         => '', 
                            'uncheckValue'  => null,
                            'value'         => $newsletterApiConsentText,
                        ))); ?>
                        <?php echo $newsletterApiConsentText;?>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($model, 'newsletter_consent');?>
                    </div>
                    <?php } ?>
                    
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="pull-left">
                        <a href="<?php echo $this->createUrl('guest/index')?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('prev') . '&nbsp;' .Yii::t('app', 'Go to login');?></a>
                    </div>
                    <div class="pull-right">
                        <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('fa-user') . '&nbsp;' .Yii::t('app', 'Register');?></button>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <?php if (!empty($facebookEnabled) || !empty($twitterEnabled)) { ?>
                        <hr />
                        <div class="pull-left">
                            <?php if (!empty($facebookEnabled)) { ?>
                                <a href="<?php echo $this->createUrl('guest/facebook')?>" class="btn btn-success btn-flat btn-facebook"><i class="fa fa-facebook-square"></i> <?php echo Yii::t('app', 'Login with Facebook');?></a>
                            <?php } ?>
                            <?php if (!empty($twitterEnabled)) { ?>
                                <a href="<?php echo $this->createUrl('guest/twitter')?>" class="btn btn-success btn-flat btn-twitter"><i class="fa fa-twitter-square"></i> <?php echo Yii::t('app', 'Login with Twitter');?></a>
                            <?php } ?>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    <?php } ?>
                </div>
            </div>
            <?php
            /**
             * This hook gives a chance to append content after the active form fields.
             * Please note that from inside the action callback you can access all the controller view variables
             * via {@CAttributeCollection $collection->controller->data}
             *
             * @since 1.3.3.1
             */
            $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                'controller'    => $this,
                'form'          => $form
            )));
            ?>
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