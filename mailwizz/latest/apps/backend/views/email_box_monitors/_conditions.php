<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
?>
<div class="row">
    <div class="conditions-container">
        <div class="col-lg-12">
            <h5>
                <div class="pull-left">
                    <?php echo Yii::t('servers', 'Defined conditions:');?>
                </div>
                <div class="pull-right">
                    <a href="javascript:;" class="btn btn-primary btn-flat btn-add-condition"><?php echo IconHelper::make('create');?></a>
                    <a href="#page-info-conditions" data-toggle="modal" class="btn btn-primary btn-flat"><?php echo IconHelper::make('info');?></a>
                </div>
                <div class="clearfix"><!-- --></div>
            </h5>

            <div class="row">
                <div class="col-lg-12">
                    <?php echo $form->error($server, 'conditions');?>
                </div>
            </div>
            
            <hr />
        </div>
        <?php foreach ($server->conditions as $index => $cond) {?>
            <div class="item">
                <hr />
                <div class="col-lg-3">
                    <?php echo CHtml::label(Yii::t('servers', 'Condition'), 'condition');?>
                    <?php echo CHtml::dropDownList($server->modelName . '[conditions]['.$index.'][condition]', $cond['condition'], $server->getConditionsList(), $server->getHtmlOptions('conditions')); ?>
                </div>
                <div class="col-lg-3">
                    <?php echo CHtml::label(Yii::t('servers', 'Value'), 'value');?>
                    <?php echo CHtml::textField($server->modelName . '[conditions]['.$index.'][value]', $cond['value'], $server->getHtmlOptions('conditions', array('placeholder' => Yii::t('servers', 'Unsubscribe me')))); ?>
                </div>
                <div class="col-lg-3">
                    <?php echo CHtml::label(Yii::t('servers', 'Subscriber action'), 'action');?>
                    <?php echo CHtml::dropDownList($server->modelName . '[conditions]['.$index.'][action]', $cond['action'], $server->getActionsList(), $server->getHtmlOptions('conditions')); ?>
                </div>
                <div class="col-lg-3">
                    <label><?php echo Yii::t('app', 'Action');?></label><br />
                    <a href="javascript:;" class="btn btn-danger btn-flat btn-remove-condition"><?php echo IconHelper::make('delete');?></a>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        <?php } ?>
    </div>
</div>

<div id="condition-template" style="display: none;">
    <div class="item">
        <hr />
        <div class="col-lg-3">
            <?php echo CHtml::label(Yii::t('servers', 'Condition'), 'condition');?>
            <?php echo CHtml::dropDownList($server->modelName . '[conditions][{index}][condition]', '', $server->getConditionsList(), $server->getHtmlOptions('conditions')); ?>
        </div>
        <div class="col-lg-3">
            <?php echo CHtml::label(Yii::t('servers', 'Value'), 'value');?>
            <?php echo CHtml::textField($server->modelName . '[conditions][{index}][value]', '', $server->getHtmlOptions('conditions', array('placeholder' => Yii::t('servers', 'Unsubscribe me')))); ?>
        </div>
        <div class="col-lg-3">
            <?php echo CHtml::label(Yii::t('servers', 'Subscriber action'), 'action');?>
            <?php echo CHtml::dropDownList($server->modelName . '[conditions][{index}][action]', '', $server->getActionsList(), $server->getHtmlOptions('conditions')); ?>
        </div>
        <div class="col-lg-3">
            <label><?php echo Yii::t('app', 'Action');?></label><br />
            <a href="javascript:;" class="btn btn-danger btn-flat btn-remove-condition"><?php echo IconHelper::make('delete');?></a>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>

<!-- modals -->
<div class="modal modal-info fade" id="page-info-conditions" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php
                $text = 'These conditions will be applied to the email body and if matched, the given action will be taken against the email address.<br />Conditions are applied in the order they are added and execution stops at first match. Empty value matches everything.';
                echo Yii::t('servers', StringHelper::normalizeTranslationString($text));
                ?>
            </div>
        </div>
    </div>
</div>
        