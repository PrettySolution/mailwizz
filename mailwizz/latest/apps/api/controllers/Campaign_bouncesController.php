<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign_bouncesController
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class Campaign_bouncesController extends Controller
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
     * Handles the listing of the bounces for a campaign.
     * The listing is based on page number and number of lists per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex($campaign_uid)
    {
        $campaign = $this->loadCampaignByUid($campaign_uid);
        if (empty($campaign)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist!')
            ), 404);
        }
        
        $request    = Yii::app()->request;
        $perPage    = (int)$request->getQuery('per_page', 10);
        $page       = (int)$request->getQuery('page', 1);
        $maxPerPage = 50;
        $minPerPage = 10;

        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }

        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }

        if ($page < 1) {
            $page = 1;
        }

        $data = array(
            'count'        => null,
            'total_pages'  => null,
            'current_page' => null,
            'next_page'    => null,
            'prev_page'    => null,
            'records'      => array(),
        );

        $criteria = new CDbCriteria();
        $criteria->compare('t.campaign_id', (int)$campaign->campaign_id);

        $count = CampaignBounceLog::model()->count($criteria);

        if ($count == 0) {
            return $this->renderJson(array(
                'status' => 'success',
                'data'   => $data
            ), 200);
        }

        $totalPages = ceil($count / $perPage);

        $data['count']        = $count;
        $data['current_page'] = $page;
        $data['next_page']    = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']    = $page > 1 ? $page - 1 : null;
        $data['total_pages']  = $totalPages;

        $criteria->order  = 't.log_id DESC';
        $criteria->limit  = $perPage;
        $criteria->offset = ($page - 1) * $perPage;

        $bounces = CampaignBounceLog::model()->findAll($criteria);

        foreach ($bounces as $bounce) {
            
            $data['records'][] = array(
                'message'     => $bounce->message,
                'processed'   => $bounce->processed,
                'bounce_type' => $bounce->bounce_type,
                'subscriber'  => array(
                    'subscriber_uid' => $bounce->subscriber->subscriber_uid,
                    'email'          => $bounce->subscriber->displayEmail,
                ),
            );
        }

        return $this->renderJson(array(
            'status' => 'success',
            'data'   => $data
        ), 200);
    }

    /**
     * Handles the creation of a new bounce log.
     */
    public function actionCreate($campaign_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }

        $campaign = $this->loadCampaignByUid($campaign_uid);
        if (empty($campaign)) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The campaign does not exist!')
            ), 404);
        }

        $subscriber = $this->loadSubscriberByUid($request->getPost('subscriber_uid', ''));
        if (empty($subscriber)) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The subscriber does not exist!')
            ), 404);
        }

        $count = CampaignBounceLog::model()->countByAttributes(array(
            'campaign_id'   => $campaign->campaign_id,
            'subscriber_id' => $subscriber->subscriber_id,
        ));

        if (!empty($count)) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'This subscriber has already been marked as bounced!')
            ), 422);
        }

        $bounceType = $request->getPost('bounce_type', 'internal');
        $message    = StringHelper::truncateLength($request->getPost('message', 'BOUNCED BACK'), 250);
        $bounce     = new CampaignBounceLog();
        
        if (!in_array($bounceType, array_keys($bounce->getBounceTypesArray()))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'Invalid bounce type!')
            ), 422);
        }

        $bounce->campaign_id   = $campaign->campaign_id;
        $bounce->subscriber_id = $subscriber->subscriber_id;
        $bounce->message       = $message;
        $bounce->bounce_type   = $bounceType;
        
        if (!$bounce->save()) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => $bounce->shortErrors->getAll(),
            ), 422);
        }
        
        if ($bounce->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
            $subscriber->addToBlacklist($message);
        }

        return $this->renderJson(array(
            'status' => 'success',
            'data'   => array(
                'record' => array(
                    'message'     => $bounce->message,
                    'processed'   => $bounce->processed,
                    'bounce_type' => $bounce->bounce_type,
                    'subscriber'  => array(
                        'subscriber_uid' => $subscriber->subscriber_uid,
                        'email'          => $subscriber->displayEmail,
                    ),
                )
            ),
        ), 201);
    }

    /**
     * @param $campaign_uid
     * @return Campaign|null
     */
    public function loadCampaignByUid($campaign_uid)
    {
        if (empty($campaign_uid)) {
            return null;
        }
        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->compare('campaign_uid', $campaign_uid);
        return Campaign::model()->find($criteria);
    }

    /**
     * @param $subscriber_uid
     * @return ListSubscriber|null
     */
    public function loadSubscriberByUid($subscriber_uid)
    {
        if (empty($subscriber_uid)) {
            return null;
        }
        $criteria = new CDbCriteria();
        $criteria->compare('subscriber_uid', $subscriber_uid);
        return ListSubscriber::model()->find($criteria);
    }
}
