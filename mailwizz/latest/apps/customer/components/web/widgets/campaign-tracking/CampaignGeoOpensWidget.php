<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignGeoOpensWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
 
class CampaignGeoOpensWidget extends CWidget 
{
    /**
     * @var $campaign Campaign|null
     */
    public $campaign;

    /**
     * @var null
     */
    public $headingLeft = null;

    /**
     * @var null
     */
    public $headingRight = null;

    /**
     * @return string
     */
    public function run() 
    {
        $customer = null;
        
        if ($this->campaign) {
            $customer = $this->campaign->customer;
        } elseif (Yii::app()->apps->isAppName('customer')) {
            $customer = Yii::app()->customer->getModel();
        }
        
        if (empty($customer)) {
            return '';
        }
        
        if ($customer->getGroupOption('campaigns.show_geo_opens', 'no') != 'yes') {
            return '';
        }

	    // 1.7.9
	    if ($this->campaign && $this->campaign->option->open_tracking != CampaignOption::TEXT_YES) {
		    return '';
	    }

	    // 1.7.9 - static counters
	    if ($this->campaign && $this->campaign->option->opens_count >= 0) {
		    return '';
	    }
        
        if ($this->headingLeft === null || !is_object($this->headingLeft)) {
            $this->headingLeft = BoxHeaderContent::make(BoxHeaderContent::LEFT)->add('<h3 class="box-title">' . IconHelper::make('glyphicon-map-marker') . Yii::t('campaigns', 'Campaign Geo Opens') . '</h3>');
        }
        
        $cacheKey = __METHOD__;
        if ($this->campaign) {
            $cacheKey .= '::' . $this->campaign->campaign_uid;
        }
        if (Yii::app()->apps->isAppName('customer') && (int)Yii::app()->customer->getId() > 0) {
            $cacheKey .= '::' . Yii::app()->customer->getModel()->customer_uid;
        }
        $cacheKey = sha1($cacheKey);
        
        if (($data = Yii::app()->cache->get($cacheKey)) === false) {
            $data = $this->getData();
            Yii::app()->cache->set($cacheKey, $data, 300);
        }
        
        if (empty($data)) {
            return '';
        }
        
        $chartData = array();
        foreach ($data as $row) {
            $chartData[] = array(
                'label'           => $row['country_name'],
                'data'            => $row['opens_count'],
                'count'           => $row['opens_count'],
                'count_formatted' => $row['opens_count_formatted'],
            );
        }

        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.pie.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/campaign-geo-opens.js'));
        
        $this->render('campaign-geo-opens', compact('chartData', 'data'));
    }

	/**
	 * @return array
	 * @throws CException
	 */
    protected function getData()
    {
        $query = 'SELECT DISTINCT(`cto`.`location_id`) FROM `{{campaign_track_open}}` cto INNER JOIN `{{ip_location}}` l on `l`.`location_id` = `cto`.`location_id` '; 
        if (empty($this->campaign) && (int)Yii::app()->customer->getId() > 0) {
            $query .= ' INNER JOIN `{{campaign}}` `c` ON `c`.`campaign_id` = `cto`.`campaign_id` ';
        }
        $query .= ' WHERE `cto`.`location_id` IS NOT NULL ';
        if (empty($this->campaign) && (int)Yii::app()->customer->getId() > 0) {
            $query .= ' AND `c`.`customer_id` = ' . (int)Yii::app()->customer->getId();
        } elseif (!empty($this->campaign)) {
            $query .= ' AND `cto`.`campaign_id` = ' . (int)$this->campaign->campaign_id;
        }
        $query .= ' GROUP BY `cto`.`location_id` ';
        
        $rows = Yii::app()->getDb()->createCommand($query)->queryAll(true);
        if (empty($rows)) {
            return array();
        }

        $ids = array();
        foreach ($rows as $row) {
            $ids[] = (int)$row['location_id'];
        }

        $query = 'SELECT `location_id`, `country_code`, `country_name` FROM `{{ip_location}}` WHERE `location_id` IN ('. implode(',', $ids) .')';
        $rows  = Yii::app()->getDb()->createCommand($query)->queryAll(true);

        if (empty($rows)) {
            return array();
        }
        
        $countries = array();
        foreach ($rows as $row) {
            if (!isset($countries[$row['country_name']])) {
                $countries[$row['country_name']] = array();
            }
            $countries[$row['country_name']][] = $row;
        }

        $sorts = array();
        $data  = array();
        foreach ($countries as $countryName => $locations) {
            $countryCode = '';
            $ids = array();
            foreach ($locations as $location) {
                $ids[] = (int)$location['location_id'];
                if (!$countryCode) {
                    $countryCode = $location['country_code'];
                }
            }
            
            $query = 'SELECT COUNT(*) as `cnt` FROM `{{campaign_track_open}}` cto ';
            if (empty($this->campaign) && (int)Yii::app()->customer->getId() > 0) {
                $query .= ' INNER JOIN `{{campaign}}` `c` ON `c`.`campaign_id` = `cto`.`campaign_id` ';
            }
            $query .= ' WHERE `location_id` IN ('. implode(',', $ids) .') ';
            if (empty($this->campaign) && (int)Yii::app()->customer->getId() > 0) {
                $query .= ' AND `c`.`customer_id` = ' . (int)Yii::app()->customer->getId();
            } elseif (!empty($this->campaign)) {
                $query .= ' AND `cto`.`campaign_id` = ' . (int)$this->campaign->campaign_id;
            }
            
            $row = Yii::app()->getDb()->createCommand($query)->queryRow(true);

            $apps       = Yii::app()->apps;
            $controller = $this->controller;
            
            $actionLinks = '';
            
            if ($apps->isAppName('customer') || $apps->isAppName('frontend')) {

                if ($this->campaign) {

                    $canExport = $this->campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes';

                    if ($canExport) {

                        $campaignsReport       = 'campaign_reports';
                        $campaignsReportExport = 'campaign_reports_export';
                        if (Yii::app()->apps->isAppName('frontend')) {
                            $campaignsReport        = 'campaigns_reports';
                            $campaignsReportExport  = 'campaigns_reports_export';
                        }

                        $actionLinks  = '[%s] [' . Yii::t('campaigns', 'Export') . ': %s / %s]';
                        $campaignUid  = $this->campaign->campaign_uid;
                        $detailsUrl   = CHtml::link(Yii::t('campaigns', 'Details'), $controller->createUrl($campaignsReport . '/open', array('campaign_uid' => $campaignUid, 'country_code' => $countryCode)));
                        $exportAll    = CHtml::link(Yii::t('campaigns', 'All'), $controller->createUrl($campaignsReportExport . '/open', array('campaign_uid' => $campaignUid, 'country_code' => $countryCode)));
                        $exportUnique = CHtml::link(Yii::t('campaigns', 'Unique'), $controller->createUrl($campaignsReportExport . '/open_unique', array('campaign_uid' => $campaignUid, 'country_code' => $countryCode)));
                        $actionLinks  = sprintf($actionLinks, $detailsUrl, $exportAll, $exportUnique);

                    } else {

                        $actionLinks = '[%s]';
                        $campaignUid = $this->campaign->campaign_uid;
                        $detailsUrl  = CHtml::link(Yii::t('campaigns', 'Details'), $controller->createUrl('campaign_reports/open', array('campaign_uid' => $campaignUid, 'country_code' => $countryCode)));
                        $actionLinks = sprintf($actionLinks, $detailsUrl);

                    }

                } elseif (Yii::app()->customer->getId()) {

                    $canExport = Yii::app()->customer->getModel()->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes';

                    if ($canExport) {

                        $actionLinks  = '[%s] [' . Yii::t('campaigns', 'Export') . ': %s / %s]';
                        $detailsUrl   = CHtml::link(Yii::t('campaigns', 'Details'), $controller->createUrl('campaigns_geo_opens/all', array('country_code' => $countryCode)));
                        $exportAll    = CHtml::link(Yii::t('campaigns', 'All'), $controller->createUrl('campaigns_geo_opens/export_all', array('country_code' => $countryCode)));
                        $exportUnique = CHtml::link(Yii::t('campaigns', 'Unique'), $controller->createUrl('campaigns_geo_opens/export_unique', array('country_code' => $countryCode)));
                        $actionLinks  = sprintf($actionLinks, $detailsUrl, $exportAll, $exportUnique);

                    } else {

                        $actionLinks = '[%s]';
                        $detailsUrl  = CHtml::link(Yii::t('campaigns', 'Details'), $controller->createUrl('campaigns_geo_opens/opens', array('country_code' => $countryCode)));
                        $actionLinks = sprintf($actionLinks, $detailsUrl);

                    }

                }
                
            }

            $data[] = array(
                'location_ids'          => $ids,
                'country_name'          => $countryName,
                'country_code'          => $countryCode,
                'opens_count'           => $row['cnt'],
                'opens_count_formatted' => Yii::app()->numberFormatter->formatDecimal($row['cnt']),
                'action_links'          => $actionLinks,
                'flag_url'              => Yii::app()->apps->getAppUrl('frontend', 'assets/img/country-flags/' . strtolower($countryCode) . '.png', true, true),
            );
            $sorts[] = $row['cnt'];
        }

        array_multisort($sorts, SORT_NUMERIC | SORT_DESC, $data);
        
        return $data;
    }
}