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
     * @since 1.3.9.2
     */
    $itemsCount = DeliveryServer::model()->count();
    ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-send') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php $box = BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $server, 'columns' => array('customer_id', 'name', 'hostname', 'username', 'from_email', 'type', 'status')), true), $itemsCount)
                    ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new server'), '#select-server-type-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new server'))), AccessHelper::hasRouteAccess('delivery_servers/create'))
                    ->addIf(HtmlHelper::accessLink(IconHelper::make('import') . Yii::t('app', 'Import'), '#csv-import-modal', array('data-toggle' => 'modal', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Import'))), AccessHelper::hasRouteAccess('delivery_servers/import'))
                    ->add(HtmlHelper::accessLink(IconHelper::make('export') . Yii::t('app', 'Export'), array('delivery_servers/export'), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Export'))))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('delivery_servers/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->render();
                    
                    unset($box);
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
                // since 1.3.5.4
                if (AccessHelper::hasRouteAccess('delivery_servers/bulk_action')) { 
                    $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                        'model'      => $server,
                        'formAction' => $this->createUrl('delivery_servers/bulk_action'),
                    ));
                }
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $server->modelName.'-grid',
                    'dataProvider'      => $server->search(),
                    'filter'            => $server,
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
                            'name'                => 'server_id',
                            'selectableRows'      => 100,  
                            'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                            'visible'             => AccessHelper::hasRouteAccess('delivery_servers/bulk_action'),
                        ),
                        array(
                            'name'  => 'customer_id',
                            'value' => '!empty($data->customer) ? $data->customer->getFullName() : Yii::t("app", "System")',
                            'filter'=> CHtml::activeTextField($server, 'customer_id'),
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'empty($data->name) ? null : HtmlHelper::accessLink($data->name, array("delivery_servers/update", "type" => $data->type, "id" => $data->server_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ), 
                        array(
                            'name'  => 'hostname',
                            'value' => 'HtmlHelper::accessLink($data->hostname, array("delivery_servers/update", "type" => $data->type, "id" => $data->server_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'username',
                            'value' => '$data->username',
                        ),
                        array(
                            'name'  => 'from_email',
                            'value' => '$data->from_email',
                        ),
                        array(
                            'name'  => 'type',
                            'value' => 'DeliveryServer::getNameByType($data->type)',
                            'filter'=> $server->getTypesList(),
                        ),
                        array(
                            'name'  => 'status',
                            'value' => 'ucfirst(Yii::t("app", $data->status))',
                            'filter'=> $server->getStatusesList(),
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $server->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update' => array(
                                    'label'     => IconHelper::make('update'), 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/update", array("type" => $data->type, "id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Update'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/update") && $data->getCanBeUpdated()',
                                ),
                                'copy'=> array(
                                    'label'     => IconHelper::make('copy'), 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/copy", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Copy'), 'class' => 'btn btn-primary btn-flat copy-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/copy")',
                                ),
                                'enable'=> array(
                                    'label'     => IconHelper::make('glyphicon-open'), 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/enable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Enable'), 'class' => 'btn btn-primary btn-flat enable-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/enable") && $data->getIsDisabled()',
                                ),
                                'disable'=> array(
                                    'label'     => IconHelper::make('glyphicon-save'), 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/disable", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Disable'), 'class' => 'btn btn-danger btn-flat disable-server'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/disable") && $data->getIsActive()',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'), 
                                    'url'       => 'Yii::app()->createUrl("delivery_servers/delete", array("id" => $data->server_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app','Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("delivery_servers/delete") && $data->getCanBeDeleted()',
                                ),    
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{update} {copy} {enable} {disable} {delete}'
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

    <?php if (AccessHelper::hasRouteAccess('delivery_servers/create')) { ?>
    <div class="modal fade" id="select-server-type-modal" tabindex="-1" role="dialog" aria-labelledby="select-server-type-modal-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo Yii::t('servers', 'Select a delivery server type');?></h4>
                </div>
                <div class="modal-body">
                    <div class="search-box">
                        <input name="search" type="text" class="form-control" placeholder="<?php echo Yii::t('servers', 'Search or scroll for more...');?>" />
                    </div>
                    <div class="clearfix"><!-- --></div>
                    <ul class="select-delivery-servers-list">
                        <?php foreach (DeliveryServer::getTypesMapping() as $type => $className) { 
                            $instance = new $className();
                            ?>
                            <li>
                                <a href="<?php echo $this->createUrl('delivery_servers/create', array('type' => $instance->type));?>">
                                    <?php echo $instance->typeName;?>
                                    <?php if ($instance->isRecommended) { ?><span title="<?php echo Yii::t('servers', 'Recommended');?>"><?php echo IconHelper::make('fa-thumbs-up');?></span><?php } ?>
                                </a>
                                
                                <?php if ($providerUrl = $instance->getProviderUrl()) { ?>
                                    <a href="<?php echo $providerUrl; ?>" target="_blank" title="<?php echo Yii::t('servers', 'Sign up');?>">
                                        <?php echo IconHelper::make('fa-sign-in'); ?>
                                    </a>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    
    <div class="modal fade" id="csv-import-modal" tabindex="-1" role="dialog" aria-labelledby="csv-import-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('servers', 'Import from CSV file');?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                    <?php echo Yii::t('servers', 'Please note, the csv file must contain a header with proper columns.');?><br />
                    <?php echo Yii::t('servers', 'If unsure about how to format your file, do an export first and see how the file looks.');?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('delivery_servers/import'),
                    'htmlOptions'   => array(
                        'id'        => 'import-csv-form', 
                        'enctype'   => 'multipart/form-data'
                    ),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($csvImport, 'file');?>
                    <?php echo $form->fileField($csvImport, 'file', $csvImport->getHtmlOptions('file')); ?>
                    <?php echo $form->error($csvImport, 'file');?>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#import-csv-form').submit();"><?php echo IconHelper::make('import') . '&nbsp;' . Yii::t('app', 'Import file');?></button>
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