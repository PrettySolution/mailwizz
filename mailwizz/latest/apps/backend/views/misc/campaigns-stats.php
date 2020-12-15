<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.7
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
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $campaign, 'columns' => array('customer_id', 'name', 'hardBounceRate', 'softBounceRate', 'unsubscribeRate', 'status', 'send_at')), true))
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

                // and render if allowed
                if ($collection->renderGrid) {
                    $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                        'ajaxUrl'           => $this->createUrl($this->route),
                        'id'                => $campaign->modelName.'-grid',
                        'dataProvider'      => $campaign->search(),
                        'filter'            => $campaign,
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
                                'value' => 'HtmlHelper::accessLink($data->customer->fullName, array("customers/update", "id" => $data->customer_id))',
                                'type'  => 'raw',
                            ),
                            array(
                                'name'  => 'name',
                                'value' => 'HtmlHelper::accessLink($data->name, array("campaigns/overview", "campaign_uid" => $data->campaign_uid))',
                                'type'  => 'raw',
                            ),
                            array(
                                'name'  => 'hardBounceRate',
                                'value' => '$data->stats->getHardBouncesRate(true) . "%"',
                                'filter'=> false,
                            ),
                            array(
                                'name'  => 'softBounceRate',
                                'value' => '$data->stats->getSoftBouncesRate(true) . "%"',
                                'filter'=> false,
                            ),
                            array(
                                'name'  => 'unsubscribeRate',
                                'value' => '$data->stats->getUnsubscribesRate(true) . "%"',
                                'filter'=> false,
                            ),
                            array(
                                'name'  => 'status',
                                'value' => '$data->getStatusWithStats()',
                                'filter'=> false,
                            ),
                            array(
                                'name'  => 'send_at',
                                'value' => '$data->sendAt',
                                'filter'=> false,
                            ),
                            array(
                                'class'     => 'CButtonColumn',
                                'header'    => Yii::t('app', 'Options'),
                                'footer'    => $campaign->paginationOptions->getGridFooterPagination(),
                                'buttons'   => array(
                                    'overview'=> array(
                                        'label'     => IconHelper::make('glyphicon-info-sign'),
                                        'url'       => 'Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('campaigns', 'Overview'), 'class' => 'btn btn-primary btn-flat'),
                                        'visible'   => 'AccessHelper::hasRouteAccess("campaigns/overview") && (!$data->editable || $data->isPaused) && !$data->isPendingDelete && !$data->isDraft',
                                    ),
                                ),
                                'headerHtmlOptions' => array('style' => 'text-align: right'),
                                'footerHtmlOptions' => array('align' => 'right'),
                                'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                                'template'          => '{overview}'
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