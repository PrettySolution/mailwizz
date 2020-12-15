<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
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
     * @since 1.3.9.2
     */
    $itemsCount = CustomerSuppressionListEmail::model()->countByAttributes(array(
        'list_id' => $list->list_id,
    ));
    ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-ban-circle') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $email, 'columns' => array('email', 'date_added', 'last_updated')), true), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('suppression_list_emails/create', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->addIf(CHtml::link(IconHelper::make('export') . Yii::t('app', 'Export'), array('suppression_list_emails/export', 'list_uid' => $list->list_uid), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Export'))), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('glyphicon-import') . Yii::t('app', 'Import'), '#csv-import-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Import'))))
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('suppression_list_emails/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->add(CHtml::link(IconHelper::make('back') . Yii::t('app', 'Back'), array('suppression_lists/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Back'))))
                    ->render();
                ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
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
                    'controller'  => $this,
                    'renderGrid'  => true,
                )));

                /**
                 * This widget renders default getting started page for this particular section.
                 * @since 1.3.9.2
                 */
                $this->widget('common.components.web.widgets.StartPagesWidget', array(
                    'collection' => $collection,
                    'enabled'    => !$itemsCount,
                ));
                
                // and render if allowed
                if ($collection->renderGrid) {
                    $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                        'model'      => $email,
                        'formAction' => $this->createUrl('suppression_list_emails/bulk_action', array('list_uid' => $list->list_uid)),
                    ));
                    $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                        'ajaxUrl'           => $this->createUrl($this->route, array('list_uid' => $list->list_uid)),
                        'id'                => $email->modelName.'-grid',
                        'dataProvider'      => $email->search(),
                        'filter'            => $email,
                        'filterPosition'    => 'body',
                        'filterCssClass'    => 'grid-filter-cell',
                        'itemsCssClass'     => 'table table-hover',
                        'selectableRows'    => 0,
                        'enableSorting'     => false,
                        'cssFile'           => false,
                        'pagerCssClass'     => 'pagination pull-right',
                        'pager'             => array(
                            'class'         => 'CLinkPager',
                            'cssFile'       => false,
                            'header'        => false,
                            'htmlOptions'   => array('class' => 'pagination')
                        ),
                        'columns' => $hooks->applyFilters('grid_view_columns', array(
                            array(
                                'class'               => 'CCheckBoxColumn',
                                'name'                => 'email_id',
                                'selectableRows'      => 100,
                                'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                            ),
                            array(
                                'name'  => 'email',
                                'value' => '$data->displayEmail',
                            ),
                            array(
                                'class'     => 'CButtonColumn',
                                'header'    => Yii::t('app', 'Options'),
                                'footer'    => $email->paginationOptions->getGridFooterPagination(),
                                'buttons'   => array(
                                    'update' => array(
                                        'label'     => IconHelper::make('update'),
                                        'url'       => 'Yii::app()->createUrl("suppression_list_emails/update", array("list_uid" => $data->list->list_uid, "email_id" => $data->email_id))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                    ),
                                    'delete' => array(
                                        'label'     => IconHelper::make('delete'),
                                        'url'       => 'Yii::app()->createUrl("suppression_list_emails/delete", array("list_uid" => $data->list->list_uid, "email_id" => $data->email_id))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    ),
                                ),
                                'headerHtmlOptions' => array('style' => 'text-align: right'),
                                'footerHtmlOptions' => array('align' => 'right'),
                                'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                                'template'          => '{update} {delete}'
                            ),
                        ), $this),
                    ), $this));
                }
                /**
                 * This hook gives a chance to append content after the grid view content.
                 * Please note that from inside the action callback you can access all the controller view
                 * variables via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                $hooks->doAction('after_grid_view', new CAttributeCollection(array(
                    'controller'  => $this,
                    'renderedGrid'=> $collection->renderGrid,
                )));
                ?>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="csv-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo Yii::t('suppression_lists', 'Import from CSV file');?></h4>
                </div>
                <div class="modal-body">
                    <div class="callout callout-info">
                        <?php echo Yii::t('suppression_lists', 'Please note, the csv file must contain a header with the email column.');?><br />
                        <?php echo Yii::t('suppression_lists', 'If unsure about how to format your file, do an export first and see how the file looks.');?>
                    </div>
                    <?php
                    $form = $this->beginWidget('CActiveForm', array(
                        'action'        => $importUrl,
                        'htmlOptions'   => array(
                            'id'        => 'import-csv-form',
                            'enctype'   => 'multipart/form-data'
                        ),
                    ));
                    ?>
                    <div class="form-group">
                        <?php echo $form->labelEx($email, 'file');?>
                        <?php echo $form->fileField($email, 'file', $email->getHtmlOptions('file')); ?>
                        <?php echo $form->error($email, 'file');?>
                    </div>
                    <?php $this->endWidget(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                    <button type="button" class="btn btn-primary btn-flat" onclick="$('#import-csv-form').submit();"><?php echo Yii::t('app', 'Import file');?></button>
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