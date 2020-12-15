<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
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
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-file') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $log, 'columns' => array('customer_id', 'campaign_id', 'list_id', 'segment_id', 'subscriber_id', 'message', 'status', 'server_id', 'date_added')), true))
                    ->addIf(HtmlHelper::accessLink(Yii::t('misc', 'View archived logs'), array('misc/campaigns_delivery_logs', 'archive' => 1), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('misc', 'View archived logs'))), empty($archive))
                    ->addIf(HtmlHelper::accessLink(Yii::t('misc', 'View current logs'), array('misc/campaigns_delivery_logs'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('misc', 'View current logs'))), !empty($archive))
                    ->add(HtmlHelper::accessLink(Yii::t('misc', 'Delete delivery temporary errors'), array('misc/delete_delivery_temporary_errors'), array('class' => 'btn btn-danger btn-flat btn-delete-delivery-temporary-errors', 'title' => Yii::t('app', 'Delete delivery temporary errors'), 'data-confirm' => Yii::t('misc', 'Are you sure you want to delete the delivery temporary errors? Please note that this will affect running campaigns, continue only if you really know what you are doing!'))))
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
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $log->modelName.'-grid',
                    'dataProvider'      => $log->search(),
                    'filter'            => $log,
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
                            'name'  => 'customer_id',
                            'value' => 'empty($data->campaign) ? "-" : HtmlHelper::accessLink($data->campaign->customer->getFullName(), array("customers/update", "id" => $data->campaign->customer->customer_id))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'campaign_id',
                            'value' => 'empty($data->campaign) ? "-" : HtmlHelper::accessLink($data->campaign->name, array("campaigns/overview", "campaign_uid" => $data->campaign->campaign_uid))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'list_id',
                            'value' => 'empty($data->campaign) ? "-" : HtmlHelper::accessLink($data->campaign->list->name, array("lists/overview", "list_uid" => $data->campaign->list->list_uid))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'segment_id',
                            'value' => '!empty($data->campaign) && !empty($data->campaign->segment_id) ? $data->campaign->segment->name : "-"',
                        ),
                        array(
                            'name'  => 'subscriber_id',
                            'value' => 'empty($data->subscriber) ? "-" : $data->subscriber->displayEmail',
                        ),
                        array(
                            'name'  => 'message',
                            'value' => '$data->message',
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->statusName',
                            'filter'=> $log->getStatusesArray(),
                        ),
                        array(
                            'name'  => 'server_id',
                            'value' => 'empty($data->server) ? "-" : HtmlHelper::accessLink((!empty($data->server->name) ? $data->server->name : $data->server->hostname), array("delivery_servers/update", "type" => $data->server->type, "id" => $data->server_id))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $log->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
	                            'webversion' => array(
		                            'label'     => IconHelper::make('view'),
		                            'url'       => 'Yii::app()->options->get("system.urls.frontend_absolute_url") . "campaigns/" . $data->campaign->campaign_uid . "/web-version/" . $data->subscriber->subscriber_uid',
		                            'imageUrl'  => null,
		                            'options'   => array('title' => Yii::t('campaign_reports', 'View what was sent'), 'class' => 'btn btn-primary btn-flat', 'target' => '_blank'),
	                            ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{webversion}'
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