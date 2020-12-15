<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-file') .  $pageHeading;?>
            </h3>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <ul class="nav nav-tabs" style="border-bottom: 0px;">
            <li class="active">
                <a href="#tab-web-server" data-toggle="tab"><?php echo Yii::t('settings', 'Web Server');?></a>
            </li>
            <li>
                <a href="#tab-cli" data-toggle="tab"><?php echo Yii::t('settings', 'Command Line Interface (CLI)');?></a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="tab-web-server">
                <iframe src="<?php echo $this->createUrl($this->route, array('show' => 1));?>" width="100%" height="700" frameborder="0"></iframe>
            </div>
            <div class="tab-pane" id="tab-cli">
                <textarea class="form-control" rows="30"><?php echo $phpInfoCli; ?></textarea>
            </div>
        </div>
    </div>
</div>