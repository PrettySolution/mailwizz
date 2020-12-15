<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.5
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    
    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewWidget', array(
        'campaign' => $campaign,
    ));

    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewCounterBoxesWidget', array(
        'campaign' => $campaign,
    ));

    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignOverviewRateBoxesWidget', array(
        'campaign' => $campaign,
    ));

    $this->widget('customer.components.web.widgets.campaign-tracking.Campaign24HoursPerformanceWidget', array(
        'campaign' => $campaign,
    ));
    
    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTopDomainsOpensClicksGraphWidget', array(
        'campaign' => $campaign,
    ));

    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignGeoOpensWidget', array(
        'campaign' => $campaign,
    ));

	$this->widget('customer.components.web.widgets.campaign-tracking.CampaignOpenUserAgentsWidget', array(
		'campaign' => $campaign,
	));
    
    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingTopClickedLinksWidget', array(
        'campaign'        => $campaign,
        'showDetailLinks' => false,
    ));

    $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestClickedLinksWidget', array(
        'campaign'        => $campaign,
        'showDetailLinks' => false,
    ));

    ?>

    <div class="row">
        <div class="col-lg-6">
            <?php
            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingLatestOpensWidget', array(
                'campaign' => $campaign,
                'showDetailLinks' => false,
            ));
            ?>
        </div>
        <div class="col-lg-6">
            <?php
            $this->widget('customer.components.web.widgets.campaign-tracking.CampaignTrackingSubscribersWithMostOpensWidget', array(
                'campaign' => $campaign,
                'showDetailLinks' => false,
            ));
            ?>
        </div>
    </div>
    
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));