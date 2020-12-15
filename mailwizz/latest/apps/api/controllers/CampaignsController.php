<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignsController
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
     * Handles the listing of the campaigns.
     * The listing is based on page number and number of campaigns per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex()
    {
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
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => array(),
        );

        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));

        $count = Campaign::model()->count($criteria);

        if ($count == 0) {
            return $this->renderJson(array(
                'status'    => 'success',
                'data'      => $data
            ), 200);
        }

        $totalPages = ceil($count / $perPage);

        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;

        $criteria->order    = 't.campaign_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $campaigns = Campaign::model()->findAll($criteria);

        foreach ($campaigns as $campaign) {
            $record = $campaign->getAttributes(array('campaign_uid', 'name', 'status'));
            
            // since 1.5.2
            $record['group'] = array();
            if (!empty($campaign->group_id)) {
                $record['group'] = $campaign->group->getAttributes(array('group_uid', 'name'));
            }
            
            $data['records'][] = $record;
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }

    /**
     * Handles the listing of a single campaign.
     * This action will produce a valid ETAG for caching purposes.
     * 
     * @param $campaign_uid
     * @return BaseController
     */
    public function actionView($campaign_uid)
    {
        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        $record = $campaign->getAttributes(array('campaign_uid', 'name', 'type', 'from_name', 'from_email', 'to_name', 'reply_to', 'subject', 'status'));

        $record['date_added']   = $campaign->dateAdded;
        $record['send_at']      = $campaign->sendAt;
        $record['list']         = $campaign->list->getAttributes(array('list_uid', 'name'));
        $record['list']['subscribers_count'] = $campaign->list->confirmedSubscribersCount;

        $record['segment'] = array();
        if (!empty($campaign->segment)) {
            $record['segment'] = $campaign->segment->getAttributes(array('segment_uid', 'name'));
            $record['segment']['subscribers_count'] = $campaign->segment->countSubscribers();
        }

        // since 1.5.2
        $record['group'] = array();
        if (!empty($campaign->group_id)) {
            $record['group'] = $campaign->group->getAttributes(array('group_uid', 'name'));
        }

        $data = array(
            'record' => $record
        );

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }

    /**
     * Handles the creation of a new campaign.
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }

        $customer = Yii::app()->user->getModel();
        if (($maxCampaigns = (int)$customer->getGroupOption('campaigns.max_campaigns', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
            $campaignsCount = Campaign::model()->count($criteria);
            if ($campaignsCount >= $maxCampaigns) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'You have reached the maximum number of allowed campaigns.')
                ), 403);
            }
        }

        $attributes = (array)$request->getPost('campaign', array());
        $campaign   = new Campaign();
        $campaignOption = new CampaignOption();

        // since 1.3.4.8
        if (isset($attributes['group_uid'])) {
            $campaignGroup = CampaignGroup::model()->findByAttributes(array('group_uid' => $attributes['group_uid']));
            unset($attributes['group_uid']);
            if (!empty($campaignGroup)) {
                $attributes['group_id'] = $campaignGroup->group_id;
            }
        }

        $this->data->campaign       = $campaign;
        $campaign->onBeforeValidate = array($this, '_beforeValidate');
        $campaign->onRules          = array($this, '_setValidationRules');
        $campaign->attributes       = $attributes;
        $campaign->customer_id      = (int)$customer->customer_id;

        if (!$campaign->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $campaign->shortErrors->getAll(),
            ), 422);
        }

        if (empty($attributes['list_uid'])) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a list for this campaign.')
            ), 422);
        }

        if (!($list = $this->loadListByUid($attributes['list_uid']))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Provided list does not exist.')
            ), 422);
        }
        $campaign->list_id = $list->list_id;

        if (!empty($attributes['segment_uid'])) {
            $segment = ListSegment::model()->findByAttributes(array(
                'segment_uid'   => $attributes['segment_uid'],
                'list_id'       => $list->list_id,
            ));

            if (empty($segment)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Provided list segment does not exist.')
                ), 422);
            }

            $campaign->segment_id = $segment->segment_id;
        }

        // set the campaign options, fallback on defaults
        if (!empty($attributes['options']) && is_array($attributes['options'])) {
            foreach ($attributes['options'] as $name => $value) {
                if ($campaignOption->hasAttribute($name)) {
                    $campaignOption->setAttribute($name, $value);
                }
            }
        }

        $template       = new CampaignTemplate();
        $templateAttr   = !empty($attributes['template']) && is_array($attributes['template']) ? $attributes['template'] : array();

        $template->name            = !empty($templateAttr['name']) ? $templateAttr['name'] : '';
        $template->content         = null;
        $template->auto_plain_text = !empty($templateAttr['auto_plain_text']) && $templateAttr['auto_plain_text'] == CampaignTemplate::TEXT_NO ? CampaignTemplate::TEXT_NO : CampaignTemplate::TEXT_YES;
        $template->plain_text      = !empty($templateAttr['plain_text']) && $campaignOption->plain_text_email == CampaignOption::TEXT_YES ? @base64_decode($templateAttr['plain_text']) : null;

        if (!empty($templateAttr['template_uid'])) {
            $_template = CustomerEmailTemplate::model()->findByAttributes(array(
                'template_uid'  => $templateAttr['template_uid'],
                'customer_id'   => (int)$customer->customer_id
            ));

            if (empty($_template)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Provided template does not exist.')
                ), 422);
            }

            $template->name    = $_template->name;
            $template->content = $_template->content;
        }

        if (empty($template->content) && !empty($templateAttr['content'])) {
            $template->content = @base64_decode($templateAttr['content']);
        }

        if (empty($template->content) && !empty($templateAttr['archive'])) {
            $archivePath    = FileSystemHelper::getTmpDirectory() . '/' . StringHelper::random() . '.zip';
            $archiveContent = @base64_decode($templateAttr['archive']);

            if (empty($archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'It does not seem that you have selected an archive.')
                ), 422);
            }

            // http://www.garykessler.net/library/file_sigs.html
            $magicNumbers   = array('504B0304');
            $substr         = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
            $firstBytes     = strtoupper(bin2hex($substr($archiveContent, 0, 4)));

            if (!in_array($firstBytes, $magicNumbers)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Your archive does not seem to be a valid zip file.')
                ), 422);
            }

            if (!@file_put_contents($archivePath, $archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Cannot write archive in the temporary location.')
                ), 422);
            }

            $_FILES['archive'] = array(
                'name'      => basename($archivePath),
                'type'      => 'application/zip',
                'tmp_name'  => $archivePath,
                'error'     => 0,
                'size'      => filesize($archivePath),
            );

            $archiveTemplate = new CampaignEmailTemplateUpload('upload');
            $archiveTemplate->archive = CUploadedFile::getInstanceByName('archive');

            if (!$archiveTemplate->validate()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $archiveTemplate->shortErrors->getAll()
                ), 422);
            }

            $template->content = 'DUMMY DATA, IF YOU SEE THIS, SOMETHING WENT WRONG FROM THE API CALL!';
        }

        if (empty($template->content)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a template for your campaign.')
            ), 422);
        }

        // since 1.3.4.8
        // delivery servers for this campaign - start
        $deliveryServers = array();
        if (isset($attributes['delivery_servers']) && $customer->getGroupOption('servers.can_select_delivery_servers_for_campaign', 'no') == 'yes') {
            if (!is_array($attributes['delivery_servers'])) {
                $attributes['delivery_servers'] = explode(',', $attributes['delivery_servers']);
            }
            $attributes['delivery_servers'] = array_map('trim', $attributes['delivery_servers']);
            $attributes['delivery_servers'] = array_map('intval', $attributes['delivery_servers']);
            $_deliveryServers = $customer->getAvailableDeliveryServers();
            $servers = array();
            foreach ($_deliveryServers as $srv) {
                $servers[] = $srv->server_id;
            }
            foreach ($attributes['delivery_servers'] as $index => $serverId) {
                if (in_array($serverId, $servers)) {
                    $deliveryServers[] = $serverId;
                }
            }
            unset($_deliveryServers, $servers);
        }
        // delivery servers for this campaign - end

        $transaction = Yii::app()->getDb()->beginTransaction();
        try {

            // since the date is already in customer timezone we need to convert it back to utc
            $sourceTimeZone         = new DateTimeZone($customer->timezone);
            $destinationTimeZone    = new DateTimeZone(Yii::app()->timeZone);

            $dateTime = new DateTime($campaign->send_at, $sourceTimeZone);
            $dateTime->setTimezone($destinationTimeZone);
            $campaign->send_at  = $dateTime->format('Y-m-d H:i:s');
            $campaign->status   = Campaign::STATUS_PENDING_SENDING;
            
            // since 1.3.6.2
            $allowedStatuses = array(Campaign::STATUS_PENDING_SENDING, Campaign::STATUS_DRAFT, Campaign::STATUS_PAUSED);
            if (isset($attributes['status']) && in_array($attributes['status'], $allowedStatuses)) {
                $campaign->status = $attributes['status'];
            }

            if (!$campaign->save()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $campaign->shortErrors->getAll(),
                ), 422);
            }

            $campaignOption->campaign_id = $campaign->campaign_id;
            if (!$campaignOption->save()) {
                $transaction->rollback();
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $campaignOption->shortErrors->getAll(),
                ), 422);
            }

            if (!empty($archiveTemplate)) {
                $archiveTemplate->customer_id = (int)$customer->customer_id;
                $archiveTemplate->campaign    = $campaign;

                if (!$archiveTemplate->uploader->handleUpload()) {
                    $transaction->rollback();
                    return $this->renderJson(array(
                        'status'    => 'error',
                        'error'     => $archiveTemplate->shortErrors->getAll()
                    ), 422);
                }

                $template->content  = $archiveTemplate->content;
            }

            if (empty($template->plain_text) && $template->auto_plain_text == CampaignTemplate::TEXT_YES) {
                $template->plain_text = CampaignHelper::htmlToText($template->content);
            }

            if ($template->plain_text) {
                $template->plain_text = Yii::app()->ioFilter->purify($template->plain_text);
            }

            $template->campaign_id = (int)$campaign->campaign_id;

            if (!$template->save()) {
                $transaction->rollback();
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $template->shortErrors->getAll(),
                ), 422);
            }

            // since 1.3.4.8
            if (!empty($deliveryServers)) {
                foreach ($deliveryServers as $serverId) {
                    $campaignToDeliveryServer = new CampaignToDeliveryServer();
                    $campaignToDeliveryServer->campaign_id = $campaign->campaign_id;
                    $campaignToDeliveryServer->server_id = (int)$serverId;
                    $campaignToDeliveryServer->save();
                }
            }
            
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $e->getMessage(),
            ), 422);
        }

        return $this->renderJson(array(
            'status'        => 'success',
            'campaign_uid'  => $campaign->campaign_uid,
        ), 201);
    }

    /**
     * Handles the updating of an existing campaign.
     * 
     * @param $campaign_uid
     * @return BaseController
     * @throws CException
     */
    public function actionUpdate($campaign_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => 'Requested campaign does not exist.',
            ), 404);
        }

        if (!$campaign->editable) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => 'This campaign is not ediable.',
            ), 422);
        }

        $campaignOption = new CampaignOption();
        if (!empty($campaign->option)) {
            $campaignOption = $campaign->option;
        }
        $campaignOption->campaign_id = $campaign->campaign_id;

        $this->data->campaign = $campaign;

        $sendAt     = $campaign->send_at;
        $attributes = (array)$request->getPut('campaign', array());
        $customer   = Yii::app()->user->getModel();
        
        // since 1.3.4.8
        if (isset($attributes['group_uid'])) {
            $campaignGroup = CampaignGroup::model()->findByAttributes(array('group_uid' => $attributes['group_uid']));
            unset($attributes['group_uid']);
            if (!empty($campaignGroup)) {
                $attributes['group_id'] = $campaignGroup->group_id;
            }
        }

        $campaign->onBeforeValidate = array($this, '_beforeValidate');
        $campaign->onRules          = array($this, '_setValidationRules');
        $campaign->attributes       = $attributes;
        $campaign->customer_id      = (int)Yii::app()->user->getId();

        if (!$campaign->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $campaign->shortErrors->getAll(),
            ), 422);
        }

        $list = !empty($campaign->list) ? $campaign->list : null;
        if (!empty($attributes['list_uid'])) {
            $list = $this->loadListByUid($attributes['list_uid']);
        }

        if (empty($list)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a list for this campaign.')
            ), 422);
        }
        $campaign->list_id = $list->list_id;

        if (!empty($attributes['segment_uid'])) {
            $segment = ListSegment::model()->findByAttributes(array(
                'segment_uid'   => $attributes['segment_uid'],
                'list_id'       => $list->list_id,
            ));

            if (empty($segment)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Provided list segment does not exist.')
                ), 422);
            }

            $campaign->segment_id = $segment->segment_id;
        }

        // set the campaign options, fallback on defaults
        if (!empty($attributes['options']) && is_array($attributes['options'])) {
            foreach ($attributes['options'] as $name => $value) {
                if ($campaignOption->hasAttribute($name)) {
                    $campaignOption->setAttribute($name, $value);
                }
            }
        }

        $template       = !empty($campaign->template) ? $campaign->template : new CampaignTemplate();
        $templateAttr   = !empty($attributes['template']) && is_array($attributes['template']) ? $attributes['template'] : array();
        $tempContent    = $template->content;
        
        $template->name    = !empty($templateAttr['name']) ? $templateAttr['name'] : '';
        $template->content = null;

        if (!empty($templateAttr['auto_plain_text'])) {
            $template->auto_plain_text = $templateAttr['auto_plain_text'] == CampaignTemplate::TEXT_NO ? CampaignTemplate::TEXT_NO : CampaignTemplate::TEXT_YES;
        }

        if (!empty($templateAttr['plain_text']) && $campaignOption->plain_text_email == CampaignOption::TEXT_YES) {
            $template->plain_text = @base64_decode($templateAttr['plain_text']);
        }

        if (!empty($templateAttr['minify'])) {
            $template->minify = $templateAttr['minify'] == CampaignTemplate::TEXT_YES ? CampaignTemplate::TEXT_YES : CampaignTemplate::TEXT_NO;
        }

        if (!empty($templateAttr['template_uid'])) {
            $_template = CustomerEmailTemplate::model()->findByAttributes(array(
                'template_uid'  => $templateAttr['template_uid'],
                'customer_id'   => (int)Yii::app()->user->getId()
            ));

            if (empty($_template)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Provided template does not exist.')
                ), 422);
            }

            $template->content = $_template->content;
        }

        if (empty($template->content) && !empty($templateAttr['content'])) {
            $template->content = @base64_decode($templateAttr['content']);
        }

        if (empty($template->content) && !empty($templateAttr['archive'])) {
            $archivePath = FileSystemHelper::getTmpDirectory() . '/' . StringHelper::random() . '.zip';
            $archiveContent = @base64_decode($templateAttr['archive']);

            if (empty($archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'It does not seem that you have selected an archive.')
                ), 422);
            }

            // http://www.garykessler.net/library/file_sigs.html
            $magicNumbers   = array('504B0304');
            $substr         = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
            $firstBytes     = strtoupper(bin2hex($substr($archiveContent, 0, 4)));

            if (!in_array($firstBytes, $magicNumbers)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Your archive does not seem to be a valid zip file.')
                ), 422);
            }

            if (!@file_put_contents($archivePath, $archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Cannot write archive in the temporary location.')
                ), 422);
            }

            $_FILES['archive'] = array(
                'name'      => basename($archivePath),
                'type'      => 'application/zip',
                'tmp_name'  => $archivePath,
                'error'     => 0,
                'size'      => filesize($archivePath),
            );

            $archiveTemplate = new CampaignEmailTemplateUpload('upload');
            $archiveTemplate->archive = CUploadedFile::getInstanceByName('archive');
            
            if (!$archiveTemplate->validate()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $archiveTemplate->shortErrors->getAll()
                ), 422);
            }

            $template->content = 'DUMMY DATA, IF YOU SEE THIS, SOMETHING WENT WRONG FROM THE API CALL!';
        }

        if (empty($template->content) && !empty($tempContent)) {
            $template->content = $tempContent;
            $archiveTemplate = null;
            unset($tempContent);
        }

        if (empty($template->content)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Please provide a template for your campaign.')
            ), 422);
        }

        // since 1.3.4.8
        // delivery servers for this campaign - start
        $deliveryServers = array();
        if (isset($attributes['delivery_servers']) && $customer->getGroupOption('servers.can_select_delivery_servers_for_campaign', 'no') == 'yes') {
            if (!is_array($attributes['delivery_servers'])) {
                $attributes['delivery_servers'] = explode(',', $attributes['delivery_servers']);
            }
            $attributes['delivery_servers'] = array_map('trim', $attributes['delivery_servers']);
            $attributes['delivery_servers'] = array_map('intval', $attributes['delivery_servers']);
            $_deliveryServers = $customer->getAvailableDeliveryServers();
            $servers = array();
            foreach ($_deliveryServers as $srv) {
                $servers[] = $srv->server_id;
            }
            foreach ($attributes['delivery_servers'] as $index => $serverId) {
                if (in_array($serverId, $servers)) {
                    $deliveryServers[] = $serverId;
                }
            }
            unset($_deliveryServers, $servers);
        }
        // delivery servers for this campaign - end
	    
        $transaction = Yii::app()->getDb()->beginTransaction();
        try {

            if ($sendAt != $campaign->send_at) {
                // since the date is already in customer timezone we need to convert it back to utc
                $sourceTimeZone         = new DateTimeZone($campaign->customer->timezone);
                $destinationTimeZone    = new DateTimeZone(Yii::app()->timeZone);

                $dateTime = new DateTime($campaign->send_at, $sourceTimeZone);
                $dateTime->setTimezone($destinationTimeZone);
                $campaign->send_at = $dateTime->format('Y-m-d H:i:s');
            }

            if (!$campaign->save()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $campaign->shortErrors->getAll(),
                ), 422);
            }

            if (!$campaignOption->save()) {
                $transaction->rollback();
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $campaignOption->shortErrors->getAll(),
                ), 422);
            }

            if (!empty($archiveTemplate)) {
                $archiveTemplate->customer_id = (int)Yii::app()->user->getId();
                $archiveTemplate->campaign    = $campaign;

                if (!$archiveTemplate->uploader->handleUpload()) {
                    $transaction->rollback();
                    return $this->renderJson(array(
                        'status'    => 'error',
                        'error'     => $archiveTemplate->shortErrors->getAll()
                    ), 422);
                }

                $template->content  = $archiveTemplate->content;
            }

            if (empty($template->plain_text) && $template->auto_plain_text == CampaignTemplate::TEXT_YES) {
                $template->plain_text = CampaignHelper::htmlToText($template->content);
            }

            if ($template->plain_text) {
                $template->plain_text = Yii::app()->ioFilter->purify($template->plain_text);
            }

            $template->campaign_id = (int)$campaign->campaign_id;

            if (!$template->save()) {
                $transaction->rollback();
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $template->shortErrors->getAll(),
                ), 422);
            }

            // since 1.3.4.8
            if (!empty($deliveryServers)) {
                if (isset($attributes['delivery_servers']) && is_array($attributes['delivery_servers'])) {
                    CampaignToDeliveryServer::model()->deleteAllByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                    ));
                }
                foreach ($deliveryServers as $serverId) {
                    $campaignToDeliveryServer = new CampaignToDeliveryServer();
                    $campaignToDeliveryServer->campaign_id = $campaign->campaign_id;
                    $campaignToDeliveryServer->server_id = (int)$serverId;
                    $campaignToDeliveryServer->save();
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $e->getMessage(),
            ), 422);
        }

        return $this->renderJson(array(
            'status'    => 'success',
        ), 200);
    }

    public function actionCopy($campaign_uid)
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
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        if (!($newCampaign = $campaign->copy())) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Unable to copy the campaign.')
            ), 400);
        }

        return $this->renderJson(array(
            'status'       => 'success',
            'campaign_uid' => $newCampaign->campaign_uid,
        ), 200);
    }

    public function actionPause_unpause($campaign_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        $campaign->pauseUnpause();

        return $this->renderJson(array(
            'status'   => 'success',
            'campaign' => array(
                'status' => $campaign->status,
            ),
        ), 200);
    }

    public function actionMark_sent($campaign_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        if (!$campaign->markAsSent()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not allow marking it as sent!')
            ), 400);
        }
        
        return $this->renderJson(array(
            'status'   => 'success',
            'campaign' => array(
                'status' => $campaign->status,
            ),
        ), 200);
    }

    public function actionDelete($campaign_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isDeleteRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only DELETE requests allowed for this endpoint.')
            ), 400);
        }

        if (!($campaign = $this->loadCampaignByUid($campaign_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The campaign does not exist.')
            ), 404);
        }

        if (!$campaign->getRemovable()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'This campaign cannot be removed now.')
            ), 400);
        }

        $campaign->delete();

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $campaign,
        )));

        return $this->renderJson(array(
            'status'    => 'success',
        ), 200);
    }

    public function loadCampaignByUid($campaign_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_uid', $campaign_uid);
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Campaign::STATUS_PENDING_DELETE));
        return Campaign::model()->find($criteria);
    }

    public function loadListByUid($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        return Lists::model()->find($criteria);
    }

    /**
     * It will generate the timestamp that will be used to generate the ETAG for GET requests.
     */
    public function generateLastModified()
    {
        static $lastModified;

        if ($lastModified !== null) {
            return $lastModified;
        }

        $request    = Yii::app()->request;
        $row        = array();

        if ($this->action->id == 'index') {

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

            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                    SELECT `a`.`customer_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                    FROM `{{campaign}}` `a`
                    WHERE `a`.`customer_id` = :cid
                    ORDER BY a.`campaign_id` DESC
                    LIMIT :l OFFSET :o
                ) AS t
                WHERE `t`.`customer_id` = :cid
            ';

            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);

            $row = $command->queryRow();

        } elseif ($this->action->id == 'view') {

            $sql = 'SELECT UNIX_TIMESTAMP(t.last_updated) as `timestamp` FROM `{{campaign}}` t WHERE `t`.`campaign_uid` = :uid AND `t`.`customer_id` = :cid LIMIT 1';
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':uid', $request->getQuery('campaign_uid'), PDO::PARAM_STR);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);

            $row = $command->queryRow();

        }

        if (isset($row['timestamp'])) {
            $timestamp = round($row['timestamp']);
            if (preg_match('/\.(\d+)/', $row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = $timestamp;
        }

        return $lastModified = parent::generateLastModified();
    }

    public function _setValidationRules(CEvent $event)
    {
        $event->params['rules']->clear();
        $event->params['rules']->add(array('name, from_name, from_email, subject, reply_to, to_name, send_at', 'required'));
        $event->params['rules']->add(array('name, to_name, subject', 'length', 'max'=>255));
        $event->params['rules']->add(array('from_name, reply_to, from_email', 'length', 'max'=>100));
        $event->params['rules']->add(array('reply_to, from_email', '_validateEMailWithTag'));
        $event->params['rules']->add(array('send_at', 'date', 'format' => 'yyyy-MM-dd HH:mm:ss'));
    }

    public function _beforeValidate($event)
    {
        $campaign   = $this->data->campaign;
        $tags       = $campaign->getSubjectToNameAvailableTags();
        $hasErrors  = false;
        $attributes = array('subject', 'to_name');

        foreach ($attributes as $attribute) {
            $content = CHtml::decode($campaign->$attribute);
            foreach ($tags as $tag) {
                if (!isset($tag['tag']) || !isset($tag['required']) || !$tag['required']) {
                    continue;
                }
                if (!isset($tag['pattern']) && strpos($content, $tag['tag']) === false) {
                    $campaign->addError($attribute, Yii::t('lists', 'The following tag is required but was not found in your content: {tag}', array(
                        '{tag}' => $tag['tag'],
                    )));
                    $hasErrors = true;
                } elseif (isset($tag['pattern']) && !preg_match($tag['pattern'], $content)) {
                    $campaign->addError($attribute, Yii::t('lists', 'The following tag is required but was not found in your content: {tag}', array(
                        '{tag}' => $tag['tag'],
                    )));
                    $hasErrors = true;
                }
            }
        }
    }
}
