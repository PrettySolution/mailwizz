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
<form action="" method="post">
    <div class="box box-primary borderless">
        <div class="box-header">
            <h3 class="box-title">Database credentials and import</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="form-group">
                        <label class="required">Hostname <span class="required">*</span></label>
                        <input class="form-control has-help-text<?php echo $context->getError('hostname') ? ' error':'';?>" name="hostname" type="text" value="<?php echo getPost('hostname', 'localhost');?>"/>
                        <?php if ($error = $context->getError('hostname')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-lg-2 pull-right">
                    <div class="form-group">
                        <label class="required">Port</label>
                        <input class="form-control has-help-text<?php echo $context->getError('port') ? ' error':'';?>" name="port" type="text" value="<?php echo getPost('port', '');?>"/>
                        <?php if ($error = $context->getError('port')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="required">Username</label>
                        <input class="form-control has-help-text<?php echo $context->getError('username') ? ' error':'';?>" name="username" type="text" value="<?php echo getPost('username');?>"/>
                        <?php if ($error = $context->getError('username')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control has-help-text" name="password" type="text" value="<?php echo getPost('password');?>"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10">
                    <div class="form-group">
                        <label class="required">Database name <span class="required">*</span></label>
                        <input class="form-control has-help-text<?php echo $context->getError('dbname') ? ' error':'';?>" name="dbname" type="text" value="<?php echo getPost('dbname');?>"/>
                        <?php if ($error = $context->getError('dbname')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-lg-2 pull-right">
                    <div class="form-group">
                        <label>Tables prefix</label>
                        <input class="form-control has-help-text<?php echo $context->getError('prefix') ? ' error':'';?>" name="prefix" type="text" value="<?php echo getPost('prefix', 'mw_');?>"/>
                        <?php if ($error = $context->getError('prefix')) { ?>
                            <div class="errorMessage" style="display: block;"><?php echo $error;?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>    
        </div>
        <div class="box-footer">
            <div class="pull-right">
                <button type="submit" name="next" value="1" class="btn btn-primary btn-flat"><?php echo IconHelper::make('fa-arrow-circle-o-right');?> Start importing</button>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
</form>