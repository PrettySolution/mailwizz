<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
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
    $this->renderPartial('_campaigns_tabs');
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
                <h3 class="box-title"><?php echo IconHelper::make('fa-cog') . Yii::t('settings', 'Campaign attachments')?></h3>
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
                            <?php echo $form->labelEx($model, 'enabled');?>
                            <?php echo $form->dropDownList($model, 'enabled', $model->getEnabledOptions(), $model->getHtmlOptions('enabled')); ?>
                            <?php echo $form->error($model, 'enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'allowed_file_size');?>
                            <?php echo $form->dropDownList($model, 'allowed_file_size', $model->getFileSizeOptions(), $model->getHtmlOptions('allowed_file_size')); ?>
                            <?php echo $form->error($model, 'allowed_file_size');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'allowed_files_count');?>
                            <?php echo $form->numberField($model, 'allowed_files_count', $model->getHtmlOptions('allowed_files_count')); ?>
                            <?php echo $form->error($model, 'allowed_files_count');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <hr />
                        <div class="pull-left">
                            <h5><?php echo Yii::t('settings', 'Allowed extensions');?>:</h5>
                        </div>
                        <div class="pull-right">
                            <a href="javascript:;" class="btn btn-primary btn-flat add-campaign-allowed-extension"><?php echo IconHelper::make('create');?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($model, 'allowed_extensions');?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div id="campaign-allowed-ext-list">
                            <?php foreach ($model->allowed_extensions as $ext) { ?>
                                <div class="col-lg-3 item">
                                    <div class="input-group">
                                        <?php echo CHtml::textField($model->modelName . '[allowed_extensions][]', $ext, $model->getHtmlOptions('allowed_extensions', array('class' => 'form-control')));?>
                                        <span class="input-group-btn">
                                            <a href="javascript:;" class="btn btn-danger btn-flat remove-campaign-allowed-ext"><?php echo IconHelper::make('delete');?></a>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <hr />
                        <div class="pull-left">
                            <h5><?php echo Yii::t('settings', 'Allowed mime types');?>:</h5>
                        </div>
                        <div class="pull-right">
                            <a href="javascript:;" class="btn btn-primary btn-flat add-campaign-allowed-mime"><?php echo IconHelper::make('create');?></a>
                        </div>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($model, 'allowed_mime_types');?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div id="campaign-allowed-mime-list">
                                <?php foreach ($model->allowed_mime_types as $mime) { ?>
                                    <div class="col-lg-3 item">
                                        <div class="input-group">
                                            <?php echo CHtml::textField($model->modelName . '[allowed_mime_types][]', $mime, $model->getHtmlOptions('allowed_mime_types', array('class' => 'form-control')));?>
                                            <span class="input-group-btn">
                                                <a href="javascript:;" class="btn btn-danger btn-flat remove-campaign-allowed-mime"><?php echo IconHelper::make('delete');?></a>
                                            </span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix"><!-- --></div>
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
        </div>
        <div class="box box-primary borderless">
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
    <div style="display: none;" id="campaign-allowed-ext-item">
        <div class="col-lg-3 item">
            <div class="input-group">
                <?php echo CHtml::textField($model->modelName . '[allowed_extensions][]', null, $model->getHtmlOptions('allowed_extensions', array('class' => 'form-control')));?>
                <span class="input-group-btn">
                    <a href="javascript:;" class="btn btn-danger btn-flat remove-campaign-allowed-ext"><?php echo IconHelper::make('delete');?></a>
                </span>
            </div>
        </div>
    </div>
    <div style="display: none;" id="campaign-allowed-mime-item">
        <div class="col-lg-3 item">
            <div class="input-group">
                <?php echo CHtml::textField($model->modelName . '[allowed_mime_types][]', null, $model->getHtmlOptions('allowed_mime_types', array('class' => 'form-control')));?>
                <span class="input-group-btn">
                    <a href="javascript:;" class="btn btn-danger btn-flat remove-campaign-allowed-mime"><?php echo IconHelper::make('delete');?></a>
                </span>
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