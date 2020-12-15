<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.9
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
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                        ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-ban-circle') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('email_blacklist_monitors/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$monitor->isNewRecord)
                        ->add(HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('email_blacklist_monitors/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
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
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'name');?>
                            <?php echo $form->textField($monitor, 'name', $monitor->getHtmlOptions('name')); ?>
                            <?php echo $form->error($monitor, 'name');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'email_condition');?>
                            <?php echo $form->dropDownList($monitor, 'email_condition', CMap::mergeArray(array('' => ''), $monitor->getConditionsList()), $monitor->getHtmlOptions('email_condition')); ?>
                            <?php echo $form->error($monitor, 'email_condition');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'email');?>
                            <?php echo $form->textField($monitor, 'email', $monitor->getHtmlOptions('email')); ?>
                            <?php echo $form->error($monitor, 'email');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'reason_condition');?>
                            <?php echo $form->dropDownList($monitor, 'reason_condition', CMap::mergeArray(array('' => ''), $monitor->getConditionsList()), $monitor->getHtmlOptions('reason_condition')); ?>
                            <?php echo $form->error($monitor, 'reason_condition');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'reason');?>
                            <?php echo $form->textField($monitor, 'reason', $monitor->getHtmlOptions('reason')); ?>
                            <?php echo $form->error($monitor, 'reason');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'condition_operator');?>
                            <?php echo $form->dropDownList($monitor, 'condition_operator', $monitor->getConditionOperatorsList(), $monitor->getHtmlOptions('condition_operator')); ?>
                            <?php echo $form->error($monitor, 'condition_operator');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'notifications_to');?>
                            <?php echo $form->textField($monitor, 'notifications_to', $monitor->getHtmlOptions('notifications_to')); ?>
                            <?php echo $form->error($monitor, 'notifications_to');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($monitor, 'status');?>
                            <?php echo $form->dropDownList($monitor, 'status', $monitor->getStatusesList(), $monitor->getHtmlOptions('status')); ?>
                            <?php echo $form->error($monitor, 'status');?>
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