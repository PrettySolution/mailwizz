<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.8
 */

?>

<div class="modal modal-info fade" id="subscriber-modal-profile-info" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('fa-user') . Yii::t('app',  'Subscriber profile info');?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="modal-body-loader">
                        <div class="text-center">
                            <?php echo IconHelper::make('fa-spinner fa-spin') . ' ' . Yii::t('app', 'Please wait...'); ?>
                        </div>
                    </div>
                    <div class="modal-body-content" style="display:none">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
