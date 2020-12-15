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
        <div class="box-header" id="chatter-header">
            <div class="pull-left">
                <h3 class="box-title"><?php echo IconHelper::make('list');?> <?php echo Yii::t('lists', 'Overview');?></h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('lists/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo CHtml::link(IconHelper::make('update') . Yii::t('app', 'Update'), array('lists/update', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Update')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="row boxes-mw-wrapper">
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6><?php echo CHtml::link(Yii::app()->format->formatNumber($confirmedSubscribersCount), Yii::app()->createUrl("list_subscribers/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('list_subscribers', 'Confirmed Subscribers')));?></h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($subscribersCount), Yii::app()->createUrl("list_subscribers/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('list_subscribers', 'Subscribers');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-people"></i>
                        </div>
                    </div>
                </div>
                <?php if (!empty($canSegmentLists)) { ?>
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($segmentsCount), Yii::app()->createUrl("list_segments/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('list_segments', 'Segments');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-gear-b"></i>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($customFieldsCount), Yii::app()->createUrl("list_fields/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('list_fields', 'Custom fields');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-android-list"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($pagesCount), Yii::app()->createUrl("list_page/index", array("list_uid" => $list->list_uid, 'type' => 'subscribe-form')), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('list_pages', 'Pages');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-folder"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::t('list_forms', 'Forms'), Yii::app()->createUrl("list_forms/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('app', 'Tools');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-photos"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::t('lists', 'Tools'), Yii::app()->createUrl("list_tools/index", array("list_uid" => $list->list_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('lists', 'List tools');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-hammer"></i>
                        </div>
                    </div>
                </div>
                    
                <div class="clearfix"><!-- --></div>    
            </div>
        </div>
    </div>
    
    <?php 
    // since 1.5.2 
    $this->widget('customer.components.web.widgets.list-subscribers.ListSubscribers7DaysActivityWidget', array(
        'list' => $list,
    ));
    ?>
    
    <div id="campaigns-overview-wrapper" data-url="<?php echo $this->createUrl('dashboard/campaigns');?>" data-list="<?php echo $list->list_id;?>">
        <!-- ajax content -->
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