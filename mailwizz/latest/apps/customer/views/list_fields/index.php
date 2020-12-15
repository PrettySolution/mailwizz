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
<div class="pull-left">
    <?php $this->widget('customer.components.web.widgets.MailListSubNavWidget', array(
        'list' => $list,
    ))?>
</div>
<div class="clearfix"><!-- --></div>
<hr />
<?php $hooks->doAction('customer_controller_list_fields_before_form');?>
<?php echo CHtml::form();?>
<div class="box box-primary borderless">
    <div class="box-header">
        <h3 class="box-title"><?php echo IconHelper::make('glyphicon-tasks') .  $pageHeading;?></h3>
    </div>
    <div class="box-body">
        <div class="list-fields">
            <?php echo $fieldsHtml; ?>
        </div>
        <div class="clearfix"><!-- --></div>
        <div class="list-fields-buttons">
            <?php $hooks->doAction('customer_controller_list_fields_render_buttons');?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php echo CHtml::endForm();?>
<?php $hooks->doAction('customer_controller_list_fields_after_form');?>