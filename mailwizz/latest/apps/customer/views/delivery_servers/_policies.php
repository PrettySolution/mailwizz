<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

// feature disabled in 1.3.9.3
return;

?>

<hr />

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('glyphicon-lock') . Yii::t('servers', 'Domain policies');?></h3>
        </div>
        <div class="pull-right">
            <a href="javascript:;" class="btn btn-primary btn-sm btn-flat btn-add-policy"><?php echo IconHelper::make('create');?></a>
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-policies', array('class' => 'btn btn-primary btn-sm btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div id="policies-list">
                <?php if (!empty($policies)) { ?>
                    <?php $i = 0; foreach ($policies as $policyModel) { ?>
                        <div class="col-lg-6 policy-item">
                            <div class="row">
                                <div class="col-lg-5">
                                    <label class="required"><?php echo Yii::t('servers', 'Domain name');?> <span class="required">*</span></label>
                                    <div class="clearfix"><!-- --></div>
                                    <?php echo CHtml::textField($policyModel->modelName . '['.$i.'][domain]', $policyModel->domain, $policyModel->getHtmlOptions('domain'));?>
                                </div>
                                <div class="col-lg-5">
                                    <label class="required"><?php echo Yii::t('servers', 'Policy');?> <span class="required">*</span></label>
                                    <div class="clearfix"><!-- --></div>
                                    <?php echo CHtml::dropDownList($policyModel->modelName . '['.$i.'][policy]', $policyModel->policy, $policyModel->getPoliciesList(), $policyModel->getHtmlOptions('policy'));?>
                                </div>
                                <div class="col-lg-2">
                                    <label>&nbsp;</label>
                                    <div class="clearfix"><!-- --></div>
                                    <a href="javascript:;" class="btn btn-danger btn-flat remove-policy"><?php echo IconHelper::make('delete');?></a>
                                </div>
                            </div>
                        </div>
                        <?php ++$i; } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div id="policies-template" style="display: none;" data-count="<?php echo !empty($policies) ? count($policies) : 0;?>">
    <div class="col-lg-6 policy-item">
        <div class="row">
            <div class="col-lg-5">
                <label class="required"><?php echo Yii::t('servers', 'Domain name');?> <span class="required">*</span></label>
                <div class="clearfix"><!-- --></div>
                <?php echo CHtml::textField($policy->modelName . '[__#__][domain]', null, $policy->getHtmlOptions('domain', array('disabled' => true)));?>
            </div>
            <div class="col-lg-5">
                <label class="required"><?php echo Yii::t('servers', 'Policy');?> <span class="required">*</span></label>
                <div class="clearfix"><!-- --></div>
                <?php echo CHtml::dropDownList($policy->modelName . '[__#__][policy]', null, $policy->getPoliciesList(), $policy->getHtmlOptions('policy', array('disabled' => true)));?>
            </div>
            <div class="col-lg-2">
                <label>&nbsp;</label>
                <div class="clearfix"><!-- --></div>
                <a href="javascript:;" class="btn btn-danger btn-flat remove-policy"><?php echo IconHelper::make('delete');?></a>
            </div>
        </div>
    </div>
</div>

<!-- modals -->
<div class="modal modal-info fade" id="page-info-policies" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('servers', 'If your delivery server cannot send emails to certain domains, or it can only send to a small list of domains, you can add domain policies to reflect this.');?><br />
                <?php echo Yii::t('servers', 'If you want to send emails only to yahoo.com but deny for any other domain, you will need a allow policy for the domain yahoo.com and a deny policy on domain *');?><br />
                <?php echo Yii::t('servers', 'If you want to send to all domains except yahoo, then a deny policy on yahoo domain is enough.');?><br />
                <?php echo Yii::t('servers', 'If you want a policy for all yahoo emails, including yahoo.co.uk, yahoo.com.br, etc you can simply enter "yahoo" as policy domain.');?><br />
                <?php echo Yii::t('servers', 'The sign * acts as a policy wildcard matching any domain. A domain of domain*.com or *domain.com has no effect.');?><br />
            </div>
        </div>
    </div>
</div>