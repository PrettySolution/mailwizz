<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.2
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
				<?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
		              ->add('<h3 class="box-title">' . IconHelper::make('fa-bar-chart') . Yii::t('lists', 'Completing action') . '</h3>')
		              ->render();
				?>
			</div>
			<div class="pull-right"></div>
			<div class="clearfix"><!-- --></div>
		</div>
		<div class="box-body">
			<div class="alert alert-info alert-dismissable">
				<strong>
					<?php echo Yii::t('lists', 'Please wait, this  might take a while...');?>
				</strong>
			</div>
			<div class="clearfix"><!-- --></div>
		</div>
	</div>
    <script>window.location.href = '<?php echo $redirect; ?>';</script>
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
