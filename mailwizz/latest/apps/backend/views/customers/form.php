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
    if (!$customer->isNewRecord && $twoFaSettings->isEnabled) { ?>
        <ul class="nav nav-tabs" style="border-bottom: 0px;">
            <li class="active"><a href="<?php echo $this->createUrl('customers/update', array('id' => $customer->customer_id)); ?>"><?php echo CHtml::encode(Yii::t('app', 'Profile'));?></a></li>
            <li class="inactive"><a href="<?php echo $this->createUrl('customers/2fa', array('id' => $customer->customer_id))?>"><?php echo CHtml::encode(Yii::t('app', '2FA'));?></a></li>
        </ul>
    <?php }
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
        $form = $this->beginWidget('CActiveForm', array(
            'htmlOptions' => array('enctype' => 'multipart/form-data')
        )); 
        ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                        ->add('<h3 class="box-title">' . IconHelper::make('fa-users') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('customers/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$customer->isNewRecord)
                        ->add(HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('customers/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
                        ->render();
                    ?>
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
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'first_name');?>
                            <?php echo $form->textField($customer, 'first_name', $customer->getHtmlOptions('first_name')); ?>
                            <?php echo $form->error($customer, 'first_name');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'last_name');?>
                            <?php echo $form->textField($customer, 'last_name', $customer->getHtmlOptions('last_name')); ?>
                            <?php echo $form->error($customer, 'last_name');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'email');?>
                            <?php echo $form->emailField($customer, 'email', $customer->getHtmlOptions('email')); ?>
                            <?php echo $form->error($customer, 'email');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'confirm_email');?>
                            <?php echo $form->emailField($customer, 'confirm_email', $customer->getHtmlOptions('confirm_email')); ?>
                            <?php echo $form->error($customer, 'confirm_email');?>
                        </div>
                    </div>
                </div>     
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'fake_password');?>
                            <?php echo $form->passwordField($customer, 'fake_password', $customer->getHtmlOptions('password')); ?>
                            <?php echo $form->error($customer, 'fake_password');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'confirm_password');?>
                            <?php echo $form->passwordField($customer, 'confirm_password', $customer->getHtmlOptions('confirm_password')); ?>
                            <?php echo $form->error($customer, 'confirm_password');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'timezone');?>
                            <?php echo $form->dropDownList($customer, 'timezone', $customer->getTimeZonesArray(), $customer->getHtmlOptions('timezone')); ?>
                            <?php echo $form->error($customer, 'timezone');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'birthDate');?>
                            <?php echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model'     => $customer,
                                'attribute' => 'birthDate',
                                'cssFile'   => null,
                                'language'  => $customer->getDatePickerLanguage(),
                                'options'   => array(
                                    'showAnim'    => 'fold',
                                    'dateFormat'  => $customer->getDatePickerFormat(),
                                    'changeYear'  => true,
                                    'changeMonth' => true,
                                    'defaultDate' => sprintf('-%dy', (int)Yii::app()->options->get('system.customer_registration.minimum_age', 16)),
                                    'maxDate'     => sprintf('-%dy', (int)Yii::app()->options->get('system.customer_registration.minimum_age', 16)),
                                    'yearRange'   => '-100:+0',
                                ),
                                'htmlOptions' => $customer->getHtmlOptions('birthDate'),
                            ), true); ?>
                            <?php echo $form->error($customer, 'birthDate');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'language_id');?>
                            <?php echo $form->dropDownList($customer, 'language_id', CMap::mergeArray(array('' => Yii::t('app', 'Application default')), Language::getLanguagesArray()), $customer->getHtmlOptions('language_id')); ?>
                            <?php echo $form->error($customer, 'language_id');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'group_id');?>
                            <?php echo $form->dropDownList($customer, 'group_id', CMap::mergeArray(array('' => ''), CustomerGroup::getGroupsArray()), $customer->getHtmlOptions('group_id')); ?>
                            <?php echo $form->error($customer, 'group_id');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'status');?>
                            <?php echo $form->dropDownList($customer, 'status', $customer->getStatusesArray(), $customer->getHtmlOptions('status')); ?>
                            <?php echo $form->error($customer, 'status');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'email_details');?>
                            <?php echo $form->dropDownList($customer, 'email_details', $customer->getYesNoOptions(), $customer->getHtmlOptions('email_details')); ?>
                            <?php echo $form->error($customer, 'email_details');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
			                <?php echo $form->labelEx($customer, 'phone');?>
			                <?php echo $form->textField($customer, 'phone', $customer->getHtmlOptions('phone')); ?>
			                <?php echo $form->error($customer, 'phone');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($customer, 'inactive_at');?>
                            <?php echo $form->textField($customer, 'inactiveAt', $customer->getHtmlOptions('inactive_at')); ?>
                            <?php echo CHtml::textField('fake_inactive_at', $customer->getInactiveAt(), array(
                                'data-date-format'  => 'yyyy-mm-dd hh:ii:ss',
                                'data-autoclose'    => true,
                                'data-language'     => LanguageHelper::getAppLanguageCode(),
                                'class'             => 'form-control',
                                'style'             => 'visibility:hidden; height:1px; margin:0; padding:0;',
                            )); ?>
                            <?php echo $form->error($customer, 'inactiveAt');?>
                        </div>
                    </div>

                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-2">
                                <img src="<?php echo $customer->getAvatarUrl(90, 90);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-10">
                                <?php echo $form->labelEx($customer, 'new_avatar');?>
                                <?php echo $form->fileField($customer, 'new_avatar', $customer->getHtmlOptions('new_avatar')); ?>
                                <?php echo $form->error($customer, 'new_avatar');?>
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
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="clearfix"><!-- --></div>    
            </div>
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