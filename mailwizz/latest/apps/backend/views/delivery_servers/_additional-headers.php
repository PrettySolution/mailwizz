<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3
 */

?>

<hr />

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('glyphicon-plus-sign') . Yii::t('servers', 'Additional headers');?></h3>
        </div>
        <div class="pull-right">
            <a href="javascript:;" class="btn btn-primary btn-flat btn-sm btn-add-header"><?php echo IconHelper::make('create');?></a>
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-headers', array('class' => 'btn btn-primary btn-sm btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div id="headers-list">
                <?php $i = 0; foreach ($server->additional_headers as $header) { ?>
                    <div class="col-lg-6 header-item">
                        <div class="row">
                            <div class="col-lg-5">
                                <label class="required"><?php echo Yii::t('servers', 'Header name');?> <span class="required">*</span></label>
                                <div class="clearfix"><!-- --></div>
                                <?php echo CHtml::textField($server->modelName . '[additional_headers]['.$i.'][name]', $header['name'], $server->getHtmlOptions('additional_headers', array('placeholder' => Yii::t('servers', 'X-Header-Name'))));?>
                            </div>
                            <div class="col-lg-5">
                                <label class="required"><?php echo Yii::t('servers', 'Header value');?> <span class="required">*</span></label>
                                <div class="clearfix"><!-- --></div>
                                <?php echo CHtml::textField($server->modelName . '[additional_headers]['.$i.'][value]', $header['value'], $server->getHtmlOptions('additional_headers', array('placeholder' => Yii::t('servers', 'Header value'))));?>
                            </div>
                            <div class="col-lg-2">
                                <label>&nbsp;</label>
                                <div class="clearfix"><!-- --></div>
                                <a href="javascript:;" class="btn btn-danger btn-flat remove-header"><?php echo IconHelper::make('delete');?></a>
                            </div>
                        </div>
                    </div>
                    <?php ++$i; } ?>
            </div>
        </div>
    </div>
</div>

<div id="headers-template" style="display: none;" data-count="<?php echo count($server->additional_headers);?>">
    <div class="col-lg-6 header-item">
        <div class="row">
            <div class="col-lg-5">
                <label class="required"><?php echo Yii::t('servers', 'Header name');?> <span class="required">*</span></label>
                <div class="clearfix"><!-- --></div>
                <?php echo CHtml::textField($server->modelName . '[additional_headers][__#__][name]', null, $server->getHtmlOptions('additional_headers', array('disabled' => true, 'placeholder' => Yii::t('servers', 'X-Header-Name'))));?>
            </div>
            <div class="col-lg-5">
                <label class="required"><?php echo Yii::t('servers', 'Header value');?> <span class="required">*</span></label>
                <div class="clearfix"><!-- --></div>
                <?php echo CHtml::textField($server->modelName . '[additional_headers][__#__][value]', null, $server->getHtmlOptions('additional_headers', array('disabled' => true, 'placeholder' => Yii::t('servers', 'Header value'))));?>
            </div>
            <div class="col-lg-2">
                <label>&nbsp;</label>
                <div class="clearfix"><!-- --></div>
                <a href="javascript:;" class="btn btn-danger btn-flat remove-header"><?php echo IconHelper::make('delete');?></a>
            </div>
        </div>
    </div>
</div>

<!-- modals -->
<div class="modal modal-info fade" id="page-info-headers" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('servers', 'If your delivery server needs extra headers in order to make the delivery, you can add them here.');?><br />
                <?php echo Yii::t('servers', 'If a header is not in the correct format or if it is part of the restricted headers, it will not be added.');?><br />
                <?php echo Yii::t('servers', 'Use this with caution and only if you know what you are doing, wrong headers can make your email delivery fail.');?><br />
                <?php echo Yii::t('servers', 'Following dynamic tags will be parsed depending on context:');?> <em><strong>[CAMPAIGN_UID], [SUBSCRIBER_UID], [SUBSCRIBER_EMAIL]</strong></em>
            </div>
        </div>
    </div>
</div>