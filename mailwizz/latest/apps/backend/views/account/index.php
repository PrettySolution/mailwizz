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
	if (!$user->isNewRecord && $twoFaSettings->isEnabled) { ?>
        <ul class="nav nav-tabs" style="border-bottom: 0px;">
            <li class="active"><a href="<?php echo $this->createUrl('account/index'); ?>"><?php echo CHtml::encode(Yii::t('app', 'Profile'));?></a></li>
            <li class="inactive"><a href="<?php echo $this->createUrl('account/2fa')?>"><?php echo CHtml::encode(Yii::t('app', '2FA'));?></a></li>
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
                <h3 class="box-title"><?php echo IconHelper::make('glyphicon-user') . Yii::t('users', 'Update your account data.');?></h3>
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
                            <?php echo $form->labelEx($user, 'first_name');?>
                            <?php echo $form->textField($user, 'first_name', $user->getHtmlOptions('first_name')); ?>
                            <?php echo $form->error($user, 'first_name');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'last_name');?>
                            <?php echo $form->textField($user, 'last_name', $user->getHtmlOptions('last_name')); ?>
                            <?php echo $form->error($user, 'last_name');?>
                        </div>
                    </div>
                </div>   
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'email');?>
                            <?php echo $form->emailField($user, 'email', $user->getHtmlOptions('email')); ?>
                            <?php echo $form->error($user, 'email');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'confirm_email');?>
                            <?php echo $form->emailField($user, 'confirm_email', $user->getHtmlOptions('confirm_email')); ?>
                            <?php echo $form->error($user, 'confirm_email');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'fake_password');?>
                            <?php echo $form->passwordField($user, 'fake_password', $user->getHtmlOptions('password')); ?>
                            <?php echo $form->error($user, 'fake_password');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'confirm_password');?>
                            <?php echo $form->passwordField($user, 'confirm_password', $user->getHtmlOptions('confirm_password')); ?>
                            <?php echo $form->error($user, 'confirm_password');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'timezone');?>
                            <?php echo $form->dropDownList($user, 'timezone', $user->getTimeZonesArray(), $user->getHtmlOptions('timezone')); ?>
                            <?php echo $form->error($user, 'timezone');?>
                        </div>  
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($user, 'language_id');?>
                            <?php echo $form->dropDownList($user, 'language_id', CMap::mergeArray(array('' => Yii::t('app', 'Application default')), Language::getLanguagesArray()), $user->getHtmlOptions('language_id')); ?>
                            <?php echo $form->error($user, 'language_id');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <div class="col-lg-2">
                                <img src="<?php echo $user->getAvatarUrl(90, 90);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-10">
                                <?php echo $form->labelEx($user, 'new_avatar');?>
                                <?php echo $form->fileField($user, 'new_avatar', $user->getHtmlOptions('new_avatar')); ?>
                                <?php echo $form->error($user, 'new_avatar');?>
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