<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */
?>
<div class="modal fade" id="campaign-share-code-import-modal" tabindex="-1" role="dialog" aria-labelledby="bulk-send-test-email-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo Yii::t('campaigns', 'Import campaigns from share code');?></h4>
			</div>
            <div class="modal-body">
                <?php
                $form = $this->beginWidget('CActiveForm', array(
                    'action' => array('campaigns/import-from-share-code'),
                    'id'     => $shareCode->modelName
                ));
                ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($shareCode, 'code');?>
                            <?php echo $form->textField($shareCode, 'code', $shareCode->getHtmlOptions('code')); ?>
                            <?php echo $form->error($shareCode, 'code');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->labelEx($shareCode, 'list_id');?>
                            <?php echo $form->dropDownList($shareCode, 'list_id', $shareCode->getListsAsDropDownOptionsByCustomerId(), $shareCode->getHtmlOptions('list_id')); ?>
                            <?php echo $form->error($shareCode, 'list_id');?>
                        </div>
                    </div>
                </div>

                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
                <button type="button" class="btn btn-primary btn-flat" onclick="$('#<?php echo $shareCode->modelName; ?>').submit();"><?php echo IconHelper::make('fa-save') . '&nbsp;' . Yii::t('app', 'Submit');?></button>
            </div>
		</div>
	</div>
</div>