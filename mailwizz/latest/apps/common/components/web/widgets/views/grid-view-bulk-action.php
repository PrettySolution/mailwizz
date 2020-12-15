<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.4
 */
?>
<div class="row">
    <?php if (!empty($bulkActions)) {
        $form = $this->beginWidget('CActiveForm', array(
            'action'      => $formAction,
            'id'          => 'bulk-action-form',
            'htmlOptions' => array('style' => 'display:none'),
        ));
        $this->endWidget();
        ?>
        <div class="col-lg-3" id="bulk-actions-wrapper" style="display: none;">
            <div class="row">
                <div class="col-lg-6">
                    <?php echo CHtml::dropDownList('bulk_action', null, CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $bulkActions), array(
                        'class'           => 'form-control',
                        'data-delete-msg' => Yii::t('app', 'Are you sure you want to remove the selected items?'),
                    ));?>
                </div>
                <div class="col-lg-4">
                    <a href="javascript:;" class="btn btn-flat btn-primary" id="btn-run-bulk-action" style="display:none"><?php echo Yii::t('app', 'Run bulk action');?></a>
                </div>
            </div>
        </div>
    <?php } ?>
</div>