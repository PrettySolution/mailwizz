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
    $itemsCount = Campaign::model()->countByAttributes(array(
        'customer_id' => (int)Yii::app()->customer->getId(),
        'type'        => Campaign::TYPE_REGULAR,
        'status'      => Campaign::STATUS_SENT,
    ));
    ?>

    <?php $this->renderPartial('_filters');?>
    
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('envelope') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $filter, 'columns' => array('name', 'subject', 'listName', 'subscribersCount', 'deliverySuccess', 'uniqueOpens', 'allOpens', 'uniqueClicks', 'allClicks', 'unsubscribes', 'bounces', 'softBounces', 'hardBounces', 'internalBounces', 'sendAt')), true), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('campaigns_stats/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->addIf(CHtml::link(IconHelper::make('filter') . Yii::t('app', 'Filters'), 'javascript:;', array('class' => 'btn btn-primary btn-flat toggle-filters-form', 'title' => Yii::t('app', 'Filters'))), $itemsCount)
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
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $filter->modelName.'-grid',
                    'dataProvider'      => $filter->search(),
                    'filter'            => null,
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
                            'value' => 'CHtml::link($data->name, Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid)))',
                            'type'  => 'raw',
                            'filter'=> false,
                        ),
                        array(
                            'name'   => 'subject',
                            'value'  => '$data->subject',
                            'filter' => false,
                        ),
                        array(
                            'name'  => 'listName',
                            'value' => 'empty($data->list) ? "" : CHtml::link($data->listName, Yii::app()->createUrl("lists/overview", array("list_uid" => $data->list->list_uid)))',
                            'type'  => 'raw',
                            'filter'=> false,
                        ),
                        array(
                            'name'   => 'subscribersCount',
                            'value'  => '$data->subscribersCount',
                            'filter' => false,
                        ),
                        array(
                            'name'   => 'deliverySuccess',
                            'value'  => '$data->deliverySuccess',
                            'filter' => false,
                        ),
                        array(
                            'name'   => 'uniqueOpens',
                            'value'  => '$data->uniqueOpens',
                            'filter' => false,
                        ),
                        array(
                            'name'   => 'allOpens',
                            'value'  => '$data->allOpens',
                            'filter' => false,
                        ),
                        array(
                            'name'  => 'uniqueClicks',
                            'value' => '$data->uniqueClicks',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'allClicks',
                            'value' => '$data->allClicks',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'unsubscribes',
                            'value' => '$data->unsubscribes',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'bounces',
                            'value' => '$data->bounces',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'softBounces',
                            'value' => '$data->softBounces',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'hardBounces',
                            'value' => '$data->hardBounces',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'internalBounces',
                            'value' => '$data->internalBounces',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'sendAt',
                            'value' => '$data->sendAt',
                            'filter'=> false,
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
