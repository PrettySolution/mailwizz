<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignsController
 *
 * Handles the actions for campaigns related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class CampaignsController extends Controller
{
    /**
     * @var string
     */
    public $campaignReportsController = 'campaign_reports';

    /**
     * @var string
     */
    public $campaignReportsExportController = 'campaign_reports_export';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageStyles')->add(array('src' => AssetsUrl::js('datetimepicker/css/bootstrap-datetimepicker.min.css')));
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('datetimepicker/js/bootstrap-datetimepicker.min.js')));

        $languageCode = LanguageHelper::getAppLanguageCode();
        if (Yii::app()->language != Yii::app()->sourceLanguage && is_file(AssetsPath::js($languageFile = 'datetimepicker/js/locales/bootstrap-datetimepicker.'.$languageCode.'.js'))) {
            $this->getData('pageScripts')->add(array('src' => AssetsUrl::js($languageFile)));
        }

        if (MW_COMPOSER_SUPPORT) {
            $this->getData('pageStyles')->add(array('src' => Yii::app()->apps->getBaseUrl('assets/js/jqcron/jqCron.css')));
            $this->getData('pageScripts')->add(array('src' => Yii::app()->apps->getBaseUrl('assets/js/jqcron/jqCron.js')));
            if (is_file(Yii::getPathOfAlias('root.assets.js') . '/jqcron/jqCron.'.$languageCode.'.js')) {
                $this->getData('pageScripts')->add(array('src' => Yii::app()->apps->getBaseUrl('assets/js/jqcron/jqCron.'.$languageCode.'.js')));
                $this->setData('jqCronLanguage', $languageCode);
            } else {
                $this->getData('pageScripts')->add(array('src' => Yii::app()->apps->getBaseUrl('assets/js/jqcron/jqCron.en.js')));
                $this->setData('jqCronLanguage', 'en');
            }
        }

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('campaigns.js')));
        $this->getData('pageStyles')->add(array('src' => AssetsUrl::css('wizard.css')));

        $this->onBeforeAction = array($this, '_registerJuiBs');

        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete, pause_unpause, copy, resume_sending, remove_attachment',
        ), parent::filters());
    }

    /**
     * List available campaigns
     */
    public function actionIndex()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();

        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();
        $campaign->customer_id = (int)Yii::app()->customer->getId();

	    // 1.6.0
	    $this->setData(array(
		    'lastTestEmails'    => Yii::app()->session->get('campaignLastTestEmails'),
		    'lastTestFromEmail' => Yii::app()->session->get('campaignLastTestFrom'),
	    ));

	    // 1.7.6
        $shareCode = new CampaignShareCodeImport();
        $shareCode->customer_id = (int)Yii::app()->customer->getId();
        $this->setData(array(
            'shareCode' => $shareCode
        ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns'),
            'pageHeading'       => Yii::t('campaigns', 'Campaigns'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('index', compact('campaign'));
    }

    /**
     * List available regular campaigns
     */
    public function actionRegular()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();

        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();
        $campaign->customer_id = (int)Yii::app()->customer->getId();
        $campaign->type        = Campaign::TYPE_REGULAR;

	    // 1.6.0
	    $this->setData(array(
		    'lastTestEmails'    => Yii::app()->session->get('campaignLastTestEmails'),
		    'lastTestFromEmail' => Yii::app()->session->get('campaignLastTestFrom'),
	    ));
	    
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns') . ' | ' . Yii::t('campaigns', 'Regular campaigns'),
            'pageHeading'       => Yii::t('campaigns', 'Regular campaigns'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Regular campaigns') => $this->createUrl('campaigns/regular'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render($campaign->type, compact('campaign'));
    }

	/**
	 * List available autoresponder campaigns
	 */
    public function actionAutoresponder()
    {
        $campaign = new Campaign('search');
        $campaign->unsetAttributes();

        // 1.4.4
        $campaign->stickySearchFilters->setStickySearchFilters();
        $campaign->customer_id = (int)Yii::app()->customer->getId();
        $campaign->type        = Campaign::TYPE_AUTORESPONDER;
        $campaign->addRelatedRecord('option', new CampaignOption(), false);

	    // 1.6.0
	    $this->setData(array(
		    'lastTestEmails'    => Yii::app()->session->get('campaignLastTestEmails'),
		    'lastTestFromEmail' => Yii::app()->session->get('campaignLastTestFrom'),
	    ));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns') . ' | ' . Yii::t('campaigns', 'Autoresponders'),
            'pageHeading'       => Yii::t('campaigns', 'Autoresponders'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Autoresponders') => $this->createUrl('campaigns/autoresponder'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render($campaign->type, compact('campaign'));
    }

	/**
	 * Show the overview for a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionOverview($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
     
        if (!$campaign->accessOverview) {
            $this->redirect(array('campaigns/setup', 'campaign_uid' => $campaign->campaign_uid));
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaign overview'),
            'pageHeading'       => Yii::t('campaigns', 'Campaign overview'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/' . $campaign->type),
                $campaign->name . ' ' => $this->createUrl('campaigns/overview', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Overview')
            )
        ));

        // render
        $this->render('overview', compact('campaign'));
    }

	/**
	 * Create a new campaign
	 * 
	 * @param string $type
	 *
	 * @throws CException
	 */
    public function actionCreate($type = '')
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $customer   = Yii::app()->customer->getModel();
        $indexUrl   = array('campaigns/index');

        $campaign = new Campaign('step-name');
        $campaign->customer_id = (int)$customer->customer_id;
        
        $types = $campaign->getTypesList();
        if ($type && isset($types[$type])) {
            $campaign->type = $type;
            $indexUrl = array('campaigns/' . $campaign->type);
        }
        
        if (($maxCampaigns = (int)$customer->getGroupOption('campaigns.max_campaigns', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
            $campaignsCount = Campaign::model()->count($criteria);
            if ($campaignsCount >= $maxCampaigns) {
                $notify->addWarning(Yii::t('lists', 'You have reached the maximum number of allowed campaigns.'));
                $this->redirect($indexUrl);
            }
        }

        $campaignTempSource = new CampaignTemporarySource();
        $temporarySources   = array();
        $multiListsAllowed  = $customer->getGroupOption('campaigns.send_to_multiple_lists', 'no') == 'yes';

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($campaign->modelName, array()))) {
            $campaign->attributes = $attributes;
            if ($campaign->save()) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->campaignCreated($campaign);
                }

                $option = new CampaignOption();
                $option->campaign_id = $campaign->campaign_id;
                $option->save();

                if ($multiListsAllowed && ($attributes = (array)$request->getPost($campaignTempSource->modelName, array()))) {
                    foreach ($attributes as $attrs) {
                        $tempModel = new CampaignTemporarySource();
                        $tempModel->attributes  = $attrs;
                        $tempModel->campaign_id = $campaign->campaign_id;
                        $tempModel->save();
                    }
                }

                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'campaign'  => $campaign,
            )));

            if ($collection->success) {
                $this->redirect(array('campaigns/setup', 'campaign_uid' => $campaign->campaign_uid));
            }
        }

        $listsArray      = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getListsDropDownArray());
        $segmentsArray   = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getSegmentsDropDownArray());
        $groupsArray     = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getGroupsDropDownArray());
        $canSegmentLists = $customer->getGroupOption('lists.can_segment_lists', 'yes') == 'yes';

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Create new campaign'),
            'pageHeading'       => Yii::t('campaigns', 'Create new campaign'),
            'pageBreadcrumbs'   =>  array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('step-name', compact('campaign', 'listsArray', 'segmentsArray', 'groupsArray', 'campaignTempSource', 'temporarySources', 'multiListsAllowed', 'canSegmentLists'));
    }

	/**
	 * Update existing campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionUpdate($campaign_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $campaign   = $this->loadCampaignModel($campaign_uid);
        $customer   = Yii::app()->customer->getModel();

        if (!$campaign->editable) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }
        $campaignRef = clone $campaign;
        $campaign->scenario = 'step-name';
    
        $campaignTempSource = new CampaignTemporarySource();
        $temporarySources   = CampaignTemporarySource::model()->findAllByAttributes(array('campaign_id' => $campaign->campaign_id));
        $multiListsAllowed  = $customer->getGroupOption('campaigns.send_to_multiple_lists', 'no') == 'yes';

        if ($request->isPostRequest) {
            
            // 1.3.8.8
            $attributes = (array)$request->getPost($campaign->modelName, array());
            
            // since 1.3.4.2 we don't allow changing the list/segment if the campaign is paused.
            if ($campaign->getIsPaused()) {
	            
            	// 1.6.0
	            $attributes = array(
	            	'name'     => isset($attributes['name']) ? $attributes['name'] : '',
		            'group_id' => isset($attributes['group_id']) ? $attributes['group_id'] : null,
	            );
            }
            
            $campaign->attributes = $attributes;
            if ($campaign->save()) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->campaignUpdated($campaign);
                }
                CampaignTemporarySource::model()->deleteAllByAttributes(array('campaign_id' => $campaign->campaign_id));
                if ($multiListsAllowed && ($attributes = (array)$request->getPost($campaignTempSource->modelName, array()))) {
                    foreach ($attributes as $attrs) {
                        $tempModel = new CampaignTemporarySource();
                        $tempModel->attributes  = $attrs;
                        $tempModel->campaign_id = $campaign->campaign_id;
                        $tempModel->save();
                    }
                }
                
                // since 1.3.6.2
                if ($campaignRef->list_id != $campaign->list_id) {
                    CampaignOpenActionSubscriber::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                    CampaignOpenActionListField::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                    CampaignSentActionListField::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                    CampaignTemplateUrlActionSubscriber::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                    CampaignTemplateUrlActionListField::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                }
                //
                
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'campaign'  => $campaign,
            )));

            if ($collection->success) {
                $this->redirect(array('campaigns/setup', 'campaign_uid' => $campaign->campaign_uid));
            }
        }

        $listsArray      = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getListsDropDownArray());
        $segmentsArray   = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getSegmentsDropDownArray());
        $groupsArray     = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $campaign->getGroupsDropDownArray());
        $canSegmentLists = $customer->getGroupOption('lists.can_segment_lists', 'yes') == 'yes';

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Update campaign'),
            'pageHeading'       => Yii::t('campaigns', 'Update campaign'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                $campaign->name . ' ' => $this->createUrl('campaigns/update', array('campaign_uid' => $campaign_uid)),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('step-name', compact('campaign', 'listsArray', 'segmentsArray', 'groupsArray', 'campaignTempSource', 'temporarySources', 'multiListsAllowed', 'canSegmentLists'));
    }

    /**
     * Setup campaign
     * 
     * @param $campaign_uid
     * @throws CException
     * @throws CHttpException
     */
    public function actionSetup($campaign_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $options    = Yii::app()->options;
        $campaign   = $this->loadCampaignModel($campaign_uid);

        if (!$campaign->editable) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $campaign->scenario = 'step-setup';
        $default    = $campaign->list->default;
        $sameFields = array('from_name', 'from_email', 'subject', 'reply_to');

        if (!empty($default)) {
            foreach ($sameFields as $attribute) {
                if (empty($campaign->$attribute)) {
                    $campaign->$attribute = $default->$attribute;
                }
            }
        }

        // customer reference
        $customer = $campaign->list->customer;
        
        // tracking domains:
        $canSelectTrackingDomains = $customer->getGroupOption('tracking_domains.can_select_for_campaigns', 'no') == 'yes';
        
        // delivery servers for this campaign - start
        $canSelectDeliveryServers       = $customer->getGroupOption('servers.can_select_delivery_servers_for_campaign', 'no') == 'yes';
        $campaignDeliveryServersArray   = array();
        $campaignToDeliveryServers      = CampaignToDeliveryServer::model();
        if ($canSelectDeliveryServers) {
            $deliveryServers = $customer->getAvailableDeliveryServers();

            $campaignDeliveryServers = $campaignToDeliveryServers->findAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
            ));

            foreach ($campaignDeliveryServers as $srv) {
                $campaignDeliveryServersArray[] = $srv->server_id;
            }
        }
        // delivery servers for this campaign - end

        // suppression lists for this campaign - start
        $suppressionListToCampaign = CustomerSuppressionListToCampaign::model(); 
        $canSelectSuppressionLists = $customer->getGroupOption('lists.can_use_own_blacklist', 'no') == 'yes';
        $selectedSuppressionLists  = array();
        $allSuppressionLists       = array();
        if ($canSelectSuppressionLists) {
            $allSuppressionLists = CustomerSuppressionList::model()->findAllByAttributes(array(
                'customer_id' => $campaign->customer_id,
            ));
            $campaignSuppressionLists = $suppressionListToCampaign->findAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
            ));
            foreach ($campaignSuppressionLists as $suppressionList) {
                $selectedSuppressionLists[] = $suppressionList->list_id;
            }
        }
        
        // suppression lists for this campaign - end

        // attachments - start
        $canAddAttachments = $options->get('system.campaign.attachments.enabled', 'no') == 'yes';
        $attachment        = null;
        if ($canAddAttachments) {
            $attachment = new CampaignAttachment('multi-upload');
            $attachment->campaign_id = $campaign->campaign_id;
        }
        // attachments - end

        // actions upon open - start
        $openAction = new CampaignOpenActionSubscriber();
        $openAction->campaign_id = $campaign->campaign_id;
        $openAllowedActions = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $openAction->getActions());
        $openActionLists    = $campaign->getListsDropDownArray();
        foreach ($openActionLists as $list_id => $name) {
            if ($list_id == $campaign->list_id) {
                unset($openActionLists[$list_id]);
                break;
            }
        }
        $canShowOpenActions = !empty($openActionLists);
        $openActionLists    = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $openActionLists);
        $openActions        = CampaignOpenActionSubscriber::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        // actions upon open - end

        // actions upon sent - start
        $sentAction = new CampaignSentActionSubscriber();
        $sentAction->campaign_id = $campaign->campaign_id;
        $sentAllowedActions = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $sentAction->getActions());
        $sentActionLists    = $campaign->getListsDropDownArray();
        foreach ($sentActionLists as $list_id => $name) {
            if ($list_id == $campaign->list_id) {
                unset($sentActionLists[$list_id]);
                break;
            }
        }
        $canShowSentActions = !empty($sentActionLists);
        $sentActionLists    = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $sentActionLists);
        $sentActions        = CampaignSentActionSubscriber::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        // actions upon sent - end
	    
	    // 1.6.8 - webhooks for opens - start
	    $webhooksEnabled = Yii::app()->options->get('system.campaign.webhooks.enabled', 'no') == 'yes';
	    $opensWebhook    = new CampaignTrackOpenWebhook();
	    $opensWebhooks   = CampaignTrackOpenWebhook::model()->findAllByAttributes(array(
	    	'campaign_id' => $campaign->campaign_id,
	    ));
	    // webhooks for opens - end 

        // 1.5.3 - campaign extra tags - start
        $extraTag = new CampaignExtraTag();
        $extraTag->campaign_id = $campaign->campaign_id;
        $extraTags = CampaignExtraTag::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        // campaign extra tags - end
        
        // populate list custom field upon open - start
        $openListFieldAction = new CampaignOpenActionListField();
        $openListFieldAction->campaign_id = $campaign->campaign_id;
        $openListFieldAction->list_id     = $campaign->list_id;
        $openListFieldActionOptions       = $openListFieldAction->getCustomFieldsAsDropDownOptions();
        $canShowOpenListFieldActions      = !empty($openListFieldActionOptions);
        $openListFieldActionOptions       = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $openListFieldActionOptions);
        $openListFieldActions = CampaignOpenActionListField::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        // populate list custom field upon open - end

        // populate list custom field upon sent - start
        $sentListFieldAction = new CampaignSentActionListField();
        $sentListFieldAction->campaign_id = $campaign->campaign_id;
        $sentListFieldAction->list_id     = $campaign->list_id;
        $sentListFieldActionOptions       = $sentListFieldAction->getCustomFieldsAsDropDownOptions();
        $canShowSentListFieldActions      = !empty($sentListFieldActionOptions);
        $sentListFieldActionOptions       = CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $sentListFieldActionOptions);
        $sentListFieldActions = CampaignSentActionListField::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        // populate list custom field upon sent - end

        // 1.3.8.8
        $openUnopenFiltersSelected = array();
        $openUnopenFilters = CampaignFilterOpenUnopen::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));
        foreach ($openUnopenFilters as $_filter) {
            $openUnopenFiltersSelected[] = $_filter->previous_campaign_id;
        }
        $openUnopenFilter = new CampaignFilterOpenUnopen();
        $openUnopenFilter->previous_campaign_id = $openUnopenFiltersSelected;
        $openUnopenFilter->action = !empty($openUnopenFilters) ? $openUnopenFilters[0]->action : '';
        //
        
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($campaign->modelName, array()))) {
            $campaign->attributes = $attributes;
            $campaign->option->attributes = (array)$request->getPost($campaign->option->modelName, array());
            $unfiltered = Yii::app()->request->getOriginalPost($campaign->modelName, array());
            if (isset($unfiltered['subject'])) {
                $campaign->subject = CHtml::decode(strip_tags(Yii::app()->ioFilter->purify(CHtml::decode($unfiltered['subject']))));
            }
            if ($campaign->save() && $campaign->option->save()) {

                // 1.3.8.8 - open/unopen filters
                CampaignFilterOpenUnopen::model()->deleteAllByAttributes(array('campaign_id' => $campaign->campaign_id));
                if ($postAttributes = $request->getPost($openUnopenFilter->modelName, array())) {
                    $openUnopenFilter->attributes = $postAttributes;

                    if (!empty($postAttributes['previous_campaign_id']) && is_array($postAttributes['previous_campaign_id'])) {
                        foreach ($postAttributes['previous_campaign_id'] as $previous_campaign_id) {
                            $openUnopenFilterModel = new CampaignFilterOpenUnopen();
                            $openUnopenFilterModel->campaign_id = $campaign->campaign_id;
                            $openUnopenFilterModel->action = $postAttributes['action'];
                            $openUnopenFilterModel->previous_campaign_id = $previous_campaign_id;
                            $openUnopenFilterModel->save();
                        }
                    }
                }
                // 
                
                // actions upon open against subscriber
                CampaignOpenActionSubscriber::model()->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id
                ));
                if ($postAttributes = (array)$request->getPost($openAction->modelName, array())) {
                    foreach ($postAttributes as $index => $attributes) {
                        $openAct = new CampaignOpenActionSubscriber();
                        $openAct->attributes = $attributes;
                        $openAct->campaign_id = $campaign->campaign_id;
                        $openAct->save();
                    }
                }

                // actions upon sent against subscriber
                CampaignSentActionSubscriber::model()->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id
                ));
                if ($postAttributes = (array)$request->getPost($sentAction->modelName, array())) {
                    foreach ($postAttributes as $index => $attributes) {
                        $sentAct = new CampaignSentActionSubscriber();
                        $sentAct->attributes = $attributes;
                        $sentAct->campaign_id = $campaign->campaign_id;
                        $sentAct->save();
                    }
                }

                // 1.5.3 - campaign extra tags
                CampaignExtraTag::model()->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id
                ));
                if ($postAttributes = (array)$request->getPost($extraTag->modelName, array())) {
                    foreach ($postAttributes as $index => $attributes) {
                        $_extraTag = new CampaignExtraTag();
                        $_extraTag->attributes  = $attributes;
                        $_extraTag->campaign_id = $campaign->campaign_id;
                        $_extraTag->save();
                    }
                }

                // action upon open against subscriber custom fields.
                CampaignOpenActionListField::model()->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id
                ));
                if ($postAttributes = (array)$request->getPost($openListFieldAction->modelName, array())) {
                    foreach ($postAttributes as $index => $attributes) {
                        $openListFieldAct = new CampaignOpenActionListField();
                        $openListFieldAct->attributes  = $attributes;
                        $openListFieldAct->campaign_id = $campaign->campaign_id;
                        $openListFieldAct->list_id     = $campaign->list_id;
                        $openListFieldAct->save();
                    }
                }

                // action upon sent against subscriber custom fields.
                CampaignSentActionListField::model()->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id
                ));
                if ($postAttributes = (array)$request->getPost($sentListFieldAction->modelName, array())) {
                    foreach ($postAttributes as $index => $attributes) {
                        $sentListFieldAct = new CampaignSentActionListField();
                        $sentListFieldAct->attributes  = $attributes;
                        $sentListFieldAct->campaign_id = $campaign->campaign_id;
                        $sentListFieldAct->list_id     = $campaign->list_id;
                        $sentListFieldAct->save();
                    }
                }
                
                // 1.6.8
	            CampaignTrackOpenWebhook::model()->deleteAllByAttributes(array(
		            'campaign_id' => $campaign->campaign_id
	            ));
	            if ($postAttributes = (array)$request->getPost($opensWebhook->modelName, array())) {
		            foreach ($postAttributes as $index => $attributes) {
			            $openWebhookModel = new CampaignTrackOpenWebhook();
			            $openWebhookModel->attributes  = $attributes;
			            $openWebhookModel->campaign_id = $campaign->campaign_id;
			            $openWebhookModel->save();
		            }
	            }
	            //

                $campaignToDeliveryServers->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id,
                ));
                if ($canSelectDeliveryServers && ($attributes = (array)$request->getPost($campaignToDeliveryServers->modelName, array()))) {
                    foreach ($attributes as $serverId) {
                        $relation = new CampaignToDeliveryServer();
                        $relation->campaign_id = $campaign->campaign_id;
                        $relation->server_id = (int)$serverId;
                        $relation->save();
                    }
                }

                $suppressionListToCampaign->deleteAllByAttributes(array(
                    'campaign_id' => $campaign->campaign_id,
                ));
                if ($canSelectSuppressionLists && ($attributes = (array)$request->getPost($suppressionListToCampaign->modelName, array()))) {
                    foreach ($attributes as $listId) {
                        $relation = new CustomerSuppressionListToCampaign();
                        $relation->campaign_id = $campaign->campaign_id;
                        $relation->list_id     = (int)$listId;
                        $relation->save();
                    }
                }

	            // since 1.3.5.9
	            $showSuccess = true;
                
                if ($canAddAttachments && $attachments = CUploadedFile::getInstances($attachment, 'file')) {
                    $attachment->file = $attachments;
                    $attachment->validateAndSave();
                    
                    // 1.8.1
                    if ($attachment->hasErrors()) {
                    	$notify->addWarning(Yii::t('campaigns', 'Some files failed to be attached, here is why: {message}', array(
                    		'{message}' => '<br />' . $attachment->shortErrors->getAllAsString(),
	                    )));
	                    $showSuccess = false;
	                    $hasError    = true;
                    }
                }

                // since 1.3.5.9
                $emailParts  = explode('@', $campaign->from_email);
                $emailDomain = strtolower($emailParts[1]);
                $notAllowedFromDomains = CommonHelper::getArrayFromString(strtolower($options->get('system.campaign.misc.not_allowed_from_domains', '')));
                if (!empty($notAllowedFromDomains) && in_array($emailDomain, $notAllowedFromDomains)) {
                    $notify->addWarning(Yii::t('campaigns', 'You are not allowed to use "{domain}" domain in your "From email" field!', array(
                        '{domain}' => CHtml::tag('strong', array(), $emailDomain),
                    )));
                    $campaign->from_email = '';
                    $campaign->save(false);
                    $campaign->from_email = implode('@', $emailParts);
                    $showSuccess = false;
                    $hasError    = true;
                }
                //
	            
	            // since 1.6.3
	            if (empty($hasError)) {
		            $notAllowedFromPatterns = CommonHelper::getArrayFromString(strtolower($options->get('system.campaign.misc.not_allowed_from_patterns', '')));
		            if (!empty($notAllowedFromPatterns)) {
						foreach ($notAllowedFromPatterns as $notAllowedFromPattern) {
							if (!@preg_match($notAllowedFromPattern, $campaign->from_email)) {
								continue;
							}

							$notify->addWarning(Yii::t('campaigns', 'You are not allowed to use "{email}" email in your "From email" field!', array(
								'{email}' => CHtml::tag('strong', array(), $campaign->from_email),
							)));
							
							$campaign->from_email = '';
							$campaign->save(false);
							$campaign->from_email = implode('@', $emailParts);
							$showSuccess = false;
							$hasError    = true;
							break;
						}
		            }
	            }
	            //

                // since 1.3.4.7 - whether must validate sending domain - start
                if (empty($hasError) && !SendingDomain::model()->getRequirementsErrors() && $customer->getGroupOption('campaigns.must_verify_sending_domain', 'no') == 'yes') {
                    $sendingDomain = SendingDomain::model()->findVerifiedByEmail($campaign->from_email, $campaign->customer_id);
                    if (empty($sendingDomain)) {
                        $notify->addWarning(Yii::t('campaigns', 'You are required to verify your sending domain({domain}) in order to be able to send this campaign!', array(
                            '{domain}' => CHtml::tag('strong', array(), $emailDomain),
                        )));
                        $notify->addWarning(Yii::t('campaigns', 'Please click {link} to add and verify {domain} domain name. After verification, you can send your campaign.', array(
                            '{link}'   => CHtml::link(Yii::t('app', 'here'), array('sending_domains/create')),
                            '{domain}' => CHtml::tag('strong', array(), $emailDomain),
                        )));
                    }
                }
                // whether must validate sending domain - end

                if ($showSuccess) {
                    $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                }
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'campaign'  => $campaign,
            )));

            if ($collection->success) {
                $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Setup campaign'),
            'pageHeading'       => Yii::t('campaigns', 'Campaign setup'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                $campaign->name . ' ' => $this->createUrl('campaigns/update', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Setup')
            )
        ));

        $this->render('step-setup', compact(
            'campaign',
            'canSelectDeliveryServers',
            'campaignToDeliveryServers',
            'deliveryServers',
            'campaignDeliveryServersArray',
            'canAddAttachments',
            'attachment',
            'canShowOpenActions',
            'openAction',
            'openActions',
            'openAllowedActions',
            'openActionLists',
            'canShowSentActions',
            'sentAction',
            'sentActions',
            'sentAllowedActions',
            'sentActionLists',
            'webhooksEnabled',
            'opensWebhook',
            'opensWebhooks',
            'openListFieldAction',
            'openListFieldActions',
            'openListFieldActionOptions',
            'sentListFieldAction',
            'sentListFieldActions',
            'sentListFieldActionOptions',
            'canShowOpenListFieldActions',
            'canShowSentListFieldActions',
            'canSelectTrackingDomains',
            'openUnopenFilter',
            'suppressionListToCampaign',
            'canSelectSuppressionLists',
            'selectedSuppressionLists',
            'allSuppressionLists',
            'extraTag',
            'extraTags'
        ));
    }

	/**
	 * Choose or create campaign template
	 * 
	 * @param $campaign_uid
	 * @param string $do
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionTemplate($campaign_uid, $do = 'create')
    {
        $request = Yii::app()->request;
        $campaign = $this->loadCampaignModel($campaign_uid);

        if (!$campaign->editable) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        if ($do === 'select') {

            if ($template_uid = $request->getQuery('template_uid')) {
                $campaignTemplate = $campaign->template;
                if (empty($campaignTemplate)) {
                    $campaignTemplate = new CampaignTemplate();
                }
                $campaignTemplate->setScenario('copy');

                if (!empty($campaignTemplate->template_id)) {
                    CampaignTemplateUrlActionSubscriber::model()->deleteAllByAttributes(array(
                        'template_id' => $campaignTemplate->template_id
                    ));
                }

                $selectedTemplate = CustomerEmailTemplate::model()->findByAttributes(array(
                    'template_uid'  => $template_uid,
                    'customer_id'   => (int)Yii::app()->customer->getId(),
                ));

                $redirect = array('campaigns/template', 'campaign_uid' => $campaign_uid, 'do' => 'create');

                if (!empty($selectedTemplate)) {

                    // 1.4.4
                    foreach ($selectedTemplate->attributes as $key => $value) {
                        if (in_array($key, array('template_id'))) {
                            continue;
                        }
                        if ($campaignTemplate->hasAttribute($key)) {
                            $campaignTemplate->$key = $value;
                        }
                    }
                    //
                    
                    $campaignTemplate->campaign_id           = $campaign->campaign_id;
                    $campaignTemplate->customer_template_id  = $selectedTemplate->template_id;
                    $campaignTemplate->name                  = $selectedTemplate->name;
                    $campaignTemplate->content               = $selectedTemplate->content;

                    if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES && $campaignTemplate->auto_plain_text === CampaignTemplate::TEXT_YES) {
                        $campaignTemplate->plain_text = CampaignHelper::htmlToText($selectedTemplate->content);
                    }

                    $campaignTemplate->save();

                    /**
                     * We also need to create a copy of the template files.
                     * This avoids the scenario where a campaign based on a uploaded template is sent
                     * then after a while the template is deleted.
                     * In this scenario, the campaign will remain without images.
                     */

                    $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');

                    // make sure the new template has images, otherwise don't bother.
                    $filesPath = $storagePath.'/'.$selectedTemplate->template_uid;
                    if (!file_exists($filesPath) || !is_dir($filesPath)) {
                        $this->redirect($redirect);
                    }

                    // check if there's already a copy if this campaign template. if so, remove it, we don't want a folder with 1000 images.
                    $campaignFiles = $storagePath.'/cmp'.$campaign->campaign_uid;
                    if (file_exists($campaignFiles) && is_dir($campaignFiles)) {
                        FileSystemHelper::deleteDirectoryContents($campaignFiles, true, 1);
                    }

                    // copy the template folder to the campaign folder.
                    if (!FileSystemHelper::copyOnlyDirectoryContents($filesPath, $campaignFiles)) {
                        $this->redirect($redirect);
                    }

                    $search = array (
                        'frontend/assets/gallery/cmp'.$campaign->campaign_uid,
                        'frontend/assets/gallery/'.$selectedTemplate->template_uid
                    );
                    $replace = 'frontend/assets/gallery/cmp'.$campaign->campaign_uid;
                    $campaignTemplate->content = str_ireplace($search, $replace, $campaignTemplate->content);

                    if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES && $campaignTemplate->auto_plain_text === CampaignTemplate::TEXT_YES) {
                        $campaignTemplate->plain_text = CampaignHelper::htmlToText($campaignTemplate->content);
                    }

                    $campaignTemplate->save(false);
                }
                $this->redirect($redirect);
            }

            $template = new CustomerEmailTemplate('search');
            $template->unsetAttributes();

            // for filters.
            $template->attributes  = (array)$request->getQuery($template->modelName, array());
            $template->customer_id = (int)Yii::app()->customer->getId();
            
            // pass to view
            $this->data->template = $template;

            $viewFile = 'step-template-select';

        } elseif ($do === 'create' || $do === 'from-url') {

            $template = $campaign->template;
            if (empty($template)) {
                $template = new CampaignTemplate();
            }
            $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');
            $template->campaign_id = $campaign->campaign_id;
            $this->data->template  = $template;

            // 1.3.9.5
            $randomContent = new CampaignRandomContent();
            $randomContent->campaign_id = $campaign->campaign_id;
            $randomContent->fieldDecorator->onHtmlOptionsSetup = array($this, '_setRandomContentEditorOptions');
            $this->data->randomContent = $randomContent;
            
            if ($request->getQuery('prev') == 'upload' && !empty($template->template_id)) {
                CampaignTemplateUrlActionSubscriber::model()->deleteAllByAttributes(array(
                    'template_id' => $template->template_id
                ));
                CampaignTemplateUrlActionListField::model()->deleteAllByAttributes(array(
                    'template_id' => $template->template_id
                ));
                $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid, 'do' => 'create'));
            }

            if ($request->isPostRequest && ($attributes = (array)$request->getPost($template->modelName, array()))) {
                $template->attributes = $attributes;

                if (isset(Yii::app()->params['POST'][$template->modelName]['content'])) {
                    $template->content = Yii::app()->params['POST'][$template->modelName]['content'];
                } else {
                    $template->content = null;
                }

                if ($campaign->option->plain_text_email != CampaignOption::TEXT_YES) {
                    $template->only_plain_text = CampaignTemplate::TEXT_NO;
                    $template->auto_plain_text = CampaignTemplate::TEXT_NO;
                    $template->plain_text      = null;
                }

                $template->campaign_id = $campaign->campaign_id;

                // since 1.3.4.2, allow content fetched from url
                // TO DO: Add an option in backend to enable/disable this feature!
                $errors = array();
                if ($do === 'from-url' && isset($attributes['from_url'])) {
                    if (!FilterVarHelper::url($attributes['from_url'])) {
                        $errors[] = Yii::t('campaigns', 'The provided url does not seem to be valid!');
                    } else {
                        $response = AppInitHelper::simpleCurlGet($attributes['from_url']);
                        if ($response['status'] == 'error') {
                            $errors[] = $response['message'];
                        } else {
                            // do a blind search after some common html elements
                            $elements = array('<div', '<table', '<a', '<p', '<br', 'style=');
                            $found = false;
                            foreach ($elements as $elem) {
                                if (stripos($response['message'], $elem) !== false) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $errors[] = Yii::t('campaigns', 'The provided url does not seem to contain valid html!');
                            } else {
                                $template->content = $response['message'];
                            }
                        }
                    }
                }
                
                if ($template->isOnlyPlainText) {
                    $template->content    = CHtml::decode(Yii::app()->ioFilter->purify($template->plain_text));
                    $template->plain_text = $template->content;
                }
                
                $isNext = $request->getPost('is_next', 0);

                if (!empty($template->content) && !empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES && $template->auto_plain_text === CampaignTemplate::TEXT_YES && empty($template->plain_text)) {
                    $template->plain_text = CampaignHelper::htmlToText($template->content);
                }
                
                if (empty($errors) && $template->save()) {
                    Yii::app()->notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                    $redirect = array('campaigns/template', 'campaign_uid' => $campaign_uid);
                    if ($isNext) {
                        $redirect = array('campaigns/confirm', 'campaign_uid' => $campaign_uid);
                    }
                    
                    // since 1.3.4.3
                    CampaignTemplateUrlActionSubscriber::model()->deleteAllByAttributes(array(
                        'template_id' => $template->template_id
                    ));
                    if ($postAttributes = (array)$request->getPost('CampaignTemplateUrlActionSubscriber', array())) {
                        foreach ($postAttributes as $index => $attributes) {
                            $templateUrlActionSubscriber = new CampaignTemplateUrlActionSubscriber();
                            $templateUrlActionSubscriber->attributes  = $attributes;
                            $templateUrlActionSubscriber->url         = StringHelper::normalizeUrl($templateUrlActionSubscriber->url);
                            $templateUrlActionSubscriber->template_id = $template->template_id;
                            $templateUrlActionSubscriber->campaign_id = $campaign->campaign_id;
                            $templateUrlActionSubscriber->save();
                        }
                    }

	                // since 1.6.8
	                CampaignTrackUrlWebhook::model()->deleteAllByAttributes(array(
		                'campaign_id' => $campaign->campaign_id
	                ));
	                if ($postAttributes = (array)$request->getPost('CampaignTrackUrlWebhook', array())) {
		                foreach ($postAttributes as $index => $attributes) {
			                $urlWebhookModel = new CampaignTrackUrlWebhook();
			                $urlWebhookModel->attributes  = $attributes;
			                $urlWebhookModel->track_url   = StringHelper::normalizeUrl($urlWebhookModel->track_url);
			                $urlWebhookModel->campaign_id = $campaign->campaign_id;
			                $urlWebhookModel->save();
		                }
	                }
                    
                    // since 1.3.4.5
                    CampaignTemplateUrlActionListField::model()->deleteAllByAttributes(array(
                        'template_id' => $template->template_id
                    ));
                    if ($postAttributes = (array)$request->getPost('CampaignTemplateUrlActionListField', array())) {
                        foreach ($postAttributes as $index => $attributes) {
                            $templateUrlActionListField = new CampaignTemplateUrlActionListField();
                            $templateUrlActionListField->attributes  = $attributes;
                            $templateUrlActionListField->template_id = $template->template_id;
                            $templateUrlActionListField->campaign_id = $campaign->campaign_id;
                            $templateUrlActionListField->list_id     = $campaign->list_id;
                            $templateUrlActionListField->save();
                        }
                    }
                    
                    // since 1.3.9.5
                    CampaignRandomContent::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id
                    ));
                    if ($postAttributes = (array)$request->getPost('CampaignRandomContent', array())) {
                        foreach ($postAttributes as $index => $attributes) {
                            $rndContent = new CampaignRandomContent();
                            $rndContent->attributes  = $attributes;
                            $rndContent->campaign_id = $campaign->campaign_id;
                            $rndContent->content     = Yii::app()->params['POST']['CampaignRandomContent'][$index]['content'];
                            try {
                                $rndContent->save();
                            } catch (Exception $e) {
                                
                            }
                        }
                    }
                    
                } else {
                    Yii::app()->notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                    if (!empty($errors)) {
                        Yii::app()->notify->addError($errors);
                    }
                }

                Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                    'controller'=> $this,
                    'success'   => Yii::app()->notify->hasSuccess,
                    'do'        => $do,
                    'campaign'  => $campaign,
                    'template'  => $template,
                )));
                
                if ($collection->success) {
                    if (!empty($redirect)) {
                        $this->redirect($redirect);
                    }
                }
            }

            // since 1.3.4.3
            if ($campaign->option->url_tracking === CampaignOption::TEXT_YES && !empty($template->content)) {
                $contentUrls = $template->getContentUrls();
                if (!empty($contentUrls)) {
                    $templateListsArray = $campaign->getListsDropDownArray();
                    foreach ($templateListsArray as $list_id => $name) {
                        if ($list_id == $campaign->list_id) {
                            unset($templateListsArray[$list_id]);
                            break;
                        }
                    }
                    
                    $templateUrlActionSubscriber = new CampaignTemplateUrlActionSubscriber();
                    $templateUrlActionSubscriber->campaign_id = $campaign->campaign_id;

                    // 1.6.8
	                $webhooksEnabled = Yii::app()->options->get('system.campaign.webhooks.enabled', 'no') == 'yes';
	                $urlWebhook      = new CampaignTrackUrlWebhook();
	                $urlWebhook->campaign_id = $campaign->campaign_id;
	                
                    $this->setData(array(
                        'templateListsArray'                => !empty($templateListsArray) ? CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateListsArray) : array(),
                        'templateContentUrls'               => CMap::mergeArray(array('' => Yii::t('app', 'Choose')), array_combine($contentUrls, $contentUrls)),
                        'clickAllowedActions'               => CMap::mergeArray(array('' => Yii::t('app', 'Choose')), $templateUrlActionSubscriber->getActions()),
                        'templateUrlActionSubscriber'       => $templateUrlActionSubscriber,
                        'templateUrlActionSubscriberModels' => $templateUrlActionSubscriber->findAllByAttributes(array('template_id' => $template->template_id)),
	                    'webhooksEnabled'                   => $webhooksEnabled,
                        'urlWebhook'                        => $urlWebhook,
	                    'urlWebhookModels'                  => $urlWebhook->findAllByAttributes(array('campaign_id' => $campaign->campaign_id)),
                    ));

                    // since 1.3.4.5
                    $templateUrlActionListField = new CampaignTemplateUrlActionListField();
                    $templateUrlActionListField->campaign_id = $campaign->campaign_id;
                    $templateUrlActionListField->list_id     = $campaign->list_id;
                    $this->setData(array(
                        'templateUrlActionListField'  => $templateUrlActionListField,
                        'templateUrlActionListFields' => $templateUrlActionListField->findAllByAttributes(array('template_id' => $template->template_id)),
                    ));
                }
            }

            $this->data->templateUp = new CampaignEmailTemplateUpload('upload');
            $viewFile = 'step-template-create';

        } elseif ($do == 'upload') {

            if ($request->isPostRequest && $request->getPost('is_next', 0)) {
                $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
            }

            // 1.3.9.5
            $randomContent = new CampaignRandomContent();
            $randomContent->campaign_id = $campaign->campaign_id;
            $randomContent->fieldDecorator->onHtmlOptionsSetup = array($this, '_setRandomContentEditorOptions');
            $this->data->randomContent = $randomContent;

            $template = $campaign->template;
            if (empty($template)) {
                $template = new CampaignTemplate();
            }
            $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');
            $template->campaign_id = $campaign->campaign_id;

            $templateUp = new CampaignEmailTemplateUpload('upload');
            $templateUp->customer_id = (int)Yii::app()->customer->getId();
            $templateUp->campaign    = $campaign;

            $redirect = array('campaigns/template', 'campaign_uid' => $campaign_uid, 'do' => 'create', 'prev' => 'upload');

            if ($request->isPostRequest && ($attributes = (array)$request->getPost($templateUp->modelName, array()))) {
                $templateUp->attributes = $attributes;
                $templateUp->archive = CUploadedFile::getInstance($templateUp, 'archive');
                if (!$templateUp->validate() || !$templateUp->uploader->handleUpload()) {
                    Yii::app()->notify->addError($templateUp->shortErrors->getAllAsString());
                } else {
                    $template->content = $templateUp->content;
                    $template->name    = basename($templateUp->archive->name, '.zip');

                    if (!empty($campaign->option) && $campaign->option->plain_text_email == CampaignOption::TEXT_YES && $templateUp->auto_plain_text === CampaignTemplate::TEXT_YES && empty($templateUp->plain_text)) {
                        $template->plain_text = CampaignHelper::htmlToText($templateUp->content);
                    }

                    if ($template->save()) {
                        Yii::app()->notify->addSuccess(Yii::t('app', 'Your file has been successfully uploaded!'));
                    } else {
                        Yii::app()->notify->addError($template->shortErrors->getAllAsString());
                    }
                }

                Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                    'controller'=> $this,
                    'success'   => Yii::app()->notify->hasSuccess,
                    'do'        => $do,
                    'campaign'  => $campaign,
                    'template'  => $template,
                    'templateUp'=> $templateUp,
                )));

                if ($collection->success) {
                    $this->redirect($redirect);
                }
            }

            $this->data->templateUp = $templateUp;
            $this->data->template   = $template;
            $viewFile = 'step-template-create';

        } else {

            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid, 'do' => 'create'));

        }

        // since 1.3.4.2, add a warning if the campaign is paused and template changed
        if ($campaign->getIsPaused()) {
            Yii::app()->notify->addWarning(Yii::t('campaigns', 'This campaign is paused, please have this in mind if you are going to change the template, it will affect subscribers that already received the current template!'));
        }
        
        // 1.3.7.3
        $this->setData(array(
            'lastTestEmails'    => Yii::app()->session->get('campaignLastTestEmails'),
            'lastTestFromEmail' => Yii::app()->session->get('campaignLastTestFrom'),
        ));
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaign template'),
            'pageHeading'       => Yii::t('campaigns', 'Campaign template'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                $campaign->name . ' ' => $this->createUrl('campaigns/update', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Template')
            )
        ));

        $this->render($viewFile, compact('campaign'));
    }

	/**
	 * Confirm the campaign and schedule it for sending
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionConfirm($campaign_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $campaign   = $this->loadCampaignModel($campaign_uid);
        
        if (!$campaign->editable) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        $customer = Yii::app()->customer->getModel();
        $campaign->scenario = 'step-confirm';

        if ($campaign->isAutoresponder) {
            $campaign->option->setScenario('step-confirm-ar');
        }

        $segmentSubscribers = 0;
        $listSubscribers = 0;
        $hasError = false;

        if (empty($campaign->template->content)) {
            $hasError = true;
            $notify->addError(Yii::t('campaigns', 'Missing campaign template!'));
        }

        // since 1.3.4.7 - whether must validate sending domain - start
        if (!SendingDomain::model()->getRequirementsErrors() && $customer->getGroupOption('campaigns.must_verify_sending_domain', 'no') == 'yes') {
            $sendingDomain = SendingDomain::model()->findVerifiedByEmail($campaign->from_email, $campaign->customer_id);
            if (empty($sendingDomain)) {
                $emailParts = explode('@', $campaign->from_email);
                $domain = $emailParts[1];
                $notify->addError(Yii::t('campaigns', 'You are required to verify your sending domain({domain}) in order to be able to send this campaign!', array(
                    '{domain}' => CHtml::tag('strong', array(), $domain),
                )));
                $notify->addError(Yii::t('campaigns', 'Please click {link} to add and verify {domain} domain name. After verification, you can send your campaign.', array(
                    '{link}'   => CHtml::link(Yii::t('app', 'here'), array('sending_domains/create')),
                    '{domain}' => CHtml::tag('strong', array(), $domain),
                )));
                $hasError = true;
            }
        }
        // whether must validate sending domain - end
        
        if (!$hasError && $request->isPostRequest) {
            $campaign->attributes = (array)$request->getPost($campaign->modelName, array());
            $campaign->status     = Campaign::STATUS_PENDING_SENDING;

            // since 1.3.4.2, we allow paused campaigns to be edited.
            if ($campaign->getIsPaused()) {
                $campaign->status = Campaign::STATUS_PAUSED;
            }

            // 1.4.5
            $requireApproval = $customer->getGroupOption('campaigns.require_approval', 'no') == 'yes';
            if ($requireApproval) {
                $campaign->markPendingApprove();
            }

            $transaction = Yii::app()->getDb()->beginTransaction();
            $redirect    = array('campaigns/' . $campaign->type);
            $saved       = false;

            if (!empty($campaign->temporarySources)) {
                $redirect = array('campaigns/merge_lists', 'campaign_uid' => $campaign_uid);
                $campaign->status = Campaign::STATUS_DRAFT;
            }

            if ($campaign->save()) {
                $saved = true;
                if ($campaign->isAutoresponder || $campaign->isRegular) {
                    $campaign->option->attributes = (array)$request->getPost($campaign->option->modelName, array());
                    if (!$campaign->option->save()) {
                        $saved = false;
                        $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                    }
                }

                if ($saved) {
                    if (!empty($campaign->temporarySources)) {
                        $notify->addSuccess(Yii::t('campaigns', 'Please wait while all selected lists for this campaigns are merged. You will be redirected back once everything is done.'));
                    } else {
                        if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                            $logAction->campaignScheduled($campaign);
                        }

                        // since 1.3.5.9
                        $hasAddedSuccessMessage = false;
                        if (($sbw = $campaign->subjectBlacklistWords) || ($cbw = $campaign->contentBlacklistWords)) {
                            $hasAddedSuccessMessage = true;
                            $reason = array();
                            if (!empty($sbw)) {
                                $reason[] = 'Contains blacklisted words in campaign subject!';
                            }
                            if (!empty($cbw)) {
                                $reason[] = 'Contains blacklisted words in campaign body!';
                            }
                            $campaign->block($reason);

                            $notify->addSuccess(Yii::t('campaigns', 'Your campaign({type}) named "{campaignName}" has been successfully saved but it will be blocked from sending until it is reviewed by one of our administrators!', array(
                                '{campaignName}' => $campaign->name,
                                '{type}'         => Yii::t('campaigns', $campaign->type),
                            )));
                        }
                        //

                        if (!$hasAddedSuccessMessage) {
                            $message = Yii::t('campaigns', 'Your campaign({type}) named "{campaignName}" has been successfully saved and will start sending at {sendDateTime}!', array(
                                '{campaignName}'    => $campaign->name,
                                '{sendDateTime}'    => $campaign->getSendAt(),
                                '{type}'            => Yii::t('campaigns', $campaign->type),
                            ));
                            if ($requireApproval) {
                                $message = Yii::t('campaigns', 'Your campaign({type}) named "{campaignName}" has been successfully saved and will start sending after it will be approved, no early than {sendDateTime}!', array(
                                    '{campaignName}'    => $campaign->name,
                                    '{sendDateTime}'    => $campaign->getSendAt(),
                                    '{type}'            => Yii::t('campaigns', $campaign->type),
                                ));
                            }
                            $notify->addSuccess($message);
                        }
                    }
                }
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }

            if ($saved) {
                $transaction->commit();
            } else {
                $transaction->rollback();
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'campaign'  => $campaign,
            )));

            if ($collection->success) {
                if (!empty($redirect)) {
                    $this->redirect($redirect);
                }
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaign overview'),
            'pageHeading'       => Yii::t('campaigns', 'Campaign confirmation'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                CHtml::encode($campaign->name) . ' ' => $this->createUrl('campaigns/update', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Confirmation')
            )
        ));

        $this->render('step-confirm', compact('campaign', 'listSubscribers', 'segmentSubscribers'));
    }

	/**
	 * Merge multiple lists into one for this campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @return BaseController|mixed|void
	 * @throws CDbException
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionMerge_lists($campaign_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $campaign   = $this->loadCampaignModel($campaign_uid);
        $customer   = Yii::app()->customer->getModel();

        if ($customer->getGroupOption('campaigns.send_to_multiple_lists', 'no') != 'yes') {
            if ($request->isAjaxRequest) {
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'Your don\'t have enough priviledges to access this feature!'),
                ));
            }
            $notify->addError(Yii::t('campaigns', 'Your don\'t have enough priviledges to access this feature!'));
            $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
        }

        if (empty($campaign->temporarySources)) {
            if ($request->isAjaxRequest) {
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'This campaign does not support sending to multiple lists!'),
                ));
            }
            $notify->addError(Yii::t('campaigns', 'This campaign does not support sending to multiple lists!'));
            $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
        }
        
        $mutex    = Yii::app()->mutex;
        $lockName = 'mergeListsFor:' . $campaign_uid . ':' . date('Y-m-d H:00:00');
        if (!$mutex->acquire($lockName, 30)) {
            if ($request->isAjaxRequest) {
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'Unable to acquire lock!'),
                ));
            }
            $notify->addError(Yii::t('campaigns', 'Unable to acquire lock!'));
            $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
        }

        $listId           = (int)$request->getPost('list_id', $campaign->list_id);
        $segmentId        = (int)$request->getPost('segment_id', $campaign->segment_id);
        $sourceId         = (int)$request->getPost('source_id');
        $clid             = (int)$request->getPost('clid');
        $processedTotal   = (int)$request->getPost('processed_total', 0);
        $processedSuccess = (int)$request->getPost('processed_success', 0);
        $processedError   = (int)$request->getPost('processed_error', 0);
        $progressText     = Yii::t('campaigns', 'The merging process is running, please wait...');
        $finished         = false;

        if ($memoryLimit = $customer->getGroupOption('lists.copy_subscribers_memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', $listId);
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        $fromList = Lists::model()->find($criteria);

        if (empty($fromList)) {
            $mutex->release($lockName);
            if ($request->isAjaxRequest) {
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'Unable to load the list!'),
                ));
            }
            $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
        }

        $fromSegment = null;
        if (!empty($segmentId)) {
            $fromSegment = ListSegment::model()->findByAttributes(array(
                'list_id'    => $fromList->list_id,
                'segment_id' => $segmentId,
            ));

            if (empty($fromSegment)) {
                $mutex->release($lockName);
                if ($request->isAjaxRequest) {
                    return $this->renderJson(array(
                        'finished'      => true,
                        'progress_text' => Yii::t('campaigns', 'Unable to load the segment!'),
                    ));
                }
                $this->redirect(array('campaigns/confirm', 'campaign_uid' => $campaign_uid));
            }
        }

        if (!empty($fromSegment)) {
            $count = $fromSegment->countSubscribers();
        } else {
            $count = $fromList->confirmedSubscribersCount;
        }

        $limit  = (int)$customer->getGroupOption('lists.copy_subscribers_at_once', 100);
        $pages  = $count <= $limit ? 1 : ceil($count / $limit);
        $page   = (int)$request->getPost('page', 1);
        $page   = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $limit;

        $attributes = array(
            'total'             => $count,
            'processed_total'   => $processedTotal,
            'processed_success' => $processedSuccess,
            'processed_error'   => $processedError,
            'percentage'        => 0,
            'progress_text'     => Yii::t('campaigns', 'The merging process is starting, please wait...'),
            'post_url'          => $this->createUrl('campaigns/merge_lists', array('campaign_uid' => $campaign_uid)),
            'list_id'           => (int)$listId,
            'segment_id'        => (int)$segmentId,
            'source_id'         => (int)$sourceId,
            'clid'              => (int)$clid,
            'page'              => (int)$page,
        );

        $jsonAttributes = CJSON::encode($attributes);

        if (!$request->isAjaxRequest) {
	        $mutex->release($lockName);
            $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('campaign-lists-merge.js')));
            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('campaigns', 'Merge lists'),
                'pageHeading'       => Yii::t('campaigns', 'Merge lists'),
                'pageBreadcrumbs'   => array(
                    Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                    CHtml::encode($campaign->name) . ' ' => $this->createUrl('campaigns/update', array('campaign_uid' => $campaign_uid)),
                    Yii::t('campaigns', 'Merge lists')
                )
            ));
            return $this->render('merge-lists', compact('campaign', 'jsonAttributes'));
        }

        if (empty($clid)) {
            if (!($list = $fromList->copy())) {
                $mutex->release($lockName);
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'Unable to copy the campaign initial list.'),
                ));
            }
            
            $name = $list->name;
            if (!empty($fromSegment)) {
            	$name .= '/' . $fromSegment->name;
            }
            $listName = array('MERGED - ' . $name);
            
            foreach ($campaign->temporarySources as $source) {
                $listName[] = empty($source->segment_id) ? $source->list->name : $source->list->name . '/' . $source->segment->name;
            }
            $list->name   = implode(', ', $listName);
            $list->name   = StringHelper::truncateLength($list->name, 255);
            $list->merged = Lists::TEXT_YES;
            $list->save(false);
            $clid = $list->list_id;
            $attributes['clid'] = (int)$clid;

            // since 1.3.5.4, make sure we unsubscribe from all lists.
            $sourceLists = array($campaign->list_id);
            foreach ($campaign->temporarySources as $source) {
                $sourceLists[] = $source->list_id;
            }
            foreach ($sourceLists as $sourceListID) {
                $action = new ListSubscriberAction();
                $action->source_list_id = $list->list_id;
                $action->source_action  = ListSubscriberAction::ACTION_UNSUBSCRIBE;
                $action->target_list_id = $sourceListID;
                $action->target_action  = ListSubscriberAction::ACTION_UNSUBSCRIBE;
                $action->save(false);
            }
        }

        if (empty($list)) {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$clid);
            $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
            $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
            $list = Lists::model()->find($criteria);

            if (empty($list)) {
                $mutex->release($lockName);
                return $this->renderJson(array(
                    'finished'      => true,
                    'progress_text' => Yii::t('campaigns', 'Unable to copy the campaign initial list.'),
                ));
            }
        }

        if (!empty($fromSegment)) {
            $criteria = new CDbCriteria;
            $criteria->select = 't.*';
            $subscribers = $fromSegment->findSubscribers($offset, $limit, $criteria);
        } else {
            $criteria = new CDbCriteria();
            $criteria->compare('list_id', (int)$listId);
            $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);
            $criteria->limit  = $limit;
            $criteria->offset = $offset;
            $subscribers = ListSubscriber::model()->findAll($criteria);
        }

        if (empty($subscribers)) {
            if (!empty($campaign->temporarySources)) {
                $sources = $campaign->temporarySources;
                foreach ($sources as $index => $source) {
                    if ($source->source_id == $sourceId) {
                        $source->delete();
                        unset($sources[$index]);
                        break;
                    }
                }
                if (!empty($sources)) {
                    $mutex->release($lockName);
                    $source  = array_shift($sources);
                    return $this->renderJson(array_merge($attributes, array(
                        'total'             => 0,
                        'processed_total'   => 0,
                        'processed_success' => 0,
                        'processed_error'   => 0,
                        'percentage'        => 0,
                        'page'              => 1,
                        'reset_counters'    => true,
                        'progress_text'     => Yii::t('campaigns', 'Now merging the list/segment "{sourceName}"', array('{sourceName}' => $source->name)),
                        'list_id'           => (int)$source->list_id,
                        'segment_id'        => (int)$source->segment_id,
                        'source_id'         => (int)$source->source_id,
                        'clid'              => (int)$clid,
                    )));
                }
            }

            // update the list id for custom fields action so that it properly loads the custom fields.
            $openActionListFields = CampaignOpenActionListField::model()->findAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
            ));
            if (!empty($openActionListFields)) {
                foreach ($openActionListFields as $openActionListField) {
                    $theOtherListField = ListField::model()->findByAttributes(array(
                        'list_id' => $list->list_id,
                        'tag'     => $openActionListField->field->tag
                    ));
                    if (empty($theOtherListField)) {
                        continue;
                    }
                    $openActionListField->field_id = $theOtherListField->field_id;
                    $openActionListField->list_id  = $list->list_id;
                    $openActionListField->save(false);
                }
            }

            // update the list id for custom fields action so that it properly loads the custom fields.
            $sentActionListFields = CampaignSentActionListField::model()->findAllByAttributes(array(
                'campaign_id' => $campaign->campaign_id,
            ));
            if (!empty($sentActionListFields)) {
                foreach ($sentActionListFields as $sentActionListField) {
                    $theOtherListField = ListField::model()->findByAttributes(array(
                        'list_id' => $list->list_id,
                        'tag'     => $sentActionListField->field->tag
                    ));
                    if (empty($theOtherListField)) {
                        continue;
                    }
                    $sentActionListField->field_id = $theOtherListField->field_id;
                    $sentActionListField->list_id  = $list->list_id;
                    $sentActionListField->save(false);
                }
            }

            // delete, just in case...
            CampaignTemporarySource::model()->deleteAllByAttributes(array(
                'campaign_id' => (int)$campaign->campaign_id,
            ));
            
            $requireApproval      = $customer->getGroupOption('campaigns.require_approval', 'no') == 'yes';
            $campaign->segment_id = null;
            $campaign->list_id    = $list->list_id;
            $campaign->status     = Campaign::STATUS_PENDING_SENDING;
            
            // 1.4.5
            if ($requireApproval) {
                $campaign->markPendingApprove();
            }
            
            $campaign->save(false);
            
            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                $logAction->campaignScheduled($campaign);
            }
            
            $message = Yii::t('campaigns', 'Your campaign({type}) named "{campaignName}" has been successfully saved and will start sending at {sendDateTime}!', array(
                '{campaignName}'    => $campaign->name,
                '{sendDateTime}'    => $campaign->getSendAt(),
                '{type}'            => Yii::t('campaigns', $campaign->type),
            ));
            
            if ($requireApproval) {
                $message = Yii::t('campaigns', 'Your campaign({type}) named "{campaignName}" has been successfully saved and will start sending after it will be approved, no early than {sendDateTime}!', array(
                    '{campaignName}'    => $campaign->name,
                    '{sendDateTime}'    => $campaign->getSendAt(),
                    '{type}'            => Yii::t('campaigns', $campaign->type),
                ));
            }
            $notify->addSuccess($message);
            //

            $mutex->release($lockName);
            return $this->renderJson(array(
                'finished'      => true,
                'progress_text' => Yii::t('campaigns', 'The merging process is done, your merged list for this campaign is {list}. Please wait to be redirected...', array('{list}' => $list->name)),
                'redirect'      => $this->createUrl('campaigns/' . $campaign->type),
                'timeout'       => 5000,
            ));
        }

        $transaction = Yii::app()->getDb()->beginTransaction();

        try {
            foreach ($subscribers as $subscriber) {
                $processedTotal++;
                if ($newSubscriber = $subscriber->copyToList($list->list_id, false)) {
                    $processedSuccess++;
                } else {
                    $processedError++;
                }
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
        }

        $mutex->release($lockName);
        $percentage = round((($processedTotal / $count) * 100), 2);

        return $this->renderJson(array_merge($attributes, array(
            'processed_total'   => $processedTotal,
            'processed_success' => $processedSuccess,
            'processed_error'   => $processedError,
            'percentage'        => $percentage,
            'page'              => $page + 1,
            'progress_text'     => $progressText,
            'finished'          => $finished,
            'clid'              => (int)$clid,
        )));
    }

	/**
	 * Test the campaign email template by sending it to desired email addressed
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 * @throws Throwable
	 */
    public function actionTest($campaign_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $campaign   = $this->loadCampaignModel($campaign_uid);
        $template   = $campaign->template;

        if ($campaign->isPendingDelete) {
            $this->redirect(array('campaigns/' . $campaign->type));
        }

        if (!$campaign->editable) {
            $this->redirect(array('campaigns/overview', 'campaign_uid' => $campaign->campaign_uid));
        }

        if (!$request->getPost('email')) {
            $notify->addError(Yii::t('campaigns', 'Please specify the email address to where we should send the test email.'));
            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid));
        }

        $emails = explode(',', $request->getPost('email'));
        $emails = array_map('trim', $emails);
        $emails = array_unique($emails);
        $emails = array_slice($emails, 0, 10);
        
        $dsParams = array('useFor' => array(DeliveryServer::USE_FOR_EMAIL_TESTS, DeliveryServer::USE_FOR_CAMPAIGNS));
        $server = DeliveryServer::pickServer(0, $campaign, $dsParams);
        if (empty($server)) {
            $notify->addError(Yii::t('app', 'Email delivery is temporary disabled.'));
            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid));
        }

        foreach ($emails as $index => $email) {
            if (!FilterVarHelper::email($email)) {
                $notify->addError(Yii::t('email_templates',  'The email address {email} does not seem to be valid!', array('{email}' => CHtml::encode($email))));
                unset($emails[$index]);
                continue;
            }
        }

        if (empty($emails)) {
            $notify->addError(Yii::t('campaigns', 'Cannot send using provided email address(es)!'));
            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid));
        }

        Yii::app()->session->add('campaignLastTestEmails', $request->getPost('email'));
        Yii::app()->session->add('campaignLastTestFromEmail', $request->getPost('from_email'));

        // 1.4.4
        $subscribers = array();
        foreach ($emails as $email) {
            if (array_key_exists($email, $subscribers)) {
                continue;
            }
            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id' => $campaign->list->list_id,
                'email'   => $email,
                'status'  => ListSubscriber::STATUS_CONFIRMED,
            ));
            if (empty($subscriber)) {
                $subscriber = ListSubscriber::model()->findByAttributes(array(
                    'list_id' => $campaign->list->list_id,
                    'status'  => ListSubscriber::STATUS_CONFIRMED,
                ));
            }
            $subscribers[$email] = $subscriber;
        }
        //
        
        foreach ($emails as $email) {

            $subscriber      = !empty($subscribers[$email]) ? $subscribers[$email] : null;
            $fromEmailCustom = null;
            $fromNameCustom  = null;
            $replyToCustom   = null;
            
            $plainTextContent = $template->plain_text;
            $emailSubject     = $campaign->subject;
            $onlyPlainText    = !empty($template->only_plain_text) && $template->only_plain_text === CampaignTemplate::TEXT_YES;
            $emailContent     = !$onlyPlainText ? $template->content : $plainTextContent;
            $embedImages      = array();
	        
            if (!$onlyPlainText && $server->canEmbedImages && !empty($campaign->option) && !empty($campaign->option->embed_images) && $campaign->option->embed_images == CampaignOption::TEXT_YES) {
                list($emailContent, $embedImages) = CampaignHelper::embedContentImages($emailContent, $campaign);
            }
            
            if (!empty($subscriber)) {

                // since 1.3.5.9
                // really blind check to see if it contains a tag
                if (strpos($campaign->from_email, '[') !== false || strpos($campaign->from_name, '[') !== false || strpos($campaign->reply_to, '[') !== false) {
                    if (strpos($campaign->from_email, '[') !== false) {
	                    $searchReplace   = CampaignHelper::getCommonTagsSearchReplace($campaign->from_email, $campaign, $subscriber);
                        $fromEmailCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_email);
                        if (!FilterVarHelper::email($fromEmailCustom)) {
                            $fromEmailCustom = null;
	                        $campaign->from_email = $server->from_email;
                        }
                    }
                    if (strpos($campaign->from_name, '[') !== false) {
	                    $searchReplace  = CampaignHelper::getCommonTagsSearchReplace($campaign->from_name, $campaign, $subscriber);
                        $fromNameCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_name);
                    }
                    if (strpos($campaign->reply_to, '[') !== false) {
	                    $searchReplace = CampaignHelper::getCommonTagsSearchReplace($campaign->reply_to, $campaign, $subscriber);
                        $replyToCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->reply_to);
                        if (!FilterVarHelper::email($replyToCustom)) {
                            $replyToCustom = null;
	                        $campaign->reply_to = $server->from_email;
                        }
                    }
                }
                //
                
                if (!$onlyPlainText && !empty($campaign->option) && !empty($campaign->option->preheader)) {
                    $emailContent = CampaignHelper::injectPreheader($emailContent, $campaign->option->preheader, $campaign);
                }

                if (!$onlyPlainText && CampaignHelper::contentHasXmlFeed($emailContent)) {
                    $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, false, null, $server);
                }

                if (!$onlyPlainText && CampaignHelper::contentHasJsonFeed($emailContent)) {
                    $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, false, null, $server);
                }

	            // 1.5.3
	            if (!$onlyPlainText && CampaignHelper::hasRemoteContentTag($emailContent)) {
		            $emailContent = CampaignHelper::fetchContentForRemoteContentTag($emailContent, $campaign, $subscriber);
	            }
	            //

                $emailData  = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, false, $server);
                list(, $_emailSubject, $emailContent) = $emailData;
	            
                // since 1.3.5.3
                if (CampaignHelper::contentHasXmlFeed($_emailSubject)) {
                    $_emailSubject = CampaignXmlFeedParser::parseContent($_emailSubject, $campaign, $subscriber, false, $emailSubject, $server);
                }

                if (CampaignHelper::contentHasJsonFeed($_emailSubject)) {
                    $_emailSubject = CampaignJsonFeedParser::parseContent($_emailSubject, $campaign, $subscriber, false, $emailSubject, $server);
                }

	            // 1.5.3
	            if (CampaignHelper::hasRemoteContentTag($_emailSubject)) {
		            $_emailSubject = CampaignHelper::fetchContentForRemoteContentTag($_emailSubject, $campaign, $subscriber);
	            }
	            //
	            
                if (!empty($_emailSubject)) {
                    $emailSubject = $_emailSubject;
                }
            }

            if (empty($emailSubject)) {
                $emailSubject   = '['. strtoupper(Yii::t('app', 'Test')) .'] ' . $campaign->name;
            }

            if ($onlyPlainText) {
                $emailContent = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailContent);
            }

            $customer = Yii::app()->customer->getModel();
            $fromName = !empty($fromNameCustom) ? $fromNameCustom : $campaign->from_name;

            if (empty($fromName)) {
                $fromName = $customer->getFullName();
                if (!empty($customer->company)) {
                    $fromName = $customer->company->name;
                }
                if (empty($fromName)) {
                    $fromName = $customer->email;
                }
            }

            $fromEmail = $request->getPost('from_email');
            if (!empty($fromEmail) && !FilterVarHelper::email($fromEmail)) {
                $fromEmail = null;
            }

            if (empty($fromEmail) && !empty($fromEmailCustom)) {
                $fromEmail = $fromEmailCustom;
            }

            if (CampaignHelper::isTemplateEngineEnabled()) {
                if (!$onlyPlainText && !empty($emailContent)) {
                    $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailContent, $campaign, $subscriber, $server);
                    $emailContent = CampaignHelper::parseByTemplateEngine($emailContent, $searchReplace);
                }
                if (!empty($emailSubject)) {
                    $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailSubject, $campaign, $subscriber, $server);
                    $emailSubject  = CampaignHelper::parseByTemplateEngine($emailSubject, $searchReplace);
                }
                if (!empty($plainTextContent)) {
                    $searchReplace   = CampaignHelper::getCommonTagsSearchReplace($plainTextContent, $campaign, $subscriber, $server);
                    $plainTextContent = CampaignHelper::parseByTemplateEngine($plainTextContent, $searchReplace);
                }
            }
            
            $params = array(
                'to'            => $email,
                'fromName'      => $fromName,
                'subject'       => $emailSubject,
                'body'          => $onlyPlainText ? null : $emailContent,
                'embedImages'   => $embedImages,
                'plainText'     => $plainTextContent,
                'onlyPlainText' => $onlyPlainText,

                // since 1.3.5.9
                'fromEmailCustom' => $fromEmailCustom,
                'fromNameCustom'  => $fromNameCustom,
                'replyToCustom'   => $replyToCustom,
            );

            if ($fromEmail) {
                $params['from'] = array($fromEmail => $fromName);
            }

            $serverLog = null;
            $sent = false;
            for ($i = 0; $i < 3; ++$i) {
                if ($sent = $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN_TEST)->setDeliveryObject($campaign)->sendEmail($params)) {
                    break;
                }
                $serverLog = $server->getMailer()->getLog();
                sleep(1);
                if (!($server = DeliveryServer::pickServer($server->server_id, $campaign, $dsParams))) {
                    break;
                }
            }

            if (!$sent) {
                $notify->addError(Yii::t('campaigns', 'Unable to send the test email to {email}!', array(
                    '{email}' => CHtml::encode($email),
                )) . (!empty($serverLog) ? sprintf(' (%s)', $serverLog) : ''));
            } else {
                $notify->addSuccess(Yii::t('campaigns', 'Test email successfully sent to {email}!', array(
                    '{email}' => CHtml::encode($email),
                )));
            }
        }

        $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign_uid));
    }

	/**
	 * List available list segments when choosing a list for a campaign
	 * 
	 * @param $list_id
	 *
	 * @return BaseController
	 */
    public function actionList_segments($list_id)
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            $this->redirect(array('campaigns/index'));
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$list_id);
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));

        $list = Lists::model()->find($criteria);
        if (empty($list)){
            return $this->renderJson(array('segments' => array()));
        }

        $campaign = new Campaign();
        $campaign->list_id = $list->list_id;
        $segments = $campaign->getSegmentsDropDownArray();

        $json = array();
        $json[] = array(
            'segment_id' => '',
            'name'       => Yii::t('app', 'Choose')
        );

        foreach ($segments as $segment_id => $name) {
            $json[] = array(
                'segment_id' => $segment_id,
                'name'       => CHtml::encode($name)
            );
        }

        return $this->renderJson(array('segments' => $json));
    }

	/**
	 * Copy campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @return BaseController
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionCopy($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $list     = $campaign->list;
        $customer = $list->customer;
        $canCopy  = true;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        if (($maxCampaigns = (int)$customer->getGroupOption('campaigns.max_campaigns', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
            $campaignsCount = Campaign::model()->count($criteria);
            if ($campaignsCount >= $maxCampaigns) {
                $notify->addWarning(Yii::t('lists', 'You have reached the maximum number of allowed campaigns.'));
                $canCopy = false;
            }
        }

        $copied = false;
        if ($canCopy) {
            $copied = $campaign->copy();
        }

        if ($copied) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully copied!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }

        return $this->renderJson(array(
            'next' => !empty($copied) ? $this->createUrl('campaigns/update', array('campaign_uid' => $copied->campaign_uid)) : '',
        ));
    }

	/**
	 * Delete campaign, will remove all campaign related data
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CDbException
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionDelete($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->removable) {

            $campaign->delete();

            if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                $logAction->campaignDeleted($campaign);
            }
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('campaigns/' . $campaign->type));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $campaign,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

	/**
	 * Allows to pause/unpause the sending of a campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionPause_unpause($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        $campaign->pauseUnpause();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to resume sending of a stuck campaign
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionResume_sending($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if ($campaign->isProcessing) {
            $campaign->saveStatus(Campaign::STATUS_SENDING);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Allows to mark a campaign as sent
	 * 
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionMarksent($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $campaign->markAsSent();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign was successfully changed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * Run a bulk action against the campaigns
	 * 
	 * @param string $type
	 *
	 * @throws CDbException
	 * @throws CException
	 * @throws Throwable
	 */
    public function actionBulk_action($type = '')
    {
        // 1.4.5
        set_time_limit(0);
        ini_set('memory_limit', -1);
        
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $action = $request->getPost('bulk_action');
        $items  = array_unique((array)$request->getPost('bulk_item', array()));
        
        $returnRoute = array('campaigns/index');
        $campaign    = new Campaign();
        if (in_array($type, $campaign->getTypesList())) {
            $returnRoute = array('campaigns/' . $type);
        }
        
        if ($action == Campaign::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                if (!$campaign->removable) {
                    continue;
                }
                $campaign->delete();
                $affected++;
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->campaignDeleted($campaign);
                }
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        } elseif ($action == Campaign::BULK_ACTION_COPY && count($items)) {
            $customer = Yii::app()->customer->getModel();
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                if (($maxCampaigns = (int)$customer->getGroupOption('campaigns.max_campaigns', -1)) > -1) {
                    $criteria = new CDbCriteria();
                    $criteria->compare('customer_id', (int)$customer->customer_id);
                    $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
                    $campaignsCount = Campaign::model()->count($criteria);
                    if ($campaignsCount >= $maxCampaigns) {
                        continue;
                    }
                }
                if (!$campaign->copy()) {
                    continue;
                }
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        
        } elseif ($action == Campaign::BULK_ACTION_PAUSE_UNPAUSE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                $campaign->pauseUnpause();
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        
        } elseif ($action == Campaign::BULK_ACTION_MARK_SENT && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                if (!$campaign->markAsSent()) {
                    continue;
                }
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        
        } elseif ($action == Campaign::BULK_EXPORT_BASIC_STATS && count($items)) {

            if (Yii::app()->customer->getModel()->getGroupOption('campaigns.can_export_stats', 'yes') != 'yes') {
                $this->redirect($returnRoute);
            }

            if (!($fp = @fopen('php://output', 'w'))) {
                $notify->addError(Yii::t('campaign_reports', 'Cannot open export temporary file!'));
                $this->redirect($returnRoute);
            }

            /* Set the download headers */
            HeaderHelper::setDownloadHeaders('bulk-basic-stats-' . date('Y-m-d-h-i-s') . '.csv');
            
            $header = array(
                Yii::t('campaign_reports', 'Name'),
	            Yii::t('campaign_reports', 'Subject'),
                Yii::t('campaign_reports', 'Unique ID'),
                Yii::t('campaign_reports', 'Processed'),
                Yii::t('campaign_reports', 'Sent with success'),
                Yii::t('campaign_reports', 'Sent success rate'),
                Yii::t('campaign_reports', 'Send error'),
                Yii::t('campaign_reports', 'Send error rate'),
                Yii::t('campaign_reports', 'Unique opens'),
                Yii::t('campaign_reports', 'Unique open rate'),
                Yii::t('campaign_reports', 'All opens'),
                Yii::t('campaign_reports', 'All opens rate'),
                Yii::t('campaign_reports', 'Bounced back'),
                Yii::t('campaign_reports', 'Bounce rate'),
                Yii::t('campaign_reports', 'Hard bounce'),
                Yii::t('campaign_reports', 'Hard bounce rate'),
                Yii::t('campaign_reports', 'Soft bounce'),
                Yii::t('campaign_reports', 'Soft bounce rate'),
                Yii::t('campaign_reports', 'Unsubscribe'),
                Yii::t('campaign_reports', 'Unsubscribe rate'),
                Yii::t('campaign_reports', 'Total urls for tracking'),
                Yii::t('campaign_reports', 'Unique clicks'),
                Yii::t('campaign_reports', 'Unique clicks rate'),
                Yii::t('campaign_reports', 'All clicks'),
                Yii::t('campaign_reports', 'All clicks rate')
            );

            fputcsv($fp, $header, ',', '"');
 
            foreach ($items as $item) {
                
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                
                $row = array(
                    $campaign->name,
	                $campaign->subject,
                    $campaign->campaign_uid,
                    $campaign->stats->getProcessedCount(true),
                    $campaign->stats->getDeliverySuccessCount(true),
                    $campaign->stats->getDeliverySuccessRate(true) . '%',
                    $campaign->stats->getDeliveryErrorCount(true),
                    $campaign->stats->getDeliveryErrorRate(true) . '%',
                    $campaign->stats->getUniqueOpensCount(true),
                    $campaign->stats->getUniqueOpensRate(true) . '%',
                    $campaign->stats->getOpensCount(true),
                    $campaign->stats->getOpensRate(true) . '%',
                    $campaign->stats->getBouncesCount(true),
                    $campaign->stats->getBouncesRate(true) . '%',
                    $campaign->stats->getHardBouncesCount(true),
                    $campaign->stats->getHardBouncesRate(true) . '%',
                    $campaign->stats->getSoftBouncesCount(true) . '%',
                    $campaign->stats->getSoftBouncesRate(true) . '%',
                    $campaign->stats->getUnsubscribesCount(true),
                    $campaign->stats->getUnsubscribesRate(true) . '%',
                );
                
                if ($campaign->option->url_tracking == CampaignOption::TEXT_YES) {
                    $row[] = $campaign->stats->getTrackingUrlsCount(true);
                    $row[] = $campaign->stats->getUniqueClicksCount(true);
                    $row[] = $campaign->stats->getUniqueClicksRate(true) . '%';
                    $row[] = $campaign->stats->getClicksCount(true);
                    $row[] = $campaign->stats->getClicksRate(true) . '%';
                } else {
                    $row[] = "";
                    $row[] = "";
                    $row[] = "";
                    $row[] = "";
                    $row[] = "";
                }
                fputcsv($fp, $row, ',', '"');
            }

            @fclose($fp);
            Yii::app()->end();
        
        } elseif ($action == Campaign::BULK_ACTION_SEND_TEST_EMAIL && count($items)) {

	        if (!$request->getPost('recipients_emails')) {
		        $notify->addError(Yii::t('campaigns', 'Please specify the email address to where we should send the test email.'));
		        $this->redirect($request->getPost('returnUrl', $request->getServer('HTTP_REFERER', $returnRoute)));
	        }

	        $emails = explode(',', $request->getPost('recipients_emails'));
	        $emails = array_map('trim', $emails);
	        $emails = array_unique($emails);
	        $emails = array_slice($emails, 0, 10);

	        foreach ($emails as $index => $email) {
		        if (!FilterVarHelper::email($email)) {
			        $notify->addError(Yii::t('campaigns',  'The email address {email} does not seem to be valid!', array('{email}' => CHtml::encode($email))));
			        unset($emails[$index]);
			        continue;
		        }
	        }

	        if (empty($emails)) {
		        $notify->addError(Yii::t('campaigns', 'Cannot send using provided email address(es)!'));
		        $this->redirect($request->getPost('returnUrl', $request->getServer('HTTP_REFERER', $returnRoute)));
	        }

	        Yii::app()->session->add('campaignLastTestEmails', $request->getPost('recipients_emails'));
	        
	        $affected = 0;
	        foreach ($items as $item) {
		        
	        	if (!($campaign = $this->loadCampaignByUid($item))) {
			        continue;
		        }
		        
		        if (empty($campaign->template)) {
	        		continue;
		        }
		        
		        if ($campaign->getIsPendingDelete()) {
			        continue;
		        }

		        $dsParams = array('useFor' => array(DeliveryServer::USE_FOR_EMAIL_TESTS, DeliveryServer::USE_FOR_CAMPAIGNS));
		        $server   = DeliveryServer::pickServer(0, $campaign, $dsParams);
		        if (empty($server)) {
			        continue;
		        }

		        // 1.4.4
		        $subscribers = array();
		        foreach ($emails as $email) {
			        if (array_key_exists($email, $subscribers)) {
				        continue;
			        }
			        $subscriber = ListSubscriber::model()->findByAttributes(array(
				        'list_id' => $campaign->list_id,
				        'email'   => $email,
				        'status'  => ListSubscriber::STATUS_CONFIRMED,
			        ));
			        if (empty($subscriber)) {
				        $subscriber = ListSubscriber::model()->findByAttributes(array(
					        'list_id' => $campaign->list_id,
					        'status'  => ListSubscriber::STATUS_CONFIRMED,
				        ));
			        }
			        $subscribers[$email] = $subscriber;
		        }
		        //

		        $template = $campaign->template;
		        
		        foreach ($emails as $email) {

			        $subscriber      = !empty($subscribers[$email]) ? $subscribers[$email] : null;
			        $fromEmailCustom = null;
			        $fromNameCustom  = null;
			        $replyToCustom   = null;

			        $plainTextContent = $template->plain_text;
			        $emailSubject     = $campaign->subject;
			        $onlyPlainText    = !empty($template->only_plain_text) && $template->only_plain_text === CampaignTemplate::TEXT_YES;
			        $emailContent     = !$onlyPlainText ? $template->content : $plainTextContent;
			        $embedImages      = array();

			        if (!$onlyPlainText && $server->canEmbedImages && !empty($campaign->option) && !empty($campaign->option->embed_images) && $campaign->option->embed_images == CampaignOption::TEXT_YES) {
				        list($emailContent, $embedImages) = CampaignHelper::embedContentImages($emailContent, $campaign);
			        }

			        if (!empty($subscriber)) {

				        // since 1.3.5.9
				        // really blind check to see if it contains a tag
				        if (strpos($campaign->from_email, '[') !== false || strpos($campaign->from_name, '[') !== false || strpos($campaign->reply_to, '[') !== false) {
					        if (strpos($campaign->from_email, '[') !== false) {
						        $searchReplace   = CampaignHelper::getCommonTagsSearchReplace($campaign->from_email, $campaign, $subscriber);
						        $fromEmailCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_email);
						        if (!FilterVarHelper::email($fromEmailCustom)) {
							        $fromEmailCustom = null;
							        $campaign->from_email = $server->from_email;
						        }
					        }
					        if (strpos($campaign->from_name, '[') !== false) {
						        $searchReplace  = CampaignHelper::getCommonTagsSearchReplace($campaign->from_name, $campaign, $subscriber);
						        $fromNameCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->from_name);
					        }
					        if (strpos($campaign->reply_to, '[') !== false) {
						        $searchReplace = CampaignHelper::getCommonTagsSearchReplace($campaign->reply_to, $campaign, $subscriber);
						        $replyToCustom = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->reply_to);
						        if (!FilterVarHelper::email($replyToCustom)) {
							        $replyToCustom = null;
							        $campaign->reply_to = $server->from_email;
						        }
					        }
				        }
				        //

				        if (!$onlyPlainText && !empty($campaign->option) && !empty($campaign->option->preheader)) {
					        $emailContent = CampaignHelper::injectPreheader($emailContent, $campaign->option->preheader, $campaign);
				        }

				        if (!$onlyPlainText && CampaignHelper::contentHasXmlFeed($emailContent)) {
					        $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, false, null, $server);
				        }

				        if (!$onlyPlainText && CampaignHelper::contentHasJsonFeed($emailContent)) {
					        $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, false, null, $server);
				        }

				        // 1.5.3
				        if (!$onlyPlainText && CampaignHelper::hasRemoteContentTag($emailContent)) {
					        $emailContent = CampaignHelper::fetchContentForRemoteContentTag($emailContent, $campaign, $subscriber);
				        }
				        //

				        $emailData  = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, false, $server);
				        list(, $_emailSubject, $emailContent) = $emailData;

				        // since 1.3.5.3
				        if (CampaignHelper::contentHasXmlFeed($_emailSubject)) {
					        $_emailSubject = CampaignXmlFeedParser::parseContent($_emailSubject, $campaign, $subscriber, false, $emailSubject, $server);
				        }

				        if (CampaignHelper::contentHasJsonFeed($_emailSubject)) {
					        $_emailSubject = CampaignJsonFeedParser::parseContent($_emailSubject, $campaign, $subscriber, false, $emailSubject, $server);
				        }

				        // 1.5.3
				        if (CampaignHelper::hasRemoteContentTag($_emailSubject)) {
					        $_emailSubject = CampaignHelper::fetchContentForRemoteContentTag($_emailSubject, $campaign, $subscriber);
				        }
				        //
				        
				        if (!empty($_emailSubject)) {
					        $emailSubject = $_emailSubject;
				        }
			        }

			        if (empty($emailSubject)) {
				        $emailSubject   = '['. strtoupper(Yii::t('app', 'Test')) .'] ' . $campaign->name;
			        }

			        if ($onlyPlainText) {
				        $emailContent = preg_replace('%<br(\s{0,}?/?)?>%i', "\n", $emailContent);
			        }

			        $customer = Yii::app()->customer->getModel();
			        $fromName = !empty($fromNameCustom) ? $fromNameCustom : $campaign->from_name;

			        if (empty($fromName)) {
				        $fromName = $customer->getFullName();
				        if (!empty($customer->company)) {
					        $fromName = $customer->company->name;
				        }
				        if (empty($fromName)) {
					        $fromName = $customer->email;
				        }
			        }

			        $fromEmail = null;
			        if (!empty($fromEmailCustom)) {
				        $fromEmail = $fromEmailCustom;
			        }

			        if (CampaignHelper::isTemplateEngineEnabled()) {
				        if (!$onlyPlainText && !empty($emailContent)) {
					        $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailContent, $campaign, $subscriber, $server);
					        $emailContent = CampaignHelper::parseByTemplateEngine($emailContent, $searchReplace);
				        }
				        if (!empty($emailSubject)) {
					        $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailSubject, $campaign, $subscriber, $server);
					        $emailSubject  = CampaignHelper::parseByTemplateEngine($emailSubject, $searchReplace);
				        }
				        if (!empty($plainTextContent)) {
					        $searchReplace   = CampaignHelper::getCommonTagsSearchReplace($plainTextContent, $campaign, $subscriber, $server);
					        $plainTextContent = CampaignHelper::parseByTemplateEngine($plainTextContent, $searchReplace);
				        }
			        }

			        $params = array(
				        'to'            => $email,
				        'fromName'      => $fromName,
				        'subject'       => $emailSubject,
				        'body'          => $onlyPlainText ? null : $emailContent,
				        'embedImages'   => $embedImages,
				        'plainText'     => $plainTextContent,
				        'onlyPlainText' => $onlyPlainText,

				        // since 1.3.5.9
				        'fromEmailCustom' => $fromEmailCustom,
				        'fromNameCustom'  => $fromNameCustom,
				        'replyToCustom'   => $replyToCustom,
			        );

			        if ($fromEmail) {
				        $params['from'] = array($fromEmail => $fromName);
			        }
			        
			        $sent = false;
			        for ($i = 0; $i < 3; ++$i) {
				        if ($sent = $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN_TEST)->setDeliveryObject($campaign)->sendEmail($params)) {
					        break;
				        }
				        
				        if (!($server = DeliveryServer::pickServer($server->server_id, $campaign, $dsParams))) {
					        break;
				        }
			        }
			        
			        if (!$sent) {
				        $notify->addError(Yii::t('campaigns', 'Campaign {campaign}: Unable to send the test email to {email}!', array(
				        	'{campaign}' => $campaign->name . '['. $campaign->campaign_uid .'] ',
					        '{email}'    => CHtml::encode($email),
				        )));
			        } else {
				        $notify->addSuccess(Yii::t('campaigns', 'Campaign {campaign}: Test email successfully sent to {email}!', array(
					        '{campaign}' => $campaign->name . '['. $campaign->campaign_uid .'] ',
					        '{email}'    => CHtml::encode($email),
				        )));
			        }

			        $affected++;
		        }
	        }
	        
	        if ($affected) {
		        $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
	        }
        } elseif ($action == Campaign::BULK_ACTION_SHARE_CAMPAIGN_CODE && count($items)) {
            $affected = 0;
            $success  = false;
            $campaignsIds = array();

            /* Collect the campaign ids */
            foreach ($items as $item) {
                if (!($campaign = $this->loadCampaignByUid($item))) {
                    continue;
                }
                $campaignsIds[] = $campaign->campaign_id;
            }

            if (!empty($campaignsIds)) {
                $campaignShareCode = new CampaignShareCode();

                $transaction = Yii::app()->db->beginTransaction();

                try {
                    if (!$campaignShareCode->save()) {
                        throw new Exception(Yii::t('campaigns', 'Could not save the sharing code'));
                    }

                    foreach ($campaignsIds as $campaignId) {
                        $campaignShareCodeToCampaign              = new CampaignShareCodeToCampaign();
                        $campaignShareCodeToCampaign->code_id     = $campaignShareCode->code_id;
                        $campaignShareCodeToCampaign->campaign_id = (int)$campaignId;

                        if (!$campaignShareCodeToCampaign->save()) {
                            throw new Exception(Yii::t('campaigns', 'Could not save the sharing code to campaign'));
                        }

                        $affected++;
                    }

                    $transaction->commit();
                    $success = true;
                } catch (Exception $e) {
                    Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
                    $transaction->rollback();
                }
            }

            if ($success) {
                $notify->addSuccess(Yii::t('campaigns', 'The sharing code is: {code}', array(
                    '{code}' => sprintf('<strong>%s</strong>', $campaignShareCode->code_uid)
                )));
            }
        }

        $defaultReturn = $request->getServer('HTTP_REFERER', $returnRoute);
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

	/**
	 * Remove certain campaign attachment
	 * 
	 * @param $campaign_uid
	 * @param $attachment_id
	 *
	 * @throws CDbException
	 * @throws CHttpException
	 */
    public function actionRemove_attachment($campaign_uid, $attachment_id)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $attachment = CampaignAttachment::model()->findByAttributes(array(
            'attachment_id' => (int)$attachment_id,
            'campaign_id'   => (int)$campaign->campaign_id,
        ));

        if (!empty($attachment)) {
            $attachment->delete();
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'Your campaign attachment was successfully removed!'));
            $this->redirect($request->getPost('returnUrl', array('campaigns/' . $campaign->type)));
        }
    }

	/**
	 * @return BaseController
	 * @throws Exception
	 */
    public function actionSync_datetime()
    {
        $customer   = Yii::app()->customer->getModel();
        $request    = Yii::app()->request;

        $timeZoneDateTime   = date('Y-m-d H:i:s', strtotime($request->getQuery('date', date('Y-m-d H:i:s'))));
        $timeZoneTimestamp  = strtotime($timeZoneDateTime);
        $localeDateTime     = Yii::app()->dateFormatter->formatDateTime($timeZoneTimestamp, 'short', 'short');

        // since the date is already in customer timezone we need to convert it back to utc
        $sourceTimeZone      = new DateTimeZone($customer->timezone);
        $destinationTimeZone = new DateTimeZone(Yii::app()->timeZone);
        $dateTime            = new DateTime($timeZoneDateTime, $sourceTimeZone);
        $dateTime->setTimezone($destinationTimeZone);
        $utcDateTime = $dateTime->format('Y-m-d H:i:s');

        return $this->renderJson(array(
            'localeDateTime'  => $localeDateTime,
            'utcDateTime'     => $utcDateTime,
        ));
    }

	/**
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionGoogle_utm_tags($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        if (empty($campaign->template) || empty($campaign->template->content)) {
            $notify->addError(Yii::t('campaigns', 'Please use a template for this campaign in order to insert the google utm tags!'));
            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid));
        }

        $pattern = $request->getPost('google_utm_pattern');
        if (empty($pattern)) {
            $notify->addError(Yii::t('campaigns', 'Please specify a pattern in order to insert the google utm tags!'));
            $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid));
        }

        $campaign->template->content = CampaignHelper::injectGoogleUtmTagsIntoTemplate($campaign->template->content, $pattern);
        $campaign->template->save(false);

        $notify->addSuccess(Yii::t('campaigns', 'The google utm tags were successfully inserted into your template!'));
        $this->redirect(array('campaigns/template', 'campaign_uid' => $campaign->campaign_uid));
    }

	/**
	 * @param $campaign_uid
	 *
	 * @return BaseController|void
	 * @throws CHttpException
	 */
    public function actionShare_reports($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('campaigns/' . $campaign->type));
        }
        
        $shareReports = $campaign->shareReports;
        $shareReports->attributes  = (array)$request->getPost($shareReports->modelName, array());
        $shareReports->campaign_id = $campaign->campaign_id;
        
        if (!$shareReports->save()) {
            return $this->renderJson(array(
                'result'  => 'error',
                'message' =>  CHtml::errorSummary($shareReports, null, null, array('class' => '')),
            ));
        }

        return $this->renderJson(array(
            'result'  => 'success',
            'message' =>  Yii::t('app', 'Your form has been successfully saved!'),
        ));
    }

	/**
	 * @param $campaign_uid
	 *
	 * @return BaseController|void
	 * @throws CHttpException
	 */
    public function actionShare_reports_send_email($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);
        $request  = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('campaigns/' . $campaign->type));
        }
        
        $shareReports              = $campaign->shareReports;
        $shareReportsEnabled       = $shareReports->share_reports_enabled == CampaignOptionShareReports::TEXT_YES;
        $shareReports->scenario    = 'send-email';
        $shareReports->attributes  = (array)$request->getPost($shareReports->modelName, array());
        $shareReports->campaign_id = $campaign->campaign_id;
        
        if (!$shareReports->validate()) {
            return $this->renderJson(array(
                'result'  => 'error',
                'message' =>  CHtml::errorSummary($shareReports, null, null, array('class' => '')),
            ));
        }
        
        if (!$shareReportsEnabled) {
            return $this->renderJson(array(
                'result'  => 'error',
                'message' =>  Yii::t('campaigns', 'It seems share reports is disabled for this campaign, did you forget to save changes?'),
            ));
        }

        $dsParams = array('useFor' => DeliveryServer::USE_FOR_CAMPAIGNS);
        if (!($server = DeliveryServer::pickServer(0, $campaign, $dsParams))) {
            return $this->renderJson(array(
                'result'  => 'error',
                'message' =>  Yii::t('campaigns', 'Email delivery is disabled at the moment, please try again later!'),
            ));
        }

	    $params = CommonEmailTemplate::getAsParamsArrayBySlug('campaign-share-reports-access',
		    array(
			    'to'      => array($shareReports->share_reports_email => $shareReports->share_reports_email),
			    'subject' => Yii::t('lists', 'Campaign reports access!'),
		    ), array(
			    '[CAMPAIGN_NAME]'             => $campaign->name,
			    '[CAMPAIGN_REPORTS_URL]'      => $shareReports->getShareUrl(),
			    '[CAMPAIGN_REPORTS_PASSWORD]' => $shareReports->share_reports_password,
		    )
	    );

        if (!($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_CAMPAIGN)->setDeliveryObject($campaign)->sendEmail($params))) {
            return $this->renderJson(array(
                'result'  => 'error',
                'message' =>  Yii::t('campaigns', 'Unable to send the email at this time, please try again later!'),
            ));
        }

        return $this->renderJson(array(
            'result'  => 'success',
            'message' =>  Yii::t('campaigns', 'The email has been sent successfully!'),
        ));
    }

	/**
	 * @param $campaign_uid
	 *
	 * @return BaseController
	 * @throws CHttpException
	 */
	public function actionResend_giveups($campaign_uid)
	{
		$campaign = $this->loadCampaignModel($campaign_uid);
		$request  = Yii::app()->request;
		if (!$request->isAjaxRequest || !$request->isPostRequest) {
			return $this->redirect(array('campaigns/' . $campaign->type));
		}

		if (!$campaign->getIsSent()) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'Resending to giveups only works for sent campaigns!'),
			));
		}

		if (empty($campaign->option->giveup_count)) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'It seems this campaign has no giveups!'),
			));
		}

		$queued = CampaignResendGiveupQueue::model()->countByAttributes(array(
			'campaign_id' => $campaign->campaign_id
		));

		if ($queued) {
			return $this->renderJson(array(
				'result'  => 'error',
				'message' =>  Yii::t('campaigns', 'It seems this campaign has already been queued to resend to giveups!'),
			));
		}
		
		$queue = new CampaignResendGiveupQueue();
		$queue->campaign_id = $campaign->campaign_id;
		$queue->save(false);
		
		return $this->renderJson(array(
			'result'  => 'success',
			'message' =>  Yii::t('campaigns', 'The campaigns has been queued successfully, it will start sending in a few minutes!'),
		));
	}

    /**
     * Export 
     * @param $type
     */
    public function actionExport($type = '')
    {
        $notify = Yii::app()->notify;

        $attributes = array(
            'customer_id' => (int)Yii::app()->customer->getId(),
        );
        if ($type) {
            $attributes['type'] = $type;
        }
        
        $models = Campaign::model()->findAllByAttributes($attributes);

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }
        
        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('campaigns.csv');
        
        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes);
        $columns    = array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes));
        $columns    = CMap::mergeArray($columns, array(
            'group'   => $models[0]->getAttributeLabel('group_id'),
            'list'    => $models[0]->getAttributeLabel('list_id'),
            'segment' => $models[0]->getAttributeLabel('segment_id'),
        ));
        @fputcsv($fp, $columns, ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->attributes);
            $attributes = CMap::mergeArray($attributes, array(
                'group'   => $model->group_id   ? $model->group->name   : '',
                'list'    => $model->list_id    ? $model->list->name    : '',
                'segment' => $model->segment_id ? $model->segment->name : '',
            ));
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * @throws CException
     */
    public function actionImport_from_share_code()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $returnRoute = array('campaigns/index');
        if (!$request->isPostRequest) {
            $this->redirect($returnRoute);
        }

        $shareCode = new CampaignShareCodeImport();
        $shareCode->attributes  = (array)$request->getPost($shareCode->modelName, array());
        $shareCode->customer_id = (int)Yii::app()->customer->getId();

        if (!$shareCode->validate()) {
            $notify->addError($shareCode->shortErrors->getAllAsString());
            $this->redirect($returnRoute);
        }

        $campaignShareCodeModel = $shareCode->getCampaignShareCode();

        $success = false;
        $message = '';

        $transaction = Yii::app()->db->beginTransaction();

        try {

            if (!($campaignModels = $campaignShareCodeModel->campaigns)) {
                throw new Exception(Yii::t('campaigns', 'Could not find any campaign to share'));
            }

            $campaigns = array();
            foreach ($campaignModels as $campaignModel) {
                if ($campaignModel->getIsPendingDelete()) {
                    continue;
                }
                $campaigns[] = $campaignModel;
            }

            if (empty($campaigns)) {
                throw new Exception(Yii::t('campaigns', 'Could not find any campaign to share'));
            }

            foreach ($campaigns as $campaign) {

                if (!($newCampaign = $campaign->copy(false))) {
                    throw new Exception(Yii::t('campaigns', 'Could not copy the shared campaign'));
                }

                $newCampaign->customer_id = $shareCode->customer_id;
                $newCampaign->list_id     = $shareCode->list_id;

                if (!$newCampaign->save()) {
                    throw new Exception(Yii::t('campaigns', 'Could not save the shared campaign'));
                }
            }

            $campaignShareCodeModel->used = CampaignShareCode::TEXT_YES;
            if (!$campaignShareCodeModel->save()) {
                throw new Exception(Yii::t('campaigns', 'Could not update the campaign shared code status'));
            }

            $transaction->commit();
            $success = true;
        } catch (Exception $e) {
            $transaction->rollback();
            $message = $e->getMessage();
        }

        if (!$success) {
            $notify->addError($message);
            $this->redirect($returnRoute);
        }

        $notify->addSuccess(Yii::t('campaigns', 'Successful imported the shared campaigns'));
        $this->redirect($returnRoute);
    }

    /**
     * Helper method to load the campaign AR model
     */
    public function loadCampaignModel($campaign_uid)
    {
    	$model = $this->loadCampaignByUid($campaign_uid);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($model->isPendingDelete) {
            $this->redirect(array('campaigns/' . $model->type));
        }

        if (empty($model->option)) {
            $option = new CampaignOption();
            $option->campaign_id = $model->campaign_id;
            $model->addRelatedRecord('option', $option, false);
        }

        return $model;
    }

	/**
	 * @param $campaign_uid
	 *
	 * @return Campaign|null
	 */
    public function loadCampaignByUid($campaign_uid)
    {
	    $criteria = new CDbCriteria();
	    $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
	    $criteria->compare('campaign_uid', $campaign_uid);
	    $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));

	    return Campaign::model()->find($criteria);
    }

    /**
     * Callback method to setup the editor for the template step
     */
    public function _setEditorOptions(CEvent $event)
    {
        if ($event->params['attribute'] == 'content') {
            $options = array();
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }
            $options['id'] = CHtml::activeId($event->sender->owner, 'content');
            $options['fullPage'] = true;
            $options['allowedContent'] = true;
            $options['contentsCss'] = array();
            $options['height'] = 800;

            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }

    /**
     * Callback method to setup the random content editor for the template step
     */
    public function _setRandomContentEditorOptions(CEvent $event)
    {
        if ($event->params['attribute'] == 'content') {
            $options = array();
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }
            $options['id']          = CHtml::activeId($event->sender->owner, 'content');
            $options['toolbar']     = 'Simple';
            $options['contentsCss'] = array();
            $options['height']      = 100;

            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('index'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
