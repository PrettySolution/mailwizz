<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
 */
?>


<?php $form = $this->beginWidget('CActiveForm', array(
    'id'          => 'filters-form',
    'method'      => 'get',
    'action'      => $this->createUrl($this->route),
    'htmlOptions' => array(
        'style'        => 'display:' . ( $filter->hasFilters ? 'block' : 'none' ),
    ),
));?>
<hr />
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><span class="glyphicon glyphicon-filter"><!-- --></span> <?php echo Yii::t('filters', 'Filters');?></h3>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">

        <div class="row">
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'lists');?>
                    <?php echo $form->dropDownList($filter, 'lists', CMap::mergeArray(array('' => ''), Lists::getListsForCampaignFilterDropdown($customerId)), $filter->getHtmlOptions('lists', array('multiple' => true))); ?>
                    <?php echo $form->error($filter, 'lists');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'campaigns');?>
                    <?php echo $form->dropDownList($filter, 'campaigns', CMap::mergeArray(array('' => ''), CampaignStatsFilter::getCampaignsForCampaignFilterDropdown($customerId)), $filter->getHtmlOptions('campaigns', array('multiple' => true))); ?>
                    <?php echo $form->error($filter, 'campaigns');?>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group" style="margin-bottom: 5px">
                            <?php
                            echo $form->labelEx($filter, 'date_start');
                            echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model'     => $filter,
                                'attribute' => 'date_start',
                                'cssFile'   => null,
                                'language'  => $filter->getDatePickerLanguage(),
                                'options'   => array(
                                    'showAnim'   => 'fold',
                                    'dateFormat' => $filter->getDatePickerFormat(),
                                ),
                                'htmlOptions' => array('class' => ''),
                            ), true);
                            echo $form->error($filter, 'date_start')
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php
                            echo $form->labelEx($filter, 'date_end');
                            echo $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                                'model'     => $filter,
                                'attribute' => 'date_end',
                                'cssFile'   => null,
                                'language'  => $filter->getDatePickerLanguage(),
                                'options'   => array(
                                    'showAnim'   => 'fold',
                                    'dateFormat' => $filter->getDatePickerFormat(),
                                ),
                                'htmlOptions' => array('class' => ''),
                            ), true);
                            echo $form->error($filter, 'date_end')
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group">
                    <?php echo $form->labelEx($filter, 'action');?>
                    <?php echo $form->dropDownList($filter, 'action', CMap::mergeArray(array('' => ''), $filter->getFilterActionsList()), $filter->getHtmlOptions('action')); ?>
                    <?php echo $form->error($filter, 'action');?>
                </div>
            </div>
        </div>
        
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?php echo CHtml::submitButton(Yii::t('filters', 'Submit'), array('name' => '', 'class' => 'btn btn-primary btn-flat'));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $this->endWidget(); ?>
<div class="clearfix"><!-- --></div>
