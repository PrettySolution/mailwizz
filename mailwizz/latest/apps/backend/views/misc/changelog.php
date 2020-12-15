<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.2
 */
 
?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-file') .  $pageHeading;?>
            </h3>
        </div>
        <div class="pull-right">
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <textarea class="form-control" rows="30"><?php echo $changeLog;?></textarea>  
    </div>
</div>