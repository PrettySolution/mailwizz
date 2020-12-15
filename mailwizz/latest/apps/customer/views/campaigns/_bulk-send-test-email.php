<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.0
 */
?>
<div class="modal fade" id="bulk-send-test-email" tabindex="-1" role="dialog" aria-labelledby="bulk-send-test-email-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo Yii::t('campaigns', 'Send a test email');?></h4>
			</div>
			<div class="modal-body">
				<div class="callout callout-info">
					<strong><?php echo Yii::t('app', 'Notes');?>: </strong><br />
					<?php
					$text = '* if multiple recipients, separate the email addresses by a comma.<br />
                     * the email tags will be parsed and we will pick a random subscriber to impersonate.<br />
                     * the tracking will not be enabled.';
					echo Yii::t('campaigns', StringHelper::normalizeTranslationString($text));
					?>
				</div>
				<?php echo CHtml::form('', 'post', array('id' => 'bulk-send-test-email-form'));?>
				<div class="form-group">
					<?php echo CHtml::label(Yii::t('campaigns', 'Recipient(s)'), 'email');?>
					<?php echo CHtml::textField('recipients_emails', $lastTestEmails, array('class' => 'form-control', 'placeholder' => Yii::t('campaigns', 'i.e: a@domain.com, b@domain.com, c@domain.com')));?>
				</div>
				<?php echo CHtml::endForm();?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
				<button type="button" class="btn btn-primary btn-flat" onclick="$('#bulk-send-test-email-form').submit();"><?php echo IconHelper::make('fa-send') . '&nbsp;' . Yii::t('campaigns', 'Send test');?></button>
			</div>
		</div>
	</div>
</div>