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
        'data-confirm' => Yii::t('list_subscribers', 'Are you sure you want to run this action?')
    ),
));?>
<hr />
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><span class="glyphicon glyphicon-filter"><!-- --></span> <?php echo Yii::t('list_subscribers', 'Filters');?></h3>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <table class="table table-hover">
            <tr>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'lists');?>
                        <?php echo $form->dropDownList($filter, 'lists', $filter->getListsList(), $filter->getHtmlOptions('lists', array('multiple' => true, 'name' => 'lists'))); ?>
                        <?php echo $form->error($filter, 'lists');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'statuses');?>
                        <?php echo $form->dropDownList($filter, 'statuses', $filter->getStatusesList(), $filter->getHtmlOptions('statuses', array('multiple' => true, 'name' => 'statuses'))); ?>
                        <?php echo $form->error($filter, 'statuses');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'sources');?>
                        <?php echo $form->dropDownList($filter, 'sources', $filter->getSourcesList(), $filter->getHtmlOptions('sources', array('multiple' => true, 'name' => 'sources'))); ?>
                        <?php echo $form->error($filter, 'sources');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'unique');?>
                        <?php echo $form->dropDownList($filter, 'unique', CMap::mergeArray(array('' => ''), $filter->getYesNoOptions()), $filter->getHtmlOptions('unique', array('name' => 'unique'))); ?>
                        <?php echo $form->error($filter, 'unique');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'action');?>
                        <?php echo $form->dropDownList($filter, 'action', $filter->getActionsList(), $filter->getHtmlOptions('action', array('name' => 'action'))); ?>
                        <?php echo $form->error($filter, 'action');?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'email');?>
                        <?php echo $form->textField($filter, 'email', $filter->getHtmlOptions('email', array('name' => 'email'))); ?>
                        <?php echo $form->error($filter, 'email');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'uid');?>
                        <?php echo $form->textField($filter, 'uid', $filter->getHtmlOptions('uid', array('name' => 'uid'))); ?>
                        <?php echo $form->error($filter, 'uid');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'ip');?>
                        <?php echo $form->textField($filter, 'ip', $filter->getHtmlOptions('ip', array('name' => 'ip'))); ?>
                        <?php echo $form->error($filter, 'ip');?>
                    </div>
                </td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'date_added_start');?>
                        <?php echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'     => $filter,
                            'attribute' => 'date_added_start',
                            'cssFile'   => null,
                            'language'  => $filter->getDatePickerLanguage(),
                            'options'   => array(
                                'showAnim'   => 'fold',
                                'dateFormat' => $filter->getDatePickerFormat(),
                            ),
                            'htmlOptions' => $filter->getHtmlOptions('date_added_start', array('name' => 'date_added_start')),
                        ), true); ?>
                        <?php echo $form->error($filter, 'date_added_start');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'date_added_end');?>
                        <?php echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                            'model'     => $filter,
                            'attribute' => 'date_added_end',
                            'cssFile'   => null,
                            'language'  => $filter->getDatePickerLanguage(),
                            'options'   => array(
                                'showAnim'   => 'fold',
                                'dateFormat' => $filter->getDatePickerFormat(),
                            ),
                            'htmlOptions' => $filter->getHtmlOptions('date_added_end', array('name' => 'date_added_end')),
                        ), true); ?><?php echo $form->error($filter, 'date_added_end');?>
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'campaigns_action');?>
                        <?php echo $form->dropDownList($filter, 'campaigns_action', CMap::mergeArray(array('' => ''), $filter->getCampaignFilterActions()), $filter->getHtmlOptions('campaigns_action', array('name' => 'campaigns_action'))); ?>
                        <?php echo $form->error($filter, 'campaigns_action');?>
                    </div>
                </td>
                <td>
                    <div class="form-group">
                        <?php echo $form->labelEx($filter, 'campaigns');?>
                        <?php echo $form->dropDownList($filter, 'campaigns', $filter->getCampaignsList(), $filter->getHtmlOptions('campaigns', array('multiple' => true, 'name' => 'campaigns'))); ?>
                        <?php echo $form->error($filter, 'campaigns');?>
                    </div>
                </td>
                <td style="width:280px">
                    <label><?php echo Yii::t('list_subscribers', 'In the last');?>:</label>
                    <div class="input-group">
                        <?php echo $form->numberField($filter, 'campaigns_atuc', $filter->getHtmlOptions('campaign_atuc', array('name' => 'campaigns_atuc', 'type' => 'number', 'placeholder' => 30))); ?>
                        <span class="input-group-addon">
                            <?php echo $form->dropDownList($filter, 'campaigns_atu', $filter->getFilterTimeUnits(), $filter->getHtmlOptions('campaigns_atu', array('name' => 'campaigns_atu', 'class' => 'xform-control'))); ?>
                        </span>
                    </div>
                </td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?php echo CHtml::submitButton(Yii::t('list_subscribers', 'Submit'), array('name' => '', 'class' => 'btn btn-primary btn-flat'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>
<div class="clearfix"><!-- --></div>
