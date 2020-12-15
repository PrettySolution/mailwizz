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
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { 
    if ($campaign->hasErrors()) { ?>
    <div class="alert alert-block alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo CHtml::errorSummary($campaign);?>
    </div>
    <?php 
    }
    
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
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <h3 class="box-title">
                        <?php echo IconHelper::make('envelope') .  $pageHeading;?>
                    </h3>
                </div>
                <div class="pull-right">
                    <?php echo CHtml::link(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('campaigns/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel')));?>
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
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($campaign, 'send_at');?>
                            <?php echo $form->hiddenField($campaign, 'send_at', $campaign->getHtmlOptions('send_at')); ?>
                            <?php echo $form->textField($campaign, 'sendAt', $campaign->getHtmlOptions('send_at', array(
                                    'data-keyup' => Yii::t('campaigns', 'Please use the date/time picker to set the value, do not enter it manually!')
                            ))); ?>
                            <?php echo CHtml::textField('fake_send_at', $campaign->dateTimeFormatter->formatDateTime($campaign->send_at), array(
                                'data-date-format'  => 'yyyy-mm-dd hh:ii:ss',
                                'data-autoclose'    => true,
                                'data-language'     => LanguageHelper::getAppLanguageCode(),
                                'data-syncurl'      => $this->createUrl('campaigns/sync_datetime'),
                                'class'             => 'form-control',
                                'style'             => 'visibility:hidden; height:1px; margin:0; padding:0;',
                            )); ?>
                            <?php echo $form->error($campaign, 'send_at');?>
                        </div>
                    </div>
                    <?php if (MW_COMPOSER_SUPPORT && $campaign->isRegular) { ?>
                    <div class="col-lg-8 jqcron-holder">
                        <?php echo $form->checkbox($campaign->option, 'cronjob_enabled', $campaign->option->getHtmlOptions('cronjob_enabled', array('uncheckValue' => 0, 'class' => 'btn btn-primary btn-flat', 'style' => 'padding-top:3px')));?>&nbsp;<?php echo $form->labelEx($campaign->option, 'cronjob');?>
                        <div class="col-lg-12 jqcron-wrapper">
                            <?php echo $form->hiddenField($campaign->option, 'cronjob', $campaign->option->getHtmlOptions('cronjob', array('data-lang' => $jqCronLanguage))); ?>
                        </div>
                        <?php echo $form->error($campaign->option, 'cronjob');?>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($campaign->option, 'cronjob_max_runs');?>
                            <?php echo $form->numberField($campaign->option, 'cronjob_max_runs', $campaign->option->getHtmlOptions('cronjob_max_runs', array('min' => -1, 'max' => 10000000, 'step' => 1))); ?>
                            <?php echo $form->error($campaign->option, 'cronjob_max_runs');?>
                        </div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <?php } ?>
                    
                    <?php if (MW_COMPOSER_SUPPORT && $campaign->customer->getGroupOption('campaigns.can_use_timewarp', 'no') == Campaign::TEXT_YES && $campaign->isRegular) { ?>
                    <div class="clearfix"><!-- --></div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($campaign->option, 'timewarp_enabled');?>
                            <?php echo $form->dropDownList($campaign->option, 'timewarp_enabled', $campaign->option->getYesNoOptions(), $campaign->option->getHtmlOptions('timewarp_enabled')); ?>
                            <?php echo $form->error($campaign->option, 'timewarp_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($campaign->option, 'timewarp_hour');?>
                            <?php echo $form->dropDownList($campaign->option, 'timewarp_hour', $campaign->option->getTimewarpHours(), $campaign->option->getHtmlOptions('timewarp_hour')); ?>
                            <?php echo $form->error($campaign->option, 'timewarp_hour');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo $form->labelEx($campaign->option, 'timewarp_minute');?>
                            <?php echo $form->dropDownList($campaign->option, 'timewarp_minute', $campaign->option->getTimewarpMinutes(), $campaign->option->getHtmlOptions('timewarp_minute')); ?>
                            <?php echo $form->error($campaign->option, 'timewarp_minute');?>
                        </div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <?php } ?>
                    
                    <?php if ($campaign->isAutoresponder) { ?>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_event');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_event', $campaign->option->getAutoresponderEvents(), $campaign->option->getHtmlOptions('autoresponder_event')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_event');?>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_time_value');?>
                                <?php echo $form->numberField($campaign->option, 'autoresponder_time_value', $campaign->option->getHtmlOptions('autoresponder_time_value')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_time_value');?>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_time_unit');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_time_unit', $campaign->option->getAutoresponderTimeUnits(), $campaign->option->getHtmlOptions('autoresponder_time_unit')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_time_unit');?>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_time_min_hour');?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_time_min_hour', CMap::mergeArray(array('' => Yii::t('app', 'Hour')), $campaign->option->getAutoresponderTimeMinHoursList()), $campaign->option->getHtmlOptions('autoresponder_time_min_hour')); ?>
                                            </div>
                                            <div class="col-lg-6">
                                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_time_min_minute', CMap::mergeArray(array('' => Yii::t('app', 'Minute')), $campaign->option->getAutoresponderTimeMinMinutesList()), $campaign->option->getHtmlOptions('autoresponder_time_min_minute')); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php echo $form->error($campaign->option, 'autoresponder_time_min_hour');?>
                                <?php echo $form->error($campaign->option, 'autoresponder_time_min_minute');?>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_include_imported');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_include_imported', $campaign->option->getYesNoOptions(), $campaign->option->getHtmlOptions('autoresponder_include_imported')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_include_imported');?>
                            </div>
                        </div>
                        <div class="clearfix"><!-- --></div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_include_current');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_include_current', $campaign->option->getYesNoOptions(), $campaign->option->getHtmlOptions('autoresponder_include_current')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_include_current');?>
                            </div>
                        </div>
                        <div class="col-lg-3 autoresponder-open-campaign-id-wrapper" style="display: <?php echo !empty($campaign->option->autoresponder_open_campaign_id) || $campaign->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN ? 'block' : 'none';?>;">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_open_campaign_id');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_open_campaign_id', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getRelatedCampaignsAsOptions()), $campaign->option->getHtmlOptions('autoresponder_open_campaign_id')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_open_campaign_id');?>
                            </div>
                        </div>
                        <div class="col-lg-3 autoresponder-sent-campaign-id-wrapper" style="display: <?php echo !empty($campaign->option->autoresponder_sent_campaign_id) || $campaign->option->autoresponder_event == CampaignOption::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_SENT ? 'block' : 'none';?>;">
                            <div class="form-group">
                                <?php echo $form->labelEx($campaign->option, 'autoresponder_sent_campaign_id');?>
                                <?php echo $form->dropDownList($campaign->option, 'autoresponder_sent_campaign_id', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getRelatedCampaignsAsOptions()), $campaign->option->getHtmlOptions('autoresponder_sent_campaign_id')); ?>
                                <?php echo $form->error($campaign->option, 'autoresponder_sent_campaign_id');?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="clearfix"><!-- --></div>
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
                <hr />
                <div class="table-responsive">
                    <?php
                    $this->widget('zii.widgets.CDetailView', array(
                        'data'          => $campaign,
                        'cssFile'       => false,
                        'htmlOptions'   => array('class' => 'table table-striped table-bordered table-hover table-condensed'),
                        'attributes'    => array(
                            'name',
                            array(
                                'label' => Yii::t('campaigns', 'List/Segment'),
                                'value' => $campaign->getListSegmentName(),
                            ),
                            'from_name', 'reply_to', 'to_name', 'subject',
                            array(
                                'label' => $campaign->getAttributeLabel('date_added'),
                                'value' => $campaign->dateAdded,
                            ),
                            array(
                                'label' => $campaign->getAttributeLabel('last_updated'),
                                'value' => $campaign->lastUpdated,
                            ),
                        ),
                    ));
                    ?>
                </div>
                <div class="clearfix"><!-- --></div>    
            </div>
            <div class="box-footer">
                <div class="wizard">
                    <ul class="steps">
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/update', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Details');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/setup', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Setup');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/template', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Template');?></a><span class="chevron"></span></li>
                        <li class="active"><a href="<?php echo $this->createUrl('campaigns/confirm', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Confirmation');?></a><span class="chevron"></span></li>
                        <li><a href="javascript:;"><?php echo Yii::t('app', 'Done');?></a><span class="chevron"></span></li>
                    </ul>
                    <div class="actions">
                        <button type="submit" id="is_next" name="is_next" value="1" class="btn btn-primary btn-flat btn-go-next">
                            <?php echo $campaign->isAutoresponder ? IconHelper::make('next') . '&nbsp;' . Yii::t('campaigns', 'Save and activate') : IconHelper::make('fa-send') . '&nbsp;' . Yii::t('campaigns', 'Send campaign');?>
                        </button>
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