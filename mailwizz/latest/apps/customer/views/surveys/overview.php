<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
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
                <h3 class="box-title"><?php echo IconHelper::make('glyphicon-list');?> <?php echo Yii::t('surveys', 'Overview');?></h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('surveys/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new')));?>
                <?php echo CHtml::link(IconHelper::make('update') . Yii::t('app', 'Update'), array('surveys/update', 'survey_uid' => $survey->survey_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Update')));?>
                <?php echo CHtml::link(IconHelper::make('view') . Yii::t('app', 'View'), $survey->getViewUrl(), array('target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'View')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="row boxes-mw-wrapper">
                <div class="col-lg-4 col-xs-4">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($respondersCount), Yii::app()->createUrl("survey_responders/index", array("survey_uid" => $survey->survey_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('survey_responders', 'Responders');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xs-4">
                    <div class="small-box">
                        <div class="inner">
                            <div class="middle">
                                <h6>&nbsp;</h6>
                                <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($customFieldsCount), Yii::app()->createUrl("survey_fields/index", array("survey_uid" => $survey->survey_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                <p><?php echo Yii::t('survey_fields', 'Custom fields');?></p>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="ion ion-android-list"></i>
                        </div>
                    </div>
                </div>
	            <?php if (!empty($canSegmentSurveys)) { ?>
                    <div class="col-lg-4 col-xs-4">
                        <div class="small-box">
                            <div class="inner">
                                <div class="middle">
                                    <h6>&nbsp;</h6>
                                    <h3><?php echo CHtml::link(Yii::app()->format->formatNumber($segmentsCount), Yii::app()->createUrl("survey_segments/index", array("survey_uid" => $survey->survey_uid)), array('title' => Yii::t('app', 'View')));?></h3>
                                    <p><?php echo Yii::t('survey_segments', 'Segments');?></p>
                                </div>
                            </div>
                            <div class="icon">
                                <i class="ion ion-gear-b"></i>
                            </div>
                        </div>
                    </div>
	            <?php } ?>
                <div class="clearfix"><!-- --></div>    
            </div>
        </div>
    </div>

    <?php
    // since 1.5.2
    $this->widget('customer.components.web.widgets.survey-responders.SurveyResponders7DaysActivityWidget', array(
        'survey' => $survey,
    ));
    ?>

    <div class="row">
        <?php
        $this->widget('customer.components.web.widgets.survey-fields-stats.SurveyFieldsStatsWidget', array(
            'survey' => $survey,
        ));
        ?>
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