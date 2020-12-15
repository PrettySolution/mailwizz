<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');?>

<?php echo Yii::t('campaign_reports', 'The campaign {name} has finished sending, here are the stats', array('{name}' => $campaign->name));?>:<br />
<br /><br />

<table cellpadding="0" cellspacing="0" border="0" style="background:#f5f5f5;font-size:12px; padding:5px;width: 100%;">
    <tr style="background:#eeeeee;">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Processed');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getProcessedCount(true);?></td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Sent with success');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getDeliverySuccessCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Sent success rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getDeliverySuccessRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Send error');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getDeliveryErrorCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Send error rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getDeliveryErrorRate(true);?>%</td>
    </tr>
    
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unique opens');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUniqueOpensCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unique open rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUniqueOpensRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'All opens');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getOpensCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'All opens rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getOpensRate(true);?>%</td>
    </tr>

    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Bounced back');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getBouncesCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Bounce rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getBouncesRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Hard bounce');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getHardBouncesCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Hard bounce rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getHardBouncesRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Soft bounce');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getSoftBouncesCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Soft bounce rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getSoftBouncesRate(true);?>%</td>
    </tr>
    
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unsubscribe');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUnsubscribesCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unsubscribe rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUnsubscribesRate(true);?>%</td>
    </tr>
    
    <?php if ($campaign->option->url_tracking == CampaignOption::TEXT_YES) { ?>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Click through rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getClicksThroughRate(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Total urls for tracking');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getTrackingUrlsCount(true);?></td>
    </tr>
    <tr style="background:ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unique clicks');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUniqueClicksCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'Unique clicks rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getUniqueClicksRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'All clicks');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getClicksCount(true);?></td>
    </tr>
    <tr style="background:#eeeeee">
        <td style="padding:5px;"><?php echo Yii::t('campaign_reports', 'All clicks rate');?></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"><?php echo $campaign->stats->getClicksRate(true);?>%</td>
    </tr>
    <tr style="background:#ffffff">
        <td style="padding:5px;"></td>
        <td style="padding:5px;text-align: right; font-weight:bold;"></td>
    </tr>
    <?php } ?>
</table>

<br /><br />
<?php echo Yii::t('campaign_reports', 'Please note, you can view the full campaign reports by clicking on the link below');?><br />
<?php $url = Yii::app()->options->get('system.urls.customer_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/overview';?>
<a href="<?php echo $url;?>"><?php echo $url;?></a>

<br /><br />
<?php echo Yii::t('campaign_reports', 'The web version of this campaign is located at:');?><br />
<?php $url = Yii::app()->options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid;?>
<a href="<?php echo $url;?>"><?php echo $url;?></a>