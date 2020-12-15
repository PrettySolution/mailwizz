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
                    ->add(CHtml::link(IconHelper::make('fa-envelope') . Yii::t('campaign_reports', 'Campaign overview'), array($this->campaignOverviewRoute, 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Back to campaign overview'))))
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $deliveryLogs, 'columns' => array('subscriber.email', 'status', 'message', 'date_added')), true))
                    ->addIf($this->widget('common.components.web.widgets.GridViewDropDownLinksSelector', array( 
                        'heading' => Yii::t('app', 'Export'),
                        'links'   => array(
                            CHtml::link(Yii::t('app', 'Export all'), array($this->campaignReportsExportController . '/delivery', 'campaign_uid' => $campaign->campaign_uid), array('target' => '_blank', 'class' => 'btn btn-default btn-flat', 'title' => Yii::t('campaign_reports', 'Export all reports'))),
	                        CHtml::link(Yii::t('app', 'Success only'), array($this->campaignReportsExportController . '/delivery', 'campaign_uid' => $campaign->campaign_uid, 'CampaignDeliveryLog[status]' => CampaignDeliveryLog::STATUS_SUCCESS), array('target' => '_blank', 'class' => 'btn btn-default btn-flat', 'title' => Yii::t('campaign_reports', 'Export success only'))),
	                        CHtml::link(Yii::t('app', 'Error only'), array($this->campaignReportsExportController . '/delivery', 'campaign_uid' => $campaign->campaign_uid, 'CampaignDeliveryLog[status]' => CampaignDeliveryLog::STATUS_ERROR), array('target' => '_blank', 'class' => 'btn btn-default btn-flat', 'title' => Yii::t('campaign_reports', 'Export error only'))),
	                        CHtml::link(Yii::t('app', 'Giveup only'), array($this->campaignReportsExportController . '/delivery', 'campaign_uid' => $campaign->campaign_uid, 'CampaignDeliveryLog[status]' => CampaignDeliveryLog::STATUS_GIVEUP), array('target' => '_blank', 'class' => 'btn btn-default btn-flat', 'title' => Yii::t('campaign_reports', 'Export giveup only'))),
	                        CHtml::link(Yii::t('app', 'Blacklist only'), array($this->campaignReportsExportController . '/delivery', 'campaign_uid' => $campaign->campaign_uid, 'CampaignDeliveryLog[status]' => CampaignDeliveryLog::STATUS_BLACKLISTED), array('target' => '_blank', 'class' => 'btn btn-default btn-flat', 'title' => Yii::t('campaign_reports', 'Export blacklist only'))),
                        )
                    ), true), !empty($canExportStats))
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
                    'id'                => $deliveryLogs->modelName.'-grid',
                    'dataProvider'      => $deliveryLogs->customerSearch(),
                    'filter'            => $deliveryLogs,
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
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'status',
                            'value' => 'strtoupper($data->status)',
                            'filter'=> $deliveryLogs->getStatusesArray(),
                        ),
                        array(
                            'name'  => 'message',
                            'value' => '$data->message',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $deliveryLogs->paginationOptions->getGridFooterPagination(),
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
                            'template'          =>'{webversion}'
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
                    $text = 'This report shows all the subscribers that were processed in order to receive your email.<br />
                    It also show if the emails have been sent successfully or not.';
                    echo Yii::t('campaign_reports', StringHelper::normalizeTranslationString($text));
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
