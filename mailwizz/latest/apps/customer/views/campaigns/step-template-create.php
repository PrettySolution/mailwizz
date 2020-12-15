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
        $form = $this->beginWidget('CActiveForm', array(
            'action' => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'create')
        ));
        echo CHtml::hiddenField('selected_template_id', 0, array('id' => 'selected_template_id'));
        ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <h3 class="box-title">
                        <?php echo IconHelper::make('envelope') .  $pageHeading;?>
                    </h3>
                </div>
                <div class="pull-right">
                    <?php echo CHtml::link(Yii::t('email_templates', 'Import html from url'), '#template-import-modal', array('class' => 'btn btn-primary btn-flat', 'data-toggle' => 'modal', 'title' => Yii::t('email_templates', 'Import html from url')));?>
                    <?php echo CHtml::link(Yii::t('email_templates', 'Upload template'), '#template-upload-modal', array('class' => 'btn btn-primary btn-flat', 'data-toggle' => 'modal', 'title' => Yii::t('email_templates', 'Upload template')));?>
                    <?php echo CHtml::link(Yii::t('campaigns', 'Change/Select template'), array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'select'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaigns', 'Change/Select template')));?>
                    <?php if (!empty($template->content)) { ?>
                    <?php echo CHtml::link(Yii::t('campaigns', 'Test template'), '#template-test-email', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaigns', 'Test template'), 'data-toggle' => 'modal'));?>
                    <?php } ?>
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

                <?php
                // since 1.3.9.0
                $hooks->doAction('campaign_form_template_step_before_top_options', array(
                    'controller' => $this,
                    'campaign'   => $campaign,
                    'form'       => $form,
                    'template'   => $template,
                ));
                ?>
                
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($template, 'name');?>
                            <?php echo $form->textField($template, 'name', $template->getHtmlOptions('name')); ?>
                            <?php echo $form->error($template, 'name');?>
                        </div>
                    </div>
                    <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <?php echo $form->labelEx($template, 'only_plain_text');?>
                                <?php echo $form->dropDownList($template, 'only_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('only_plain_text')); ?>
                                <?php echo $form->error($template, 'only_plain_text');?>
                            </div>
                        </div>
                        <div class="col-lg-4 auto-plain-text-wrapper" style="display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;">
                            <div class="form-group">
                                <?php echo $form->labelEx($template, 'auto_plain_text');?>
                                <?php echo $form->dropDownList($template, 'auto_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('auto_plain_text')); ?>
                                <?php echo $form->error($template, 'auto_plain_text');?>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <?php
                // since 1.3.9.0
                $hooks->doAction('campaign_form_template_step_after_top_options', array(
                    'controller' => $this,
                    'campaign'   => $campaign,
                    'form'       => $form,
                    'template'   => $template,
                ));
                ?>
                
                <hr />
                
                <div class="row">
                    <div class="col-lg-12">
                        <div class="html-version" style="display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;">
                            <div class="form-group">
                                <div class="pull-left">
                                    <?php echo $form->labelEx($template, 'content');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                                    <?php
                                    // since 1.3.5
                                    $hooks->doAction('before_wysiwyg_editor_left_side', array(
                                        'controller' => $this, 
                                        'template'   => $template, 
                                        'campaign'   => $campaign,
                                        'form'       => $form,
                                    ));
                                    ?>
                                </div>
                                <div class="pull-right">
                                    <?php
                                    // since 1.3.5
                                    $hooks->doAction('before_wysiwyg_editor_right_side', array(
                                        'controller' => $this,
                                        'template'   => $template,
                                        'campaign'   => $campaign,
                                        'form'       => $form,
                                    ));
                                    ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                                <?php echo $form->textArea($template, 'content', $template->getHtmlOptions('content', array('rows' => 30))); ?>
                                <?php echo $form->error($template, 'content');?>
                                <?php 
                                // since 1.4.4
                                $hooks->doAction('after_wysiwyg_editor', array(
                                    'controller' => $this, 
                                    'template'   => $template,
                                    'campaign'   => $campaign,
                                    'form'       => $form,
                                )); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr />
                
                <?php if (!empty($templateContentUrls)) { ?>
                    
                    <div class="template-click-actions-list-fields-container" style="display: none;">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="pull-left">
                                    <h5><?php echo Yii::t('campaigns', 'Change subscriber custom field on link click');?></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:;" class="btn btn-primary btn-flat btn-template-click-actions-list-fields-add"><?php echo IconHelper::make('create');?></a>
                                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-template-click-actions-list-fields-list', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                                <div class="template-click-actions-list-fields-list">
                                    <?php if (!empty($templateUrlActionListFields)) { foreach($templateUrlActionListFields as $index => $templateUrlActionListFieldMdl) { ?>
                                        <div class="template-click-actions-list-fields-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionListFieldMdl, 'url');?>
                                                        <?php echo CHtml::dropDownList($templateUrlActionListFieldMdl->modelName.'['.$index.'][url]', $templateUrlActionListFieldMdl->url, $templateContentUrls, $templateUrlActionListFieldMdl->getHtmlOptions('url')); ?>
                                                        <?php echo $form->error($templateUrlActionListFieldMdl, 'url');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionListField, 'field_id');?>
                                                        <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'['.$index.'][field_id]', $templateUrlActionListFieldMdl->field_id, CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateUrlActionListFieldMdl->getCustomFieldsAsDropDownOptions()), $templateUrlActionListFieldMdl->getHtmlOptions('field_id')); ?>
                                                        <?php echo $form->error($templateUrlActionListField, 'field_id');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionListFieldMdl, 'field_value');?>
                                                        <?php echo CHtml::textField($templateUrlActionListFieldMdl->modelName.'['.$index.'][field_value]', $templateUrlActionListFieldMdl->field_value, $templateUrlActionListFieldMdl->getHtmlOptions('field_value')); ?>
                                                        <?php echo $form->error($templateUrlActionListFieldMdl, 'field_value');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1">
                                                    <a style="margin-top: 25px;" href="javascript:;" class="btn btn-danger btn-flat btn-template-click-actions-list-fields-remove" data-url-id="<?php echo $templateUrlActionListFieldMdl->url_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }} ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                                <hr />
                            </div>
                        </div>
                    </div>
                    <div class="template-click-actions-container" style="display: none;">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="pull-left">
                                    <h5><?php echo Yii::t('campaigns', 'Actions against subscriber on link click');?></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:;" class="btn btn-primary btn-flat btn-template-click-actions-add"><?php echo IconHelper::make('create');?></a>
                                    <?php echo CHtml::link(IconHelper::make('info'), '#page-info-template-click-actions-list', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                                <div class="template-click-actions-list">
                                    <?php if (!empty($templateUrlActionSubscriberModels)) { foreach($templateUrlActionSubscriberModels as $index => $templateUrlActionSub) { ?>
                                        <div class="template-click-actions-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionSub, 'url');?>
                                                        <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][url]', $templateUrlActionSub->url, $templateContentUrls, $templateUrlActionSub->getHtmlOptions('url')); ?>
                                                        <?php echo $form->error($templateUrlActionSub, 'url');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionSub, 'action');?>
                                                        <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][action]', $templateUrlActionSub->action, $clickAllowedActions, $templateUrlActionSub->getHtmlOptions('action')); ?>
                                                        <?php echo $form->error($templateUrlActionSub, 'action');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($templateUrlActionSub, 'list_id');?>
                                                        <?php echo CHtml::dropDownList($templateUrlActionSub->modelName.'['.$index.'][list_id]', $templateUrlActionSub->list_id, $templateListsArray, $templateUrlActionSub->getHtmlOptions('list_id')); ?>
                                                        <?php echo $form->error($templateUrlActionSub, 'list_id');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1">
                                                    <a style="margin-top: 25px;" href="javascript:;" class="btn btn-danger btn-template-click-actions-remove" data-url-id="<?php echo $templateUrlActionSub->url_id;?>" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }} ?>
                                </div>
                                <div class="clearfix"><!-- --></div>
                                <hr />
                            </div>
                        </div>
                    </div>
                        
                    <?php if (!empty($webhooksEnabled)) { ?>    
                        <div class="campaign-track-url-webhook-container" style="display: none;">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="pull-left">
                                        <h5><?php echo Yii::t('campaigns', 'Subscribers webhooks on link click');?></h5>
                                    </div>
                                    <div class="pull-right">
                                        <a href="javascript:;" class="btn btn-primary btn-flat btn-campaign-track-url-webhook-add"><?php echo IconHelper::make('create');?></a>
                                        <?php echo CHtml::link(IconHelper::make('info'), '#page-info-campaign-track-url-webhook', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                                    </div>
                                    <div class="clearfix"><!-- --></div>
                                    <div class="campaign-track-url-webhook-list">
                                        <?php if (!empty($urlWebhookModels)) { foreach($urlWebhookModels as $index => $urlWebhookModel) { ?>
                                            <div class="campaign-track-url-webhook-row" data-start-index="<?php echo $index;?>" style="margin-bottom: 10px;">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <?php echo $form->labelEx($urlWebhookModel, 'track_url');?>
                                                            <?php echo CHtml::dropDownList($urlWebhookModel->modelName.'['.$index.'][track_url]', $urlWebhookModel->track_url, $templateContentUrls, $urlWebhookModel->getHtmlOptions('track_url')); ?>
                                                            <?php echo $form->error($urlWebhookModel, 'track_url');?>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-5">
                                                        <div class="form-group">
                                                            <?php echo $form->labelEx($urlWebhookModel, 'webhook_url');?>
                                                            <?php echo CHtml::textField($urlWebhookModel->modelName.'['.$index.'][webhook_url]', $urlWebhookModel->webhook_url, $urlWebhookModel->getHtmlOptions('webhook_url')); ?>
                                                            <?php echo $form->error($urlWebhookModel, 'webhook_url');?>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1">
                                                        <a style="margin-top: 25px;" href="javascript:;" class="btn btn-danger btn-campaign-track-url-webhook-remove" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }} ?>
                                    </div>
                                    <div class="clearfix"><!-- --></div>
                                    <hr />
                                </div>
                            </div>
                        </div>   
                    <?php } ?>
                        
                    <!-- modals -->
                    <div class="modal modal-info fade" id="page-info-template-click-actions-list-fields-list" tabindex="-1" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php echo Yii::t('campaigns', 'When a subscriber clicks one or more links from your email template, do following actions against one of the subscriber custom fields.')?><br />
                                    <?php echo Yii::t('campaigns', 'This is useful if you later need to segment your list and find out who clicked on links in this campaign or who did not and based on that to take another action, like sending the campaign again to subscribers that did/did not clicked certain link previously.');?><br />
                                    <?php echo Yii::t('campaigns', 'In most of the cases, you will want to keep these fields as hidden fields.')?><br />
                                    <br />
                                    <?php echo Yii::t('campaigns', 'Following tags are available to be used as dynamic values:');?><br />
                                    <div style="width: 100%; height: 200px; overflow-y: scroll">
                                        <table class="table table-bordered table-condensed">
                                            <thead>
                                            <tr>
                                                <th><?php echo Yii::t('campaigns', 'Tag');?></th>
                                                <th><?php echo Yii::t('campaigns', 'Description');?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach (CampaignHelper::getParsedFieldValueByListFieldValueTagInfo() as $tag => $tagInfo) { ?>
                                                <tr>
                                                    <td><?php echo $tag;?></td>
                                                    <td><?php echo $tagInfo;?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal modal-info fade" id="page-info-template-click-actions-list" tabindex="-1" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                                </div>
                                <div class="modal-body">
                                    <?php echo Yii::t('campaigns', 'When a subscriber clicks one or more links from your email template, do following actions against the subscriber itself:')?>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <?php if (!empty($webhooksEnabled)) { ?>
                        <div class="modal modal-info fade" id="page-info-campaign-track-url-webhook" tabindex="-1" role="dialog">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo Yii::t('campaigns', 'When a campaign url is clicked by a subscriber, send a webhook request containing event data to the given urls')?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    
                <?php } ?>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="plain-text-version" style="display:<?php echo $template->isOnlyPlainText ? 'block':'none';?>;">
                            <div class="form-group">
                                <?php echo $form->labelEx($template, 'plain_text');?> [<a data-toggle="modal" href="#available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                                <?php echo $form->textArea($template, 'plain_text', $template->getHtmlOptions('plain_text', array('rows' => 20))); ?>
                                <?php echo $form->error($template, 'plain_text');?>
                                <?php echo $form->error($template, 'content');?>
                            </div>
                            <hr />
                        </div>
                    </div>
                </div>
                
                <div class="random-content-container" style="display: none">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="pull-left">
                                <h5><?php echo Yii::t('campaigns', 'Random content blocks');?></h5>
                            </div>
                            <div class="pull-right">
                                <a href="javascript:;" class="btn btn-primary btn-flat btn-template-random-content-item-add"><?php echo IconHelper::make('create');?></a>
                                <?php echo CHtml::link(IconHelper::make('info'), '#page-info-random-content-container', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                            </div>
                            <div class="clearfix"><!-- --></div>
                            <div class="row">
                                <div class="random-content-container-items">
                                    <?php if (!empty($campaign->randomContents)) { foreach ($campaign->randomContents as $index => $rndContent) { ?>
                                        <div class="col-lg-6 random-content-item" data-counter="<?php echo $index;?>" style="margin-top:10px">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <div class="pull-left">
                                                            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-random-content-name', array('class' => 'btn btn-xs btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                                                            <?php echo $form->labelEx($rndContent, 'name');?>
                                                        </div>
                                                        <div class="pull-right">
                                                            <?php echo CHtml::link(IconHelper::make('delete'), 'javascript:;', array('class' => 'btn btn-xs btn-danger btn-flat btn-template-random-content-item-delete', 'title' => Yii::t('app', 'Delete')));?>
                                                        </div>
                                                        <div class="clearfix"><!-- --></div>
                                                        <?php echo $form->textField($rndContent, 'name', $rndContent->getHtmlOptions('name', array(
                                                            'id'   => $rndContent->modelName . '_name_' . (int)$index,
                                                            'name' => $rndContent->modelName . '['.$index.'][name]',
                                                        )));?>
                                                        <?php echo $form->error($rndContent, 'name');?>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <?php echo $form->labelEx($rndContent, 'content');?>
                                                        <?php echo $form->textArea($rndContent, 'content', $rndContent->getHtmlOptions('content', array(
                                                            'id'   => $rndContent->modelName . '_content_' . (int)$index,
                                                            'name' => $rndContent->modelName . '['.$index.'][content]',
                                                        )));?>
                                                        <?php echo $form->error($rndContent, 'content');?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }} ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="random-content-template" style="display: none">
                        <div class="col-lg-6 random-content-item" data-counter="{counter}" style="margin-top:10px">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div class="pull-left">
                                            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-random-content-name', array('class' => 'btn btn-xs btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal'));?>
                                            <?php echo $form->labelEx($randomContent, 'name');?>
                                        </div>
                                        <div class="pull-right">
                                            <?php echo CHtml::link(IconHelper::make('delete'), 'javascript:;', array('class' => 'btn btn-xs btn-danger btn-flat btn-template-random-content-item-delete', 'title' => Yii::t('app', 'Delete')));?>
                                        </div>
                                        <div class="clearfix"><!-- --></div>
                                        <?php echo $form->textField($randomContent, 'name', $randomContent->getHtmlOptions('name', array(
                                            'id'   => $randomContent->modelName . '_name_{counter}',
                                            'name' => $randomContent->modelName . '[{counter}][name]',
                                        )));?>
                                        <?php echo $form->error($randomContent, 'name');?>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <?php echo $form->labelEx($randomContent, 'content');?>
                                        <?php echo $form->textArea($randomContent, 'content', $randomContent->getHtmlOptions('content', array(
                                            'id'   => $randomContent->modelName . '_content_{counter}',
                                            'name' => $randomContent->modelName . '[{counter}][content]',
                                        )));?>
                                        <?php echo $form->error($randomContent, 'content');?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <hr />
                </div>
                <!-- Modals -->
                <div class="modal modal-info fade" id="page-info-random-content-container" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                            </div>
                            <div class="modal-body">
                                <?php echo Yii::t('campaigns', 'Random content blocks allows you to rotate random HTML content in your template body by using the [RANDOM_CONTENT] tag.');?><br />
                                <?php echo Yii::t('campaigns', 'You will define all your random content blocks, and then you will be able to call the [RANDOM_CONTENT] tag like:<br /> {exp} where N1, N2, N3 are the names of your blocks you want to use.<br />You can use an unlimited number of blocks.', array(
                                    '{exp}' => '[RANDOM_CONTENT: BLOCK: N1 | BLOCK: N2 | BLOCK: N3]',
                                ));?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal modal-info fade" id="page-info-random-content-name" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                            </div>
                            <div class="modal-body">
                                <?php echo Yii::t('campaigns', 'Please make sure you use a unique name for your block!');?><br />
                                <?php echo Yii::t('campaigns', 'You will be able to use this block in the [RANDOM_CONTENT] tag like:<br /> {exp} where N1, N2, N3 are the names of your blocks you want to use.<br />You can use an unlimited number of blocks.', array(
                                    '{exp}' => '[RANDOM_CONTENT: BLOCK: N1 | BLOCK: N2 | BLOCK: N3]',
                                ))?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
	                    
                        <?php if (!empty($templateContentUrls)) { ?>
                            <button type="button" class="btn btn-primary btn-flat btn-template-click-actions-list-fields" style="margin-top: 3px">
			                    <?php echo Yii::t('campaigns', 'Change subscriber custom field on link click({count})', array(
				                    '{count}' => sprintf('<span class="count">%d</span>', (!empty($templateUrlActionListFields) ? count($templateUrlActionListFields) : 0))
			                    ));
			                    ?>
                            </button>
	                    
                            <button type="button" class="btn btn-primary btn-flat btn-template-click-actions" style="margin-top: 3px">
			                    <?php echo Yii::t('campaigns', 'Actions against subscriber on link click({count})', array(
				                    '{count}' => sprintf('<span class="count">%d</span>', (!empty($templateUrlActionSubscriberModels) ? count($templateUrlActionSubscriberModels) : 0))
			                    ));
			                    ?>
                            </button>

		                    <?php if (!empty($webhooksEnabled)) { ?>
                                <button type="button" class="btn btn-primary btn-flat btn-campaign-track-url-webhook" style="margin-top: 3px">
                                    <?php echo Yii::t('campaigns', 'Subscribers webhooks on link click({count})', array(
                                        '{count}' => sprintf('<span class="count">%d</span>', (!empty($urlWebhookModels) ? count($urlWebhookModels) : 0))
                                    ));
                                    ?>
                                </button>
                            <?php } ?>

	                    <?php } ?>

	                    <?php echo CHtml::link(Yii::t('campaigns', 'UTM tags'), '#google-utm-tags-modal', array('class' => 'btn btn-primary btn-flat', 'data-toggle' => 'modal', 'title' => Yii::t('campaigns', 'Google UTM tags'), 'style' => 'margin-top: 3px'));?>

	                    <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                            <button type="button" class="btn btn-primary btn-flat btn-plain-text" data-showtext="<?php echo Yii::t('campaigns', 'Show plain text version');?>" data-hidetext="<?php echo Yii::t('campaigns', 'Hide plain text version');?>" style="margin-top:3px; display:<?php echo $template->isOnlyPlainText ? 'none':'';?>;"><?php echo Yii::t('campaigns', 'Show plain text version');?></button>
	                    <?php } ?>

                        <button type="button" class="btn btn-primary btn-flat btn-toggle-random-content" style="margin-top: 3px">
		                    <?php echo Yii::t('campaigns', 'Random content({count})', array(
			                    '{count}' => sprintf('<span class="count">%d</span>', (!empty($campaign->randomContents) ? count($campaign->randomContents) : 0))
		                    ));
		                    ?>
                        </button>

                        <button type="submit" id="is_next" name="is_next" value="0" class="btn btn-primary btn-flat btn-go-next" style="margin-top: 3px"><?php echo Yii::t('campaigns', 'Save template changes only');?></button>
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
            <div class="box-footer">
                <div class="wizard">
                    <ul class="steps">
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/update', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Details');?></a><span class="chevron"></span></li>
                        <li class="complete"><a href="<?php echo $this->createUrl('campaigns/setup', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Setup');?></a><span class="chevron"></span></li>
                        <li class="active"><a href="<?php echo $this->createUrl('campaigns/template', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Template');?></a><span class="chevron"></span></li>
                        <li><a href="<?php echo $this->createUrl('campaigns/confirm', array('campaign_uid' => $campaign->campaign_uid));?>"><?php echo Yii::t('campaigns', 'Confirmation');?></a><span class="chevron"></span></li>
                        <li><a href="javascript:;"><?php echo Yii::t('app', 'Done');?></a><span class="chevron"></span></li>
                    </ul>
                    <div class="actions">
                        <button type="submit" id="is_next" name="is_next" value="1" class="btn btn-primary btn-flat btn-go-next"><?php echo IconHelper::make('next') . '&nbsp;' . Yii::t('campaigns', 'Save and next');?></button>
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
                    <?php foreach ($template->getAvailableTags() as $tag) { ?>
                    <tr>
                        <td><?php echo CHtml::encode($tag['tag']);?></td>
                        <td><?php echo $tag['required'] ? strtoupper(Yii::t('app', CampaignTemplate::TEXT_YES)) : strtoupper(Yii::t('app', CampaignTemplate::TEXT_NO));?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
            </div>
          </div>
        </div>
    </div>
    
    <?php if(!empty($template->content)) { ?>
    <div class="modal fade" id="template-test-email" tabindex="-1" role="dialog" aria-labelledby="template-test-email-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('campaigns', 'Send a test email');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                     <strong><?php echo Yii::t('app', 'Notes');?>: </strong><br />
                     <?php
                     $text = '* if multiple recipients, separate the email addresses by a comma.<br />
                     * the email tags will be parsed and we will pick a random subscriber to impersonate.<br />
                     * the tracking will not be enabled.<br />
                     * make sure you save the template changes before you send the test.';
                     echo Yii::t('campaigns', StringHelper::normalizeTranslationString($text));
                     ?>
                 </div>
                 <?php echo CHtml::form(array('campaigns/test', 'campaign_uid' => $campaign->campaign_uid), 'post', array('id' => 'template-test-form'));?>
                 <div class="form-group">
                     <?php echo CHtml::label(Yii::t('campaigns', 'Recipient(s)'), 'email');?>
                     <?php echo CHtml::textField('email', $lastTestEmails, array('class' => 'form-control', 'placeholder' => Yii::t('campaigns', 'i.e: a@domain.com, b@domain.com, c@domain.com')));?>
                 </div>
                 <div class="clearfix"><!-- --></div>
                 <div class="form-group">
                     <?php echo CHtml::label(Yii::t('campaigns', 'From email (optional)'), 'from_email');?>
                     <?php echo CHtml::textField('from_email', $lastTestFromEmail, array('class' => 'form-control', 'placeholder' => Yii::t('campaigns', 'i.e: me@domain.com')));?>
                 </div>
                 <?php echo CHtml::endForm();?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#template-test-form').submit();"><?php echo IconHelper::make('fa-send') . '&nbsp;' . Yii::t('campaigns', 'Send test');?></button>
            </div>
          </div>
        </div>
    </div>
    <?php } ?>
    
    <div class="modal fade" id="template-upload-modal" tabindex="-1" role="dialog" aria-labelledby="template-upload-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('email_templates',  'Upload template archive');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php
                    $text = '
                    Please see <a href="{templateArchiveHref}">this example archive</a> in order to understand how you should format your uploaded archive!
                    Also, please note we only accept zip files.';
                    echo Yii::t('email_templates',  StringHelper::normalizeTranslationString($text), array(
                        '{templateArchiveHref}' => Yii::app()->apps->getAppUrl('customer', 'assets/files/example-template.zip', false, true),
                    ));
                    ?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'upload'),
                    'id'            => $templateUp->modelName.'-upload-form',
                    'htmlOptions'   => array(
                        'id'        => 'upload-template-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'archive');?>
                    <?php echo $form->fileField($templateUp, 'archive', $templateUp->getHtmlOptions('archive')); ?>
                    <?php echo $form->error($templateUp, 'archive');?>
                </div>
                <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                <div class="form-group">
                    <?php echo $form->labelEx($templateUp, 'auto_plain_text');?>
                    <?php echo $form->dropDownList($templateUp, 'auto_plain_text', $templateUp->getYesNoOptions(), $templateUp->getHtmlOptions('auto_plain_text')); ?>
                    <div class="help-block"><?php echo $templateUp->getAttributeHelpText('auto_plain_text');?></div>
                    <?php echo $form->error($templateUp, 'auto_plain_text');?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#upload-template-form').submit();"><?php echo Yii::t('email_templates',  'Upload archive');?></button>
            </div>
          </div>
        </div>
    </div>
    
    <div class="modal fade" id="template-import-modal" tabindex="-1" role="dialog" aria-labelledby="template-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('email_templates',  'Import html template from url');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('email_templates', 'Please note that your url must contain a valid html email template with absolute paths to resources!');?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid, 'do' => 'from-url'),
                    'id'            => $template->modelName.'-import-form',
                    'htmlOptions'   => array(
                        'id'        => 'import-template-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'from_url');?>
                    <?php echo $form->textField($template, 'from_url', $template->getHtmlOptions('from_url')); ?>
                    <?php echo $form->error($template, 'from_url');?>
                </div>
                <?php if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES) { ?>
                <div class="form-group">
                    <?php echo $form->labelEx($template, 'auto_plain_text');?>
                    <?php echo $form->dropDownList($template, 'auto_plain_text', $template->getYesNoOptions(), $template->getHtmlOptions('auto_plain_text')); ?>
                    <div class="help-block"><?php echo $template->getAttributeHelpText('auto_plain_text');?></div>
                    <?php echo $form->error($template, 'auto_plain_text');?>
                </div>
                <?php } ?>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#import-template-form').submit();"><?php echo Yii::t('email_templates',  'Import');?></button>
            </div>
          </div>
        </div>
    </div>

    <div class="modal fade" id="google-utm-tags-modal" tabindex="-1" role="dialog" aria-labelledby="google-utm-tags-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo Yii::t('campaigns', 'Google UTM tags pattern');?></h4>
                </div>
                <div class="modal-body">
                    <div class="callout">
                        <?php echo Yii::t('campaigns', 'After you insert your UTM tags pattern, each link from your email template will be transformed and this pattern will be appended for tracking. Beside all the regular template tags, following special tags are also recognized:');?>
                        <hr />
                        <table class="table table-bordered table-condensed">
                            <tr>
                                <td><?php echo Yii::t('lists', 'Tag');?></td>
                                <td><?php echo Yii::t('lists', 'Description');?></td>
                            </tr>
                            <?php foreach ($template->getExtraUtmTags() as $tag => $tagDescription) { ?>
                                <tr>
                                    <td><?php echo CHtml::encode($tag);?></td>
                                    <td><?php echo CHtml::encode($tagDescription);?></td>
                                </tr>
                            <?php } ?>
                        </table>
                        <hr />
                        <strong><?php echo Yii::t('campaigns', 'Example pattern:');?></strong><br />
                        <span>utm_source=mail_from_[CURRENT_DATE]&utm_medium=cpc&utm_term=[EMAIL]&utm_campaign=[CAMPAIGN_NAME]</span>
                    </div>
                    <?php echo CHtml::form(array('campaigns/google_utm_tags', 'campaign_uid' => $campaign->campaign_uid), 'post', array('id' => 'google-utm-tags-form'));?>
                    <div class="form-group">
                        <label><?php echo Yii::t('campaigns', 'Insert your pattern');?>:</label>
                        <?php echo CHtml::textField('google_utm_pattern', '', array('class' => 'form-control'));?>
                    </div>
                    <?php echo CHtml::endForm();?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                    <button type="button" class="btn btn-primary btn-flat" onclick="$('#google-utm-tags-form').submit(); return false;"><?php echo Yii::t('campaigns', 'Parse links and set pattern');?></button>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($templateContentUrls)) { ?>
        <div id="template-click-actions-list-fields-template" style="display: none;">
            <div class="template-click-actions-list-fields-row" data-start-index="{index}" style="margin-bottom: 10px;">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionListField, 'url');?>
                            <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'[{index}][url]', null, $templateContentUrls, $templateUrlActionListField->getHtmlOptions('url')); ?>
                            <?php echo $form->error($templateUrlActionListField, 'url');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionListField, 'field_id');?>
                            <?php echo CHtml::dropDownList($templateUrlActionListField->modelName.'[{index}][field_id]', null, CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateUrlActionListField->getCustomFieldsAsDropDownOptions()), $templateUrlActionListField->getHtmlOptions('field_id')); ?>
                            <?php echo $form->error($templateUrlActionListField, 'field_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionListField, 'field_value');?>
                            <?php echo CHtml::textField($templateUrlActionListField->modelName.'[{index}][field_value]', null, $templateUrlActionListField->getHtmlOptions('field_value')); ?>
                            <?php echo $form->error($templateUrlActionListField, 'field_value');?>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <a style="margin-top: 25px;" href="javascript:;" class="btn btn-flat btn-danger btn-template-click-actions-list-fields-remove" data-url-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                    </div>
                </div>
            </div>
        </div>
    
        <div id="template-click-actions-template" style="display: none;">
            <div class="col-lg-12 template-click-actions-row" data-start-index="{index}" style="margin-bottom: 10px;">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionSubscriber, 'url');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][url]', null, $templateContentUrls, $templateUrlActionSubscriber->getHtmlOptions('url')); ?>
                            <?php echo $form->error($templateUrlActionSubscriber, 'url');?>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionSubscriber, 'action');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][action]', null, $clickAllowedActions, $templateUrlActionSubscriber->getHtmlOptions('action')); ?>
                            <?php echo $form->error($templateUrlActionSubscriber, 'action');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($templateUrlActionSubscriber, 'list_id');?>
                            <?php echo CHtml::dropDownList($templateUrlActionSubscriber->modelName.'[{index}][list_id]', null, $templateListsArray, $templateUrlActionSubscriber->getHtmlOptions('list_id')); ?>
                            <?php echo $form->error($templateUrlActionSubscriber, 'list_id');?>
                        </div>
                    </div>
                    <div class="col-lg-1">
                        <a style="margin-top: 25px;" href="javascript:;" class="btn btn-flat btn-danger btn-template-click-actions-remove" data-url-id="0" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                    </div>
                </div>
            </div>
        </div>

		<?php if (!empty($webhooksEnabled)) { ?>
            <div id="campaign-track-url-webhook-template" style="display: none;">
                <div class="campaign-track-url-webhook-row" data-start-index="{index}" style="margin-bottom: 10px;">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <?php echo $form->labelEx($urlWebhook, 'track_url');?>
                                <?php echo CHtml::dropDownList($urlWebhook->modelName.'[{index}][track_url]', null, $templateContentUrls, $urlWebhook->getHtmlOptions('track_url')); ?>
                                <?php echo $form->error($urlWebhook, 'track_url');?>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="form-group">
                                <?php echo $form->labelEx($urlWebhook, 'webhook_url');?>
                                <?php echo CHtml::textField($urlWebhook->modelName.'[{index}][webhook_url]', null, $urlWebhook->getHtmlOptions('webhook_url')); ?>
                                <?php echo $form->error($urlWebhook, 'webhook_url');?>
                            </div>
                        </div>
                        <div class="col-lg-1">
                            <a style="margin-top: 25px;" href="javascript:;" class="btn btn-flat btn-danger btn-campaign-track-url-webhook-remove" data-message="<?php echo Yii::t('app', 'Are you sure you want to remove this item?');?>"><?php echo IconHelper::make('delete');?></a>
                        </div>
                    </div>
                </div>
            </div>    
        <?php } ?>    
            
    <?php } ?>
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