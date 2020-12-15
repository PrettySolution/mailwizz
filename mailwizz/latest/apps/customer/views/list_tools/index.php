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
 * @since 1.3.4.3
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
    
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('list');?> <?php echo $pageHeading;?></h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('back') . Yii::t('lists', 'List overview'), array('lists/overview', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'List overview')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="row boxes-mw-wrapper">
                <?php if (!empty($canImport)) { ?>
                    <div class="col-lg-4 col-xs-4">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h3><a href="<?php echo Yii::app()->createUrl("list_import/index", array("list_uid" => $list->list_uid));?>"><?php echo Yii::t('list_import', 'Import');?></a></h3>
                                    <p><?php echo Yii::t('app', 'Tools');?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <i class="ion ion-ios-upload"></i>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if (!empty($canExport)) { ?>
                    <div class="col-lg-4 col-xs-4">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h3><a href="<?php echo Yii::app()->createUrl("list_export/index", array("list_uid" => $list->list_uid));?>"><?php echo Yii::t('list_export', 'Export');?></a></h3>
                                    <p><?php echo Yii::t('app', 'Tools');?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <i class="ion ion-ios-download"></i>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if (!empty($canCopy)) { ?>
                    <div class="col-lg-4 col-xs-4">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h3><a href="#copy-list-subscribers-modal" class="btn-show-copy-subs-ajax" data-ajax="<?php echo $this->createUrl('list_tools/copy_subscribers_ajax', array('list_uid' => $list->list_uid));?>" data-toggle="modal"><?php echo Yii::t('lists', 'Copy');?></a></h3>
                                    <p><?php echo Yii::t('lists', 'Subscribers');?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <i class="ion ion-hammer"></i>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($canCopy)) { ?>
    <div class="modal fade" id="copy-list-subscribers-modal" tabindex="-1" role="dialog" aria-labelledby="copy-list-subscribers-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('lists', 'Copy subscribers from another list');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('lists', 'Copy the confirmed subscribers from the selected list/segment below into the current one.')?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('list_tools/copy_subscribers', 'list_uid' => $list->list_uid),
                    'htmlOptions'   => array('id' => 'copy-subscribers-form'),
                ));
                ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'List'), '');?>
                    <?php echo CHtml::dropDownList('copy_list_id', null, array(), $list->getHtmlOptions('list_id')); ?>
                </div>
                <?php if (!empty($canSegmentLists)) { ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'Segment'), '');?>
                    <?php echo CHtml::dropDownList('copy_segment_id', null, array(), $list->getHtmlOptions('list_id')); ?>
                </div>
                <?php } ?>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'Only with these statuses'), '');?>
                    <?php echo CHtml::dropDownList('copy_status', ListSubscriber::STATUS_CONFIRMED, $subscriber->getFilterStatusesList(), $list->getHtmlOptions('copy_status', array('multiple' => true))); ?>
                </div>
                <div class="form-group">
                    <?php echo CHtml::label(Yii::t('lists', 'Action against the status'), '');?>
                    <?php echo CHtml::dropDownList('copy_status_action', 0, array(0 => Yii::t('lists', 'Leave as is'), 1 => Yii::t('lists', 'Force confirmed')), $list->getHtmlOptions('copy_status_action')); ?>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#copy-subscribers-form').submit();"><?php echo Yii::t('app', 'Copy');?></button>
            </div>
          </div>
        </div>
    </div>
    <?php } ?>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.4.3
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));