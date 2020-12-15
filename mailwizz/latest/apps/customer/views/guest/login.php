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
        $form = $this->beginWidget('CActiveForm');
        ?>
        <div class="login-flex">
            <div class="login-form login-flex-col">
                <div class="login-box-body">
                    <p class="login-box-msg"><?php echo Yii::t('users', 'Sign in to start your session');?></p>
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
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->labelEx($model, 'email');?>
                                <?php echo $form->emailField($model, 'email', $model->getHtmlOptions('email')); ?>
                                <?php echo $form->error($model, 'email');?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->labelEx($model, 'password');?>
                                <?php echo $form->passwordField($model, 'password', $model->getHtmlOptions('password')); ?>
                                <?php echo $form->error($model, 'password');?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>
                                    <?php echo $form->checkBox($model, 'remember_me') . ' ' . $model->getAttributeLabel('remember_me');?>
                                </label>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <div class="pull-left">
                                <a href="<?php echo $this->createUrl('guest/forgot_password')?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('fa-lock') . '&nbsp;' .Yii::t('customers', 'Forgot password?');?></a>
                                <?php if ($registrationEnabled) { ?>
                                    <a href="<?php echo $this->createUrl('guest/register')?>" class="btn btn-default btn-flat"><?php echo IconHelper::make('fa-user') . '&nbsp;' . Yii::t('customers', 'Register');?></a>
                                <?php } ?>
                            </div>
                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('next') . '&nbsp;' . Yii::t('app', 'Login');?></button>
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
            </div>
            <div class="login-billboard login-flex-col" style="background-image: url('<?php echo $loginBgImage; ?>');">

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