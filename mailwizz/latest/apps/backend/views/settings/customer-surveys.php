<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
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
                <h3 class="box-title"><?php echo Yii::t('settings', 'Customer surveys')?></h3>
            </div>
            <div class="box-body">
                <?php 
                /**
                 * This hook gives a chance to prepend content before the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.4.3
                 */
                $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'max_surveys');?>
                            <?php echo $form->numberField($model, 'max_surveys', $model->getHtmlOptions('max_surveys')); ?>
                            <?php echo $form->error($model, 'max_surveys');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'max_responders');?>
                            <?php echo $form->numberField($model, 'max_responders', $model->getHtmlOptions('max_responders')); ?>
                            <?php echo $form->error($model, 'max_responders');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'max_responders_per_survey');?>
                            <?php echo $form->numberField($model, 'max_responders_per_survey', $model->getHtmlOptions('max_responders_per_survey')); ?>
                            <?php echo $form->error($model, 'max_responders_per_survey');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'can_delete_own_surveys');?>
                            <?php echo $form->dropDownList($model, 'can_delete_own_surveys', $model->getYesNoOptions(), $model->getHtmlOptions('can_delete_own_surveys')); ?>
                            <?php echo $form->error($model, 'can_delete_own_surveys');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'can_delete_own_responders');?>
                            <?php echo $form->dropDownList($model, 'can_delete_own_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_delete_own_responders')); ?>
                            <?php echo $form->error($model, 'can_delete_own_responders');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'can_edit_own_responders');?>
                            <?php echo $form->dropDownList($model, 'can_edit_own_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_edit_own_responders')); ?>
                            <?php echo $form->error($model, 'can_edit_own_responders');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'can_segment_surveys');?>
                            <?php echo $form->dropDownList($model, 'can_segment_surveys', $model->getYesNoOptions(), $model->getHtmlOptions('can_segment_surveys')); ?>
                            <?php echo $form->error($model, 'can_segment_surveys');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'max_segment_conditions');?>
                            <?php echo $form->numberField($model, 'max_segment_conditions', $model->getHtmlOptions('max_segment_conditions')); ?>
                            <?php echo $form->error($model, 'max_segment_conditions');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'max_segment_wait_timeout');?>
                            <?php echo $form->numberField($model, 'max_segment_wait_timeout', $model->getHtmlOptions('max_segment_wait_timeout')); ?>
                            <?php echo $form->error($model, 'max_segment_wait_timeout');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'can_export_responders');?>
                            <?php echo $form->dropDownList($model, 'can_export_responders', $model->getYesNoOptions(), $model->getHtmlOptions('can_export_responders')); ?>
                            <?php echo $form->error($model, 'can_export_responders');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'show_7days_responders_activity_graph');?>
                            <?php echo $form->dropDownList($model, 'show_7days_responders_activity_graph', $model->getYesNoOptions(), $model->getHtmlOptions('show_7days_responders_activity_graph')); ?>
                            <?php echo $form->error($model, 'show_7days_responders_activity_graph');?>
                        </div>
                    </div>
                </div>
                <?php 
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.4.3
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