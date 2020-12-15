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
<form method="post">
    <div class="box box-primary borderless">
        <div class="box-header">
            <h3 class="box-title">License info</h3>
        </div>
        <div class="box-body">
            <div class="col-lg-12">
                <textarea style="width: 100%; height: 500px"><?php echo $license;?></textarea>
            </div>
            <div class="clearfix"><!-- --></div>      
        </div>
        <div class="box-footer">
            <div class="clearfix"><!-- --></div>        
        </div>
    </div>
</form>


