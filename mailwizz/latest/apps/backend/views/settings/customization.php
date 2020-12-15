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
        $form = $this->beginWidget('CActiveForm', array(
            'htmlOptions' => array('enctype' => 'multipart/form-data')
        )); 
        ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo Yii::t('settings', 'Customization')?></h3>
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
                <div class="row" style="display: none">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'backend_logo_text');?>
                            <?php echo $form->textField($model, 'backend_logo_text', $model->getHtmlOptions('backend_logo_text')); ?>
                            <?php echo $form->error($model, 'backend_logo_text');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'customer_logo_text');?>
                            <?php echo $form->textField($model, 'customer_logo_text', $model->getHtmlOptions('customer_logo_text')); ?>
                            <?php echo $form->error($model, 'customer_logo_text');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'frontend_logo_text');?>
                            <?php echo $form->textField($model, 'frontend_logo_text', $model->getHtmlOptions('frontend_logo_text')); ?>
                            <?php echo $form->error($model, 'frontend_logo_text');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'backend_skin');?>
                            <?php echo $form->dropDownList($model, 'backend_skin', $model->getAppSkins('backend'), $model->getHtmlOptions('backend_skin')); ?>
                            <?php echo $form->error($model, 'backend_skin');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'customer_skin');?>
                            <?php echo $form->dropDownList($model, 'customer_skin', $model->getAppSkins('customer'), $model->getHtmlOptions('customer_skin')); ?>
                            <?php echo $form->error($model, 'customer_skin');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'frontend_skin');?>
                            <?php echo $form->dropDownList($model, 'frontend_skin', $model->getAppSkins('frontend'), $model->getHtmlOptions('frontend_skin')); ?>
                            <?php echo $form->error($model, 'frontend_skin');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <label><a href="#" class="customization-clear-logo" data-default="<?php echo $model->getDefaultLoginBgUrl(120, 60);?>"><?php echo Yii::t('settings', 'Clear');?></a></label>
                                <img src="<?php echo $model->getBackendLoginBgUrl(120, 60);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'backend_login_bg');?>
                                    <?php echo $form->fileField($model, 'backend_login_bg_up', $model->getHtmlOptions('backend_login_bg')); ?>
                                    <?php echo $form->hiddenField($model, 'backend_login_bg'); ?>
                                    <?php echo $form->error($model, 'backend_login_bg_up');?>
                                    <?php echo $form->error($model, 'backend_login_bg');?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <label><a href="#" class="customization-clear-logo" data-default="<?php echo $model->getDefaultLoginBgUrl(120, 60);?>"><?php echo Yii::t('settings', 'Clear');?></a></label>
                                <img src="<?php echo $model->getCustomerLoginBgUrl(120, 60);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'customer_login_bg');?>
                                    <?php echo $form->fileField($model, 'customer_login_bg_up', $model->getHtmlOptions('customer_login_bg')); ?>
                                    <?php echo $form->hiddenField($model, 'customer_login_bg'); ?>
                                    <?php echo $form->error($model, 'customer_login_bg_up');?>
                                    <?php echo $form->error($model, 'customer_login_bg');?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="display: none">
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <label><a href="#" class="customization-clear-logo" data-default="<?php echo $model->getDefaultLogoUrl(120, 60);?>"><?php echo Yii::t('settings', 'Clear logo');?></a></label>
                                <img src="<?php echo $model->getBackendLogoUrl(120, 60);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'backend_logo');?>
                                    <?php echo $form->fileField($model, 'backend_logo_up', $model->getHtmlOptions('backend_logo')); ?>
                                    <?php echo $form->hiddenField($model, 'backend_logo'); ?>
                                    <?php echo $form->error($model, 'backend_logo_up');?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <label><a href="#" class="customization-clear-logo" data-default="<?php echo $model->getDefaultLogoUrl(120, 60);?>"><?php echo Yii::t('settings', 'Clear logo');?></a></label>
                                <img src="<?php echo $model->getCustomerLogoUrl(120, 60);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'customer_logo');?>
                                    <?php echo $form->fileField($model, 'customer_logo_up', $model->getHtmlOptions('customer_logo')); ?>
                                    <?php echo $form->hiddenField($model, 'customer_logo'); ?>
                                    <?php echo $form->error($model, 'customer_logo_up');?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <label><a href="#" class="customization-clear-logo" data-default="<?php echo $model->getDefaultLogoUrl(120, 60);?>"><?php echo Yii::t('settings', 'Clear logo');?></a></label>
                                <img src="<?php echo $model->getFrontendLogoUrl(120, 60);?>" class="img-thumbnail"/>
                            </div>
                            <div class="col-lg-9">
                                <div class="form-group">
                                    <?php echo $form->labelEx($model, 'frontend_logo');?>
                                    <?php echo $form->fileField($model, 'frontend_logo_up', $model->getHtmlOptions('frontend_logo')); ?>
                                    <?php echo $form->hiddenField($model, 'frontend_logo'); ?>
                                    <?php echo $form->error($model, 'frontend_logo_up');?>
                                </div>
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