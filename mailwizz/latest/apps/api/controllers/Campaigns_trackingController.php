<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaigns_trackingController
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class Campaigns_trackingController extends Controller
{
    // access rules for this controller
    public function accessRules()
    {
        return array(
            // allow all authenticated users on all actions
            array('allow', 'users' => array('@')),
            // deny all rule.
            array('deny'),
        );
    }

    /**
     * Handles the click tracking.
     * 
     * @param $campaign_uid
     * @param $subscriber_uid
     * @param $hash
     * @return mixed
     */
    public function actionTrack_url($campaign_uid, $subscriber_uid, $hash)
    {
        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }
        
        if (!($subscriber = $this->loadSubscriberByUid($subscriber_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscriber does not exist.')
            ), 404);
        }
        
        if (!($url = $this->loadCampaignUrlByHash($campaign, $hash))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The url hash does not exist.')
            ), 404);
        }

        $track                = new CampaignTrackUrl();
        $track->url_id        = $url->url_id;
        $track->subscriber_id = $subscriber->subscriber_id;
        $track->ip_address    = Yii::app()->request->getUserHostAddress();
        $track->user_agent    = substr(Yii::app()->request->getUserAgent(), 0, 255);
        $track->save(false);
        
        $url->destination = StringHelper::normalizeUrl($url->destination);
        $destination = $url->destination;
        if (preg_match('/\[(.*)?\]/', $destination)) {
            list(,,$destination) = CampaignHelper::parseContent($destination, $campaign, $subscriber, false);
        }
        
        if ($campaign->option->open_tracking == CampaignOption::TEXT_YES && !$subscriber->hasOpenedCampaign($campaign)) {
            $track                = new CampaignTrackOpen();
            $track->campaign_id   = $campaign->campaign_id;
            $track->subscriber_id = $subscriber->subscriber_id;
            $track->ip_address    = Yii::app()->request->getUserHostAddress();
            $track->user_agent    = substr(Yii::app()->request->getUserAgent(), 0, 255);
            $track->save(false);
        }
        
        $options  = Yii::app()->options; 
        $trackUrl = $options->get('system.urls.frontend_absolute_url');
        $trackUrl.= sprintf('campaigns/track-url/%s/%s/%s', $campaign_uid, $subscriber_uid, $hash);
        
        return $this->renderJson(array(
            'status' => 'success',
            'data'   => array(
                'track_url'   => $trackUrl,
                'destination' => $destination,
            ),
        ), 200);
    }

    /**
     * Handles the opens tracking.
     *
     * @param $campaign_uid
     * @param $subscriber_uid
     * @return mixed
     */
    public function actionTrack_opening($campaign_uid, $subscriber_uid)
    {
        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        if (!($subscriber = $this->loadSubscriberByUid($subscriber_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscriber does not exist.')
            ), 404);
        }

        $track = new CampaignTrackOpen();
        $track->campaign_id   = $campaign->campaign_id;
        $track->subscriber_id = $subscriber->subscriber_id;
        $track->ip_address    = Yii::app()->request->getUserHostAddress();
        $track->user_agent    = substr(Yii::app()->request->getUserAgent(), 0, 255);
        $track->save(false);

        return $this->renderJson(array(
            'status' => 'success',
            'data'   => array(),
        ), 200);
    }

    /**
     * Handles unsubscription of an existing subscriber.
     * 
     * @param $campaign_uid
     * @param $subscriber_uid
     * @return BaseController
     */
    public function actionTrack_unsubscribe($campaign_uid, $subscriber_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }
        
        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        if (!($subscriber = $this->loadSubscriberByUid($subscriber_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscriber does not exist.')
            ), 404);
        }
        
        if (!$subscriber->getIsConfirmed()) {
            return $this->renderJson(array(
                'status' => 'success',
            ), 200);
        }

        if (!$subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED)) {
            return $this->renderJson(array(
                'status' => 'success',
            ), 200);
        }

        $track = CampaignTrackUnsubscribe::model()->findByAttributes(array(
            'campaign_id'   => (int)$campaign->campaign_id,
            'subscriber_id' => (int)$subscriber->subscriber_id,
        ));
        
        if (!empty($track)) {
            return $this->renderJson(array(
                'status' => 'success',
            ), 200);
        }
        
        $track = new CampaignTrackUnsubscribe();
        $track->campaign_id   = (int)$campaign->campaign_id;
        $track->subscriber_id = (int)$subscriber->subscriber_id;
        $track->ip_address    = substr($request->getPost('ip_address', $request->getUserHostAddress()), 0, 45);
        $track->user_agent    = substr($request->getPost('user_agent', $request->getUserAgent()), 0, 255);
        $track->reason        = substr($request->getPost('reason', 'Unsubscribed via API!'), 0, 255);
        $track->save();

        $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_UNSUBSCRIBE);

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->subscriberUnsubscribed($subscriber);
        }
        
        return $this->renderJson(array(
            'status' => 'success',
        ), 200);
    }

    /**
     * @param $campaign_uid
     * @return Campaign|null
     */
    public function loadCampaignByUid($campaign_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        return Campaign::model()->find($criteria);
    }

    /**
     * @param $subscriber_uid
     * @return ListSubscriber|null
     */
    public function loadSubscriberByUid($subscriber_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('subscriber_uid', $subscriber_uid);
        return ListSubscriber::model()->find($criteria);
    }

    /**
     * @param Campaign $campaign
     * @param $hash
     * @return CampaignUrl|null
     */
    public function loadCampaignUrlByHash(Campaign $campaign, $hash)
    {
        // try with a real hash
        $url = CampaignUrl::model()->findByAttributes(array(
            'campaign_id'   => $campaign->campaign_id,
            'hash'          => $hash,
        ));
        
        // maybe a url destination
        if (!$url && stripos($hash, 'http') === 0) {
            
            $url = CampaignUrl::model()->findByAttributes(array(
                'campaign_id'   => $campaign->campaign_id,
                'destination'   => $hash,
            ));
        }
        
        return $url;
    }
}
