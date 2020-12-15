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
    $itemsCount = Customer::model()->count();
    ?>
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
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $customer, 'columns' => array('customer_id', 'customer_uid', 'first_name', 'last_name', 'email', 'company_name', 'group_id', 'sending_quota_usage', 'status', 'date_added')), true), $itemsCount)
                    ->add(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('customers/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->add(HtmlHelper::accessLink(IconHelper::make('glyphicon-folder-close') . Yii::t('customers', 'Manage groups'), array('customer_groups/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('customers', 'Manage groups'))))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('customers/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
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
                    'ajaxUrl'         => $this->createUrl($this->route),
                    'id'              => $customer->modelName.'-grid',
                    'dataProvider'    => $customer->search(),
                    'filter'          => $customer,
                    'filterPosition'  => 'body',
                    'filterCssClass'  => 'grid-filter-cell',
                    'itemsCssClass'   => 'table table-hover',
                    'selectableRows'  => 0,
                    'enableSorting'   => true,
                    'cssFile'         => false,
                    'pagerCssClass'   => 'pagination pull-right',
                    'pager'           => array(
                        'class'       => 'CLinkPager',
                        'cssFile'     => false,
                        'header'      => false,
                        'htmlOptions' => array('class' => 'pagination')
                    ),
                    'columns' => $hooks->applyFilters('grid_view_columns', array(
	                    array(
		                    'name'  => 'customer_id',
		                    'value' => '$data->customer_id',
                            'filter'=> false,
	                    ),
	                    array(
		                    'name'  => 'customer_uid',
		                    'value' => '$data->customer_uid',
	                    ),
                        array(
                            'name'  => 'first_name',
                            'value' => '$data->first_name',
                        ),
                        array(
                            'name'  => 'last_name',
                            'value' => '$data->last_name',
                        ),
                        array(
                            'name'  => 'email',
                            'value' => '$data->email',
                        ),
                        array(
                            'name'     => 'company_name',
                            'value'    => '!empty($data->company) ? $data->company->name : "-"',
                            'sortable' => false,
                        ),
                        array(
                            'name'     => 'group_id',
                            'value'    => '!empty($data->group_id) ? HtmlHelper::accessLink($data->group->name, array("customer_groups/update", "id" => $data->group_id), array("fallbackText" => true)) : "-"',
                            'type'     => 'raw',
                            'filter'   => CustomerGroup::getGroupsArray(),
                            'sortable' => false,
                        ),
                        array(
                            'name'     => 'sending_quota_usage',
                            'value'    => '$data->getSendingQuotaUsageDisplay()',
                            'type'     => 'raw',
                            'filter'   => false,
                            'sortable' => false,
                        ),
                        array(
                            'name'     => 'status',
                            'value'    => '$data->statusName',
                            'filter'   => $customer->getStatusesArray(),
                            'sortable' => false,
                        ),
                        array(
                            'name'     => 'date_added',
                            'value'    => '$data->dateAdded',
                            'filter'   => false,
                            'sortable' => false,
                        ),
                        array(
                            'class'   => 'CButtonColumn',
                            'header'  => Yii::t('app', 'Options'),
                            'footer'  => $customer->paginationOptions->getGridFooterPagination(),
                            'buttons' => array(
                                'impersonate' => array(
                                    'label'     => IconHelper::make('glyphicon-random'), 
                                    'url'       => 'Yii::app()->createUrl("customers/impersonate", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Login as this customer'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/impersonate")',
                                ),
                                'reset_quota' => array(
                                    'label'     => IconHelper::make('refresh'), 
                                    'url'       => 'Yii::app()->createUrl("customers/reset_sending_quota", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Reset sending quota'), 'class' => 'btn btn-primary btn-flat reset-sending-quota', 'data-message' => Yii::t('customers', 'Are you sure you want to reset the sending quota for this customer?')),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/reset_sending_quota")',
                                ),
                                'update' => array(
                                    'label'     => IconHelper::make('update'), 
                                    'url'       => 'Yii::app()->createUrl("customers/update", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/update")',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'), 
                                    'url'       => 'Yii::app()->createUrl("customers/delete", array("id" => $data->customer_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("customers/delete") && $data->isRemovable',
                                ),    
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{impersonate} {reset_quota} {update} {delete}'
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