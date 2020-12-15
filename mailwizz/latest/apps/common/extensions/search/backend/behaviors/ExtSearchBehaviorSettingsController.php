<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorSettingsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
            'index'                              => array(
                'keywords'          => array('common settings', 'pagination'),
                'keywordsGenerator' => array($this, '_indexKeywordsGenerator'),
            ),
		    'cron'                               => array(
				'keywords'          => array('speed'),
			    'keywordsGenerator' => array($this, '_cronKeywordsGenerator'),
			),
            'system_urls'                       => array(
                'keywords'          => array('frontend urls', 'backend urls', 'customer urls', 'scheme', 'http', 'https', 'api urls'),
            ),
            'import_export'                      => array(
                'keywords'          => array('import-export'),
                'keywordsGenerator' => array($this, '_importExportKeywordsGenerator'),
            ),
            'email_templates'                    => array(
                'keywords'          => array('templating', 'common email templates', 'reinstall core templates'),
                'keywordsGenerator' => array($this, '_emailTemplatesKeywordsGenerator'),
            ),
            'email_blacklist'                    => array(
                'keywords'          => array('abuse', 'spam'),
                'keywordsGenerator' => array($this, '_emailBlacklistKeywordsGenerator'),
            ),
            'api'                                => array(
                'keywords'          => array('common api settings'),
                'keywordsGenerator' => array($this, '_apiKeywordsGenerator'),
            ),
            'api_ip_access'                      => array(
                'keywords'          => array('api ip block', 'api ip access', 'deny ip', 'allow ip'),
                'keywordsGenerator' => array($this, '_apiIpAccessKeywordsGenerator'),
            ),
            'customer_common'                    => array(
                'keywords'          => array('customers common settings'),
                'keywordsGenerator' => array($this, '_customerCommonKeywordsGenerator'),
            ),
            'customer_servers'                   => array(
                'keywords'          => array('customers servers settings', 'bounce servers limits', 'feedback loop servers limits'),
                'keywordsGenerator' => array($this, '_customerServersKeywordsGenerator'),
            ),
            'customer_domains'                   => array(
                'keywords'          => array('customers domains settings'),
                'keywordsGenerator' => array($this, '_customerDomainsKeywordsGenerator'),
            ),
            'customer_lists'                     => array(
                'keywords'          => array('customers lists settings', 'settings segments', 'settings subscribers'),
                'keywordsGenerator' => array($this, '_customerListsKeywordsGenerator'),
            ),
            'customer_registration'              => array(
                'keywords'          => array('customers registration settings'),
                'keywordsGenerator' => array($this, '_customerRegistrationKeywordsGenerator'),
            ),
            'customer_api'                       => array(
                'keywords'          => array('customers api settings'),
                'keywordsGenerator' => array($this, '_customerApiKeywordsGenerator'),
            ),
            'customer_sending'                   => array(
                'keywords'          => array('customers sending settings'),
                'keywordsGenerator' => array($this, '_customerSendingKeywordsGenerator'),
            ),
            'customer_quota_counters'            => array(
                'keywords'          => array('customers quota counters settings'),
                'keywordsGenerator' => array($this, '_customerQuotaCountersKeywordsGenerator'),
            ),
            'customer_campaigns'                 => array(
                'keywords'          => array('customer campaigns settings'),
                'keywordsGenerator' => array($this, '_customerCampaignsKeywordsGenerator'),
            ),
            'customer_cdn'                       => array(
                'keywords'          => array('customer cdn settings'),
                'keywordsGenerator' => array($this, '_customerCdnKeywordsGenerator'),
            ),
            'campaign_attachments'               => array(
                'keywords'          => array('campaigns attachments settings'),
                'keywordsGenerator' => array($this, '_campaignAttachmentsKeywordsGenerator'),
            ),
            'campaign_template_tags'             => array(
                'keywords'          => array('campaigns template tags settings'),
                'keywordsGenerator' => array($this, '_campaignTemplateTagsKeywordsGenerator'),
            ),
            'campaign_exclude_ips_from_tracking' => array(
                'keywords'          => array('campaigns exclude ip from tracking settings'),
                'keywordsGenerator' => array($this, '_campaignExcludeIpsFromTrackingKeywordsGenerator'),
            ),
            'campaign_blacklist_words'           => array(
                'keywords'          => array('campaigns blacklist words settings'),
                'keywordsGenerator' => array($this, '_campaignBlacklistWordsKeywordsGenerator'),
            ),
            'campaign_template_engine'           => array(
                'keywords'          => array('campaigns template engine settings'),
                'keywordsGenerator' => array($this, '_campaignTemplateEngineKeywordsGenerator'),
            ),
            'campaign_webhooks'                  => array(
                'keywords'          => array('campaigns webhooks settings'),
                'keywordsGenerator' => array($this, '_campaignTemplateEngineKeywordsGenerator'),
            ),
            'campaign_misc'                      => array(
                'keywords'          => array('campaigns miscellaneous settings'),
                'keywordsGenerator' => array($this, '_campaignMiscKeywordsGenerator'),
            ),
            'monetization'                       => array(
                'keywords'          => array('monetization settings'),
                'keywordsGenerator' => array($this, '_monetizationKeywordsGenerator'),
            ),
            'monetization_orders'                => array(
                'keywords'          => array('order monetization settings'),
                'keywordsGenerator' => array($this, '_monetizationOrdersKeywordsGenerator'),
            ),
            'monetization_invoices'              => array(
                'keywords'          => array('invoices monetization settings'),
                'keywordsGenerator' => array($this, '_monetizationInvoicesKeywordsGenerator'),
            ),
            'license'                            => array(
                'keywords'          => array('licensing'),
                'keywordsGenerator' => array($this, '_licenseKeywordsGenerator'),
            ),
            'social_links'                       => array(
                'keywords'          => array('links'),
                'keywordsGenerator' => array($this, '_socialLinksKeywordsGenerator'),
            ),
            'cdn'                                => array(
                'keywords'          => array('content delivery network'),
                'keywordsGenerator' => array($this, '_cdnKeywordsGenerator'),
            ),
            'spf_dkim'                           => array(
                'keywords'          => array('sender policy framework', 'domainkeys identified mail'),
                'keywordsGenerator' => array($this, '_spfDkimKeywordsGenerator'),
            ),
            'customization'                      => array(
                'keywords'          => array('background images'),
                'keywordsGenerator' => array($this, '_customizationKeywordsGenerator'),
            ),
            '2fa'                                => array(
                'keywords'          => array('two factors authentication', '2 factors authentication'),
                'keywordsGenerator' => array($this, '_2faKeywordsGenerator'),
            ),
        );
	}

    /**
     * @return array
     */
    public function _indexKeywordsGenerator()
    {
        $model = new OptionCommon();
	    return array_values($model->attributeLabels());
    }

	/**
	 * @return array
	 */
	public function _cronKeywordsGenerator()
	{
		$cronDeliveryModel      = new OptionCronDelivery();
		$cronLogsModel          = new OptionCronProcessDeliveryBounce();
		$cronSubscribersModel   = new OptionCronProcessSubscribers();
		$cronBouncesModel       = new OptionCronProcessBounceServers();
		$cronFeedbackModel      = new OptionCronProcessFeedbackLoopServers();
		$cronEmailBoxModel      = new OptionCronProcessEmailBoxMonitors();
		$cronTransEmailsModel   = new OptionCronProcessTransactionalEmails();

		$keywords = array();
		$keywords = CMap::mergeArray($keywords, array_values($cronDeliveryModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronLogsModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronSubscribersModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronBouncesModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronFeedbackModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronEmailBoxModel->attributeLabels()));
		$keywords = CMap::mergeArray($keywords, array_values($cronTransEmailsModel->attributeLabels()));

		return $keywords;
	}

    /**
     * @return array
     */
    public function _importExportKeywordsGenerator()
    {
        $importModel = new OptionImporter();
        $exportModel = new OptionExporter();

        $keywords = array();
        $keywords = CMap::mergeArray($keywords, array_values($importModel->attributeLabels()));
        $keywords = CMap::mergeArray($keywords, array_values($exportModel->attributeLabels()));

        return $keywords;
    }

    /**
     * @return array
     */
    public function _emailTemplatesKeywordsGenerator()
    {
        $model = new OptionEmailTemplate();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _emailBlacklistKeywordsGenerator()
    {
        $model = new OptionEmailBlacklist();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _apiKeywordsGenerator()
    {
        $model = new OptionApi();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _apiIpAccessKeywordsGenerator()
    {
        $model = new OptionApiIpAccess();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerCommonKeywordsGenerator()
    {
        $model = new OptionCustomerCommon();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerServersKeywordsGenerator()
    {
        $model = new OptionCustomerServers();
	    return array_values($model->attributeLabels());;
    }

    /**
     * @return array
     */
    public function _customerDomainsKeywordsGenerator()
    {
        $trackingModel = new OptionCustomerTrackingDomains();
        $sendingModel  = new OptionCustomerSendingDomains();

        $keywords = array();
        $keywords = CMap::mergeArray($keywords, array_values($trackingModel->attributeLabels()));
        $keywords = CMap::mergeArray($keywords, array_values($sendingModel->attributeLabels()));

        return $keywords;
    }

    /**
     * @return array
     */
    public function _customerListsKeywordsGenerator()
    {
        $model = new OptionCustomerLists();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerRegistrationKeywordsGenerator()
    {
        $model = new OptionCustomerRegistration();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerApiKeywordsGenerator()
    {
        $model = new OptionCustomerApi();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerSendingKeywordsGenerator()
    {
        $model = new OptionCustomerSending();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerQuotaCountersKeywordsGenerator()
    {
        $model = new OptionCustomerQuotaCounters();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerCampaignsKeywordsGenerator()
    {
        $model = new OptionCustomerCampaigns();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customerCdnKeywordsGenerator()
    {
        $model = new OptionCustomerCdn();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignAttachmentsKeywordsGenerator()
    {
        $model = new OptionCampaignAttachment();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignTemplateTagsKeywordsGenerator()
    {
        $model = new OptionCampaignTemplateTag();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignExcludeIpsFromTrackingKeywordsGenerator()
    {
        $model = new OptionCampaignExcludeIpsFromTracking();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignBlacklistWordsKeywordsGenerator()
    {
        $model = new OptionCampaignBlacklistWords();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignTemplateEngineKeywordsGenerator()
    {
        $model = new OptionCampaignTemplateEngine();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignWebhooksKeywordsGenerator()
    {
        $model = new OptionCampaignWebhooks();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _campaignMiscKeywordsGenerator()
    {
        $model = new OptionCampaignMisc();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _monetizationKeywordsGenerator()
    {
        $model = new OptionMonetizationMonetization();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _monetizationOrdersKeywordsGenerator()
    {
        $model = new OptionMonetizationOrders();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _monetizationInvoicesKeywordsGenerator()
    {
        $model = new OptionMonetizationInvoices();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _licenseKeywordsGenerator()
    {
        $model = new OptionLicense();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _socialLinksKeywordsGenerator()
    {
        $model = new OptionSocialLinks();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _cdnKeywordsGenerator()
    {
        $model = new OptionCdn();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _spfDkimKeywordsGenerator()
    {
        $model = new OptionSpfDkim();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _customizationKeywordsGenerator()
    {
        $model = new OptionCustomization();
	    return array_values($model->attributeLabels());
    }

    /**
     * @return array
     */
    public function _2faKeywordsGenerator()
    {
        $model = new OptionTwoFactorAuth();
        return array_values($model->attributeLabels());
    }
}
	