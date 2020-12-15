<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
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
    $this->renderPartial($this->extension->getPathAlias('backend.views._tabs')); 
    ?>
    
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('glyphicon-book') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('ext_tour_slideshows/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo CHtml::link(Yii::t('app', 'Refresh'), array('ext_tour_slideshows/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
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
                    'controller'   => $this,
                    'renderGrid'   => true,
                )));

                // and render if allowed
                if ($collection->renderGrid) {
                    $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                        'ajaxUrl'           => $this->createUrl($this->route),
                        'id'                => $slideshow->modelName.'-grid',
                        'dataProvider'      => $slideshow->search(),
                        'filter'            => $slideshow,
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
                                'name'      => 'application',
                                'value'     => 'ucfirst($data->getExtensionInstance()->t($data->application))',
                                'filter'    => $slideshow->getApplicationsList(),
                            ),
                            array(
                                'name'      => 'slidesCount',
                                'value'     => 'CHtml::link($data->slidesCount, array("ext_tour_slideshow_slides/index", "slideshow_id" => $data->slideshow_id))',
                                'filter'    => false,
                                'type'      => 'raw',
                            ),
                            array(
                                'name'      => 'status',
                                'value'     => 'ucfirst(Yii::t("app", $data->status))',
                                'filter'    => $slideshow->getStatusesList(),
                            ),
                            array(
                                'name'      => 'date_added',
                                'value'     => '$data->dateAdded',
                                'filter'    => false,
                            ),
                            array(
                                'name'      => 'last_updated',
                                'value'     => '$data->lastUpdated',
                                'filter'    => false,
                            ),
                            array(
                                'class'     => 'CButtonColumn',
                                'header'    => Yii::t('app', 'Options'),
                                'footer'    => $slideshow->paginationOptions->getGridFooterPagination(),
                                'buttons'   => array(
                                    'view' => array(
                                        'label'     => IconHelper::make('view'),
                                        'url'       => 'Yii::app()->createUrl("ext_tour_slideshow_slides/index", array("slideshow_id" => $data->slideshow_id))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => $this->extension->t('View slides'), 'class' => 'btn btn-primary btn-flat'),
                                    ),
                                    'update' => array(
                                        'label'     => IconHelper::make('update'),
                                        'url'       => 'Yii::app()->createUrl("ext_tour_slideshows/update", array("id" => $data->slideshow_id))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                    ),
                                    'delete' => array(
                                        'label'     => IconHelper::make('delete'),
                                        'url'       => 'Yii::app()->createUrl("ext_tour_slideshows/delete", array("id" => $data->slideshow_id))',
                                        'imageUrl'  => null,
                                        'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    ),
                                ),
                                'headerHtmlOptions' => array('style' => 'text-align: right'),
                                'footerHtmlOptions' => array('align' => 'right'),
                                'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                                'template'          => '{view} {update} {delete}'
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
                    'controller'   => $this,
                    'renderedGrid' => $collection->renderGrid,
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