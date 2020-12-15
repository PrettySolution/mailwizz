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

?>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('fa-envelope') .  Yii::t('campaigns', 'Campaign overview');?>
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
            <?php if (!empty($shareReports)) { ?>
                <?php echo CHtml::link(IconHelper::make('fa-share-square-o'), '#page-share-stats', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Share campaign stats'), 'data-toggle' => 'modal'));?>
            <?php } ?>
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard" style="padding-bottom: 0px">
                    <div class="progress-box" style="padding-bottom: 0px">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Recipients');?></span><span class="number"><?php echo CHtml::link($campaign->stats->getProcessedCount(true), $recipientsUrl);?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Name');?></span><span class="cl-span"><?php echo $campaign->name; ?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'List/Segment');?></span><span class="cl-span"><?php echo $campaign->getListSegmentName();?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('subject');?></span><span class="cl-span"><?php echo $campaign->subject;?></span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <?php if ($campaign->isRegular) { ?>
                            <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('lastOpen'); ?></span><span class="cl-span"><?php echo $campaign->lastOpen;?></span></li>
                            <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('started_at');?></span><span class="cl-span"><?php echo $campaign->startedAt ? $campaign->startedAt : $campaign->sendAt; ?></span></li>
                            <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('finished_at');?></span><span class="cl-span"><?php echo $campaign->finishedAt; ?></span></li>
                        <?php } ?>
                        <?php if ($campaign->isAutoresponder) { ?>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Autoresponder event');?></span><span class="cl-span"><?php echo Yii::t('campaigns', $campaign->option->autoresponder_event);?></span></li>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Autoresponder time unit');?></span><span class="cl-span"><?php echo ucfirst(Yii::t('app', $campaign->option->autoresponder_time_unit));?></span></li>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Autoresponder time value');?></span><span class="cl-span"><?php echo $campaign->option->autoresponder_time_value;?></span></li>
                            <?php if ($arTimeMinHourMinute = $campaign->option->getAutoresponderTimeMinHourMinute()) { ?>
                                <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Send only at/after this time');?></span><span class="cl-span"><?php echo $arTimeMinHourMinute; ?> (UTC 00:00)</span></li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><i class="fa fa-bars" aria-hidden="true"></i><?php echo Yii::t('campaign_reports', 'Details');?></h3>
        </div>
        <div class="pull-right"></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Type');?></span><span class="cl-span"><?php echo ucfirst(Yii::t('campaigns', $campaign->type));?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('from_name');?></span><span class="cl-span"><?php echo $campaign->from_name;?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('from_email');?></span><span class="cl-span"><?php echo $campaign->from_email;?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('reply_to');?></span><span class="cl-span"><?php echo $campaign->reply_to; ?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('to_name');?></span><span class="cl-span"><?php echo $campaign->to_name;?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Web version');?></span><span class="cl-span"><?php echo CHtml::link(Yii::t('app', 'View'), $webVersionUrl, array('target' => '_blank'));?></span></li>
                        
                        <?php if (!empty($campaign->template->name)) { ?>
                            <li><span class="cl-span"><?php echo $campaign->template->getAttributeLabel('name');?></span><span class="cl-span"><?php echo $campaign->template->name;?></span></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Forwards');?></span><span class="cl-span"><?php echo CHtml::link($campaign->countForwards(), $forwardsUrl);?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Abuse reports');?></span><span class="cl-span"><?php echo CHtml::link($campaign->countAbuseReports(), $abusesUrl);?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('date_added');?></span><span class="cl-span"><?php echo $campaign->dateAdded;?></span></li>
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('send_at');?></span><span class="cl-span"><?php echo $campaign->sendAt;?></span></li>

	                    <?php if ($campaign->isRegular && $campaign->option->getTimewarpEnabled()) { ?>
                            <li>
                                <span class="cl-span"><?php echo Yii::t('campaigns', 'Timewarp');?></span>
                                <span class="cl-span"><?php echo $campaign->option->timewarp_hour; ?>:<?php echo $campaign->option->timewarp_minute; ?></span>
                            </li>
                        <?php } ?>
                        
                        <?php if ($campaign->isRegular) { ?>
                            <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('totalDeliveryTime');?></span><span class="cl-span"><?php echo $campaign->totalDeliveryTime; ?></span></li>
                        <?php } ?>

                        <?php if ($campaign->getRegularOpenUnopenDisplayText()) { ?>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Filtered sent to');?></span><span class="cl-span"><?php echo $campaign->getRegularOpenUnopenDisplayText();?></span></li>
                        <?php } ?>

                        
                        <?php if (!empty($recurringInfo)) { ?>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Recurring');?></span><span class="cl-span"><?php echo !empty($recurringInfo) ? $recurringInfo : Yii::t('app', 'No');?></span></li>
                        <?php } ?>

                        <?php if ($campaign->isAutoresponder) { ?>
                            <?php if (!empty($campaign->option->autoresponder_open_campaign_id)) { ?>
                                <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Campaign to send for');?></span><span class="cl-span"><?php echo $campaign->option->autoresponderOpenCampaign->name;?></span></li>
                                <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Current opens count for target campaign');?></span><span class="cl-span"><?php echo (int)$campaign->option->autoresponderOpenCampaign->stats->getUniqueOpensCount(true);?></span></li>
                            <?php } ?>

                            <?php if (!empty($campaign->option->autoresponder_sent_campaign_id)) { ?>
                                <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Campaign to send for');?></span><span class="cl-span"><?php echo $campaign->option->autoresponderSentCampaign->name;?></span></li>
                                <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Current sent count for target campaign');?></span><span class="cl-span"><?php echo (int)$campaign->option->autoresponderSentCampaign->stats->getProcessedCount(true);?></span></li>
                            <?php } ?>

                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Include imported subscribers');?></span><span class="cl-span"><?php echo ucfirst(Yii::t('app', $campaign->option->autoresponder_include_imported));?></span></li>
                            <li><span class="cl-span"><?php echo Yii::t('campaigns', 'Include current subscribers');?></span><span class="cl-span"><?php echo ucfirst(Yii::t('app', $campaign->option->autoresponder_include_current));?></span></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- modals -->
<?php if (!empty($shareReports)) { ?>
<div class="modal modal-info fade" id="page-share-stats" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('fa-share-square-o') . Yii::t('app',  'Share campaign stats');?></h4>
            </div>
            <div class="modal-body">
                <?php $form = $this->beginWidget('CActiveForm', array(
                    'id'     => 'campaign-share-reports-form',
                    'action' => array('campaigns/share_reports', 'campaign_uid' => $shareReports->campaign->campaign_uid),
                ));?>
                <div class="row message" data-wait="<?php echo Yii::t('app', 'Please wait...');?>"></div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($shareReports, 'share_reports_enabled');?>
                            <?php echo $form->dropDownList($shareReports, 'share_reports_enabled', $shareReports->getYesNoOptions(), $shareReports->getHtmlOptions('share_reports_enabled')); ?>
                            <?php echo $form->error($shareReports, 'share_reports_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($shareReports, 'share_reports_mask_email_addresses');?>
                            <?php echo $form->dropDownList($shareReports, 'share_reports_mask_email_addresses', $shareReports->getYesNoOptions(), $shareReports->getHtmlOptions('share_reports_mask_email_addresses')); ?>
                            <?php echo $form->error($shareReports, 'share_reports_mask_email_addresses');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <?php echo $form->labelEx($shareReports, 'share_reports_password');?>
                        <div class="input-group">
                            <?php echo $form->textField($shareReports, 'share_reports_password', $shareReports->getHtmlOptions('share_reports_password')); ?>
                            <span class="input-group-btn">
                                <button class="btn btn-primary btn-flat btn-generate-share-password" type="button"><?php echo IconHelper::make('refresh');?></button>
                            </span>
                        </div>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($shareReports, 'share_reports_password');?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($shareReports, 'shareUrl');?>
                            <?php echo $form->textField($shareReports, 'shareUrl', $shareReports->getHtmlOptions('shareUrl', array('readonly' => true))); ?>
                            <?php echo $form->error($shareReports, 'shareUrl');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div style="display: none"><?php echo $form->labelEx($shareReports, 'share_reports_email');?></div>
                        <label><?php echo Yii::t('campaigns', 'Email above info to below email address');?></label>
                        <div class="input-group">
                            <?php echo $form->textField($shareReports, 'share_reports_email', $shareReports->getHtmlOptions('share_reports_email')); ?>
                            <span class="input-group-btn">
                                <button class="btn btn-primary btn-flat btn-send-share-stats-details" type="button" data-action="<?php echo Yii::app()->createUrl('campaigns/share_reports_send_email', array('campaign_uid' => $shareReports->campaign->campaign_uid));?>"><?php echo IconHelper::make('envelope') . '&nbsp;' . Yii::t('app', 'Send email');?></button>
                            </span>
                        </div>
                        <div class="clearfix"><!-- --></div>
                        <?php echo $form->error($shareReports, 'share_reports_email');?>
                    </div>
                </div>
                <hr />
                <div class="row">
                   <div class="col-lg-12">
                       <div class="pull-right">
                           <button type="submit" class="btn btn-primary btn-flat"><?php echo Yii::t('app', 'Save changes');?></button>
                       </div>
                   </div>
                </div>
                <div class="clearfix"><!-- --></div>
                <?php $this->endWidget();?>
            </div>
        </div>
    </div>
</div>
<?php } ?>

<div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
            </div>
            <div class="modal-body">
                <?php echo Yii::t('campaigns', 'Please note that the stats are based only on your list confirmed subscribers count.');?> <br />
                <?php echo Yii::t('campaigns', 'The number of confirmed subscribers can change during a sendout, subscribers can unsubscribe, get blacklisted or report the email as spam, case in which actions are taken and those subscribers are not confirmed anymore.');?><br />
                <b><?php echo Yii::t('campaigns', 'Stats data is cached for 5 minutes.');?> <br /></b>
            </div>
        </div>
    </div>
</div>