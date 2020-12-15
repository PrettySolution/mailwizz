<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */
?>


<?php echo CHtml::form($this->createUrl($this->route, array('list_uid' => $list->list_uid)), 'get', array(
    'id'    => 'campaigns-filters-form',
    'style' => 'display:' . (!empty($getFilterSet) ? 'block' : 'none') . ';',
));?>
<hr />
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><span class="glyphicon glyphicon-filter"><!-- --></span> <?php echo Yii::t('list_subscribers', 'Campaigns filters');?></h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::submitButton(Yii::t('list_subscribers', 'Set filters'), array('name' => 'submit', 'class' => 'btn btn-primary btn-flat'));?>
            <?php echo CHtml::link(Yii::t('list_subscribers', 'Reset filters'), array('list_subscribers/index', 'list_uid' => $list->list_uid), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('list_subscribers', 'Reset filters')));?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <label><?php echo Yii::t('list_subscribers', 'Show only subscribers that');?>:</label>
                <?php echo CHtml::dropDownList('filter[campaigns][action]', $getFilter['campaigns']['action'], CMap::mergeArray(array('' => ''), $subscriber->getCampaignFilterActions()), array('class' => 'form-control'));?>
            </div>    
            <div class="col-lg-4">
                <label><?php echo Yii::t('list_subscribers', 'This campaign');?>:</label>
                <?php echo CHtml::dropDownList('filter[campaigns][campaign]', $getFilter['campaigns']['campaign'], CMap::mergeArray(array('' => ''), $listCampaigns), array('class' => 'form-control'));?>
            </div>
            <div class="col-lg-4">
                <label><?php echo Yii::t('list_subscribers', 'In the last');?>:</label>
                <div class="input-group">
                    <?php echo CHtml::numberField('filter[campaigns][atuc]', $getFilter['campaigns']['atuc'], array('class' => 'form-control', 'placeholder' => 2));?>
                    <span class="input-group-addon">
                        <?php echo CHtml::dropDownList('filter[campaigns][atu]', $getFilter['campaigns']['atu'], $subscriber->getFilterTimeUnits(), array('class' => 'form-control'));?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo CHtml::endForm();?>
<div class="clearfix"><!-- --></div>
