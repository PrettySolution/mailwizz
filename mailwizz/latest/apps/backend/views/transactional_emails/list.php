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
$hooks->doAction('views_before_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) { ?>
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
                    ->add($this->widget('common.components.web.widgets.GridViewToggleColumns', array('model' => $email, 'columns' => array('to_email', 'to_name', 'reply_to_email', 'reply_to_name', 'from_email', 'from_name', 'subject', 'status', 'send_at')), true))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('transactional_emails/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh'))))
                    ->render();
                ?>
    		</div>
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
            $hooks->doAction('views_before_grid', $collection = new CAttributeCollection(array(
                'controller'   => $this,
                'renderGrid'   => true,
            )));
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $email->modelName.'-grid',
                    'dataProvider'      => $email->search(),
                    'filter'            => $email,
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
                            'name'  => 'to_email',
                            'value' => '$data->to_email',
                        ),
                        array(
                            'name'  => 'to_name',
                            'value' => '$data->to_name',
                        ),
                        
                        array(
                            'name'  => 'reply_to_email',
                            'value' => '$data->reply_to_email',
                        ),
                        array(
                            'name'  => 'reply_to_name',
                            'value' => '$data->reply_to_name',
                        ),
                        array(
                            'name'  => 'from_email',
                            'value' => '$data->from_email',
                        ),
                        array(
                            'name'  => 'from_name',
                            'value' => '$data->from_name',
                        ),
                        array(
                            'name'  => 'subject',
                            'value' => '$data->subject',
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->statusName',
                            'filter'=> $email->getStatusesList(),
                        ),
                        array(
                            'name'  => 'send_at',
                            'value' => '$data->sendAt',
                            'filter'=> false,
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'footer'    => $email->paginationOptions->getGridFooterPagination(),
                            'buttons'   => array(
                                'resend' => array(
                                    'label'     => IconHelper::make('glyphicon-play-circle'), 
                                    'url'       => 'Yii::app()->createUrl("transactional_emails/resend", array("id" => $data->email_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Resend'), 'class' => 'btn btn-primary btn-flat'),
                                    'visible'   => '$data->status == TransactionalEmail::STATUS_SENT && AccessHelper::hasRouteAccess("transactional_emails/resend")',
                                ),
                                'preview' => array(
                                    'label'     => IconHelper::make('view'), 
                                    'url'       => 'Yii::app()->createUrl("transactional_emails/preview", array("id" => $data->email_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Preview'), 'class' => 'btn btn-primary btn-flat preview-transactional-email', 'target' => '_blank'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("transactional_emails/preview")',
                                ),
                                'delete' => array(
                                    'label'     => IconHelper::make('delete'), 
                                    'url'       => 'Yii::app()->createUrl("transactional_emails/delete", array("id" => $data->email_id))',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("transactional_emails/delete")',
                                ),    
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{resend} {preview} {delete}'
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
            $hooks->doAction('views_after_grid', new CAttributeCollection(array(
                'controller'   => $this,
                'renderedGrid' => $collection->renderGrid,
            )));
            ?>
            </div>   
            <div class="clearfix"><!-- --></div> 
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
$hooks->doAction('views_after_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));