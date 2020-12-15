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
<div>
    <label><?php echo Yii::t('lists', 'Available tags:');?></label>
    <?php foreach ($tags as $tag) { ?>
    <a href="javascript:;" class="btn btn-xs btn-primary btn-flat" data-tag-name="<?php echo CHtml::encode($tag['tag']);?>">
        <?php echo CHtml::encode($tag['tag']);?>
    </a>
    <?php } ?>
</div>