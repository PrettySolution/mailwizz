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
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                        ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-credit-card') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('price_plans/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$pricePlan->isNewRecord)
                        ->add(HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('price_plans/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
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
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'name');?>
                            <?php echo $form->textField($pricePlan, 'name', $pricePlan->getHtmlOptions('name')); ?>
                            <?php echo $form->error($pricePlan, 'name');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'group_id');?>
                            <?php echo $form->dropDownList($pricePlan, 'group_id', CustomerGroup::getGroupsArray(), $pricePlan->getHtmlOptions('group_id')); ?>
                            <?php echo $form->error($pricePlan, 'group_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'price');?>
                            <?php echo $form->textField($pricePlan, 'price', $pricePlan->getHtmlOptions('price')); ?>
                            <?php echo $form->error($pricePlan, 'price');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'description');?>
                            <?php echo $form->textArea($pricePlan, 'description', $pricePlan->getHtmlOptions('description')); ?>
                            <?php echo $form->error($pricePlan, 'description');?>
                        </div> 
                    </div>
                </div>     
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'status');?>
                            <?php echo $form->dropDownList($pricePlan, 'status', $pricePlan->getStatusesList(), $pricePlan->getHtmlOptions('status')); ?>
                            <?php echo $form->error($pricePlan, 'status');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'visible');?>
                            <?php echo $form->dropDownList($pricePlan, 'visible', $pricePlan->getYesNoOptions(), $pricePlan->getHtmlOptions('visible')); ?>
                            <?php echo $form->error($pricePlan, 'visible');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'sort_order');?>
                            <?php echo $form->dropDownList($pricePlan, 'sort_order', $pricePlan->getSortOrderList(), $pricePlan->getHtmlOptions('sort_order')); ?>
                            <?php echo $form->error($pricePlan, 'sort_order');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($pricePlan, 'recommended');?>
                            <?php echo $form->dropDownList($pricePlan, 'recommended', $pricePlan->getYesNoOptions(), $pricePlan->getHtmlOptions('recommended')); ?>
                            <?php echo $form->error($pricePlan, 'recommended');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
		                    <?php echo $form->labelEx($pricePlanDisplay, 'group_id');?>
		                    <?php echo $form->dropDownList($pricePlanDisplay, 'group_id', CMap::mergeArray(array('0' => ''), $pricePlanDisplay->getCustomerGroupsList()), $pricePlanDisplay->getHtmlOptions('group_id', array(
                                'multiple' => true,
                                'options'  => $pricePlanDisplaySelected
                            ))); ?>
		                    <?php echo $form->error($pricePlanDisplay, 'group_id');?>
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