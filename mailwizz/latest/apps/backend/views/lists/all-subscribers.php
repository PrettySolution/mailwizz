<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.2
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
    
    <?php $this->renderPartial('_filters');?>
    
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
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $filter, 'columns' => array('customer_id', 'list_id', 'subscriber_uid', 'email', 'source', 'ip_address', 'status', 'date_added', 'last_updated')), true))
                    ->add(CHtml::link(IconHelper::make('back') . Yii::t('list_subscribers', 'Back to lists'), array('lists/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('list_subscribers', 'Back to lists'))))
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('lists/all_subscribers'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->add(CHtml::link(IconHelper::make('filter') . Yii::t('app', 'Filters'), 'javascript:;', array('class' => 'btn btn-primary btn-flat toggle-filters-form', 'title' => Yii::t('app', 'Filters'))))
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
                    'id'                => $filter->modelName.'-grid',
                    'dataProvider'      => $filter->getActiveDataProvider(),
                    'filter'            => $filter,
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
                        'htmlOptions'   => array('class' => 'pagination'),
                        // 'pages'         => $pages, 
                    ),
                    'columns' => $hooks->applyFilters('grid_view_columns', array(
                        array(
                            'name'  => 'customer_id',
                            'value' => 'CHtml::link($data->list->customer->fullName, Yii::app()->createUrl("customers/update", array("id" => $data->list->customer_id)))',
                            'type'  => 'raw',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'list_id',
                            'value' => '$data->list->name',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'subscriber_uid',
                            'value' => '$data->subscriber_uid',
                            'filter'=> CHtml::textField('uid', $filter->uid),
                        ),
                        array(
                            'name'  => 'email',
                            'value' => '$data->email',
                            'filter'=> CHtml::textField('email', $filter->email),
                        ),
                        array(
                            'name'  => 'source',
                            'value' => 'Yii::t("list_subscribers", ucfirst($data->source))',
                            'filter'=> CHtml::dropDownList('sources[]', !empty($filter->sources) && count($filter->sources) === 1 ? $filter->sources[0] : '', CMap::mergeArray(array('' => ''), $filter->getSourcesList())),
                        ),
                        array(
                            'name'  => 'ip_address',
                            'value' => '$data->ip_address',
                            'filter'=> CHtml::textField('ip', $filter->ip),
                        ),
                        array(
                            'name'  => 'status',
                            'value' => 'Yii::t("list_subscribers", ucfirst($data->status))',
                            'filter'=> CHtml::dropDownList('statuses[]', !empty($filter->statuses) && count($filter->statuses) === 1 ? $filter->statuses[0] : '', CMap::mergeArray(array('' => ''), $filter->getStatusesList())),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'last_updated',
                            'value' => '$data->lastUpdated',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $filter->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'profile' => array(
                                    'label'     => IconHelper::make('fa-user'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/profile", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Profile info'), 'class' => 'btn btn-primary btn-flat btn-subscriber-profile-info'),
                                ),
                                'profile_export' => array(
                                    'label'     => IconHelper::make('export'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/profile_export", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Export profile info'), 'target' => '_blank', 'class' => 'btn btn-primary btn-flat btn-export-subscriber-profile-info'),
                                ),
                                'unsubscribe' => array(
                                    'label'     => IconHelper::make('glyphicon-log-out'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/unsubscribe", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Unsubscribe'), 'class' => 'btn btn-primary btn-flat unsubscribe', 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to unsubscribe this subscriber?')),
                                    'visible'   => '$data->getCanBeUnsubscribed() && $data->status == ListSubscriber::STATUS_CONFIRMED',
                                ),
                                'subscribe' => array(
                                    'label'     => IconHelper::make('glyphicon-log-in'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/subscribe", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('list_subscribers', 'Subscribe back'), 'class' => 'btn btn-primary btn-flat subscribe', 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to subscribe back this unsubscriber?')),
                                    'visible'   => '$data->getCanBeConfirmed() && $data->status == ListSubscriber::STATUS_UNCONFIRMED',
                                ),
                                'confirm' => array(
                                    'label'     => IconHelper::make('glyphicon-log-in'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/subscribe", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('list_subscribers', 'Confirm subscriber'), 'class' => 'btn btn-primary btn-flat subscribe', 'data-message' => Yii::t('list_subscribers', 'Are you sure you want to confirm this subscriber?')),
                                    'visible'   => '$data->getCanBeConfirmed() && $data->status == ListSubscriber::STATUS_UNSUBSCRIBED',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/delete", array("subscriber_uid" => $data->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete', 'data-message' => Yii::t('app', 'Are you sure you want to delete this item? There is no coming back after you do it.')),
                                    'visible'   => '$data->getCanBeDeleted()',
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{profile} {profile_export} {unsubscribe} {subscribe} {confirm} {delete}'
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
            <?php
            /**
             * Since 1.3.9.8
             * This creates a modal placeholder to push subscriber profile info in.
             */
            $this->widget('customer.components.web.widgets.SubscriberModalProfileInfoWidget');
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
