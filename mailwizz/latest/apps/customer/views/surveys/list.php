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
    $itemsCount = Survey::model()->countByAttributes(array(
        'customer_id' => (int)Yii::app()->customer->getId(),
    ));
    ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-list') . $pageHeading . '</h3>')
                    ->render();
                ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $survey, 'columns' => array('survey_uid', 'name', 'display_name', 'responders_count', 'status', 'date_added', 'last_updated')), true), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('surveys/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('surveys/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
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
                    'id'                => $survey->modelName.'-grid',
                    'dataProvider'      => $survey->search(),
                    'filter'            => $survey,
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
                            'name'  => 'survey_uid',
                            'value' => 'CHtml::link($data->survey_uid,Yii::app()->createUrl("surveys/overview", array("survey_uid" => $data->survey_uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'CHtml::link($data->name,Yii::app()->createUrl("surveys/overview", array("survey_uid" => $data->survey_uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'display_name',
                            'value' => 'CHtml::link($data->display_name,Yii::app()->createUrl("surveys/overview", array("survey_uid" => $data->survey_uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'     => 'responders_count',
                            'value'    => 'CHtml::link(Yii::app()->format->formatNumber($data->respondersCount), Yii::app()->createUrl("survey_responders/index", array("survey_uid" => $data->survey_uid)))',
                            'filter'   => false,
                            'sortable' => false,
                            'type'     => 'raw',
                        ),
                        array(
                            'name'   => 'status',
                            'value'  => '$data->statusName',
                            'filter' => $survey->getStatusesList(),
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
                            'footer'    => $survey->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'overview' => array(
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'Yii::app()->createUrl("surveys/overview", array("survey_uid" => $data->survey_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('surveys', 'Overview'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => '!$data->isPendingDelete',
                                ),
                                'view' => array(
                                    'label'     => IconHelper::make('view'),
                                    'url'       => '$data->getViewUrl()',
                                    'imageUrl'  => null,
                                    'options'   => array('target' => '_blank', 'title' => Yii::t('surveys', 'View'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => '!$data->isPendingDelete',
                                ),
                                'copy'=> array(
                                    'label'     => IconHelper::make('copy'),
                                    'url'       => 'Yii::app()->createUrl("surveys/copy", array("survey_uid" => $data->survey_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Copy'), 'class' => 'btn btn-primary btn-flat copy-list'),
                                    'visible'   => '!$data->isPendingDelete',
                                ),
                                'update' => array(
                                    'label'     => IconHelper::make('update'),
                                    'url'       => 'Yii::app()->createUrl("surveys/update", array("survey_uid" => $data->survey_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => '$data->editable',
                                ),
                                'confirm_delete' => array(
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'Yii::app()->createUrl("surveys/delete", array("survey_uid" => $data->survey_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat'),
                                    'visible'   => '$data->isRemovable',
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          =>'{overview} {view} {copy} {update} {confirm_delete}'
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
