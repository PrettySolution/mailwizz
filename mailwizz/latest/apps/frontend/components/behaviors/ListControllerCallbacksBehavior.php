<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListControllerCallbacksBehavior
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ListControllerCallbacksBehavior extends CBehavior
{
    /**
     * @param CEvent $event
     * @return array
     */
    public function _orderFields(CEvent $event)
    {
        $fields = array();
        $sort   = array();

        foreach ($event->params['fields'] as $type => $_fields) {
            foreach ($_fields as $index => $field) {
                if (!isset($field['sort_order'], $field['field_html'])) {
                    unset($event->params['fields'][$type][$index]);
                    continue;
                }
                $fields[] = $field;
                $sort[] = (int)$field['sort_order'];
            }
        }

        array_multisort($sort, $fields);

        return $event->params['fields'] = $fields;
    }

    /**
     * @param $event
     */
    public function _addUnsubscribeEmailValidationRules($event)
    {
        // get the refrence
        $rules = $event->params['rules'];
        // clear all of them
        $rules->clear();
        // add the email rules
        $rules->add(array('email', 'required'));
        $rules->add(array('email', 'email'));
    }

    /**
     * @param $event
     * @throws CException
     */
    public function _unsubscribeAfterValidate($event)
    {
        $ownerData  = $this->owner->data;
        $list       = $ownerData->list;
        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'list_id'   => $list->list_id,
            'email'     => $event->sender->email
        ));
        $allowedStatuses = array(
            ListSubscriber::STATUS_CONFIRMED, 
            ListSubscriber::STATUS_UNSUBSCRIBED,
            ListSubscriber::STATUS_MOVED,
        );

        if (empty($subscriber) || !in_array($subscriber->status, $allowedStatuses)) {
            $event->sender->addError('email', Yii::t('lists', 'The specified email address does not exist in the list!'));
            return;
        }
        
        if ($subscriber->status == ListSubscriber::STATUS_UNSUBSCRIBED) {
            $event->sender->addError('email', Yii::t('lists', 'The specified email address is already unsubscribed from this list!'));
	        return;
        }

        /* // disabled because lists might have cascade actions
        if ($subscriber->status == ListSubscriber::STATUS_MOVED) {
            Yii::app()->notify->addSuccess(Yii::t('list_subscribers', 'You have been unsubscribed successfully!'));
            return;
        }
        */

        if ($event->sender->hasErrors()) {
            return;
        }

        // 1.3.9.8 - Create optout history
        $subscriber->createOptoutHistory();

        $unsubscribeUrl = $this->owner->createAbsoluteUrl('lists/unsubscribe_confirm', array(
            'list_uid'          => $list->list_uid,
            'subscriber_uid'    => $subscriber->subscriber_uid
        ));

        if (!empty($ownerData->_campaign)) {
            $unsubscribeUrl = $this->owner->createAbsoluteUrl('lists/unsubscribe_confirm', array(
                'list_uid'          => $list->list_uid,
                'subscriber_uid'    => $subscriber->subscriber_uid,
                'campaign_uid'      => $ownerData->_campaign->campaign_uid
            ));
        }

        if ($list->opt_out == Lists::OPT_OUT_SINGLE || $this->owner->getData('unsubscribeDirect')) {
            $this->owner->redirect($unsubscribeUrl);
        }

        $dsParams = array('useFor' => DeliveryServer::USE_FOR_LIST_EMAILS);
        if (!($server = DeliveryServer::pickServer(0, $list, $dsParams))) {
            return;
        }

        $pageType = ListPageType::model()->findBySlug('unsubscribe-confirm-email');

        if (empty($pageType)) {
            return;
        }

        $page = ListPage::model()->findByAttributes(array(
            'list_id' => $list->list_id,
            'type_id' => $pageType->type_id
        ));

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $subject = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $options = Yii::app()->options;

        $searchReplace = array(
            '[LIST_NAME]'       => $list->display_name,
            '[COMPANY_NAME]'    => !empty($list->company) ? $list->company->name : null,
            '[UNSUBSCRIBE_URL]' => $unsubscribeUrl,
            '[CURRENT_YEAR]'    => date('Y'),

            // 1.5.3
            '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
        );

        // since 1.3.5.9
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //

        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            $subject = CampaignHelper::parseByTemplateEngine($subject, $searchReplace);
        }
        
        $params = array(
            'to'        => $subscriber->email,
            'fromName'  => $list->default->from_name,
            'subject'   => $subject,
            'body'      => $content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params)) {
                break;
            }
            if (!($server = DeliveryServer::pickServer($server->server_id, $list, $dsParams))) {
                break;
            }
        }

        Yii::app()->notify->addSuccess(Yii::t('list_subscribers', 'Please check your email and click on the provided unsubscribe link.'));
        $this->owner->redirect(array('lists/unsubscribe', 'list_uid' => $list->list_uid));
    }

    /**
     * @param CEvent $event
     * @throws Exception
     */
    public function _sendSubscribeConfirmationEmail(CEvent $event)
    {
        $dsParams = array('useFor' => DeliveryServer::USE_FOR_LIST_EMAILS);
        if (!($server = DeliveryServer::pickServer(0, $event->params['list'], $dsParams))) {
            throw new Exception(Yii::t('app', 'Email delivery is disabled at the moment, please try again later!'));
        }

        $subscriber = $event->params['subscriber'];
        $list       = $event->params['list'];
        $pageType   = ListPageType::model()->findBySlug('subscribe-confirm-email');

        if (empty($pageType)) {
            throw new Exception(Yii::t('app', 'Temporary error, please try again later!'));
        }

        $page = ListPage::model()->findByAttributes(array(
            'list_id' => $list->list_id,
            'type_id' => $pageType->type_id
        ));

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $subject = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $options = Yii::app()->options;

        $subscribeUrl = $options->get('system.urls.frontend_absolute_url');
        $subscribeUrl .= 'lists/' . $list->list_uid . '/confirm-subscribe/' . $subscriber->subscriber_uid;
        
        // 1.5.3
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid;

        $searchReplace = array(
            '[LIST_NAME]'       => $list->display_name,
            '[COMPANY_NAME]'    => !empty($list->company) ? $list->company->name : null,
            '[SUBSCRIBE_URL]'   => $subscribeUrl,
            '[CURRENT_YEAR]'    => date('Y'),

            // 1.5.3
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($list->company) ? nl2br($list->company->getFormattedAddress()) : null,
        );

        // since 1.3.5.9
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //

        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            $subject = CampaignHelper::parseByTemplateEngine($subject, $searchReplace);
        }
        
        $params = array(
            'to'        => $subscriber->email,
            'fromName'  => $list->default->from_name,
            'subject'   => $subject,
            'body'      => $content,
        );

        $sent = false;
        for ($i = 0; $i < 3; ++$i) {
            if ($sent = $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params)) {
                break;
            }
            Yii::log(print_r($server->getMailer()->getLogs(), true), CLogger::LEVEL_ERROR);
            if (!($server = DeliveryServer::pickServer($server->server_id, $list, $dsParams))) {
                break;
            }
        }

        if (!$sent) {
            throw new Exception(Yii::t('app', 'We are sorry, but we cannot deliver the confirmation email right now!'));
        }
    }

    /**
     * @param CEvent $event
     */
    public function _profileUpdatedSuccessfully(CEvent $event)
    {
        // mark action log
        if (Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
            $list = $event->params['list'];
            $subscriber = $event->params['subscriber'];

            $customer = $list->customer;
            $customer->attachBehavior('logAction', array(
                'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
            ));
            $customer->logAction->subscriberUpdated($subscriber);
        }

        Yii::app()->notify->addSuccess(Yii::t('app', 'Your profile has been successfully updated!'));
    }

    /**
     * @param CEvent $event
     */
    public function _collectAndShowErrorMessages(CEvent $event)
    {
        $instances = isset($event->params['instances']) ? (array)$event->params['instances'] : array();

        // collect and show visible errors.
        foreach ($instances as $instance) {
            if (empty($instance->errors)) {
                continue;
            }
            foreach ($instance->errors as $error) {
                if (empty($error['show']) || empty($error['message'])) {
                    continue;
                }
                Yii::app()->notify->addError($error['message']);
            }
        }
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onSubscriberFieldsSorting(CEvent $event)
    {
        $this->raiseEvent('onSubscriberFieldsSorting', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onSubscriberSave(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSave', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onSubscriberFieldsDisplay(CEvent $event)
    {
        $this->raiseEvent('onSubscriberFieldsDisplay', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onSubscriberSaveSuccess(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSaveSuccess', $event);
    }

    /**
     * @param CEvent $event
     * @throws CException
     */
    public function onSubscriberSaveError(CEvent $event)
    {
        $this->raiseEvent('onSubscriberSaveError', $event);
    }
}
