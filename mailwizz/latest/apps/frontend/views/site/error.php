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

<div class="box box-primary borderless">
    <div class="box-heading"><h3 class="box-title"><?php echo Yii::t('app', 'Error {code}!', array('{code}' => (int)$code));?></h3></div>
    <div class="box-body">
        <p class="info"><?php echo CHtml::encode($message);?></p>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <a href="<?php echo $this->createUrl('site/index');?>" class="btn btn-default"> <i class="glyphicon glyphicon-circle-arrow-left"></i> <?php echo Yii::t('app', 'Back');?></a>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>