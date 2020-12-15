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
 
?>


<div class="login-box-body">
    <p class="login-box-msg">
        <h3><?php echo Yii::t('app', 'Error {code}!', array('{code}' => (int)$code));?></h3>
    </p>
    <div class="row">
        <div class="col-lg-12">
            <h5><?php echo CHtml::encode($message);?></h5>
        </div>
    </div>
    <hr />
    <div class="row">
        <div class="col-lg-12">
            <div class="pull-right">
                <a href="javascript:history.back(-1);" class="btn btn-default"> <i class="glyphicon glyphicon-circle-arrow-left"></i> <?php echo Yii::t('app', 'Back')?></a>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    </div>
</div>