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
 
?>

<div id="field-radiolist-javascript-template" style="display: none;">
    <div class="field-row" data-start-index="{index}" data-field-type="<?php echo $fieldType->identifier;?>">
        <?php echo CHtml::hiddenField($model->modelName.'['.$fieldType->identifier.'][{index}][field_id]', (int)$model->field_id); ?>
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="javascript:;"><span class="glyphicon glyphicon-th-list"></span> <?php echo Yii::t('survey_fields', 'New radiolist field');?></a>
            </li>
        </ul>
        <div class="panel panel-default no-top-border">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-4">
                        <?php echo CHtml::activeLabelEx($model, 'label');?>
                        <?php echo CHtml::textField($model->modelName.'['.$fieldType->identifier.'][{index}][label]', $model->label, $model->getHtmlOptions('label')); ?>
                        <?php echo CHtml::error($model, 'label');?>
                    </div>
                    <div class="col-lg-2">
                        <?php echo CHtml::activeLabelEx($model, 'required');?>
                        <?php echo CHtml::dropDownList($model->modelName.'['.$fieldType->identifier.'][{index}][required]', $model->required, $model->getRequiredOptionsArray(), $model->getHtmlOptions('required')); ?>
                        <?php echo CHtml::error($model, 'required');?>
                    </div>
                    <div class="col-lg-2">
                        <?php echo CHtml::activeLabelEx($model, 'visibility');?>
                        <?php echo CHtml::dropDownList($model->modelName.'['.$fieldType->identifier.'][{index}][visibility]', $model->visibility, $model->getVisibilityOptionsArray(), $model->getHtmlOptions('visibility')); ?>
                        <?php echo CHtml::error($model, 'visibility');?>
                    </div>
                    <div class="col-lg-2">
                        <?php echo CHtml::activeLabelEx($model, 'sort_order');?>
                        <?php echo CHtml::dropDownList($model->modelName.'['.$fieldType->identifier.'][{index}][sort_order]', $model->sort_order, $model->getSortOrderOptionsArray(), $model->getHtmlOptions('sort_order', array('data-placement' => 'left'))); ?>
                        <?php echo CHtml::error($model, 'sort_order');?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <?php echo CHtml::activeLabelEx($model, 'help_text');?>
                        <?php echo CHtml::textField($model->modelName.'['.$fieldType->identifier.'][{index}][help_text]', $model->help_text, $model->getHtmlOptions('help_text')); ?>
                        <?php echo CHtml::error($model, 'help_text');?>
                    </div>
                    <div class="col-lg-6">
                        <?php echo CHtml::activeLabelEx($model, 'default_value');?>
                        <?php echo CHtml::textField($model->modelName.'['.$fieldType->identifier.'][{index}][default_value]', $model->default_value, $model->getHtmlOptions('default_value')); ?>
                        <?php echo CHtml::error($model, 'default_value');?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo CHtml::activeLabelEx($model, 'description');?>
                            <?php echo CHtml::textArea($model->modelName.'['.$fieldType->identifier.'][{index}][description]', $model->description, $model->getHtmlOptions('description')); ?>
                            <?php echo CHtml::error($model, 'description');?>
                        </div>
                    </div>
                </div>
                <hr />
                <h4><?php echo Yii::t('survey_fields', 'Options');?> <a href="javascript:;" class="btn btn-primary pull-right btn-flat btn-radiolist-add-option"><?php echo IconHelper::make('create');?></a></h4>
                <hr />
                <div class="row">
                    <div class="radiolist-options-list">

                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="pull-right">
                    <a href="javascript:;" class="btn btn-danger btn-flat btn-remove-radiolist-field" data-field-id="0" data-message="<?php echo Yii::t('survey_fields', 'Are you sure you want to remove this field? There is no coming back from this after you save the changes.');?>"><?php echo IconHelper::make('delete');?></a>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>
</div>

<div id="field-radiolist-option-javascript-template" style="display: none;">
    <div class="col-lg-6 radiolist-option-row" data-start-index="{optionIndex}" data-parent-index="{parentIndex}">
        <div class="row">
            <div class="col-lg-5">
                <div class="form-group">
                    <?php echo CHtml::activeLabelEx($option, 'name');?>
                    <?php echo CHtml::textField($option->modelName.'['.$fieldType->identifier.'][{parentIndex}][{optionIndex}][name]', null, $option->getHtmlOptions('name')); ?>
                    <?php echo CHtml::error($option, 'name');?>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="form-group">
                    <?php echo CHtml::activeLabelEx($option, 'value');?>
                    <?php echo CHtml::textField($option->modelName.'['.$fieldType->identifier.'][{parentIndex}][{optionIndex}][value]', null, $option->getHtmlOptions('value')); ?>
                    <?php echo CHtml::error($option, 'value');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <div class="pull-left" style="margin-top: 25px;">
                        <a href="javascript:;" class="btn btn-danger btn-flat btn-remove-radiolist-option-field" data-option-id="0" data-message="<?php echo Yii::t('survey_fields', 'Are you sure you want to remove this option? There is no coming back from this after you save the changes.');?>"><?php echo IconHelper::make('delete');?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>