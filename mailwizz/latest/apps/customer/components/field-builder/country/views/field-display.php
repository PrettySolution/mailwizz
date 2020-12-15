<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.5
 */
 
?>

<div class="form-group field-<?php echo $field->type->identifier; ?> country-field wrap-<?php echo strtolower($field->tag);?>">
    <?php echo CHtml::activeLabelEx($model, 'value');?>
    <?php echo CHtml::dropDownList($field->tag, $model->value, $countriesList, $model->getHtmlOptions('value', array(
        'data-states-url' => Yii::app()->createUrl('lists/fields_country_states_by_country_name'),
    ))); ?>
    <?php echo CHtml::error($model, 'value');?>
    <?php if (!empty($field->description)) { ?>
        <div class="field-description">
            <?php echo $field->description; ?>
        </div>
    <?php } ?>
</div>