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
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('export') .  $pageHeading;?>
                </h3>
            </div>
            <div class="pull-right">
                <?php echo CHtml::link(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('survey_segments/index', 'survey_uid' => $survey->survey_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel')));?>
                <?php echo CHtml::link(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('survey_segments_export/index', 'survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-2 col-xs-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h3><?php echo Yii::t('survey_export', 'CSV');?></h3>
                            <p><?php echo Yii::t('app', 'File');?></p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-download"></i>
                        </div>
                        <div class="small-box-footer">
                            <div class="pull-left">
                                &nbsp; <a href="<?php echo $this->createUrl('survey_segments_export/csv', array('survey_uid' => $survey->survey_uid, 'segment_uid' => $segment->segment_uid));?>" target="_blank" class="btn bg-teal btn-flat btn-xs"><?php echo IconHelper::make('export') . Yii::t('survey_export', 'Click to export');?></a>
                            </div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-footer"></div>
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