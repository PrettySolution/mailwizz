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
    public $campaignReportsController = 'campaigns_reports';

    /**
     * @var string
     */
    public $campaignReportsExportController = 'campaigns_reports_export';

    /**
     * @var bool
     */
    public $trackOpeningInternalCall = false;

    /**
     * Show the overview for a campaign, needs access and login
     * 
     * @param $campaign_uid
     * @throws CHttpException
     */
    public function actionOverview($campaign_uid)
    {
        $campaign = $this->loadCampaignModel($campaign_uid);

        if (!empty($campaign->customer->language)) {
            Yii::app()->setLanguage($campaign->customer->language->getLanguageAndLocaleCode());
        }
        
        if ($campaign->shareReports->share_reports_enabled != CampaignOptionShareReports::TEXT_YES) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $session = Yii::app()->session;
        if (!isset($session['campaign_reports_access_' . $campaign_uid])) {
            return $this->redirect(array('campaigns_reports/login', 'campaign_uid' => $campaign_uid));
        }
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaign overview'),
            'pageHeading'     => Yii::t('campaigns', 'Campaign overview'),
            'pageBreadcrumbs' => array(
                Yii::t('campaigns', 'Campaigns'),
                $campaign->name . ' ' => $this->createUrl('campaigns/overview', array('campaign_uid' => $campaign_uid)),
                Yii::t('campaigns', 'Overview')
            )
        ));
        
        // render
        $this->render('customer.views.campaigns.overview', compact('campaign'));
    }

    /**
     * Will show the web version of a campaign email
     * 
     * @param $campaign_uid
     * @param null $subscriber_uid
     * @throws Exception
     */
    public function actionWeb_version($campaign_uid, $subscriber_uid = null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        $campaign = Campaign::model()->find($criteria);

        if (empty($campaign)) {
            $this->redirect(array('site/index'));
        }

        $subscriber = null;
        if (!empty($subscriber_uid)) {
            $subscriber = ListSubscriber::model()->findByUid($subscriber_uid);
        }

        $list           = $campaign->list;
        $customer       = $list->customer;
        $template       = $campaign->template;
        $emailContent   = $template->content;
        $emailHeader    = null;
        $emailFooter    = null;
        
        // 1.5.5
        if ($campaign->template->only_plain_text == CampaignTemplate::TEXT_YES) {
            $emailContent = nl2br($emailContent);
        }
        
        if (!empty($campaign->option) && !empty($campaign->option->preheader)) {
            $emailContent = CampaignHelper::injectPreheader($emailContent, $campaign->option->preheader, $campaign);
        }

        if (($emailHeader = $customer->getGroupOption('campaigns.email_header')) && strlen(trim($emailHeader)) > 5) {
            $emailContent = CampaignHelper::injectEmailHeader($emailContent, $emailHeader, $campaign);
        }
        
        if (($emailFooter = $customer->getGroupOption('campaigns.email_footer')) && strlen(trim($emailFooter)) > 5) {
            $emailContent = CampaignHelper::injectEmailFooter($emailContent, $emailFooter, $campaign);
        }

        if (CampaignHelper::contentHasXmlFeed($emailContent)) {
            $emailContent = CampaignXmlFeedParser::parseContent($emailContent, $campaign, $subscriber, true);
        }

        if (CampaignHelper::contentHasJsonFeed($emailContent)) {
            $emailContent = CampaignJsonFeedParser::parseContent($emailContent, $campaign, $subscriber, true);
        }

	    // 1.5.3
	    if (CampaignHelper::hasRemoteContentTag($emailContent)) {
		    $emailContent = CampaignHelper::fetchContentForRemoteContentTag($emailContent, $campaign, $subscriber);
	    }
	    //

        if ($subscriber) {
            if (!$campaign->isDraft && !empty($campaign->option) && $campaign->option->url_tracking == CampaignOption::TEXT_YES) {
                $emailContent = CampaignHelper::transformLinksForTracking($emailContent, $campaign, $subscriber, false);
            }
        } else {
            $subscriber = new ListSubscriber();
        }

        $emailData = CampaignHelper::parseContent($emailContent, $campaign, $subscriber, true);
        list(,,$emailContent) = $emailData;

        // 1.5.3
        if (!empty($emailContent) && CampaignHelper::isTemplateEngineEnabled()) {
            $searchReplace = CampaignHelper::getCommonTagsSearchReplace($emailContent, $campaign, $subscriber);
            $emailContent  = CampaignHelper::parseByTemplateEngine($emailContent, $searchReplace);
        }
        //
        
        // 1.4.5
        $emailContent = Yii::app()->hooks->applyFilters('frontend_campaigns_controller_web_version_action_email_content', $emailContent, $list, $customer, $template, $campaign, $subscriber);
        
        echo $emailContent;
    }

    /**
     * Will track and register the email openings
     *
     * GMail will store the email images, therefore there might be cases when successive opens by same subscriber
     * will not be tracked.
     * In order to trick this, it seems that the content length must be set to 0 as pointed out here:
     * http://www.emailmarketingtipps.de/2013/12/07/gmails-image-caching-affects-email-marketing-heal-opens-tracking/
     *
     * Note: When mod gzip enabled on server, the content length will be at least 20 bytes as explained in this bug:
     * https://issues.apache.org/bugzilla/show_bug.cgi?id=51350
     * In order to alleviate this, seems that we need to use a fake content type, like application/json
     */
    public function actionTrack_opening($campaign_uid, $subscriber_uid)
    {
        if (!$this->trackOpeningInternalCall) {
            header("Content-Type: application/json");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: private");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header('P3P: CP="OTI DSP COR CUR IVD CONi OTPi OUR IND UNI STA PRE"');
            header("Pragma: no-cache");
            header("Content-Length: 0");
        }

        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        $campaign = Campaign::model()->find($criteria);

        if (empty($campaign)) {
            if ($this->trackOpeningInternalCall) {
                return;
            }
            Yii::app()->end();
        }

	    $subscriber = ListSubscriber::model()->findByUid($subscriber_uid);
	    if (empty($subscriber)) {
		    if ($this->trackOpeningInternalCall) {
			    return;
		    }
		    Yii::app()->end();
	    }

        // since 1.3.5.8
        Yii::app()->hooks->addFilter('frontend_campaigns_can_track_opening', array($this, '_actionCanTrackOpening'));
        $canTrack = Yii::app()->hooks->applyFilters('frontend_campaigns_can_track_opening', true, $this, $campaign);

        if ($canTrack) {
            // only allow confirmed and moved subs
            $canTrack = $subscriber->getIsConfirmed() || $subscriber->getIsMoved();
        }

        if (!$canTrack) {
            if ($this->trackOpeningInternalCall) {
                return;
            }
            Yii::app()->end();
        }
        
        // 1.5.2 - update ip address if changed
        if (($ipAddress = Yii::app()->request->getUserHostAddress()) && FilterVarHelper::ip($ipAddress)) {
            $subscriber->saveIpAddress($ipAddress);
        }

        Yii::app()->hooks->addAction('frontend_campaigns_after_track_opening', array($this, '_openActionChangeSubscriberListField'), 99);
        Yii::app()->hooks->addAction('frontend_campaigns_after_track_opening', array($this, '_openActionAgainstSubscriber'), 100);
        
        // since 1.6.8 
	    Yii::app()->hooks->addAction('frontend_campaigns_after_track_opening', array($this, '_openCreateWebhookRequest'), 101);

        $track = new CampaignTrackOpen();
        $track->campaign_id     = $campaign->campaign_id;
        $track->subscriber_id   = $subscriber->subscriber_id;
        $track->ip_address      = Yii::app()->request->getUserHostAddress();
        $track->user_agent      = substr(Yii::app()->request->getUserAgent(), 0, 255);

        if ($track->save(false)) {
            // raise the action, hook added in 1.2
            $this->setData('ipLocationSaved', false);
            try {
                Yii::app()->hooks->doAction('frontend_campaigns_after_track_opening', $this, $track, $campaign, $subscriber);
            } catch (Exception $e) {

            }
        }

        if ($this->trackOpeningInternalCall) {
            return;
        }
        Yii::app()->end();
    }

    /**
     * Will track the clicks the subscribers made in the campaign email
     * 
     * @param $campaign_uid
     * @param $subscriber_uid
     * @param $hash
     * @throws CHttpException
     */
    public function actionTrack_url($campaign_uid, $subscriber_uid, $hash)
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        $campaign = Campaign::model()->find($criteria);

        if (empty($campaign)) {
            Yii::app()->hooks->doAction('frontend_campaigns_track_url_item_not_found', array(
                'step' => 'campaign'
            ));
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $subscriber = ListSubscriber::model()->findByUid($subscriber_uid);
        if (empty($subscriber)) {
            Yii::app()->hooks->doAction('frontend_campaigns_track_url_item_not_found', array(
                'step' => 'subscriber'
            ));
            if ($redirect = $campaign->list->getSubscriber404Redirect()) {
                $this->redirect($redirect);
            }
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        // 1.5.2 - update ip address if changed
        if (($ipAddress = Yii::app()->request->getUserHostAddress()) && FilterVarHelper::ip($ipAddress)) {
            $subscriber->saveIpAddress($ipAddress);
        }

        // since 1.4.2
        $hash = str_replace(array('.', ' ', '-', '_', '='), '', $hash);
        $hash = substr($hash, 0, 40);
        //
        
        $url = CampaignUrl::model()->findByAttributes(array(
            'campaign_id'   => $campaign->campaign_id,
            'hash'          => $hash,
        ));

        if (empty($url)) {
            Yii::app()->hooks->doAction('frontend_campaigns_track_url_item_not_found', array(
                'step' => 'url'
            ));
            if ($redirect = $campaign->list->getSubscriber404Redirect()) {
                $this->redirect($redirect);
            }
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        // since 1.3.5.8
        Yii::app()->hooks->addFilter('frontend_campaigns_can_track_url', array($this, '_actionCanTrackUrl'));
        $canTrack = Yii::app()->hooks->applyFilters('frontend_campaigns_can_track_url', true, $this, $campaign, $subscriber, $url);

        if ($canTrack) {
            // only allow confirmed and moved subs
            $canTrack = $subscriber->getIsConfirmed() || $subscriber->getIsMoved();
        }

        if (!$canTrack) {
            
            // since 1.3.8.8
            $url->destination = StringHelper::normalizeUrl($url->destination);
            Yii::app()->hooks->doAction('frontend_campaigns_after_track_url_before_redirect', $this, $campaign, $subscriber, $url);
            $destination = $url->destination;
            
            if (preg_match('/\[(.*)?\]/', $destination)) {
                list(,,$destination) = CampaignHelper::parseContent($destination, $campaign, $subscriber, false);
            }

	        // since 1.7.6
	        if (!empty($destination) && CampaignHelper::isTemplateEngineEnabled()) {
		        $searchReplace = CampaignHelper::getCommonTagsSearchReplace($destination, $campaign, $subscriber);
		        $destination   = CampaignHelper::parseByTemplateEngine($destination, $searchReplace);
	        }
	        //
	        
            $this->redirect($destination, true, 301);
            //
        }

        // 1.6.8
	    Yii::app()->hooks->addAction('frontend_campaigns_after_track_url', array($this, '_urlCreateWebhookRequest'), 100);
        
        Yii::app()->hooks->addAction('frontend_campaigns_after_track_url_before_redirect', array($this, '_urlActionChangeSubscriberListField'), 99);
        Yii::app()->hooks->addAction('frontend_campaigns_after_track_url_before_redirect', array($this, '_urlActionAgainstSubscriber'), 100);
	    
        $track = new CampaignTrackUrl();
        $track->url_id          = $url->url_id;
        $track->subscriber_id   = $subscriber->subscriber_id;
        $track->ip_address      = Yii::app()->request->getUserHostAddress();
        $track->user_agent      = substr(Yii::app()->request->getUserAgent(), 0, 255);

       try {
           if ($track->save(false)) {
               // hook added in 1.2
               $this->setData('ipLocationSaved', false);
               try {
                   Yii::app()->hooks->doAction('frontend_campaigns_after_track_url', $this, $track, $campaign, $subscriber);
               } catch (Exception $e) {

               }
           }
       } catch (Exception $e) {}

        // changed since 1.3.5.9
        $url->destination = StringHelper::normalizeUrl($url->destination);
        Yii::app()->hooks->doAction('frontend_campaigns_after_track_url_before_redirect', $this, $campaign, $subscriber, $url);

        $destination = $url->destination;
        if (preg_match('/\[(.*)?\]/', $destination)) {

            $server = null;
            
            // since 1.5.2
            if (strpos($destination, '[DS_') !== false) {
                $log = CampaignDeliveryLog::model()->findByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));
                if (!empty($log) && !empty($log->server_id) && !empty($log->server)) {
                    $server = $log->server;
                }
            }
            //
            
            list(,,$destination) = CampaignHelper::parseContent($destination, $campaign, $subscriber, false, $server);
        }

	    // since 1.7.6
	    if (!empty($destination) && CampaignHelper::isTemplateEngineEnabled()) {
		    $searchReplace = CampaignHelper::getCommonTagsSearchReplace($destination, $campaign, $subscriber);
		    $destination   = CampaignHelper::parseByTemplateEngine($destination, $searchReplace);
	    }
	    //
        
        // since 1.3.5.9
        if ($campaign->option->open_tracking == CampaignOption::TEXT_YES && !$subscriber->hasOpenedCampaign($campaign)) {
            $this->trackOpeningInternalCall = true;
            $this->actionTrack_opening($campaign->campaign_uid, $subscriber->subscriber_uid);
            $this->trackOpeningInternalCall = false;
        }
        //

        $this->redirect($destination, true, 301);
    }

    /**
     * Will forward this campaign link to a friend email address
     * 
     * @param $campaign_uid
     * @param null $subscriber_uid
     * @throws CException
     */
    public function actionForward_friend($campaign_uid, $subscriber_uid = null)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        $campaign = Campaign::model()->find($criteria);

        if (empty($campaign)) {
            $this->redirect(array('site/index'));
        }

        $subscriber = null;
        if (!empty($subscriber_uid)) {
            $subscriber = ListSubscriber::model()->findByUid($subscriber_uid);
            if (empty($subscriber)) {
                $this->redirect(array('site/index'));
            }
        }

        $forward     = new CampaignForwardFriend();
        $request     = Yii::app()->request;
        $notify      = Yii::app()->notify;
        $options     = Yii::app()->options;
        $forwardUrl  = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid;

        if (!empty($subscriber)) {
            $forward->from_email = $subscriber->email;
        }
        $forward->subject = Yii::t('campaigns', 'Hey, check out this url, i think you will like it.');

        if ($request->isPostRequest && ($attributes = $request->getPost($forward->modelName, array()))) {
            $forward->attributes    = $attributes;
            $forward->campaign_id   = $campaign->campaign_id;
            $forward->subscriber_id = $subscriber ? $subscriber->subscriber_id : null;
            $forward->ip_address    = $request->getUserHostAddress();
            $forward->user_agent    = substr($request->getUserAgent(), 0, 255);

            $forwardsbyIp = CampaignForwardFriend::model()->countByAttributes(array(
                'campaign_id' => $forward->campaign_id,
                'ip_address'  => $forward->ip_address
            ));

            $forwardLimit = 10;
            if ($forwardsbyIp >= $forwardLimit) {
                $notify->addError(Yii::t('campaigns', 'You can only forward a campaign {num} times!', array('{num}' => $forwardLimit)));
                $this->refresh();
            }

            if (!$forward->save()) {
            
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            
            } else {
            	
	            $message = '';
	            if (!empty($forward->message)) {
	            	$message .= Yii::t('campaigns', '{from_name} also left this message for you:', array(
	            		'{from_name}' => $forward->from_name, 
				    ));
	            	$message .= ' <br />' . $forward->message;
	            }
	            
	            $params = CommonEmailTemplate::getAsParamsArrayBySlug('forward-campaign-friend',
		            array(
			            'subject' => $forward->subject,
		            ), array(
			            '[TO_NAME]'     => $forward->to_name,
			            '[FROM_NAME]'   => $forward->from_name,
			            '[MESSAGE]'     => $message,
			            '[CAMPAIGN_URL]'=> $forwardUrl,
		            )
	            );
	            
                $email = new TransactionalEmail();
                $email->to_name     = $forward->to_name;
                $email->to_email    = $forward->to_email;
                $email->from_name   = $forward->from_name;
                $email->from_email  = $forward->from_email;
                $email->subject     = $forward->subject;
                $email->body        = $params['body'];
                $email->save();

                $notify->addSuccess(Yii::t('campaigns', 'Your message has been successfully forwarded!'));
                $this->refresh();
            }
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Forward to a friend'),
            'pageHeading'     => Yii::t('campaigns', 'Forward to a friend'),
            'pageBreadcrumbs' => array()
        ));

        $this->render('forward-friend', compact('campaign', 'subscriber', 'forward', 'forwardUrl'));
    }

    /**
     * Will record the abuse report for a campaign
     * 
     * @param $campaign_uid
     * @param $list_uid
     * @param $subscriber_uid
     * @throws CException
     */
    public function actionReport_abuse($campaign_uid, $list_uid, $subscriber_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        $campaign = Campaign::model()->find($criteria);

        if (empty($campaign)) {
            $this->redirect(array('site/index'));
        }

        $list = Lists::model()->findByUid($list_uid);
        if (empty($list)) {
            $this->redirect(array('site/index'));
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
            'status'         => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            $this->redirect(array('site/index'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $options = Yii::app()->options;
        $report  = new CampaignAbuseReport();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($report->modelName, array()))) {
            $report->attributes      = $attributes;
            $report->customer_id     = $list->customer_id;
            $report->campaign_id     = $campaign->campaign_id;
            $report->list_id         = $list->list_id;
            $report->subscriber_id   = $subscriber->subscriber_id;
            $report->customer_info   = sprintf('%s(%s)', $list->customer->getFullName(), $list->customer->email);
            $report->campaign_info   = $campaign->name;
            $report->list_info       = sprintf('%s(%s)', $list->name, $list->display_name);
            $report->subscriber_info = $subscriber->email;
            $report->ip_address      = $request->getUserHostAddress();
            $report->user_agent      = StringHelper::truncateLength($request->getUserAgent(), 255);

            if ($report->save()) {
                $subscriber->status = ListSubscriber::STATUS_UNSUBSCRIBED;
                $subscriber->save(false);

                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Abuse complaint!';
                $trackUnsubscribe->save(false);
                
                // since 1.5.2 - start notifications
	            $params = CommonEmailTemplate::getAsParamsArrayBySlug('new-abuse-report',
		            array(
			            'subject' => Yii::t('campaigns', 'New abuse report!'),
		            ), $searchReplace = array(
			            '[CUSTOMER_NAME]'       => $campaign->customer->fullName,
			            '[CAMPAIGN_NAME]'       => $campaign->name,
			            '[ABUSE_REPORTS_URL]'   => Yii::app()->apps->getAppUrl('customer', sprintf('campaigns/%s/reports/abuse-reports', $campaign->campaign_uid), true),
		            )
	            );
	            
                $email = new TransactionalEmail();
                $email->to_name     = $campaign->customer->getFullName();
                $email->to_email    = $campaign->customer->email;
                $email->from_name   = $options->get('system.common.site_name', 'Marketing website');
                $email->subject     = $params['subject'];
                $email->body        = $params['body'];
                $email->save();
                
                $message = new CustomerMessage();
                $message->customer_id = $campaign->customer->customer_id;
                $message->title       = 'New abuse report!';
                $message->message     = 'A new abuse report has been created for the campaign "{campaign_name}". Please visit the "<a href="{abuse_reports_url}">Abuse Reports</a>" area to handle it!';
                $message->message_translation_params = array(
                	'{campaign_name}'       => $searchReplace['[CAMPAIGN_NAME]'],
	                '{abuse_reports_url}'   => $searchReplace['[ABUSE_REPORTS_URL]'],
                );
                $message->save();
                //

                $notify->addSuccess(Yii::t('campaigns', 'Thank you for your report, we will take proper actions against this as soon as possible!'));
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Report abuse'),
            'pageHeading'     => Yii::t('campaigns', 'Report abuse'),
            'pageBreadcrumbs' => array()
        ));

        $this->render('report-abuse', compact('report'));
    }

	/**
	 * @since 1.7.6
	 * @param $campaign_uid
	 *
	 * @throws CHttpException
	 */
    public function actionVcard($campaign_uid)
    {
	    if (!MW_COMPOSER_SUPPORT) {
		    return $this->redirect(array('site/index'));
	    }

	    $campaign = $this->loadCampaignModel($campaign_uid);
	    $customer = $campaign->customer;
	    $list     = $campaign->list;
	    $company  = $list->company;
	    
	    if (!empty($customer->language_id)) {
		    Yii::app()->setLanguage($customer->language->getLanguageAndLocaleCode());
	    }

	    $className = '\JeroenDesloovere\VCard\VCard';
	    $vcard     = new $className();
	    
	    $vcard->addName($campaign->from_name, '', '', '', '');
	    $vcard->addCompany($company->name);
	    $vcard->addEmail($campaign->from_email);

	    if (!empty($company->phone)) {
		    $vcard->addPhoneNumber($company->phone, 'PREF;WORK');
	    }

	    $zone = !empty($company->zone_id) ? $company->zone->name : $company->zone_name;
	    $vcard->addAddress(null, null, $company->address_1, $company->city, $zone, $company->zip_code, $company->country->name);

	    if (!empty($company->website)) {
		    $vcard->addURL($company->website);
	    }

	    return $vcard->download();
    }

    /**
     * @param Controller $controller
     * @param CampaignTrackOpen $track
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @throws CException
     */
    public function _openActionChangeSubscriberListField(Controller $controller, CampaignTrackOpen $track, Campaign $campaign, ListSubscriber $subscriber)
    {
        $models = CampaignOpenActionListField::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));

        if (empty($models)) {
            return;
        }

        foreach ($models as $model) {
            $valueModel = ListFieldValue::model()->findByAttributes(array(
                'field_id'      => $model->field_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            if (empty($valueModel)) {
                $valueModel = new ListFieldValue();
                $valueModel->field_id       = $model->field_id;
                $valueModel->subscriber_id  = $subscriber->subscriber_id;
            }
            
            $valueModel->value = $model->getParsedFieldValueByListFieldValue(new CAttributeCollection(array(
                'valueModel' => $valueModel,
                'campaign'   => $campaign,
                'subscriber' => $subscriber,
                'trackOpen'  => $track
            )));
            $valueModel->save();
        }
    }

    /**
     * @param Controller $controller
     * @param CampaignTrackOpen $track
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @throws CDbException
     * @throws CException
     */
    public function _openActionAgainstSubscriber(Controller $controller, CampaignTrackOpen $track, Campaign $campaign, ListSubscriber $subscriber)
    {
        $models = CampaignOpenActionSubscriber::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
        ));

        if (empty($models)) {
            return;
        }
        
        foreach ($models as $model) {
            if ($model->action == CampaignOpenActionSubscriber::ACTION_MOVE) {
                $subscriber->moveToList($model->list_id, false, true);
            } else {
                $subscriber->copyToList($model->list_id, false, true);
            }
        }
    }

	/**
	 * @param Controller $controller
	 * @param CampaignTrackOpen $track
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * 
	 * @since 1.6.8
	 */
    public function _openCreateWebhookRequest(Controller $controller, CampaignTrackOpen $track, Campaign $campaign, ListSubscriber $subscriber)
    {
	    $models = CampaignTrackOpenWebhook::model()->findAllByAttributes(array(
		    'campaign_id' => $campaign->campaign_id,
	    ));

	    if (empty($models)) {
		    return;
	    }

	    foreach ($models as $model) {
		    $request = new CampaignTrackOpenWebhookQueue();
		    $request->webhook_id    = $model->webhook_id;
		    $request->track_open_id = $track->id;
		    $request->save(false);
	    }
    }

    /**
     * @param Controller $controller
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @param CampaignUrl $url
     * @throws CException
     */
    public function _urlActionChangeSubscriberListField(Controller $controller, Campaign $campaign, ListSubscriber $subscriber, CampaignUrl $url)
    {
        $models = CampaignTemplateUrlActionListField::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
            'url'         => $url->destination,
        ));

        if (empty($models)) {
            return;
        }

        foreach ($models as $model) {
            $valueModel = ListFieldValue::model()->findByAttributes(array(
                'field_id'      => $model->field_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            if (empty($valueModel)) {
                $valueModel = new ListFieldValue();
                $valueModel->field_id       = $model->field_id;
                $valueModel->subscriber_id  = $subscriber->subscriber_id;
            }
            
            $valueModel->value = $model->getParsedFieldValueByListFieldValue(new CAttributeCollection(array(
                'valueModel' => $valueModel,
                'campaign'   => $campaign,
                'subscriber' => $subscriber,
                'url'        => $url,
            )));
            $valueModel->save();
        }
    }

    /**
     * @param Controller $controller
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @param CampaignUrl $url
     * @throws CDbException
     * @throws CException
     */
    public function _urlActionAgainstSubscriber(Controller $controller, Campaign $campaign, ListSubscriber $subscriber, CampaignUrl $url)
    {
        $models = CampaignTemplateUrlActionSubscriber::model()->findAllByAttributes(array(
            'campaign_id' => $campaign->campaign_id,
            'url'         => $url->destination,
        ));
        
        if (empty($models)) {
            return;
        }
        
        foreach ($models as $model) {
            if ($model->action == CampaignOpenActionSubscriber::ACTION_MOVE) {
                $subscriber->moveToList($model->list_id, false, true);
            } else {
                $subscriber->copyToList($model->list_id, false, true);
            }
        }
    }

	/**
	 * @param Controller $controller
	 * @param CampaignTrackUrl $track
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * 
	 * @since 1.6.8
	 */
    public function _urlCreateWebhookRequest(Controller $controller, CampaignTrackUrl $track, Campaign $campaign, ListSubscriber $subscriber)
    {
	    $models = CampaignTrackUrlWebhook::model()->findAllByAttributes(array(
		    'campaign_id'       => $campaign->campaign_id,
		    'track_url_hash'    => $track->url->hash,
	    ));

	    if (empty($models)) {
		    return;
	    }

	    foreach ($models as $model) {
		    $request = new CampaignTrackUrlWebhookQueue();
		    $request->webhook_id    = $model->webhook_id;
		    $request->track_url_id  = $track->id;
		    $request->save(false);
	    }
    }

    /**
     * @param $canTrack
     * @param $controller
     * @param $campaign
     * @return bool
     */
    public function _actionCanTrackOpening($canTrack, $controller, $campaign)
    {
        $ipAddress    = Yii::app()->request->getUserHostAddress();
        $dontTrackIps = Yii::app()->options->get('system.campaign.exclude_ips_from_tracking.open', '');
        if (empty($dontTrackIps)) {
            return $canTrack;
        }
        $dontTrackIps = explode(',', $dontTrackIps);
        $dontTrackIps = array_unique(array_map('trim', $dontTrackIps));
        if (empty($dontTrackIps)) {
            return $canTrack;
        }
        return $canTrack = !IpHelper::isIpInRange($ipAddress, $dontTrackIps);
    }

    /**
     * @param $canTrack
     * @param $controller
     * @param $campaign
     * @return bool
     */
    public function _actionCanTrackUrl($canTrack, $controller, $campaign)
    {
        $ipAddress    = Yii::app()->request->getUserHostAddress();
        $dontTrackIps = Yii::app()->options->get('system.campaign.exclude_ips_from_tracking.url', '');
        if (empty($dontTrackIps)) {
            return $canTrack;
        }
        $dontTrackIps = explode(',', $dontTrackIps);
        $dontTrackIps = array_unique(array_map('trim', $dontTrackIps));
        if (empty($dontTrackIps)) {
            return $canTrack;
        }
        return $canTrack = !IpHelper::isIpInRange($ipAddress, $dontTrackIps);
    }

    /**
     * Helper method to load the campaign AR model
     */
    public function loadCampaignModel($campaign_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.campaign_uid', $campaign_uid);
        $statuses = array(
            Campaign::STATUS_DRAFT, Campaign::STATUS_PENDING_DELETE, Campaign::STATUS_PENDING_SENDING,
        );
        $criteria->addNotInCondition('t.status', $statuses);

        $model = Campaign::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
