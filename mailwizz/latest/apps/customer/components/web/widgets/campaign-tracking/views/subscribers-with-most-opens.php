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

<div class="box borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><i class="fa fa-link" aria-hidden="true"></i><?php echo Yii::t('campaign_reports', 'Subscribers with most opens');?></h3>
        </div>
        <div class="pull-right">
            <?php if ($this->showDetailLinks && isset($this->controller->campaignReportsController)) { ?>
                <a href="<?php echo $this->controller->createUrl($this->controller->campaignReportsController . '/open', array('campaign_uid' => $campaign->campaign_uid));?>" class="btn btn-primary btn-flat"><?php echo IconHelper::make('view') . Yii::t('campaign_reports', 'View details');?></a>
            <?php } ?>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <?php foreach ($models as $model) { ?>
                            <li>
                                <span class="cl-span">
                                    <?php echo CHtml::link($model->subscriber->displayEmail, 'javascript:;', array('title' => $model->subscriber->displayEmail));?>
                                </span>
                                <span class="cl-span">
                                    <?php echo $model->counter;?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>