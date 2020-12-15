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
    $itemsCount = Lists::model()->count();
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
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $list, 'columns' => array('list_uid', 'customer_id', 'display_name', 'default_from_name', 'default_from_email', 'subscribers_count', 'last_updated')), true), $itemsCount && !$list->getIsArchived())
                    ->addIf(CHtml::link(IconHelper::make('fa-users') . Yii::t('app', 'All subscribers'), array('lists/all_subscribers'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'All subscribers'))), $itemsCount && !$list->getIsArchived())
	                ->addIf(CHtml::link(IconHelper::make('glyphicon-compressed') . Yii::t('app', 'Archived lists'), array('lists/index', 'Lists[status]' => Lists::STATUS_ARCHIVED), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('lists', 'View archived lists'))), !$list->getIsArchived())
	                ->addIf(CHtml::link(IconHelper::make('glyphicon-list-alt') . Yii::t('app', 'All lists'), array('lists/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('lists', 'View all lists'))), $list->getIsArchived())
	                ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), $refreshRoute, array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
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
                'controller'    => $this,
                'renderGrid'    => true,
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
                    'ajaxUrl'           => $gridAjaxUrl,
                    'id'                => $list->modelName.'-grid',
                    'dataProvider'      => $list->search(),
                    'filter'            => $list,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => true,
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
                            'name'  => 'list_uid',
                            'value' => 'HtmlHelper::accessLink($data->list_uid, array("lists/overview", "list_uid" => $data->list_uid), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'customer_id',
                            'value' => 'HtmlHelper::accessLink($data->customer->fullName, array("customers/update", "id" => $data->customer_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'display_name',
                            'value' => 'HtmlHelper::accessLink($data->display_name, array("lists/overview", "list_uid" => $data->list_uid), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'      => 'default_from_name',
                            'value'     => '$data->default->from_name',
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'default_from_email',
                            'value'     => '$data->default->from_email',
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'subscribers_count',
                            'value'     => 'Yii::app()->format->formatNumber($data->getConfirmedSubscribersCount(true)) . " / " . Yii::app()->format->formatNumber($data->getSubscribersCount(true))',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'  => 'last_updated',
                            'value' => '$data->lastUpdated',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $list->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'overview' => array(
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'Yii::app()->createUrl("lists/overview", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('lists', 'Overview'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("lists/overview") && !$data->isPendingDelete',
                                ),
                                'archive' => [
	                                'label'     => IconHelper::make('glyphicon-compressed'),
	                                'url'       => 'Yii::app()->createUrl("lists/toggle_archive", array("list_uid" => $data->list_uid))',
	                                'imageUrl'  => null,
	                                'options'   => ['title' => Yii::t('app', 'Archive'), 'class' => 'btn btn-primary btn-flat'],
	                                'visible'   => '!$data->getIsPendingDelete() && !$data->getIsArchived()',
                                ],
                                'unarchive' => [
	                                'label'     => IconHelper::make('glyphicon-expand'),
	                                'url'       => 'Yii::app()->createUrl("lists/toggle_archive", array("list_uid" => $data->list_uid))',
	                                'imageUrl'  => null,
	                                'options'   => ['title' => Yii::t('app', 'Unarchive'), 'class' => 'btn btn-primary btn-flat'],
	                                'visible'   => '!$data->getIsPendingDelete() && $data->getIsArchived()',
                                ],
                                'confirm_delete' => array(
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'Yii::app()->createUrl("lists/delete", array("list_uid" => $data->list_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("lists/delete") && $data->isRemovable',
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          =>'{overview} {archive} {unarchive} {confirm_delete}'
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
                'controller'    => $this,
                'renderedGrid'  => $collection->renderGrid,
            )));
            ?>
            <div class="clearfix"><!-- --></div>
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
