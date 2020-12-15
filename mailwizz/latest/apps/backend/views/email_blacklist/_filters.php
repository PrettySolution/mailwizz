<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 */
?>


<?php $form = $this->beginWidget('CActiveForm', array(
    'id'          => 'filters-form',
    'method'      => 'get',
    'action'      => $this->createUrl($this->route),
    'htmlOptions' => array(
        'style'        => 'display:' . ($filter->hasSetFilters ? 'block' : 'none'),
        'data-confirm' => Yii::t('email_blacklist', 'Are you sure you want to run this action?')
    ),
));?>
<hr />
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><span class="glyphicon glyphicon-filter"><!-- --></span> <?php echo Yii::t('email_blacklist', 'Filters');?></h3>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'email');?>
                    <?php echo $form->textField($filter, 'email', $filter->getHtmlOptions('email', array('name' => 'email'))); ?>
                    <?php echo $form->error($filter, 'email');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'reason');?>
                    <?php echo $form->textField($filter, 'reason', $filter->getHtmlOptions('reason', array('name' => 'reason'))); ?>
                    <?php echo $form->error($filter, 'reason');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'date_start');?>
                    <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker',array(
                        'model'     => $filter,
                        'attribute' => 'date_start',
                        'language'  => $filter->getDatePickerLanguage(),
                        'cssFile'   => null,
                        'options'   => array(
                            'showAnim'      => 'fold',
                            'dateFormat'    => $filter->getDatePickerFormat(),
                        ),
                        'htmlOptions'=>$filter->getHtmlOptions('date_start', array('name' => 'date_start')),
                    ));
                    ?>
                    <?php echo $form->error($filter, 'date_start');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'date_end');?>
                    <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker',array(
                        'model'     => $filter,
                        'attribute' => 'date_end',
                        'language'  => $filter->getDatePickerLanguage(),
                        'cssFile'   => null,
                        'options'   => array(
                            'showAnim'      => 'fold',
                            'dateFormat'    => $filter->getDatePickerFormat(),
                        ),
                        'htmlOptions'=>$filter->getHtmlOptions('date_end', array('name' => 'date_end')),
                    ));
                    ?>
                    <?php echo $form->error($filter, 'date_end');?>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'action');?>
                    <?php echo $form->dropDownList($filter, 'action', $filter->getActionsList(), $filter->getHtmlOptions('action', array('name' => 'action'))); ?>
                    <?php echo $form->error($filter, 'action');?>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?php echo CHtml::submitButton(Yii::t('email_blacklist', 'Submit'), array('name' => '', 'class' => 'btn btn-primary btn-flat'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>
<div class="clearfix"><!-- --></div>
