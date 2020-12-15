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
if ($viewCollection->renderContent) { ?>
    <?php if (!$list->isNewRecord) { ?>
    <div class="pull-left">
        <?php $this->widget('customer.components.web.widgets.MailListSubNavWidget', array(
            'list' => $list,
        ))?>
    </div>
    <div class="clearfix"><!-- --></div>
    <hr />
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
                        ->addIf(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('lists/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$list->isNewRecord)
                        ->add(CHtml::link(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('lists/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
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
                 *
                 * @since 1.3.3.1
                 */
                $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form
                )));
                ?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <h3 class="box-title"><?php echo Yii::t('lists', 'General data');?></h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'name');?>
                                            <?php echo $form->textField($list, 'name', $list->getHtmlOptions('name')); ?>
                                            <?php echo $form->error($list, 'name');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'display_name');?>
                                            <?php echo $form->textField($list, 'display_name', $list->getHtmlOptions('display_name')); ?>
                                            <?php echo $form->error($list, 'display_name');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'description');?>
                                            <?php echo $form->textField($list, 'description', $list->getHtmlOptions('description')); ?>
                                            <?php echo $form->error($list, 'description');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'opt_in');?>
                                            <?php echo $form->dropDownList($list, 'opt_in', $list->getOptInArray(), $list->getHtmlOptions('opt_in', array(
                                                $forceOptIn ? 'disabled' : 'data-disabled' => $forceOptIn ? 'disabled' : 'false',
                                            ))); ?>
                                            <?php echo $form->error($list, 'opt_in');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <?php echo $form->labelEx($list, 'opt_out');?>
                                        <?php echo $form->dropDownList($list, 'opt_out', $list->getOptOutArray(), $list->getHtmlOptions('opt_out', array(
                                                $forceOptOut ? 'disabled' : 'data-disabled' => $forceOptOut ? 'disabled' : 'false',
                                        ))); ?>
                                        <?php echo $form->error($list, 'opt_out');?>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'welcome_email');?>
                                            <?php echo $form->dropDownList($list, 'welcome_email', $list->getYesNoOptions(), $list->getHtmlOptions('welcome_email')); ?>
                                            <?php echo $form->error($list, 'welcome_email');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'subscriber_404_redirect');?>
                                            <?php echo $form->textField($list, 'subscriber_404_redirect', $list->getHtmlOptions('subscriber_404_redirect')); ?>
                                            <?php echo $form->error($list, 'subscriber_404_redirect');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'subscriber_exists_redirect');?>
                                            <?php echo $form->textField($list, 'subscriber_exists_redirect', $list->getHtmlOptions('subscriber_exists_redirect')); ?>
                                            <?php echo $form->error($list, 'subscriber_exists_redirect');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($list, 'subscriber_require_approval');?>
                                            <?php echo $form->dropDownList($list, 'subscriber_require_approval', $list->getYesNoOptions(), $list->getHtmlOptions('subscriber_require_approval')); ?>
                                            <?php echo $form->error($list, 'subscriber_require_approval');?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <h3 class="box-title"><?php echo Yii::t('lists', 'Defaults');?></h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listDefault, 'from_name');?>
                                            <?php echo $form->textField($listDefault, 'from_name', $listDefault->getHtmlOptions('from_name')); ?>
                                            <?php echo $form->error($listDefault, 'from_name');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listDefault, 'from_email');?>
                                            <?php echo $form->emailField($listDefault, 'from_email', $listDefault->getHtmlOptions('from_email')); ?>
                                            <?php echo $form->error($listDefault, 'from_email');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div>
                                                <?php echo $form->labelEx($listDefault, 'reply_to');?>
                                            </div>
                                            <?php echo $form->emailField($listDefault, 'reply_to', $listDefault->getHtmlOptions('reply_to')); ?>
                                            <?php echo $form->error($listDefault, 'reply_to');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listDefault, 'subject');?>
                                            <?php echo $form->textField($listDefault, 'subject', $listDefault->getHtmlOptions('subject')); ?>
                                            <?php echo $form->error($listDefault, 'subject');?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <h3 class="box-title"><?php echo Yii::t('lists', 'Notifications');?></h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <?php echo $form->labelEx($listCustomerNotification, 'subscribe');?>
                                                    <?php echo $form->dropDownList($listCustomerNotification, 'subscribe', $listCustomerNotification->getYesNoDropdownOptions(),$listCustomerNotification->getHtmlOptions('subscribe')); ?>
                                                    <?php echo $form->error($listCustomerNotification, 'subscribe');?>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <?php echo $form->labelEx($listCustomerNotification, 'unsubscribe');?>
                                                    <?php echo $form->dropDownList($listCustomerNotification, 'unsubscribe', $listCustomerNotification->getYesNoDropdownOptions(),$listCustomerNotification->getHtmlOptions('unsubscribe')); ?>
                                                    <?php echo $form->error($listCustomerNotification, 'unsubscribe');?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <?php echo $form->labelEx($listCustomerNotification, 'subscribe_to');?>
                                                    <?php echo $form->textField($listCustomerNotification, 'subscribe_to', $listCustomerNotification->getHtmlOptions('subscribe_to')); ?>
                                                    <?php echo $form->error($listCustomerNotification, 'subscribe_to');?>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <?php echo $form->labelEx($listCustomerNotification, 'unsubscribe_to');?>
                                                    <?php echo $form->textField($listCustomerNotification, 'unsubscribe_to', $listCustomerNotification->getHtmlOptions('unsubscribe_to')); ?>
                                                    <?php echo $form->error($listCustomerNotification, 'unsubscribe_to');?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <h3 class="box-title"><?php echo Yii::t('lists', 'Subscriber actions');?></h3>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <ul class="nav nav-tabs">
                                            <li class="active">
                                                <a href="#tab-subscriber-action-when-subscribe" data-toggle="tab">
                                                    <?php echo Yii::t('lists', 'Actions when subscribe');?>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#tab-subscriber-action-when-unsubscribe" data-toggle="tab">
                                                    <?php echo Yii::t('lists', 'Actions when unsubscribe');?>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tab-subscriber-action-when-subscribe">
                                                <div class="callout callout-info" style="margin-bottom: 5px; margin-top: 5px;">
                                                    <?php echo Yii::t('lists', 'When a subscriber will subscribe into this list, if he exists in any of the lists below, unsubscribe him from them. Please note that the unsubscribe from the lists below is silent, no email is sent to the subscriber.');?>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <?php $hooks->doAction('list_subscriber_actions_subscribe_action_before_html_display', $list); ?>
                                                            <div class="list-subscriber-actions-scrollbox">
                                                                <ul class="list-group">
                                                                    <?php echo CHtml::checkBoxList($listSubscriberAction->modelName . '['. ListSubscriberAction::ACTION_SUBSCRIBE .'][]', $selectedSubscriberActions[ListSubscriberAction::ACTION_SUBSCRIBE], $subscriberActionLists, $listSubscriberAction->getHtmlOptions('target_list_id', array(
                                                                        'class'        => '',
                                                                        'template'     => '<li class="list-group-item">{beginLabel}{input} <span>{labelTitle}</span> {endLabel}</li>',
                                                                        'container'    => '',
                                                                        'separator'    => '',
                                                                        'labelOptions' => array('style' => 'margin-right: 10px;')
                                                                    ))); ?>
                                                                </ul>
                                                            </div>
                                                            <?php $hooks->doAction('list_subscriber_actions_subscribe_action_after_html_display', $list); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="tab-pane" id="tab-subscriber-action-when-unsubscribe">
                                                <div class="callout callout-info" style="margin-bottom: 5px; margin-top: 5px;">
                                                    <?php echo Yii::t('lists', 'When a subscriber will unsubscribe from this list, if he exists in any of the lists below, unsubscribe him from them too. Please note that the unsubscribe from the lists below is silent, no email is sent to the subscriber.');?>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <?php $hooks->doAction('list_subscriber_actions_unsubscribe_action_before_html_display', $list); ?>
                                                            <div class="list-subscriber-actions-scrollbox">
                                                                <ul class="list-group">
                                                                    <?php echo CHtml::checkBoxList($listSubscriberAction->modelName . '['. ListSubscriberAction::ACTION_UNSUBSCRIBE .'][]', $selectedSubscriberActions[ListSubscriberAction::ACTION_UNSUBSCRIBE], $subscriberActionLists, $listSubscriberAction->getHtmlOptions('target_list_id', array(
                                                                        'class'        => '',
                                                                        'template'     => '<li class="list-group-item">{beginLabel}{input} <span>{labelTitle}</span> {endLabel}</li>',
                                                                        'container'    => '',
                                                                        'separator'    => '',
                                                                        'labelOptions' => array('style' => 'margin-right: 10px;')
                                                                    ))); ?>
                                                                </ul>
                                                            </div>
                                                            <?php $hooks->doAction('list_subscriber_actions_unsubscribe_action_after_html_display', $list); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="box box-primary borderless">
                            <div class="box-header">
                                <div class="pull-left">
                                    <h3 class="box-title"><?php echo Yii::t('lists', 'Company details');?> <small>(<?php echo Yii::t('lists', 'defaults to <a href="{href}">account company</a>', array('{href}' => $this->createUrl('account/company')));?>)</small></h3>
                                </div>
                                <div class="pull-right"></div>
                                <div class="clearfix"><!-- --></div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'name');?>
                                            <?php echo $form->textField($listCompany, 'name', $listCompany->getHtmlOptions('name')); ?>
                                            <?php echo $form->error($listCompany, 'name');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'type_id');?>
                                            <?php echo $form->dropDownList($listCompany, 'type_id', CMap::mergeArray(array('' => Yii::t('app', 'Please select')), CompanyType::getListForDropDown()), $listCompany->getHtmlOptions('type_id')); ?>
                                            <?php echo $form->error($listCompany, 'type_id');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'country_id');?>
                                            <?php echo $listCompany->getCountriesDropDown(); ?>
                                            <?php echo $form->error($listCompany, 'country_id');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'zone_id');?>
                                            <?php echo $listCompany->getZonesDropDown(); ?>
                                            <?php echo $form->error($listCompany, 'zone_id');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'address_1');?>
                                            <?php echo $form->textField($listCompany, 'address_1', $listCompany->getHtmlOptions('address_1')); ?>
                                            <?php echo $form->error($listCompany, 'address_1');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'address_2');?>
                                            <?php echo $form->textField($listCompany, 'address_2', $listCompany->getHtmlOptions('address_2')); ?>
                                            <?php echo $form->error($listCompany, 'address_2');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2 zone-name-wrap">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'zone_name');?>
                                            <?php echo $form->textField($listCompany, 'zone_name', $listCompany->getHtmlOptions('zone_name')); ?>
                                            <?php echo $form->error($listCompany, 'zone_name');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 city-wrap">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'city');?>
                                            <?php echo $form->textField($listCompany, 'city', $listCompany->getHtmlOptions('city')); ?>
                                            <?php echo $form->error($listCompany, 'city');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 zip-wrap">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'zip_code');?>
                                            <?php echo $form->textField($listCompany, 'zip_code', $listCompany->getHtmlOptions('zip_code')); ?>
                                            <?php echo $form->error($listCompany, 'zip_code');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 phone-wrap">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'phone');?>
                                            <?php echo $form->textField($listCompany, 'phone', $listCompany->getHtmlOptions('phone')); ?>
                                            <?php echo $form->error($listCompany, 'phone');?>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'website');?>
                                            <?php echo $form->urlField($listCompany, 'website', $listCompany->getHtmlOptions('website')); ?>
                                            <?php echo $form->error($listCompany, 'website');?>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <?php echo $form->labelEx($listCompany, 'address_format');?> [<a data-toggle="modal" href="#company-available-tags-modal"><?php echo Yii::t('lists', 'Available tags');?></a>]
                                            <?php echo $form->textArea($listCompany, 'address_format', $listCompany->getHtmlOptions('address_format', array('rows' => 4))); ?>
                                            <?php echo $form->error($listCompany, 'address_format');?>
                                        </div>
                                    </div>
                                </div>
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
     * This hook gives a chance to append content after the active form fields.
     * Please note that from inside the action callback you can access all the controller view variables
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.3.1
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
    ?>
    <div class="modal fade" id="company-available-tags-modal" tabindex="-1" role="dialog" aria-labelledby="company-available-tags-modal-label" aria-hidden="true">
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
                    <?php foreach ($listCompany->getAvailableTags() as $tag) { ?>
                    <tr>
                        <td><?php echo CHtml::encode($tag['tag']);?></td>
                        <td><?php echo $tag['required'] ? strtoupper(Yii::t('app', ListCompany::TEXT_YES)) : strtoupper(Yii::t('app', ListCompany::TEXT_NO));?></td>
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
