<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
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
if ($viewCollection->renderContent) { ?>
    <?php 
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
                        ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-list-alt') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('start_pages/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$model->isNewRecord)
                        ->add(HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('start_pages/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
                        ->add(CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal')))
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
                            <?php echo $form->labelEx($model, 'application');?>
                            <?php echo $form->dropDownList($model, 'application', $model->getApplications(), $model->getHtmlOptions('application')); ?>
                            <?php echo $form->error($model, 'application');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'route');?>
                            <?php echo $form->textField($model, 'route', $model->getHtmlOptions('route')); ?>
                            <?php echo $form->error($model, 'route');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'heading');?>
                            <?php echo $form->textField($model, 'heading', $model->getHtmlOptions('heading')); ?>
                            <?php echo $form->error($model, 'heading');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'content');?>
                            <?php echo $form->textArea($model, 'content', $model->getHtmlOptions('content')); ?>
                            <?php echo $form->error($model, 'content');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 select-icon-wrapper">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'icon');?>
                            <div class="input-group">
                                <span class="input-group-addon" style="<?php echo empty($model->icon) ? 'display:none' : '';?>">
                                    <a href="javascript:;" class="icon-wrap" style="<?php echo !empty($model->icon_color) ? sprintf('color:#%s', $model->icon_color) : '';?>"><?php echo IconHelper::make($model->icon);?></a>
                                    <div class="clearfix"><!-- --></div>
                                    <a href="javascript:;" class="btn btn-xs btn-primary btn-flat btn-select-color" title="<?php echo Yii::t('start_pages', 'Select icon color');?>"><?php echo IconHelper::make('fa-paint-brush');?></a>&nbsp;<a href="javascript:;" class="btn btn-xs btn-primary btn-flat btn-reset-color" title="<?php echo Yii::t('start_pages', 'Reset icon color');?>"><?php echo IconHelper::make('fa-history');?></a>&nbsp;<a href="javascript:;" class="btn btn-xs btn-danger btn-flat btn-remove-icon" title="<?php echo Yii::t('start_pages', 'Remove icon');?>"><?php echo IconHelper::make('delete');?></a>
                                </span>
                                <?php echo $form->textField($model, 'search_icon', $model->getHtmlOptions('search_icon')); ?>
                                <?php echo $form->hiddenField($model, 'icon', $model->getHtmlOptions('icon')); ?>
                                <?php echo $form->hiddenField($model, 'icon_color', $model->getHtmlOptions('icon_color')); ?>
                            </div>
                            <?php echo $form->error($model, 'icon');?>
                        </div>
                        <div class="icons-list">
                            <?php foreach ($model->getIcons() as $icon) { ?>
                                <span class="icon-item">
                                    <a href="javascript:;" data-icon="<?php echo $icon; ?>" title="<?php echo $icon; ?>"><?php echo IconHelper::make($icon); ?></a>
                                </span>
                            <?php } ?>
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

        <!-- modals -->
        <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('start_pages', 'You can use following tags in heading and content:'); ?>
                        <div style="width: 100%; max-height: 500px; overflow-y: scroll">
                            <table class="table table-striped table-condensed table-bordered">
                                <thead>
                                <tr>
                                    <th><?php echo Yii::t('start_pages', 'Tag');?></th>
                                    <th><?php echo Yii::t('start_pages', 'Description');?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($model->getAvailableTags() as $tag => $description) { ?>
                                    <tr>
                                        <td><?php echo $tag;?></td>
                                        <td><?php echo $description;?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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