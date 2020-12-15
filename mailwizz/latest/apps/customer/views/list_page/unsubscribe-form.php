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
    $this->renderPartial('_nav-buttons');
    ?>
    <div class="row">
        <div class="col-lg-12">
            <label><?php echo Yii::t('list_pages', 'Your unsubscribe form url is:');?></label><br />
            <div class="row">
                <div class="col-lg-10">
                    <input type="text" value="<?php echo Yii::app()->apps->getAppUrl('frontend', 'lists/'.$list->list_uid.'/unsubscribe', true);?>" class="form-control"/>
                </div>
                <div class="col-lg-2">
                    <div class="pull-right">
                        <a class="btn btn-primary btn-flat" href="<?php echo Yii::app()->apps->getAppUrl('frontend', 'lists/'.$list->list_uid.'/unsubscribe', true);?>" target="_blank"><?php echo Yii::t('list_pages', 'Preview it now!');?></a>
                    </div>
                    <div class="clearfix"><!-- --></div>
                </div>
            </div>
        </div>
    </div>
    <hr />
    <?php 
    $this->renderPartial('_form');
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