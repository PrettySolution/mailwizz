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
    <div class="pull-left">
        <?php $this->widget('customer.components.web.widgets.MailListSubNavWidget', array(
            'list' => $list,
        ))?>
    </div>
    <div class="clearfix"><!-- --></div>
    <hr />
    
    <?php $this->renderPartial('_filters');?>
    
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('fa-users') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('customer.components.web.widgets.GridViewToggleSubscriberColumns', array('model' => $subscriber, 'list' => $list, 'columns' => $displayToggleColumns), true), count($rows))
                    ->addIf(CHtml::link(IconHelper::make('bulk') . Yii::t('app', 'Bulk action from source'), '#bulk-from-source-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('list_subscribers', 'Bulk action from source'))), count($rows))
                    ->add(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('list_subscribers/create', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('list_subscribers/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->addIf(CHtml::link(IconHelper::make('filter') . Yii::t('app', 'Filters'), 'javascript:;', array('class' => 'btn btn-primary btn-flat toggle-campaigns-filters-form', 'title' => Yii::t('app', 'Filters'))), count($rows))
                    ->render();
                ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">

            <?php
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderGrid} to false
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            $hooks->doAction('before_grid_view', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'renderGrid'    => true,
            )));
            
            /**
             * This widget renders default getting started page for this particular section.
             * @since 1.3.9.2
             */
            $this->widget('common.components.web.widgets.StartPagesWidget', array(
                'collection' => $collection,
                'enabled'    => !count($rows),
            ));
            ?>
        
            <?php if ($collection->renderGrid) { ?>
            
            <div id="subscribers-wrapper">
                <?php $this->renderPartial('_list');?>
            </div>

            <?php }

            /**
             * This hook gives a chance to append content after the grid view content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * @since 1.3.3.1
             */
            $hooks->doAction('after_grid_view', new CAttributeCollection(array(
                'controller'    => $this,
                'renderedGrid'  => $collection->renderGrid,
            )));
            ?>
            
            <?php
            /**
             * Since 1.3.9.8
             * This creates a modal placeholder to push subscriber profile info in.
             */
            $this->widget('customer.components.web.widgets.SubscriberModalProfileInfoWidget');
            ?>
            
        </div>
    </div>
    <div class="modal fade" id="bulk-from-source-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-from-source-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('list_subscribers', 'Bulk action from source');?></h4>
            </div>
            <div class="modal-body">
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('list_subscribers/bulk_from_source', 'list_uid' => $list->list_uid),
                    'htmlOptions'   => array(
                        'id'        => 'bulk-from-source-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="callout callout-info">
                    <?php echo Yii::t('list_subscribers', 'Match the subscribers added here against the ones existing in the list and make a bulk action against them!');?>
                    <br />
                    <strong><?php echo Yii::t('list_subscribers', 'Please note, this is not the list import ability, for list import go to your list overview, followed by Tools box followed by the Import box.');?></strong>
                </div>
                    
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'bulk_from_file');?>
                    <?php echo $form->fileField($subBulkFromSource, 'bulk_from_file', $subBulkFromSource->getHtmlOptions('bulk_from_file')); ?>
                    <?php echo $form->error($subBulkFromSource, 'bulk_from_file');?>
                    <div class="callout callout-info">
                        <?php echo $subBulkFromSource->getAttributeHelpText('bulk_from_file');?>
                    </div>
                </div>
                
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'bulk_from_text');?>
                    <?php echo $form->textArea($subBulkFromSource, 'bulk_from_text', $subBulkFromSource->getHtmlOptions('bulk_from_text', array('rows' => 5))); ?>
                    <?php echo $form->error($subBulkFromSource, 'bulk_from_text');?>
                    <div class="callout callout-info">
                        <?php echo $subBulkFromSource->getAttributeHelpText('bulk_from_text');?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->labelEx($subBulkFromSource, 'status');?>
                    <?php echo $form->dropDownList($subBulkFromSource, 'status', CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $subBulkFromSource->getBulkActionsList()), $subBulkFromSource->getHtmlOptions('status')); ?>
                    <?php echo $form->error($subBulkFromSource, 'status');?>
                    <div class="callout callout-info">
                        <?php echo Yii::t('list_subscribers', 'For all the subscribers found in file/text area take this action!');?>
                    </div>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#bulk-from-source-form').submit();"><?php echo Yii::t('app', 'Submit');?></button>
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
