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
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $model, 'columns' => array('url.destination', 'ip_address', 'user_agent', 'date_added')), true))
                    ->add(CHtml::link(Yii::t('campaign_reports', 'All clicks'), array($this->campaignReportsController . '/click', 'campaign_uid' => $campaign->campaign_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Back to clicks report'))))
                    ->add(CHtml::link(Yii::t('campaign_reports', 'Only unique urls'), array($this->campaignReportsController . '/click_by_subscriber_unique', 'campaign_uid' => $campaign->campaign_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Only unique urls'))))
                    ->addIf(CHtml::link(IconHelper::make('export') . Yii::t('campaign_reports', 'Export reports'), array($this->campaignReportsExportController . '/click_by_subscriber', 'campaign_uid' => $campaign->campaign_uid, 'subscriber_uid' => $subscriber->subscriber_uid), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('campaign_reports', 'Export reports'))), !empty($canExportStats))
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
                    'ajaxUrl'           => $this->createUrl($this->route, array('campaign_uid' => $campaign->campaign_uid, 'subscriber_uid' => $subscriber->subscriber_uid)),
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
                            'name'  => 'url.destination',
                            'value' => '$data->url->getDisplayGridDestination()',
                            'type'  => 'raw',
                            'htmlOptions' => array('style' => 'max-width:250px;word-wrap:break-word;'),
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
                    $text = 'This page shows all the urls the subscriber <b>{email}</b> clicked on.<br />
                    If the subscriber clicked same link twice, you will see it twice so that you can compare the time when the click has been made.<br />
                    If you need to see only the unique urls and their clicks count for this subscriber, please click 
                    <a href="{href}">here</a>.';
                    echo Yii::t('campaign_reports', StringHelper::normalizeTranslationString($text), array(
                        '{email}'   => $subscriber->displayEmail,
                        '{href}'    => $this->createUrl('campaign_reports/click_by_subscriber_unique', array('campaign_uid' => $campaign->campaign_uid, 'subscriber_uid' => $subscriber->subscriber_uid)),
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