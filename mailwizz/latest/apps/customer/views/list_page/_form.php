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
 * 
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
                <h3 class="box-title"><?php echo Yii::t('list_pages', $pageType->name);?></h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
            </div>
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
            <?php if ($pageType->getCanHaveEmailSubject()) { ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo $form->labelEx($page, 'email_subject');?>
                        <?php echo $form->textField($page, 'email_subject', $page->getHtmlOptions('email_subject')); ?>
                        <?php echo $form->error($page, 'email_subject');?>
                    </div>
                </div>
            </div>
            <?php } ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <?php echo $form->labelEx($page, 'content');?>
                        <?php echo $form->textArea($page, 'content', $page->getHtmlOptions('content', array('rows' => 15))); ?>
                        <?php echo $form->error($page, 'content');?>
                    </div>
                    <?php $this->renderPartial('_tags');?>
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
        </div>
        <div class="box-footer">
            <div class="pull-right">
                <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                </div>
                <div class="modal-body">
                    <?php echo $pageType->description;?>
                </div>
            </div>
        </div>
    </div>
    <?php 
    $this->endWidget(); 
} 
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_active_form', new CAttributeCollection(array(
    'controller'      => $this,
    'renderedForm'    => $collection->renderForm,
)));