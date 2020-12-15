<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
 
?>

<div class="form-group field-<?php echo $field->type->identifier; ?> wrap-<?php echo strtolower($field->tag);?>">
    <?php echo CHtml::activeLabelEx($model, 'value');?>
    <?php echo CHtml::textField($field->tag, $model->value, $model->getHtmlOptions('value', array('readonly' => true))); ?>
    <?php echo CHtml::error($model, 'value');?>
    <?php if (!empty($field->description)) { ?>
        <div class="field-description">
            <?php echo $field->description; ?>
        </div>
    <?php } ?>
</div>