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
if ($viewCollection->renderContent) { ?>
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
                    ->add(CHtml::link(IconHelper::make('envelope') . Yii::t('campaign_reports', 'Campaign overview'), array($this->campaignOverviewRoute, 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Back to campaign overview'))))
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $model, 'columns' => array('subscriber.email', 'open_times', 'ip_address', 'user_agent', 'date_added')), true))
                    ->add(CHtml::link(IconHelper::make('view') . Yii::t('campaign_reports', 'View all opens'), array($this->campaignReportsController . '/open', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'View all opens'))))
                    ->addIf(CHtml::link(IconHelper::make('export') . Yii::t('campaign_reports', 'Export reports'), array($this->campaignReportsExportController . '/open_unique', 'campaign_uid' => $campaign->campaign_uid), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Export reports'))), !empty($canExportStats))
	                ->addIf(CHtml::link(IconHelper::make('delete') . Yii::t('campaign_reports', 'Delete reports'), array($this->campaignReportsController . '/delete_opens', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-danger btn-flat btn-delete-reports', 'title' => Yii::t('campaign_reports', 'Delete reports'), 'data-confirm' => Yii::t('campaign_reports', 'Are you sure you want to remove these reports? There is no coming back after this!'))), !empty($canDeleteStats))
                    ->add(CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal')))
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
                    'ajaxUrl'           => $this->createUrl($this->route, array('campaign_uid' => $campaign->campaign_uid)),
                    'id'                => $model->modelName.'-grid',
                    'dataProvider'      => $dataProvider,
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
                            'name'  => 'subscriber.email',
                            'value' => '$data->subscriber->displayEmail',
                        ),
                        array(
                            'name'  => 'open_times',
                            'value' => '$data->counter',
                        ),
                        array(
                            'name'  => 'ip_address',
                            'value' => 'CHtml::link($data->getIpWithLocationForGrid(), CommonHelper::getIpAddressInfoUrl($data->ip_address), array("target" => "_blank"))',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => 'user_agent',
                            'value' => 'CHtml::link($data->user_agent, CommonHelper::getUserAgentInfoUrl($data->user_agent), array("target" => "_blank"))',
                            'type'  => 'raw',
                            'htmlOptions' => array('style' => 'max-width:220px;word-wrap:break-word;'),
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $model->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'update'    => array(
                                    'label'     => IconHelper::make('update'),
                                    'url'       => 'Yii::app()->createUrl("list_subscribers/update", array("list_uid" => $data->subscriber->list->list_uid, "subscriber_uid" => $data->subscriber->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('list_subscriber', 'Update subscriber'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => 'Yii::app()->apps->isAppName("customer")',
                                ),
                                'bysubscriber'=> array(
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'Yii::app()->createUrl("campaign_reports/open_by_subscriber", array("campaign_uid" => $data->campaign->campaign_uid, "subscriber_uid" => $data->subscriber->subscriber_uid))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('campaign_reports', 'View all opens by this subscriber'), 'class' => 'btn btn-primary btn-flat'),
                                ),
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{update} {bysubscriber}'
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
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    $text = 'This report shows the unique opens for this campaign, if a subscriber opens the email twice, you will see it only once and you also will see how many times it was opened.<br />
                    If you need to see all the opens please click
                    <a href="{href}">here</a>.';
                    echo Yii::t('campaign_reports', StringHelper::normalizeTranslationString($text), array(
                        '{href}' => $this->createUrl('campaign_reports/open', array('campaign_uid' => $campaign->campaign_uid)),
                    ));
                    ?>
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
