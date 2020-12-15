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

<div class="btn-group">
    <button type="button" class="btn btn-default btn-flat dropdown-toggle" data-toggle="dropdown">
        <?php echo Yii::t('app', 'Quick links');?> <span class="caret"></span>
    </button>
    <?php $this->controller->widget('zii.widgets.CMenu', array(
        'items'         => $this->getNavItems(),
        'htmlOptions'   => array(
            'class' => 'dropdown-menu',
            'role'  => 'menu'
        ),
    ));?>
</div>    