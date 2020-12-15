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

$htmlOptions = array();
if (!empty($attributes) && !empty($attributes['target']) && in_array($attributes['target'], array('_blank'))) {
    $htmlOptions['target'] = $attributes['target'];
} 
?>

<div class="row">
    <div class="<?php echo $this->layout != 'embed' ? 'col-lg-6 col-lg-push-3 col-md-6 col-md-push-3 col-sm-12' : '';?>">
        <?php echo CHtml::form('', 'post', $htmlOptions);?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo $survey->displayName; ?></h3>
            </div>

            <div class="box-body">
                <?php if (!empty($survey->description)) {?>
                    <div class="callout">
                        <?php echo $survey->description; ?>
                    </div>
                <?php } ?>
                <div class="fields-list">
                    <?php echo $fieldsHtml;?>
                </div>
            </div>

            <div class="box-footer">
                <div class="pull-right">
                    <?php echo CHtml::submitButton(Yii::t('surveys', 'Submit'), array('class' => 'btn btn-primary btn-flat')); ?>
                </div>
                <div class="clearfix"> </div>
            </div>
        </div>
        <?php echo CHtml::endForm();?>
    </div>
</div>