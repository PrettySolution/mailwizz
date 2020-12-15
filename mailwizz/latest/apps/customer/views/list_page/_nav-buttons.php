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
<div class="pull-right">
    <div class="btn-group">
        <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown">
            <?php echo str_repeat('&nbsp;', 10) . Yii::t('list_pages', 'Select another list page to edit') . str_repeat('&nbsp;', 10)?> <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <?php foreach ($pageTypes as $pType) { ?>
            <li><a href="<?php echo $this->createUrl($this->route, array('list_uid' => $list->list_uid, 'type' => $pType->slug));?>"><?php echo Yii::t('list_pages', $pType->name);?></a></li>
            <?php } ?>
        </ul>
    </div>    
</div>
<div class="clearfix"><!-- --></div>
<hr />