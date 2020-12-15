<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.9
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
    $itemsCount = EmailBlacklistMonitor::model()->count();
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
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $monitor, 'columns' => array('name', 'email_condition', 'email', 'reason_condition', 'reason', 'condition_operator', 'notifications_to', 'status', 'date_added')), true), $itemsCount)
                    ->addIf(HtmlHelper::accessLink(IconHelper::make('delete') . Yii::t('app', 'Remove all'), array('email_blacklist_monitors/delete_all'), array('class' => 'btn btn-danger btn-flat delete-all', 'title' => Yii::t('app', 'Remove all'), 'data-message' => Yii::t('dashboard', 'Are you sure you want to remove all blacklist monitors?'))), $itemsCount)
                    ->addIf(HtmlHelper::accessLink(IconHelper::make('export') . Yii::t('app', 'Export'), array('email_blacklist_monitors/export'), array('class' => 'btn btn-primary btn-flat', 'target' => '_blank', 'title' => Yii::t('app', 'Export'))), $itemsCount)
                    ->addIf(CHtml::link(IconHelper::make('import') . Yii::t('app', 'Import'), '#csv-import-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Import'))), AccessHelper::hasRouteAccess('email_blacklist_monitors/import'))
                    ->add(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('email_blacklist_monitors/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('email_blacklist_monitors/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->add(CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal')))
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
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $monitor->modelName.'-grid',
                    'dataProvider'      => $monitor->search(),
                    'filter'            => $monitor,
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
                            'name'  => 'name',
                            'value' => '$data->name',
                        ),
                        array(
                            'name'  => 'email_condition',
                            'value' => 'Yii::t("email_blacklist", ucfirst($data->email_condition))',
                            'filter'=> $monitor->getConditionsList(),
                        ),
                        array(
                            'name'  => 'email',
                            'value' => '$data->email',
                        ),
                        array(
                            'name'  => 'reason_condition',
                            'value' => 'Yii::t("email_blacklist", ucfirst($data->reason_condition))',
                            'filter'=> $monitor->getConditionsList(),
                        ),
                        array(
                            'name'  => 'reason',
                            'value' => '$data->reason',
                        ),
                        array(
                            'name'  => 'condition_operator',
                            'value' => 'Yii::t("email_blacklist", ucfirst($data->condition_operator))',
                            'filter'=> $monitor->getConditionOperatorsList(),
                        ),
                        array(
                            'name'  => 'notifications_to',
                            'value' => '$data->notifications_to',
                            'filter'=> true,
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->statusName',
                            'filter'=> $monitor->getStatusesList(),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $monitor->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => IconHelper::make('update'), 
                                    'url'       => 'Yii::app()->createUrl("email_blacklist_monitors/update", array("id" => $data->monitor_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("email_blacklist_monitors/update")',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'), 
                                    'url'       => 'Yii::app()->createUrl("email_blacklist_monitors/delete", array("id" => $data->monitor_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("email_blacklist_monitors/delete")',
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
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                </div>
                <div class="modal-body">
                    <?php echo Yii::t('email_blacklist', 'Blacklist monitors will monitor the email blacklist and when emails matching the conditions will be added in the blacklist, they will be removed automatically and subscribers matching the emails will be marked back as confirmed.');?><br />
                    <?php echo Yii::t('email_blacklist', 'Please note that in order for the monitoring to work, you need to add the following cron job, which runs once per hour:');?><br />
                    <b>0 * * * * <?php echo CommonHelper::findPhpCliPath();?> -q <?php echo MW_PATH;?>/apps/console/console.php email-blacklist-monitor >/dev/null 2>&1 </b>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="csv-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo Yii::t('email_blacklist', 'Import from CSV file');?></h4>
                </div>
                <div class="modal-body">
                    <div class="callout callout-info">
                        <?php echo Yii::t('email_blacklist', 'If unsure about how to format your file, do an export first and see how the file looks.');?>
                    </div>
                    <?php
                    $form = $this->beginWidget('CActiveForm', array(
                        'action'        => array('email_blacklist_monitors/import'),
                        'htmlOptions'   => array(
                            'id'        => 'import-csv-form',
                            'enctype'   => 'multipart/form-data'
                        ),
                    ));
                    ?>
                    <div class="form-group">
                        <?php echo $form->labelEx($monitor, 'file');?>
                        <?php echo $form->fileField($monitor, 'file', $monitor->getHtmlOptions('file')); ?>
                        <?php echo $form->error($monitor, 'file');?>
                    </div>
                    <?php $this->endWidget(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
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