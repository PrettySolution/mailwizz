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
    ));
    ?>
    
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
                    ->addIf($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $campaign, 'columns' => array('campaign_id', 'campaign_uid', 'name', 'group_id', 'list_id', 'segment_id', 'send_at', 'status', 'search_ar_event', 'search_ar_time', 'search_template_name', 'gridViewSent', 'gridViewDelivered', 'gridViewOpens', 'gridViewClicks', 'gridViewBounces', 'gridViewUnsubs')), true), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('glyphicon-folder-close') . Yii::t('campaigns', 'Manage groups'), array('campaign_groups/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaigns', 'Manage groups'))))
                    ->add(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('campaigns/create', 'type' => 'autoresponder'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))))
                    ->addIf(CHtml::link(IconHelper::make('export') . Yii::t('app', 'Export'), array('campaigns/export', 'type' => 'autoresponder'), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Export'))), $itemsCount)
                    ->add(CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('campaigns/autoresponder'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
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
                // since 1.3.5.6
                $this->widget('common.components.web.widgets.GridViewBulkAction', array(
                    'model'      => $campaign,
                    'formAction' => $this->createUrl('campaigns/bulk_action', array('type' => Campaign::TYPE_REGULAR)),
                ));

                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $campaign->modelName.'-grid',
                    'dataProvider'      => $campaign->search(),
                    'filter'            => $campaign,
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
                            'class'               => 'CCheckBoxColumn',
                            'name'                => 'campaign_uid',
                            'selectableRows'      => 100,
                            'checkBoxHtmlOptions' => array('name' => 'bulk_item[]'),
                        ),
                        array(
                            'name'   => 'campaign_id',
                            'value'  => '$data->campaign_id',
                            'filter' => false,
                        ),
                        array(
                            'name'  => 'campaign_uid',
                            'value' => 'CHtml::link($data->campaign_uid, Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid)))',
                            'type'  => 'raw',
                            
                        ),
                        array(
                            'name'  => 'name',
                            'value' => 'CHtml::link($data->name, Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'group_id',
                            'value' => '!empty($data->group_id) ? CHtml::link($data->group->name, Yii::app()->createUrl("campaign_groups/update", array("group_uid" => $data->group->uid))) : "-"',
                            'filter'=> $campaign->getGroupsDropDownArray(),
                            'type'  => 'raw'
                        ),
                        array(
                            'name'  => 'list_id',
                            'value' => 'CHtml::link(StringHelper::truncateLength($data->list->name, 100), Yii::app()->createUrl("lists/overview", array("list_uid" => $data->list->uid)))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'segment_id',
                            'value' => '!empty($data->segment_id) ? CHtml::link(StringHelper::truncateLength($data->segment->name, 100), Yii::app()->createUrl("list_segments/update", array("list_uid" => $data->list->uid, "segment_uid" => $data->segment->uid))) : "-"',
                            'filter'=> false,
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'send_at',
                            'value' => '$data->getSendAt()',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->getStatusWithStats()',
                            'filter'=> $campaign->getStatusesList(),
                        ),
                        array(
                            'name'  => 'search_ar_event',
                            'value' => '$data->option->autoresponder_event',
                            'filter'=> $campaign->option->getAutoresponderEvents(),
                        ),
                        array(
                            'name'  => 'search_ar_time',
                            'value' => '$data->option->autoresponder_time_value . " " . $data->option->autoresponder_time_unit',
                        ),
	                    array(
		                    'name'      => 'search_template_name',
		                    'value'     => '!empty($data->template) ? $data->template->name : ""',
		                    'sortable'  => false,
	                    ),
                        array(
                            'name'      => 'gridViewSent',
                            'value'     => '$data->getGridViewSent()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'gridViewDelivered',
                            'value'     => '$data->getGridViewDelivered()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'gridViewOpens',
                            'value'     => '$data->getGridViewOpens()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'gridViewClicks',
                            'value'     => '$data->getGridViewClicks()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'gridViewBounces',
                            'value'     => '$data->getGridViewBounces()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'name'      => 'gridViewUnsubs',
                            'value'     => '$data->getGridViewUnsubs()',
                            'filter'    => false,
                            'sortable'  => false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $campaign->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'overview'=> array(
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Overview'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => '(!$data->editable || $data->isPaused) && !$data->isPendingDelete',
                                ),
                                'pause'=> array(
                                    'label'     => IconHelper::make('glyphicon-pause'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/pause_unpause", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Pause sending'), 'class' => 'btn btn-primary btn-flat pause-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to pause this campaign ?')),
                                    'visible'   => '$data->canBePaused',
                                ),
                                'unpause'=> array(
                                    'label'     => IconHelper::make('glyphicon-play-circle'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/pause_unpause", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Unpause sending'), 'class' => 'btn btn-primary btn-flat unpause-sending', 'data-message' => Yii::t('campaigns', 'Are you sure you want to unpause sending emails for this campaign ?')),
                                    'visible'   => '$data->isPaused',
                                ),
                                'reset'=> array(
                                    'label'     => IconHelper::make('refresh'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/resume_sending", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Resume sending'), 'class' => 'btn btn-primary btn-flat resume-campaign-sending', 'data-message' => Yii::t('campaigns', 'Resume sending, use this option if you are 100% sure your campaign is stuck and does not send emails anymore!')),
                                    'visible'   => '$data->canBeResumed',
                                ),
                                'copy'=> array(
                                    'label'     => IconHelper::make('copy'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/copy", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'visible'   => '!$data->isPendingDelete',
                                    'options'   => array('title' => Yii::t('app', 'Copy'), 'class' => 'btn btn-primary btn-flat  copy-campaign'),
                                ),
                                'update'=> array(
                                    'label'     => IconHelper::make('update'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/update", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'visible'   => '$data->editable',
                                    'options'   => array('title' => Yii::t('app', 'Update'), 'class' => 'btn btn-primary btn-flat'),
                                ),
                                'marksent'=> array(
                                    'label'     => IconHelper::make('glyphicon-ok'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/marksent", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Mark as sent'), 'class' => 'btn btn-primary btn-flat mark-campaign-as-sent', 'data-message' => Yii::t('campaigns', 'Are you sure you want to mark this campaign as sent ?')),
                                    'visible'   => '$data->canBeMarkedAsSent',
                                ),
                                'resendgiveups'=> array(
	                                'label'     => IconHelper::make('glyphicon-envelope'),
	                                'url'       => 'Yii::app()->createUrl("campaigns/resend_giveups", array("campaign_uid" => $data->campaign_uid))',
	                                'imageUrl'  => null,
	                                'options'   => array('title' => Yii::t('campaigns', 'Resend giveups'), 'class' => 'btn btn-primary btn-flat resend-campaign-giveups', 'data-message' => Yii::t('campaigns', 'This will resend the campaign but only to the subscribers where the system was not able to send first time. Are you sure?')),
	                                'visible'   => '$data->getCanShowResetGiveupsButton()',
                                ),
                                'webversion'=> array(
                                    'label'     => IconHelper::make('view'),
                                    'url'       => 'Yii::app()->options->get("system.urls.frontend_absolute_url") . "campaigns/" . $data->campaign_uid',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaigns', 'Web version'), 'class' => 'btn btn-primary btn-flat', 'target' => '_blank'),
                                    'visible'   => '$data->canViewWebVersion',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'Yii::app()->createUrl("campaigns/delete", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'visible'   => '$data->removable',
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          =>'{overview} {pause} {unpause} {reset} {copy} {update} {marksent} {resendgiveups} {webversion} {delete}'
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
	/**
	 * @since 1.6.0
	 */
	$this->renderPartial('_bulk-send-test-email');
	?>
    
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
