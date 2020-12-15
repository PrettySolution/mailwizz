<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

?>

<div class="modal fade modal-search" id="search-modal" tabindex="-1" role="dialog" aria-labelledby="search-modal-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<div class="search-wrapper">
					<?php echo CHtml::form(Yii::app()->createUrl('ext_search/index'), 'GET', array(
					    'id'            => 'search-modal-form',
                        'autocomplete'  => 'off',
                    ));?>
					<div class="search-input">
						<input autocomplete="off" name="term" id="search-term" type="text" placeholder="<?php echo Yii::t('app', 'Enter search keyword');?>">
						<i class="fa fa-spinner fa-spin"></i>
					</div>
					<?php echo CHtml::endForm();?>
					<div id="search-results-wrapper"></div>
				</div>
			</div>
		</div>
	</div>
</div>
