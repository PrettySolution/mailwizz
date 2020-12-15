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
	?>
    <ul class="nav nav-tabs" style="border-bottom: 0px;">
        <li class="inactive"><a href="<?php echo $this->createUrl('account/index'); ?>"><?php echo CHtml::encode(Yii::t('app', 'Profile'));?></a></li>
        <li class="active"><a href="<?php echo $this->createUrl('account/2fa')?>"><?php echo CHtml::encode(Yii::t('app', '2FA'));?></a></li>
    </ul>
    <?php
	/**
	 * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
	 * Please note that from inside the action callback you can access all the controller view variables
	 * via {@CAttributeCollection $collection->controller->data}
	 * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false
	 * in order to stop rendering the default content.
	 * @since 1.3.3.1
	 */
	$hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
		'controller'    => $this,
		'renderForm'    => true,
	)));

	// and render if allowed
	if ($collection->renderForm) {
		$form = $this->beginWidget('CActiveForm');
		?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <h3 class="box-title"><?php echo IconHelper::make('glyphicon-user') . Yii::t('users', 'Update your account data.');?></h3>
            </div>
            <div class="box-body">
				<?php
				/**
				 * This hook gives a chance to prepend content before the active form fields.
				 * Please note that from inside the action callback you can access all the controller view variables
				 * via {@CAttributeCollection $collection->controller->data}
				 * @since 1.3.3.1
				 */
				$hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
					'controller'    => $this,
					'form'          => $form
				)));
				?>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <a href="#page-info" class="btn btn-primary btn-flat btn-xs" data-toggle="modal"><?php echo IconHelper::make('info'); ?></a>
				            <?php echo $form->labelEx($user, 'twofa_enabled');?>
				            <?php echo $form->dropDownList($user, 'twofa_enabled', $user->getYesNoOptions(), $user->getHtmlOptions('twofa_enabled')); ?>
				            <?php echo $form->error($user, 'twofa_enabled');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <img src="<?php echo $qrCodeUri; ?>" />
                        </div>
                    </div>
                </div>
				<?php
				/**
				 * This hook gives a chance to append content after the active form fields.
				 * Please note that from inside the action callback you can access all the controller view variables
				 * via {@CAttributeCollection $collection->controller->data}
				 * @since 1.3.3.1
				 */
				$hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
					'controller'    => $this,
					'form'          => $form
				)));
				?>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
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
						<?php echo Yii::t('users', 'Use any authenticator app such as Google Authenticator to scan the QR code below.');?><br />
						<?php echo Yii::t('users', 'You will then use the authenticator app to generate the code to login in the app.');?><br />
                    </div>
                </div>
            </div>
        </div>
		<?php
		$this->endWidget();
	}
	/**
	 * This hook gives a chance to append content after the active form.
	 * Please note that from inside the action callback you can access all the controller view variables
	 * via {@CAttributeCollection $collection->controller->data}
	 * @since 1.3.3.1
	 */
	$hooks->doAction('after_active_form', new CAttributeCollection(array(
		'controller'      => $this,
		'renderedForm'    => $collection->renderForm,
	)));
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