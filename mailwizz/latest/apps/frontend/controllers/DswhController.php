<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DswhController
 *
 * Delivery Servers Web Hooks (DSWH) handler
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 */

class DswhController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        parent::init();
        
        /* because posting too fast sometimes can lead to dupes */
        usleep(rand(100000, 3000000)); // 0.1 => 3 sec
    }

    /**
     * Entry point
     * @param $id
     */
    public function actionIndex($id)
    {
        $server = DeliveryServer::model()->findByPk((int)$id);
        
        if (empty($server)) {
            Yii::app()->end();
        }
        
        $map = array(
            'mandrill-web-api'     => array($this, 'processMandrill'),
            'amazon-ses-web-api'   => array($this, 'processAmazonSes'),
            'mailgun-web-api'      => array($this, 'processMailgun'),
            'sendgrid-web-api'     => array($this, 'processSendgrid'),
            'leadersend-web-api'   => array($this, 'processLeadersend'),
            'elasticemail-web-api' => array($this, 'processElasticemail'),
            'dyn-web-api'          => array($this, 'processDyn'),
            'sparkpost-web-api'    => array($this, 'processSparkpost'),
            'mailjet-web-api'      => array($this, 'processMailjet'),
            'sendinblue-web-api'   => array($this, 'processSendinblue'),
            'tipimail-web-api'     => array($this, 'processTipimail'),
            'pepipost-web-api'     => array($this, 'processPepipost'),
            'postmark-web-api'     => array($this, 'processPostmark')
        );

        $map = (array)Yii::app()->hooks->applyFilters('dswh_process_map', $map, $server, $this);
        if (isset($map[$server->type]) && is_callable($map[$server->type])) {
            call_user_func_array($map[$server->type], array($server, $this));
        }

        Yii::app()->end();
    }

    /**
     * Process DRH's GreenArrow
     */
    public function actionDrh()
    {
        $request = Yii::app()->request;
        if (!count($request->getPost(null))) {
            Yii::app()->end();
        }

        $event = $request->getPost('event_type');
        
        // header name: X-GreenArrow-Click-Tracking-ID
        // header value: [CAMPAIGN_UID]|[SUBSCRIBER_UID]
        $cs = explode('|', $request->getPost('click_tracking_id'));

        if (empty($event) || empty($cs) || count($cs) != 2) {
            $this->end('OK');
        }

        list($campaignUid, $subscriberUid) = $cs;

        $campaign = Campaign::model()->findByAttributes(array(
            'campaign_uid' => $campaignUid
        ));
        if (empty($campaign)) {
            $this->end('OK');
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_uid'   => $subscriberUid,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            $this->end('OK');
        }
        
        if (stripos($event, 'bounce') !== false) {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (!empty($count)) {
                $this->end('OK');
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id   = $campaign->campaign_id;
            $bounceLog->subscriber_id = $subscriber->subscriber_id;
            $bounceLog->message       = $request->getPost('bounce_text');
            $bounceLog->bounce_type   = $request->getPost('bounce_type') == 'h' ? CampaignBounceLog::BOUNCE_HARD : CampaignBounceLog::BOUNCE_SOFT;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }

            $this->end('OK');
        }

        if ($event == 'scomp') {
            
            if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                $this->end('OK');
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Abuse complaint!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }
            
            // since 1.4.4 - complaints go into their own tables
            $count = CampaignComplainLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (empty($count)) {
                $complaintLog = new CampaignComplainLog();
                $complaintLog->campaign_id = $campaign->campaign_id;
                $complaintLog->subscriber_id = $subscriber->subscriber_id;
                $complaintLog->message = 'Abuse complaint via DRH!';
                $complaintLog->save(false);
            }
            //
            
            $this->end('OK');
        }

	    if ($event == 'engine_unsub') {
	    	
		    $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

		    $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
			    'campaign_id'   => $campaign->campaign_id,
			    'subscriber_id' => $subscriber->subscriber_id,
		    ));

		    if (empty($count)) {
			    $trackUnsubscribe = new CampaignTrackUnsubscribe();
			    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
			    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
			    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
			    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
			    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
			    $trackUnsubscribe->save(false);
		    }

		    $this->end('OK');
	    }

        $this->end('OK');
    }

    /**
     * Process Postal
     */
    public function actionPostal()
    {
        $event = file_get_contents("php://input");
        if (empty($event)) {
            Yii::app()->end();
        }
        $event = CJSON::decode($event);

        if (empty($event) || !is_array($event)) {
            $event = array();
        }
        
        if (in_array($event['event'], array('MessageDeliveryFailed', 'MessageDelayed', 'MessageHeld'))) {
            
            $messageId = isset($event['payload']['message']['message_id']) ? $event['payload']['message']['message_id'] : '';
            if (!$messageId) {
                Yii::app()->end();
            }
            
            $criteria = new CDbCriteria();
            $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
            $criteria->params = array(
	            'email_message_id' => (string)$messageId,
	            'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
            );
            
            $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
            if (empty($deliveryLog)) {
                $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
            }
            
            if (empty($deliveryLog)) {
                Yii::app()->end();
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                Yii::app()->end();
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                Yii::app()->end();
            }

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }

            $message    = !empty($event['payload']['details']) ? $event['payload']['details'] : 'BOUNCED BACK';
            $bounceType = CampaignBounceLog::BOUNCE_INTERNAL;
            
            if (!empty($event['payload']['status'])) {
                if (stripos($event['payload']['status'], 'hard') !== false) {
                    $bounceType = CampaignBounceLog::BOUNCE_HARD;
                } elseif (stripos($event['payload']['status'], 'soft') !== false) {
                    $bounceType = CampaignBounceLog::BOUNCE_SOFT;
                }
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = $message;
            $bounceLog->bounce_type     = $bounceType;
            $bounceLog->save();

            if ($bounceLog->bounce_type === CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }
            
            Yii::app()->end();
        }
        
        if ($event['event'] == 'MessageBounced') {
            
            $messageId = !empty($event['payload']['original_message']['message_id']) ? $event['payload']['original_message']['message_id'] : '';
            if (!$messageId) {
                Yii::app()->end();
            }

	        $criteria = new CDbCriteria();
	        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	        $criteria->params = array(
		        'email_message_id' => (string)$messageId,
		        'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	        );

	        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	        if (empty($deliveryLog)) {
		        $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	        }

            if (empty($deliveryLog)) {
                Yii::app()->end();
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                Yii::app()->end();
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                Yii::app()->end();
            }

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }
            
            // it still unclear how we should handle these
            // https://github.com/atech/postal/issues/253
            $message    = 'BOUNCED BACK';
            $bounceType = CampaignBounceLog::BOUNCE_INTERNAL;
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = $message;
            $bounceLog->bounce_type     = $bounceType;
            $bounceLog->save();

            Yii::app()->end();
        }
    }

    /**
     * Process Postmastery
     */
    public function actionPostmastery()
    {
        $events = file_get_contents("php://input");
        if (empty($events)) {
            Yii::app()->end();
        }

        $events = CJSON::decode($events);

        if (empty($events) || !is_array($events)) {
            Yii::app()->end();
        }

        foreach ($events as $event) {

            if (empty($event['type']) || empty($event['header_Message-Id'])) {
                continue;
            }
            
            $event['header_Message-Id'] = str_replace(array('<', '>'), '', $event['header_Message-Id']);
			$messageId = $event['header_Message-Id'];

	        $criteria = new CDbCriteria();
	        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	        $criteria->params = array(
		        'email_message_id' => (string)$messageId,
		        'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	        );

	        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	        if (empty($deliveryLog)) {
		        $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	        }

            if (empty($deliveryLog)) {
                continue;
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }

            // bounces
            if (in_array($event['type'], array('b', 'rb', 'rs')) && !empty($event['bounceCat'])) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }

                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id    = $campaign->campaign_id;
                $bounceLog->subscriber_id  = $subscriber->subscriber_id;
                $bounceLog->message        = !empty($event['dsnDiag']) ? $event['dsnDiag'] : 'BOUNCED BACK';
                $bounceLog->bounce_type    = CampaignBounceLog::BOUNCE_INTERNAL;

                if (in_array($event['bounceCat'], array('bad-mailbox', 'inactive-mailbox', 'bad-domain'))) {
                    $bounceLog->bounce_type = CampaignBounceLog::BOUNCE_HARD;
                } elseif (in_array($event['bounceCat'], array('quota-issues', 'no-answer-from-host', 'relaying-issues', 'routing-errors'))) {
                    $bounceLog->bounce_type = CampaignBounceLog::BOUNCE_SOFT;
                }

                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }

                continue;
            }

            // FBL 
            if (in_array($event['type'], array('f'))) {

                if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Abuse complaint!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }

                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id   = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message       = 'Abuse complaint via Postmastery!';
                    $complaintLog->save(false);
                }

                continue;
            }
        }
    }
    
    /**
     * Process NewsMan
     */
    public function actionNewsman()
    {
        $events = Yii::app()->request->getPost('newsman_events');
        if (empty($events)) {
            Yii::app()->end();
        }
        $events = CJSON::decode($events);

        if (empty($events) || !is_array($events)) {
            $events = array();
        }
        
        foreach ($events as $event) {
            
            $messageId = !empty($event['data']['send_id']) ? $event['data']['send_id'] : '';
            if (empty($messageId)) {
                continue;
            }

	        $criteria = new CDbCriteria();
	        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	        $criteria->params = array(
		        'email_message_id' => (string)$messageId,
		        'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	        );

	        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	        if (empty($deliveryLog)) {
		        $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	        }
	        
            if (empty($deliveryLog)) {
                continue;
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }
            
            if (in_array($event['type'], array('spam', 'unsub'))) {

                if ($event['type'] == 'spam' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }

                // since 1.4.4 - complaints go into their own tables
                if ($event['type'] == 'spam') {

                    $count = CampaignComplainLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));

                    if (empty($count)) {
                        $complaintLog = new CampaignComplainLog();
                        $complaintLog->campaign_id   = $campaign->campaign_id;
                        $complaintLog->subscriber_id = $subscriber->subscriber_id;
                        $complaintLog->message       = 'Abuse complaint via NewsMan!';
                        $complaintLog->save(false);
                    }
                }

                continue;
            }
            
            if (in_array($event['type'], array('bounce', 'reject'))) {
                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }

                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id   = $campaign->campaign_id;
                $bounceLog->subscriber_id = $subscriber->subscriber_id;
                $bounceLog->bounce_type   = CampaignBounceLog::BOUNCE_INTERNAL;
                $bounceLog->message       = !empty($event['data']['meta']['subject']) ? $event['data']['meta']['subject'] : 'BOUNCED BACK';
                
                if ($event['type'] == 'reject') {
                    $bounceLog->save();
                    continue;
                }
                
                if (strpos($event['data']['meta']['reason'], 'soft') !== false) {
                    $bounceLog->bounce_type = CampaignBounceLog::BOUNCE_SOFT;
                } else {
                    $bounceLog->bounce_type = CampaignBounceLog::BOUNCE_HARD;
                }

                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }
                
                continue;
            }
        }
    }

    /**
     * Process mandrill
     */
    public function processMandrill()
    {
        if (!MW_COMPOSER_SUPPORT) {
            Yii::app()->end();
        }

        $request = Yii::app()->request;
        $mandrillEvents = $request->getPost('mandrill_events');

        if (empty($mandrillEvents)) {
            Yii::app()->end();
        }

        $mandrillEvents = CJSON::decode($mandrillEvents);
        if (empty($mandrillEvents) || !is_array($mandrillEvents)) {
            $mandrillEvents = array();
        }

        foreach ($mandrillEvents as $evt) {
            
            if (!empty($evt['type']) && $evt['type'] == 'blacklist' && !empty($evt['action']) && $evt['action'] == 'add') {
                if (!empty($evt['reject']['email'])) {
                    EmailBlacklist::addToBlacklist($evt['reject']['email'], (!empty($evt['reject']['detail']) ? $evt['reject']['detail'] : null));
                }
                continue;
            }

            if (empty($evt['msg']) || !is_array($evt['msg'])) {
                continue;
            }

            $msgData = $evt['msg'];
            $event   = !empty($evt['event']) ? $evt['event'] : null;

            $globalMetaData    = !empty($msgData['metadata']) && is_array($msgData['metadata']) ? $msgData['metadata'] : array();
            $recipientMetaData = !empty($msgData['recipient_metadata']) && is_array($msgData['recipient_metadata']) ? $msgData['recipient_metadata'] : array();
            $metaData          = array_merge($globalMetaData, $recipientMetaData);

            if (empty($metaData['campaign_uid']) || empty($metaData['subscriber_uid'])) {
                continue;
            }

            $campaignUid   = trim($metaData['campaign_uid']);
            $subscriberUid = trim($metaData['subscriber_uid']);

            $campaign = Campaign::model()->findByUid($campaignUid);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'           => $campaign->list_id,
                'subscriber_uid'    => $subscriberUid,
                'status'            => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }
            
            $returnReason = array();
            if (!empty($msgData['diag'])) {
                $returnReason[] = $msgData['diag'];
            }
            if (!empty($msgData['bounce_description'])) {
                $returnReason[] = $msgData['bounce_description'];
            }
            $returnReason = implode(" ", $returnReason);

            if (in_array($event, array('hard_bounce', 'soft_bounce'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));
                
                if (!empty($count)) {
                    continue;
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id     = $campaign->campaign_id;
                $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                $bounceLog->message         = $returnReason;
                $bounceLog->bounce_type     = $event == 'soft_bounce' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }

                continue;
            }

            if (in_array($event, array('reject', 'blacklist'))) {
                $subscriber->addToBlacklist($returnReason);
                continue;
            }

            if (in_array($event, array('spam', 'unsub'))) {
                
                if ($event == 'spam' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));
                
                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);    
                }
                
                // since 1.4.4 - complaints go into their own tables
                if ($event == 'spam') {
                    
                    $count = CampaignComplainLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));
                    
                    if (empty($count)) {
                        $complaintLog = new CampaignComplainLog();
                        $complaintLog->campaign_id   = $campaign->campaign_id;
                        $complaintLog->subscriber_id = $subscriber->subscriber_id;
                        $complaintLog->message       = 'Abuse complaint via Mandrill!';
                        $complaintLog->save(false);
                    }
                }

                continue;
            }
        }

        Yii::app()->end();
    }

    /**
     * Process Amazon SES
     */
    public function processAmazonSes($server)
    {
        if (!version_compare(PHP_VERSION, '5.5', '>=')) {
            Yii::app()->end();
        }
        
        $message   = call_user_func(array('\Aws\Sns\Message', 'fromRawPostData'));
        $className = '\Aws\Sns\MessageValidator';
        $validator = new $className(array($this, '_amazonFetchRemote'));
        try {
            $validator->validate($message);
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            Yii::app()->end();
        }
        
        if ($message['Type'] === 'SubscriptionConfirmation') {
            
            try {

                $types  = DeliveryServer::getTypesMapping();
                $type   = $types[$server->type];
                $server = DeliveryServer::model($type)->findByPk((int)$server->server_id);
                $result = $server->getSnsClient()->confirmSubscription(array(
                    'TopicArn'  => $message['TopicArn'],
                    'Token'     => $message['Token'],
                ));
                if (stripos($result->get('SubscriptionArn'), 'pending') === false) {
                    $server->subscription_arn = $result->get('SubscriptionArn');
                    $server->save(false);
                }
                Yii::app()->end();

            } catch (Exception $e) {}
            
            $className = '\Guzzle\Http\Client';
            $client    = new $className();
            $client->get($message['SubscribeURL'])->send();
            Yii::app()->end();
        }

        if ($message['Type'] !== 'Notification') {
            Yii::app()->end();
        }

        $data = new CMap((array)CJSON::decode($message['Message']));
        if (!$data->itemAt('notificationType') || $data->itemAt('notificationType') == 'AmazonSnsSubscriptionSucceeded' || !$data->itemAt('mail')) {
            Yii::app()->end();
        }

        $mailMessage = $data->itemAt('mail');
        if (empty($mailMessage['messageId'])) {
            Yii::app()->end();
        }
		$messageId = $mailMessage['messageId'];

	    $criteria = new CDbCriteria();
	    $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	    $criteria->params = array(
		    'email_message_id' => (string)$messageId,
		    'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	    );

	    $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	    if (empty($deliveryLog)) {
		    $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	    }

        if (empty($deliveryLog)) {
            Yii::app()->end();
        }

        $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_id'    => $deliveryLog->subscriber_id,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }

        if ($data->itemAt('notificationType') == 'Bounce' && ($bounce = $data->itemAt('bounce'))) {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (!empty($count)) {
                Yii::app()->end();
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = !empty($bounce['bouncedRecipients'][0]['diagnosticCode']) ? $bounce['bouncedRecipients'][0]['diagnosticCode'] : 'BOUNCED BACK';
            $bounceLog->bounce_type     = $bounce['bounceType'] !== 'Permanent' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            if ($bounceLog->bounce_type === CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }
            Yii::app()->end();
        }

        if ($data->itemAt('notificationType') == 'Complaint' && ($complaint = $data->itemAt('complaint'))) {
            
            if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
            
            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Abuse complaint!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }

            // since 1.4.4 - complaints go into their own tables
            $count = CampaignComplainLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (empty($count)) {
                $complaintLog = new CampaignComplainLog();
                $complaintLog->campaign_id   = $campaign->campaign_id;
                $complaintLog->subscriber_id = $subscriber->subscriber_id;
                $complaintLog->message       = 'Abuse complaint via Amazon SES!';
                $complaintLog->save(false);
            }
            //

            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Helper for \Aws\Sns\MessageValidator because otherwise it uses file_get_contents to fetch remote data
     * and this might be disabled oin many hosts
     * 
     * @param $url
     * @return string
     */
    public function _amazonFetchRemote($url) 
    {
        $content = AppInitHelper::simpleCurlGet($url);
        return !empty($content['message']) ? $content['message'] : '';
    }

    /**
     * Process Mailgun
     */
    public function processMailgun()
    {
        if (!version_compare(PHP_VERSION, '5.5', '>=')) {
            Yii::app()->end();
        }

        $request  = Yii::app()->request;
        $event    = $request->getPost('event');
        $metaData = $request->getPost('metadata');

        if (empty($metaData) || empty($event)) {
            Yii::app()->end();
        }

        $metaData = CJSON::decode($metaData);
        if (empty($metaData['campaign_uid']) || empty($metaData['subscriber_uid'])) {
            Yii::app()->end();
        }

        $campaign = Campaign::model()->findByAttributes(array(
            'campaign_uid' => $metaData['campaign_uid']
        ));
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_uid'   => $metaData['subscriber_uid'],
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }

        if ($event == 'bounced') {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }

            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id   = $campaign->campaign_id;
            $bounceLog->subscriber_id = $subscriber->subscriber_id;
            $bounceLog->message       = $request->getPost('notification', $request->getPost('error', $request->getPost('code', '')));
            $bounceLog->bounce_type   = CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            $subscriber->addToBlacklist($bounceLog->message);

            Yii::app()->end();
        }

        if ($event == 'dropped') {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }

            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id   = $campaign->campaign_id;
            $bounceLog->subscriber_id = $subscriber->subscriber_id;
            $bounceLog->message       = $request->getPost('description', $request->getPost('error', $request->getPost('reason', '')));
            $bounceLog->bounce_type   = $request->getPost('reason') != 'hardfail' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }

            Yii::app()->end();
        }

        if ($event == 'complained') {
            
            if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
            
            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));
            
            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Abuse complaint!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }

            // since 1.4.4 - complaints go into their own tables
            $count = CampaignComplainLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $complaintLog = new CampaignComplainLog();
                $complaintLog->campaign_id   = $campaign->campaign_id;
                $complaintLog->subscriber_id = $subscriber->subscriber_id;
                $complaintLog->message       = 'Abuse complaint via Mailgun!';
                $complaintLog->save(false);
            }
            //

            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Process Sendgrid
     */
    public function processSendgrid()
    {
        if (!version_compare(PHP_VERSION, '5.6', '>=')) {
            Yii::app()->end();
        }

        $events = file_get_contents("php://input");
        if (empty($events)) {
            Yii::app()->end();
        }

        $events = CJSON::decode($events);
        if (empty($events) || !is_array($events)) {
            $events = array();
        }

        foreach ($events as $evt) {
            
            if (empty($evt['event']) || !in_array($evt['event'], array('dropped' , 'bounce', 'spamreport'))) {
                continue;
            }

            if (empty($evt['campaign_uid']) || empty($evt['subscriber_uid'])) {
                continue;
            }

            $campaignUid   = trim($evt['campaign_uid']);
            $subscriberUid = trim($evt['subscriber_uid']);

            $campaign = Campaign::model()->findByUid($campaignUid);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'           => $campaign->list_id,
                'subscriber_uid'    => $subscriberUid,
                'status'            => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }
            
            // https://sendgrid.com/docs/API_Reference/Webhooks/event.html
            if (in_array($evt['event'], array('dropped'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));
                
                if (!empty($count)) {
                    continue;
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id   = $campaign->campaign_id;
                $bounceLog->subscriber_id = $subscriber->subscriber_id;
                $bounceLog->message       = !empty($evt['reason']) ? $evt['reason'] : $evt['event'];
                $bounceLog->message       = !empty($bounceLog->message) ? $bounceLog->message : 'Internal Bounce';
                $bounceLog->bounce_type   = CampaignBounceLog::BOUNCE_INTERNAL;
                $bounceLog->save();
                
                continue;
            }

            if (in_array($evt['event'], array('bounce'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id   = $campaign->campaign_id;
                $bounceLog->subscriber_id = $subscriber->subscriber_id;
                $bounceLog->message       = isset($evt['reason']) ? $evt['reason'] : 'BOUNCED BACK';
                $bounceLog->bounce_type   = CampaignBounceLog::BOUNCE_HARD;
                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }

                continue;
            }

            if (in_array($evt['event'], array('spamreport'))) {
                
                if ($evt['event'] == 'spamreport' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }
                
                // since 1.4.4 - complaints go into their own tables
                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id   = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message       = 'Abuse complaint via SendGrid!';
                    $complaintLog->save(false);
                }
                //

                continue;
            }
        }

        Yii::app()->end();
    }

    /**
     * Process LeaderSend
     */
    public function processLeadersend()
    {
        $request = Yii::app()->request;
        $events  = $request->getPost('leadersend_events');

        if (empty($events)) {
            Yii::app()->end();
        }

        $events = CJSON::decode($events);
        if (empty($events) || !is_array($events)) {
            $events = array();
        }

        foreach ($events as $evt) {
            if (empty($evt['msg']) || empty($evt['msg']['id'])) {
                continue;
            }
            
            if (empty($evt['event']) || !in_array($evt['event'], array('spam', 'soft_bounce', 'hard_bounce', 'reject'))) {
                continue;
            }

            $messageId = $evt['msg']['id'];

	        $criteria = new CDbCriteria();
	        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	        $criteria->params = array(
		        'email_message_id' => (string)$messageId,
		        'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	        );

	        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	        if (empty($deliveryLog)) {
		        $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	        }
	        
            if (empty($deliveryLog)) {
                continue;
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }

            if (in_array($evt['event'], array('soft_bounce', 'hard_bounce'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id     = $campaign->campaign_id;
                $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                $bounceLog->message         = !empty($evt['msg']['delivery_report']) ? $evt['msg']['delivery_report'] : 'BOUNCED BACK';
                $bounceLog->bounce_type     = $evt['event'] == 'soft_bounce' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }
                continue;
            }

            if ($evt['event'] == 'spam') {
                
                if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }

                // since 1.4.4 - complaints go into their own tables
                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id   = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message       = 'Abuse complaint via Leadersend!';
                    $complaintLog->save(false);
                }
                //

                continue;
            }

            if ($evt['event'] == 'reject') {
                $subscriber->addToBlacklist(!empty($evt['msg']['delivery_report']) ? $evt['msg']['delivery_report'] : 'BOUNCED BACK');
                continue;
            }
        }

        Yii::app()->end();
    }

    /**
     * Process EE
     */
    public function processElasticemail()
    {
        $request     = Yii::app()->request;
        $category    = trim($request->getQuery('category'));
        $messageId   = trim($request->getQuery('messageid'));
        $status      = trim($request->getQuery('status'));

        if (empty($messageId) || empty($category)) {
            Yii::app()->end();
        }

	    $criteria = new CDbCriteria();
	    $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	    $criteria->params = array(
		    'email_message_id' => (string)$messageId,
		    'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	    );

	    $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	    if (empty($deliveryLog)) {
		    $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	    }

        if (empty($deliveryLog)) {
            Yii::app()->end();
        }

        $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_id'    => $deliveryLog->subscriber_id,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }
        
        // All categories:
        // https://elasticemail.com/support/delivery/http-web-notification
        if ($status == 'AbuseReport') {
            
            if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }
            
            // since 1.4.4 - complaints go into their own tables
            $count = CampaignComplainLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $complaintLog = new CampaignComplainLog();
                $complaintLog->campaign_id   = $campaign->campaign_id;
                $complaintLog->subscriber_id = $subscriber->subscriber_id;
                $complaintLog->message       = 'Abuse complaint via ElasticEmail!';
                $complaintLog->save(false);
            }
            //

            Yii::app()->end();
        }
        
        if ($status == 'Unsubscribed') {
            
            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }

            $trackUnsubscribe = new CampaignTrackUnsubscribe();
            $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
            $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
            $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
            $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
            $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
            $trackUnsubscribe->save(false);

            Yii::app()->end();
        }
        
        if ($status == 'Error') {

            $categoryID           = strtolower($category);
            $hardBounceCategories = array('NoMailbox', 'AccountProblem');
            $hardBounceCategories = array_map('strtolower', $hardBounceCategories);
            
            $bounceType = null;

            if (in_array($categoryID, $hardBounceCategories)) {
                $bounceType = CampaignBounceLog::BOUNCE_HARD;
            } else {
                $bounceType = CampaignBounceLog::BOUNCE_SOFT;
            }

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }

            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = $category;
            $bounceLog->bounce_type     = $bounceType;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }

            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Process DynEmail
     */
    public function processDyn($server)
    {
        $request    = Yii::app()->request;
        $event      = $request->getQuery('event');
        $bounceRule = $request->getQuery('rule', $request->getQuery('bouncerule')); // bounce rule
        $bounceType = $request->getQuery('type', $request->getQuery('bouncetype')); // bounce type
        $campaign   = $request->getQuery('campaign'); // campaign uid
        $subscriber = $request->getQuery('subscriber'); // subscriber uid

        $allowedEvents = array('bounce', 'complaint', 'unsubscribe');
        if (!in_array($event, $allowedEvents)) {
            Yii::app()->end();
        }

        $campaign = Campaign::model()->findByUid($campaign);
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_uid'   => $subscriber,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }
        
        if ($event == 'bounce') {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = $bounceRule;
            $bounceLog->bounce_type     = $bounceType == 'soft' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }
            Yii::app()->end();
        }
        
        /* remove from suppression list. */
        if ($event == 'complaint') {
            $url = sprintf('https://api.email.dynect.net/rest/json/suppressions/activate?apikey=%s&emailaddress=%s', $server->password, urlencode($subscriber->email));
            AppInitHelper::simpleCurlPost($url, array(), 5);
        }

        if (in_array($event, array('complaint', 'unsubscribe'))) {
            
            if (Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }
            
            // since 1.4.4 - complaints go into their own tables
            if ($event == 'complaint') {
                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id' => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message = 'Abuse complaint via DynEmail!';
                    $complaintLog->save(false);
                }
            }
            //

            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Process Sparkpost
     */
    public function processSparkpost()
    {
        $events = file_get_contents("php://input");
        if (empty($events)) {
            Yii::app()->end();
        }
        $events = CJSON::decode($events);

        if (empty($events) || !is_array($events)) {
            $events = array();
        }
  
        foreach ($events as $evt) {
            
            if (empty($evt['msys']['message_event'])) {
                continue;
            }
            $evt = $evt['msys']['message_event'];
            if (empty($evt['type']) || !in_array($evt['type'], array('bounce', 'spam_complaint', 'list_unsubscribe', 'link_unsubscribe'))) {
                continue;
            }

            if (empty($evt['rcpt_meta']) || empty($evt['rcpt_meta']['campaign_uid']) || empty($evt['rcpt_meta']['subscriber_uid'])) {
                continue;
            }

            $campaign = Campaign::model()->findByUid($evt['rcpt_meta']['campaign_uid']);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_uid'   => $evt['rcpt_meta']['subscriber_uid'],
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }

            if (in_array($evt['type'], array('bounce'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }
                
                // https://support.sparkpost.com/customer/portal/articles/1929896-bounce-classification-codes
                $bounceType = CampaignBounceLog::BOUNCE_INTERNAL;
                if (in_array($evt['bounce_class'], array(10, 30, 90))) {
                    $bounceType = CampaignBounceLog::BOUNCE_HARD;
                } elseif (in_array($evt['bounce_class'], array(20, 40, 60))) {
                    $bounceType = CampaignBounceLog::BOUNCE_SOFT;
                }
                
                $defaultBounceMessage = 'BOUNCED BACK';
                if ($bounceType == CampaignBounceLog::BOUNCE_INTERNAL) {
                    $defaultBounceMessage = 'Internal Bounce';
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id     = $campaign->campaign_id;
                $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                $bounceLog->message         = !empty($evt['reason']) ? $evt['reason'] : $defaultBounceMessage;
                $bounceLog->bounce_type     = $bounceType;
                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }

                continue;
            }

            if (in_array($evt['type'], array('spam_complaint', 'list_unsubscribe', 'link_unsubscribe'))) {
                
                if ($evt['type'] == 'spam_complaint' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }

                // since 1.4.4 - complaints go into their own tables
                if ($evt['type'] == 'spam_complaint') {
                    $count = CampaignComplainLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));

                    if (empty($count)) {
                        $complaintLog = new CampaignComplainLog();
                        $complaintLog->campaign_id = $campaign->campaign_id;
                        $complaintLog->subscriber_id = $subscriber->subscriber_id;
                        $complaintLog->message = 'Abuse complaint via Sparkpost!';
                        $complaintLog->save(false);
                    }
                }
                //

                continue;
            }
        }

        Yii::app()->end();
    }

    /**
     * Process Pepipost
     * @throws CDbException
     */
    public function processPepipost()
    {
        $events = file_get_contents("php://input");
        if (empty($events)) {
            Yii::app()->end();
        }
        $events = CJSON::decode($events);

        if (empty($events) || !is_array($events)) {
            $events = array();
        }

        foreach ($events as $evt) {

            if (empty($evt['TRANSID']) || empty($evt['X-APIHEADER']) || empty($evt['EVENT'])) {
                continue;
            }
            
            if (!in_array($evt['EVENT'], array('bounced', 'unsubscribed', 'spam'))) {
                continue;
            }
           
            $metaData = CJSON::decode(trim(str_replace('\"', '"', $evt['X-APIHEADER']), '"'));
            if (empty($metaData['campaign_uid']) || empty($metaData['subscriber_uid'])) {
                continue;
            }

            $campaign = Campaign::model()->findByUid($metaData['campaign_uid']);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_uid'   => $metaData['subscriber_uid'],
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }

            if (in_array($evt['EVENT'], array('bounced'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }

                $bounceType = CampaignBounceLog::BOUNCE_INTERNAL;
                if ($evt['BOUNCE_TYPE'] == 'HARDBOUNCE') {
                    $bounceType = CampaignBounceLog::BOUNCE_HARD;
                } elseif ($evt['BOUNCE_TYPE'] == 'SOFTBOUNCE') {
                    $bounceType = CampaignBounceLog::BOUNCE_SOFT;
                }

                $defaultBounceMessage = 'BOUNCED BACK';
                if ($bounceType == CampaignBounceLog::BOUNCE_INTERNAL) {
                    $defaultBounceMessage = 'Internal Bounce';
                }

                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id     = $campaign->campaign_id;
                $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                $bounceLog->message         = !empty($evt['BOUNCE_REASON']) ? $evt['BOUNCE_REASON'] : $defaultBounceMessage;
                $bounceLog->bounce_type     = $bounceType;
                $bounceLog->save();

                if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }

                continue;
            }

            if (in_array($evt['EVENT'], array('spam', 'unsubscribe'))) {

                if ($evt['EVENT'] == 'spam' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }

                // since 1.4.4 - complaints go into their own tables
                if ($evt['EVENT'] == 'spam') {
                    $count = CampaignComplainLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));

                    if (empty($count)) {
                        $complaintLog = new CampaignComplainLog();
                        $complaintLog->campaign_id   = $campaign->campaign_id;
                        $complaintLog->subscriber_id = $subscriber->subscriber_id;
                        $complaintLog->message       = 'Abuse complaint via PepiPost!';
                        $complaintLog->save(false);
                    }
                }
                //

                continue;
            }
        }

        Yii::app()->end();
    }
    
    /**
     * Process MailJet
     */
    public function processMailjet()
    {
        $events = file_get_contents("php://input");
        if (empty($events)) {
            Yii::app()->end();
        }
        
        $events = CJSON::decode($events);
        
        if (empty($events) || !is_array($events)) {
            $events = array();
        }
        
        if (isset($events['event'])) {
            $events = array($events);
        }

        foreach ($events as $event) {
            if (!isset($event['MessageID'], $event['event'])) {
                continue;
            }

            $messageId = $event['MessageID'];

	        $criteria = new CDbCriteria();
	        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	        $criteria->params = array(
		        'email_message_id' => (string)$messageId,
		        'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	        );

	        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	        if (empty($deliveryLog)) {
		        $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	        }

            if (empty($deliveryLog)) {
                continue;
            }

            $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
            if (empty($campaign)) {
                continue;
            }

            $subscriber = ListSubscriber::model()->findByAttributes(array(
                'list_id'          => $campaign->list_id,
                'subscriber_id'    => $deliveryLog->subscriber_id,
                'status'           => ListSubscriber::STATUS_CONFIRMED,
            ));

            if (empty($subscriber)) {
                continue;
            }
            
            if (in_array($event['event'], array('bounce', 'blocked'))) {

                $count = CampaignBounceLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (!empty($count)) {
                    continue;
                }
                
                $bounceLog = new CampaignBounceLog();
                $bounceLog->campaign_id     = $campaign->campaign_id;
                $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                $bounceLog->message         = !empty($event['error'])  ? $event['error'] : 'BOUNCED BACK';
                $bounceLog->bounce_type     = empty($event['hard_bounce']) ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
                $bounceLog->save();

                if (!empty($event['hard_bounce'])) {
                    $subscriber->addToBlacklist($bounceLog->message);
                }
                
                continue;
            }
            
            if (in_array($event['event'], array('spam', 'unsub'))) {

                if ($event['event'] == 'spam' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                    $subscriber->delete();
                    continue;
                }

                $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
                
                $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));
                
                if (empty($count)) {
                    $trackUnsubscribe = new CampaignTrackUnsubscribe();
                    $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                    $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                    $trackUnsubscribe->note          = $event['event'] == 'spam' ? 'Abuse complaint!' : 'Unsubscribed via Web Hook!';
                    $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                    $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                    $trackUnsubscribe->save(false);
                }
                
                // since 1.4.4 - complaints go into their own tables
                if ($event['event'] == 'spam') {
                    $count = CampaignComplainLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));

                    if (empty($count)) {
                        $complaintLog = new CampaignComplainLog();
                        $complaintLog->campaign_id   = $campaign->campaign_id;
                        $complaintLog->subscriber_id = $subscriber->subscriber_id;
                        $complaintLog->message       = 'Abuse complaint via Mailjet!';
                        $complaintLog->save(false);
                    }
                }
                //

                continue;
            }
        }

        Yii::app()->end();
    }

    /**
     * Process SendinBlue
     */
    public function processSendinblue()
    {
        $event = file_get_contents("php://input");
        if (empty($event)) {
            Yii::app()->end();
        }

        $event = CJSON::decode($event);

        if (empty($event) || !is_array($event) || empty($event['event']) || empty($event['message-id'])) {
            Yii::app()->end();
        }
        
        $messageId = $event['message-id'];

	    $criteria = new CDbCriteria();
	    $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
	    $criteria->params = array(
		    'email_message_id' => (string)$messageId,
		    'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
	    );

	    $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
	    if (empty($deliveryLog)) {
		    $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
	    }

        if (empty($deliveryLog)) {
            Yii::app()->end();
        }

        $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_id'    => $deliveryLog->subscriber_id,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }

        if (in_array($event['event'], array('hard_bounce', 'soft_bounce', 'blocked', 'invalid_email'))) {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = !empty($event['reason'])  ? $event['reason'] : 'BOUNCED BACK';
            $bounceLog->bounce_type     = $event['event'] == 'soft_bounce' ? CampaignBounceLog::BOUNCE_SOFT : CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }

            Yii::app()->end();
        }

        if (in_array($event['event'], array('spam', 'unsubscribe'))) {

            if ($event['event'] == 'spam' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
            
            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = $event['event'] == 'spam' ? 'Abuse complaint!' : 'Unsubscribed via Web Hook!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }
            
            // since 1.4.4 - complaints go into their own tables
            if ($event['event'] == 'spam') {
                
                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message = 'Abuse complaint via SendInBlue!';
                    $complaintLog->save(false);
                }
            }
            //
            
            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Process Tipimail
     */
    public function processTipimail()
    {
        $event = file_get_contents("php://input");
        if (empty($event)) {
            Yii::app()->end();
        }

        $event = CJSON::decode($event);

        if (empty($event) || !is_array($event) || empty($event['status'])) {
            Yii::app()->end();
        }
        
        if (empty($event['meta']) || empty($event['meta']['campaign_uid']) || empty($event['meta']['subscriber_uid'])) {
            Yii::app()->end();
        }
        
        $campaign = Campaign::model()->findByAttributes(array(
            'campaign_uid' => $event['meta']['campaign_uid'],
        ));
        
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'          => $campaign->list_id,
            'subscriber_uid'   => $event['meta']['subscriber_uid'],
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ));

        if (empty($subscriber)) {
            Yii::app()->end();
        }
        
        if (in_array($event['status'], array('error', 'rejected', 'hardbounced'))) {

            $count = CampaignBounceLog::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (!empty($count)) {
                Yii::app()->end();
            }
            
            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id    = $campaign->campaign_id;
            $bounceLog->subscriber_id  = $subscriber->subscriber_id;
            $bounceLog->message        = !empty($event['description']) ? $event['description'] : 'BOUNCED BACK';
            $bounceLog->bounce_type    = CampaignBounceLog::BOUNCE_HARD;
            $bounceLog->save();

            $subscriber->addToBlacklist($bounceLog->message);

            Yii::app()->end();
        }

        if (in_array($event['status'], array('complaint', 'unsubscribed'))) {

            if ($event['status'] == 'complaint' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
            
            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = $event['status'] == 'complaint' ? 'Abuse complaint!' : 'Unsubscribed via Web Hook!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }

            // since 1.4.4 - complaints go into their own tables
            if ($event['status'] == 'complaint') {
                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message = 'Abuse complaint via TipiMail!';
                    $complaintLog->save(false);
                }
            }
            //
            
            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * Process Postmark
     */
    public function processPostmark()
    {
        $event = file_get_contents("php://input");

        if (empty($event)) {
            Yii::app()->end();
        }

        $event = json_decode($event, true);

        if (empty($event) || !is_array($event) || empty($event['MessageID'])) {
            Yii::app()->end();
        }

        $messageId = $event['MessageID'];

        $criteria = new CDbCriteria();
        $criteria->addCondition('`email_message_id` = :email_message_id AND `status` = :status');
        $criteria->params = [
            'email_message_id' => (string)$messageId,
            'status'           => CampaignDeliveryLog::STATUS_SUCCESS,
        ];

        $deliveryLog = CampaignDeliveryLog::model()->find($criteria);
        if (empty($deliveryLog)) {
            $deliveryLog = CampaignDeliveryLogArchive::model()->find($criteria);
        }

        if (empty($deliveryLog)) {
            Yii::app()->end();
        }

        /** @var Campaign $campaign */
        $campaign = Campaign::model()->findByPk($deliveryLog->campaign_id);
        if (empty($campaign)) {
            Yii::app()->end();
        }

        $subscriber = ListSubscriber::model()->findByAttributes([
            'list_id'          => $campaign->list_id,
            'subscriber_id'    => $deliveryLog->subscriber_id,
            'status'           => ListSubscriber::STATUS_CONFIRMED,
        ]);

        if (empty($subscriber)) {
            Yii::app()->end();
        }

        /* Please see the bounce types here: https://postmarkapp.com/developer/api/bounce-api#bounce-types */
        $bounceTypes = array(
            'HardBounce', 'SoftBounce', 'Blocked', 'BadEmailAddress', 'AutoResponder', 'DnsError', 'AddressChange',
            'ManuallyDeactivated', 'SMTPApiError', 'InboundError', 'DMARCPolicy'
        );

        if (in_array($event['Type'], $bounceTypes)) {
            $count = CampaignBounceLog::model()->countByAttributes([
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ]);

            if (!empty($count)) {
                Yii::app()->end();
            }

            $message = 'BOUNCED BACK';
            if (!empty($event['Description'])) {
                $message = $event['Description'];
            } elseif (!empty($event['Details'])) {
                $message = $event['Details'];
            }

            $mapping = array(
                CampaignBounceLog::BOUNCE_INTERNAL => array('Blocked', 'SMTPApiError', 'InboundError', 'DMARCPolicy'),
                CampaignBounceLog::BOUNCE_HARD     => array('HardBounce', 'BadEmailAddress'),
                CampaignBounceLog::BOUNCE_SOFT     => array('SoftBounce', 'AutoResponder', 'AddressChange', 'ManuallyDeactivated'),
            );

            $bounceType = CampaignBounceLog::BOUNCE_INTERNAL;
            foreach ($mapping as $bType => $bounces) {
                if (in_array($event['Type'], $bounces)) {
                    $bounceType = $bType;
                    break;
                }
            }

            $bounceLog = new CampaignBounceLog();
            $bounceLog->campaign_id     = $campaign->campaign_id;
            $bounceLog->subscriber_id   = $subscriber->subscriber_id;
            $bounceLog->message         = $message;
            $bounceLog->bounce_type     = $bounceType;
            $bounceLog->save();

            if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                $subscriber->addToBlacklist($bounceLog->message);
            }

            Yii::app()->end();
        }

        if (in_array($event['Type'], array('SpamNotification', 'Unsubscribe'))) {

            if ($event['Type'] == 'SpamNotification' && Yii::app()->options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                $subscriber->delete();
                Yii::app()->end();
            }

            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'subscriber_id' => $subscriber->subscriber_id,
            ));

            if (empty($count)) {
                $trackUnsubscribe = new CampaignTrackUnsubscribe();
                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                $trackUnsubscribe->note          = 'Unsubscribed via Web Hook!';
                $trackUnsubscribe->ip_address    = Yii::app()->request->userHostAddress;
                $trackUnsubscribe->user_agent    = StringHelper::truncateLength(Yii::app()->request->userAgent, 255);
                $trackUnsubscribe->save(false);
            }

            // since 1.4.4 - complaints go into their own tables
            if ($event['Type'] == 'SpamNotification') {

                $count = CampaignComplainLog::model()->countByAttributes(array(
                    'campaign_id'   => $campaign->campaign_id,
                    'subscriber_id' => $subscriber->subscriber_id,
                ));

                if (empty($count)) {
                    $complaintLog = new CampaignComplainLog();
                    $complaintLog->campaign_id   = $campaign->campaign_id;
                    $complaintLog->subscriber_id = $subscriber->subscriber_id;
                    $complaintLog->message       = 'Abuse complaint via PostMark!';
                    $complaintLog->save(false);
                }
            }

            Yii::app()->end();
        }

        Yii::app()->end();
    }

    /**
     * @param string $message
     */
    public function end($message = "OK")
    {
        if ($message) {
            echo $message;
        }
        Yii::app()->end();
    }
}
