<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.7
 */

?>
<hr />

<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title"><?php echo IconHelper::make('fa-envelope');?> <?php echo Yii::t('lists', 'Recently sent campaigns');?></h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::dropDownList('campaign_id', $campaign->campaign_id, $campaignsList);?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard" style="padding-bottom: 0px">
                    <div class="progress-box" style="padding-bottom: 0px">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Recipients');?></span><span class="number"><?php echo CHtml::link($campaign->stats->getProcessedCount(true), 'javascript:;');?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaigns', 'List/Segment');?></span><span class="cl-span"><?php echo $campaign->getListSegmentName();?></span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo $campaign->getAttributeLabel('subject');?></span><span class="cl-span"><?php echo $campaign->subject;?></span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <div class="progress-box">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Clicks rate');?></span><span class="number"><?php echo $campaign->stats->getUniqueClicksRate(true);?>%</span>
                        </div>
                        <div class="bar"><div class="progress" style="width: <?php echo StringHelper::asPercentFloat($campaign->stats->getClicksRate(true));?>"></div></div>
                    </div>
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Unique clicks');?></span><span class="cl-span"><?php echo $campaign->stats->getUniqueClicksCount(true);?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Total clicks / Total clicks rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getClicksCount(true);?> / <?php echo $campaign->stats->getClicksRate(true);?>%</span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Clicks to opens rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getClicksToOpensRate(true);?>%</span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Click through rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getClicksThroughRate(true);?>%</span></li>
                        <?php if ($campaign->stats->getIndustryClicksRate()) { ?>
                            <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Industry avg({industry})', array('{industry}' => CHtml::link($campaign->stats->getIndustry()->name, Yii::app()->apps->isAppName('customer') ? array('account/company') : 'javascript:;')));?></span> <span class="cl-span"><?php echo $campaign->stats->getIndustryClicksRate(true);?>%</span></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <div class="progress-box">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Opens rate');?></span><span class="number"><?php echo $campaign->stats->getUniqueOpensRate(true);?>%</span>
                        </div>
                        <div class="bar"><div class="progress" style="width: <?php echo StringHelper::asPercentFloat($campaign->stats->getOpensRate(true));?>"></div></div>
                    </div>
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Unique opens');?></span><span class="cl-span"><?php echo $campaign->stats->getUniqueOpensCount(true);?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Total opens / Total opens rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getOpensCount(true);?> / <?php echo $campaign->stats->getOpensRate(true);?>%</span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Opens to clicks rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getOpensToClicksRate(true);?>%</span></li>
                        <?php if ($campaign->stats->getIndustryOpensRate()) { ?>
                            <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Industry avg({industry})', array('{industry}' => CHtml::link($campaign->stats->getIndustry()->name, Yii::app()->apps->isAppName('customer') ? array('account/company') : 'javascript:;')));?></span> <span class="cl-span"><?php echo $campaign->stats->getIndustryOpensRate(true);?>%</span></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="clearfix hidden-lg"></div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <div class="progress-box">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Bounce rate');?></span><span class="number"><?php echo $campaign->stats->getBouncesRate(true);?>%</span>
                        </div>
                        <div class="bar"><div class="progress" style="width: <?php echo StringHelper::asPercentFloat($campaign->stats->getBouncesRate(true));?>"></div></div>
                    </div>
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Hard bounces');?></span><span class="cl-span"><?php echo $campaign->stats->getHardBouncesCount(true);?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Hard bounces rate');?></span><span class="cl-span"><?php echo $campaign->stats->getHardBouncesRate(true);?>%</span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Soft bounces');?></span></span><span class="cl-span"><?php echo $campaign->stats->getSoftBouncesCount(true);?></span></li>
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Soft bounces rate');?></span></span><span class="cl-span"><?php echo $campaign->stats->getSoftBouncesRate(true);?>%</span></li>
                    </ul>
                </div>
            </div>
            <div class="clearfix hidden-md hidden-sm"></div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <div class="progress-box">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Unsubscribe rate');?></span><span class="number"><?php echo $campaign->stats->getUnsubscribesRate(true);?>%</span>
                        </div>
                        <div class="bar"><div class="progress" style="width: <?php echo StringHelper::asPercentFloat($campaign->stats->getUnsubscribesRate(true));?>"></div></div>
                    </div>
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Unsubscribes');?></span><span class="cl-span"><?php echo $campaign->stats->getUnsubscribesCount(true);?></span></li>
                    </ul>
                </div>
            </div>
            <div class="clearfix hidden-lg"></div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
                <div class="box-dashboard">
                    <div class="progress-box">
                        <div class="info">
                            <span class="name"><?php echo Yii::t('campaign_reports', 'Complaints rate');?></span><span class="number"><?php echo $campaign->stats->getComplaintsRate(true);?>%</span>
                        </div>
                        <div class="bar"><div class="progress" style="width: <?php echo StringHelper::asPercentFloat($campaign->stats->getComplaintsRate(true));?>"></div></div>
                    </div>
                    <ul class="custom-list">
                        <li><span class="cl-span"><?php echo Yii::t('campaign_reports', 'Complaints');?></span><span class="cl-span"><?php echo $campaign->stats->getComplaintsCount(true);?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <a href="<?php echo $this->createUrl('campaigns/overview', array('campaign_uid' => $campaign->campaign_uid));?>" class="btn btn-primary btn-flat" title="<?php echo Yii::t('campaign_reports', 'View campaign reports');?>"><?php echo IconHelper::make('view') . Yii::t('campaign_reports', 'View campaign reports');?></a>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>