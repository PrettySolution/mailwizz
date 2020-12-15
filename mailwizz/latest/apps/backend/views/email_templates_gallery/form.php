<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
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
                    <h3 class="box-title">
                        <?php echo IconHelper::make('glyphicon-text-width') .  $pageHeading;?>
                    </h3>
                </div>
                <div class="pull-right">
                    <?php if(!$template->isNewRecord) { ?>
                        <a href="javascript:;" onclick="window.open('<?php echo $previewUrl;?>', '<?php echo Yii::t('email_templates',  'Preview');?>', 'height=600, width=600'); return false;" class="btn btn-primary btn-flat"><?php echo IconHelper::make('view') . Yii::t('email_templates',  'Preview');?></a>
                        <?php echo HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('email_templates_gallery/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                    <?php } ?>
                    <?php echo HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('email_templates_gallery/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel')));?>
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
                            <?php echo $form->labelEx($template, 'name');?>
                            <?php echo $form->textField($template, 'name', $template->getHtmlOptions('name')); ?>
                            <?php echo $form->error($template, 'name');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($template, 'category_id');?>
                            <?php echo $form->dropDownList($template, 'category_id', CMap::mergeArray(array('' => ''), CustomerEmailTemplateCategory::getAllAsOptions()), $template->getHtmlOptions('category_id')); ?>
                            <?php echo $form->error($template, 'category_id');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <div class="pull-left">
                                <?php echo $form->labelEx($template, 'content');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                                <?php
                                // since 1.3.5
                                $hooks->doAction('before_wysiwyg_editor_left_side', array(
                                    'controller' => $this, 
                                    'template'   => $template
                                ));
                                ?>
                            </div>
                            <div class="pull-right">
                                <?php
                                // since 1.3.5
                                $hooks->doAction('before_wysiwyg_editor_right_side', array(
                                    'controller' => $this, 
                                    'template'   => $template
                                ));
                                ?>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <?php echo $form->textArea($template, 'content', $template->getHtmlOptions('content', array('rows' => 15))); ?>
                            <?php echo $form->error($template, 'content');?>
                            <div class="clearfix"><!-- --></div>
                            <?php
                            // since 1.4.4
                            $hooks->doAction('after_wysiwyg_editor', array(
                                'controller' => $this, 
                                'template'   => $template
                            ));
                            ?>
                        </div>
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
    ?>
    <div class="modal fade" id="available-tags-modal" tabindex="-1" role="dialog" aria-labelledby="available-tags-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo Yii::t('lists', 'Available tags');?></h4>
                </div>
                <div class="modal-body" style="max-height: 300px; overflow-y:scroll;">
                    <table class="table table-hover">
                        <tr>
                            <td><?php echo Yii::t('lists', 'Tag');?></td>
                            <td><?php echo Yii::t('lists', 'Required');?></td>
                        </tr>
                        <?php foreach ($campaignTemplate->getAvailableTags() as $tag) { ?>
                            <tr>
                                <td><?php echo CHtml::encode($tag['tag']);?></td>
                                <td><?php echo $tag['required'] ? strtoupper(Yii::t('app', CampaignTemplate::TEXT_YES)) : strtoupper(Yii::t('app', CampaignTemplate::TEXT_NO));?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                </div>
            </div>
        </div>
    </div>

    <?php 
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