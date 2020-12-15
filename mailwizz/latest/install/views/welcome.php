<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

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
 
?>
<div class="callout callout-info">
    Thank you for purchasing MailWizz EMA.<br /> 
    Let's start installing the application on your server by entering your license info.
</div>

<form method="post">
    <div class="box box-primary borderless">
        <div class="box-header">
            <h3 class="box-title">Welcome - Please enter your license info</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="required">I bought the license from: <span class="required">*</span></label>
                        <select class="form-control has-help-text<?php echo $context->getError('market_place') ? ' error':'';?>" name="market_place">
                            <?php foreach ($marketPlaces as $marketPlace => $marketPlaceName) { ?>
                                <option value="<?php echo $marketPlace?>"<?php echo getPost('market_place', '') == $marketPlace ? ' selected="selected"':'';?>><?php echo $marketPlaceName;?></option>
                            <?php } ?>
                        </select>
                        <?php if ($error = $context->getError('market_place')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="required">Purchase code <span class="required">*</span></label>
                        <input placeholder="Your purchase code" class="form-control has-help-text<?php echo $context->getError('purchase_code') ? ' error':'';?>" name="purchase_code" type="text" value="<?php echo getPost('purchase_code', '');?>"/>
                        <?php if ($error = $context->getError('purchase_code')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input name="terms_consent" type="checkbox" value="1"<?php echo getPost('terms_consent', '') ? ' checked' : '';?>/> I agree with the <a href="https://www.mailwizz.com/terms" target="_blank">Terms and Conditions</a>.
                        <?php if ($error = $context->getError('terms_consent')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <div class="pull-right">
                <button class="btn btn-primary btn-flat" value="1" name="next"><?php echo IconHelper::make('fa-arrow-circle-o-right');?> Next</button>
            </div>
            <div class="clearfix"><!-- --></div>        
        </div>
    </div>
</form>