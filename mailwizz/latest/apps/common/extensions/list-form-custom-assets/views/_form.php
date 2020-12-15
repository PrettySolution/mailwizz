<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
?>
<hr />      
<div class="row">
    <div class="col-lg-12">
        <h4><?php echo Yii::t('lists', 'Custom assets');?> <a href="javascript:;" class="btn btn-primary btn-flat pull-right btn-list-custom-asset-add"><?php echo IconHelper::make('create');?></a></h4>
        <div class="clearfix"><!-- --></div>
        <div class="row">
            <div class="list-custom-assets-list">
                <?php foreach ($models as $index => $mdl) { ?>
                    <div class="col-lg-6 list-custom-assets-row" data-start-index="<?php echo $index;?>">
                        <div class="row">
                            <div class="col-lg-7">
                                <div class="form-group">
                                    <?php echo CHtml::activeLabelEx($mdl, 'asset_url');?>
                                    <?php echo CHtml::textField($mdl->modelName.'['.$index.'][asset_url]', $mdl->asset_url, $mdl->getHtmlOptions('asset_url')); ?>
                                    <?php echo CHtml::error($mdl, 'asset_url');?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <?php echo CHtml::activeLabelEx($mdl, 'asset_type');?>
                                    <?php echo CHtml::dropDownList($mdl->modelName.'['.$index.'][asset_type]', $mdl->asset_type, $mdl->getAssetTypes(), $mdl->getHtmlOptions('asset_type')); ?>
                                    <?php echo CHtml::error($mdl, 'asset_type');?>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <div class="pull-left" style="margin-top: 25px;">
                                        <a href="javascript:;" class="btn btn-danger btn-flat btn-list-custom-asset-remove" data-asset-id="<?php echo $mdl->asset_id;?>" data-message="<?php echo Yii::t('lists', 'Are you sure you want to remove this asset? There is no coming back from this after you save the changes.');?>"><?php echo IconHelper::make('delete');?></a>
                                    </div>
                                    <div class="clearfix"><!-- --></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div id="list-custom-assets-row-template" style="display: none;">
    <div class="col-lg-6 list-custom-assets-row" data-start-index="{index}">
        <div class="row">
            <div class="col-lg-7">
                <div class="form-group">
                    <?php echo CHtml::activeLabelEx($model, 'asset_url');?>
                    <?php echo CHtml::textField($model->modelName.'[{index}][asset_url]', $model->asset_url, $model->getHtmlOptions('asset_url')); ?>
                    <?php echo CHtml::error($model, 'asset_url');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo CHtml::activeLabelEx($model, 'asset_type');?>
                    <?php echo CHtml::dropDownList($model->modelName.'[{index}][asset_type]', $model->asset_type, $model->getAssetTypes(), $model->getHtmlOptions('asset_type')); ?>
                    <?php echo CHtml::error($model, 'asset_type');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <div class="pull-left" style="margin-top: 25px;">
                        <a href="javascript:;" class="btn btn-danger btn-flat btn-list-custom-asset-remove" data-asset-id="<?php echo $model->asset_id;?>" data-message="<?php echo Yii::t('lists', 'Are you sure you want to remove this asset? There is no coming back from this after you save the changes.');?>"><?php echo IconHelper::make('delete');?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>