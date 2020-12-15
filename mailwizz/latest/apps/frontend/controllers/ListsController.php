<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListsController
 *
 * Handles the actions for lists related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ListsController extends Controller
{
    /**
     * @return BaseController|void
     * @throws CException
     */
    public function init()
    {
        Yii::import('customer.components.field-builder.*');
        parent::init();
    }

    /**
	 * @inheritdoc
	 */
    public function behaviors()
    {
        return CMap::mergeArray(array(
            'callbacks' => array(
                'class' => 'frontend.components.behaviors.ListControllerCallbacksBehavior',
            ),
        ), parent::behaviors());
    }

    /**
     * Subscribe a new user to a certain email list
     */
    public function actionSubscribe($list_uid, $subscriber_uid = null)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }
  
        $pageType = $this->loadPageTypeModel('subscribe-form');
        $page     = $this->loadPageModel($list->list_id, $pageType->type_id);

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);

        // list name
        $content = str_replace('[LIST_NAME]', CHtml::encode($list->display_name), $content);

        // submit button
        $content = str_replace('[SUBMIT_BUTTON]', CHtml::button(Yii::t('lists', 'Subscribe'), array('type' => 'submit', 'class' => 'btn btn-primary btn-flat')), $content);

        // load the list fields and bind the behavior.
        $listFields = ListField::model()->findAll(array(
            'condition' => 'list_id = :lid',
            'params'    => array(':lid' => (int)$list->list_id),
            'order'     => 'sort_order ASC'
        ));

        if (empty($listFields)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $hooks   = Yii::app()->hooks;

        if (!empty($subscriber_uid)) {
            $_subscriber = $this->loadSubscriberModel($subscriber_uid, $list->list_id);
            if (!empty($_subscriber) && $_subscriber->status == ListSubscriber::STATUS_UNSUBSCRIBED) {
                $subscriber = $_subscriber;
            } else {
                $_subscriber = null;
            }
        }
        if (empty($subscriber)) {
            $subscriber = new ListSubscriber();
        }
        $subscriber->list_id = $list->list_id;
        $subscriber->ip_address = Yii::app()->request->getUserHostAddress();

        $usedTypes = array();
        foreach ($listFields as $field) {
            $usedTypes[] = (int)$field->type->type_id;
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $listFieldTypes = ListFieldType::model()->findAll($criteria);
        $instances = array();

        foreach ($listFieldTypes as $fieldType) {

            if (empty($fieldType->identifier) || !is_file(Yii::getPathOfAlias($fieldType->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $fieldType->class_alias, array(
                'fieldType'     => $fieldType,
                'list'          => $list,
                'subscriber'    => $subscriber,
            ));

            if (!($component instanceof FieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        // since 1.3.9.7
        if (!$request->isPostRequest) {
            foreach ($listFields as $listField) {
                if ($tagValue = $request->getQuery($listField->tag)) {
                    $_POST[$listField->tag] = $tagValue;
                }
            }
        }

        $fields = array();

        // if the fields are saved
        if ($request->isPostRequest) {

            // since 1.3.5.6
            Yii::app()->hooks->doAction('frontend_list_subscribe_before_transaction', $this);

            $transaction = Yii::app()->db->beginTransaction();

            try {

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_list_subscribe_at_transaction_start', $this);
                
                // since 1.5.3
                $customer = Customer::model()->findByPk($list->customer_id);
                
                $maxSubscribersPerList   = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
                $maxSubscribers          = (int)$customer->getGroupOption('lists.max_subscribers', -1);

                if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
                    $criteria = new CDbCriteria();
                    $criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

                    if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
                        $criteria->addInCondition('t.list_id', $listsIds);
                        $totalSubscribersCount = ListSubscriber::model()->count($criteria);
                        if ($totalSubscribersCount >= $maxSubscribers) {
                            throw new Exception(Yii::t('lists', 'The maximum number of allowed subscribers has been reached.'));
                        }
                    }

                    if ($maxSubscribersPerList > -1) {
                        $criteria->compare('t.list_id', (int)$list->list_id);
                        $listSubscribersCount = ListSubscriber::model()->count($criteria);
                        if ($listSubscribersCount >= $maxSubscribersPerList) {
                            throw new Exception(Yii::t('lists', 'The maximum number of allowed subscribers for this list has been reached.'));
                        }
                    }
                }

                // only if this isn't a subscriber that re-subscribes and it is a double optin
                if (empty($_subscriber) && $list->opt_in == Lists::OPT_IN_DOUBLE) {
                    // bind the event handler that will send the confirm email once the subscriber is saved.
                    $this->callbacks->onSubscriberSaveSuccess = array($this->callbacks, '_sendSubscribeConfirmationEmail');
                }

                if (!$subscriber->save()) {
                    if ($subscriber->hasErrors()) {
                        throw new Exception($subscriber->shortErrors->getAllAsString());
                    }
                    throw new Exception(Yii::t('app', 'Temporary error, please contact us if this happens too often!'));
                }
                
                // 1.3.8.8 - Create optin history
                $subscriber->createOptinHistory();
                
                // raise event
                $this->callbacks->onSubscriberSave(new CEvent($this->callbacks, array(
                    'fields' => &$fields,
                    'action' => 'subscribe',
                )));

                // if no exception thrown but still there are errors in any of the instances, stop.
                foreach ($instances as $instance) {
                    if (!empty($instance->errors)) {
                        throw new Exception(Yii::t('app', 'Your form has a few errors. Please fix them and try again!'));
                    }
                }

                // raise event. at this point everything seems to be fine.
                $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                    'action'        => 'subscribe',
                )));

                $transaction->commit();

                if (!empty($_subscriber)) {
                    $subscriber->status = ListSubscriber::STATUS_UNCONFIRMED;
                    $subscriber->save(false);
                    $this->redirect(array('lists/subscribe_confirm', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid, 'do' => 'subscribe-back'));
                }

                // is single opt in.
                if ($list->opt_in == Lists::OPT_IN_SINGLE) {
                    // because redirect will fail curl requests that doesn't follow
                    // $this->redirect(array('lists/subscribe_confirm', 'list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid));
                    $this->setData('singleOptInSubscribeConfirm', true);
                } else {
                    $this->redirect(array('lists/subscribe_pending', 'list_uid' => $list->list_uid));
                }

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_list_subscribe_at_transaction_end', $this);

            } catch (Exception $e) {

                $transaction->rollback();

                if (($message = $e->getMessage())) {
                    Yii::app()->notify->addError($message);
                }

                // bind default save error event handler
                $this->callbacks->onSubscriberSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onSubscriberSaveError(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                    'action'        => 'subscribe',
                )));

                // since 1.3.5.9
                $duplicate = isset(Yii::app()->params['validationSubscriberAlreadyExists']) && Yii::app()->params['validationSubscriberAlreadyExists'] === true;
                if ($duplicate) {
                    
                    // since 1.3.9.8
                    $existingSubscriber = clone $subscriber;
                    if (isset(Yii::app()->params['validationSubscriberAlreadyExistsSubscriber'])) {
                        $existingSubscriber = clone Yii::app()->params['validationSubscriberAlreadyExistsSubscriber'];
                        unset(Yii::app()->params['validationSubscriberAlreadyExistsSubscriber']);
                        
                        // 1.4.0
                        if ($existingSubscriber->status == ListSubscriber::STATUS_UNSUBSCRIBED) {
                            $existingSubscriber->saveStatus(ListSubscriber::STATUS_UNCONFIRMED);
                            $existingSubscriber->removeOptinHistory();
                            $existingSubscriber->confirmOptinHistory();
                            $redirect = array(
                                'lists/subscribe_confirm', 
                                'list_uid'       => $existingSubscriber->list->list_uid,
                                'subscriber_uid' => $existingSubscriber->subscriber_uid,
                                'do'             => 'subscribe-back',
                            );
                            Yii::app()->notify->clearAll();
                            $this->redirect($redirect);
                        }
                    }
                    //
                    
                    if ($redirect = $list->getSubscriberExistsRedirect($existingSubscriber)) {
                        Yii::app()->notify->clearAll();
                        unset($existingSubscriber);
                        $this->redirect($redirect);
                    }
                    
                    unset($existingSubscriber);
                }

                // since 1.3.9.8
                if (isset(Yii::app()->params['validationSubscriberAlreadyExistsSubscriber'])) {
                    unset(Yii::app()->params['validationSubscriberAlreadyExistsSubscriber']);
                }
                //
                
                // 1.3.7
                if ($duplicate) {
                    if (empty($_subscriber) || empty($_subscriber->subscriber_uid)) {
                        $_subscriber = ListSubscriber::model()->findByAttributes(array(
                            'list_id' => $list->list_id, 
                            'email'   => Yii::app()->request->getPost('EMAIL'),
                        ));
                    }
                    if (!empty($_subscriber)) {
                        Yii::app()->notify->clearAll();
                        if ($_subscriber->status == ListSubscriber::STATUS_CONFIRMED) {
                            Yii::app()->notify->addInfo(Yii::t('lists', 'The email address is already registered in the list, therefore you have been redirected to the update profile page.'));
                            $updateProfileUrl = $this->createUrl('lists/update_profile', array('list_uid' => $list->list_uid, 'subscriber_uid' => $_subscriber->subscriber_uid));
                            $this->redirect($updateProfileUrl);
                        }
                    }
                }
            }

            // since 1.3.5.6
            Yii::app()->hooks->doAction('frontend_list_subscribe_after_transaction', $this);

            // because redirect will fail curl requests that doesn't follow
            if ($this->getData('singleOptInSubscribeConfirm')) {
                $_GET['list_uid']       = $list->list_uid;
                $_GET['subscriber_uid'] = $subscriber->subscriber_uid;
                return $this->run('subscribe_confirm');
            }
        }

        // raise event. simply the fields are shown
        $this->callbacks->onSubscriberFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // add the default sorting of fields actions and raise the event
        $this->callbacks->onSubscriberFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onSubscriberFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        // since 1.3.5.6
        $content = Yii::app()->hooks->applyFilters('frontend_list_subscribe_before_transform_list_fields', $content);

        // list fields transform and handling
        $content = preg_replace('/\[LIST_FIELDS\]/', $fieldsHtml, $content, 1, $count);

        // since 1.3.5.6
        $content = Yii::app()->hooks->applyFilters('frontend_list_subscribe_after_transform_list_fields', $content);

        // embed output
        if ($request->getQuery('output') == 'embed') {
			$width  = (string)$request->getQuery('width', 400);
			$height = (string)$request->getQuery('height', 400);
			$width  = substr($width, -1)  == '%' ? (int)substr($width,  0, strlen($width) - 1)  . '%' : (int)$width  . 'px';
			$height = substr($height, -1) == '%' ? (int)substr($height, 0, strlen($height) - 1) . '%' : (int)$height . 'px';
	        
            $attributes = array(
                'width'  => $width,
                'height' => $height,
                'target' => $request->getQuery('target'),
            );
            $this->layout = 'embed';
            $this->setData('attributes', $attributes);
        }

        $this->render('display_content', compact('content'));
    }

    /**
     * This page is shown after the user has submitted the subscription form
     */
    public function actionSubscribe_pending($list_uid)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }

        $pageType = $this->loadPageTypeModel('subscribe-pending');
        $page     = $this->loadPageModel($list->list_id, $pageType->type_id);

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);

        // add the list name
        $content = str_replace('[LIST_NAME]', CHtml::encode($list->display_name), $content);

        $this->render('display_content', compact('content'));
    }

    /**
     * This pages is shown when the user clicks on the confirmation email that he received
     */
    public function actionSubscribe_confirm($list_uid, $subscriber_uid, $do = null)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }

        $subscriber = $this->loadSubscriberModel($subscriber_uid, $list->list_id);
        
        // update profile link
        $updateProfileUrl = $this->createUrl('lists/update_profile', array('list_uid' => $list->list_uid, 'subscriber_uid' => $subscriber->subscriber_uid));

        // if confirmed, redirect to update profile.
        if ($subscriber->isConfirmed) {
            $this->redirect($updateProfileUrl);
        }

        if (!$subscriber->isUnconfirmed) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        $subscriber->status = ListSubscriber::STATUS_CONFIRMED;
        
        // since 1.3.6.2
        if ($do != 'subscribe-back' && $list->subscriber_require_approval == Lists::TEXT_YES) {
            $subscriber->status = ListSubscriber::STATUS_UNAPPROVED;
        }
        //
        
        $saved = $subscriber->save(false);

        // 1.3.8.8 - Confirm optin history
        if ($saved) {
            $subscriber->confirmOptinHistory();
        }

        // since 1.3.5 - this should be expanded in future
        $takeListAction = $saved && $subscriber->getIsConfirmed();
        if ($takeListAction) {
            $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_SUBSCRIBE);
        }
        
        $name       = 'subscribe-confirm' . ($subscriber->getIsUnapproved() ? '-approval' : '');
        $pageType   = $this->loadPageTypeModel($name);
        $page       = $this->loadPageModel($list->list_id, $pageType->type_id);
        $options    = Yii::app()->options;
        
        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);

        // add the list name
        $content = str_replace('[LIST_NAME]', CHtml::encode($list->display_name), $content);

        // add update profile url
        $content = str_replace('[UPDATE_PROFILE_URL]', $updateProfileUrl, $content);

        if ($do != 'subscribe-back') {
            if ($options->get('system.customer.action_logging_enabled', true)) {
                $customer = $list->customer;
                $customer->attachBehavior('logAction', array(
                    'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
                ));
                $customer->logAction->subscriberCreated($subscriber);
            }
            
            // since 1.3.8.2
            $subscriber->sendCreatedNotifications();

            // since 1.3.6.2
            $subscriber->handleWelcome();

        } else {

            // since it subscribes again, it makes sense to remove from unsubscribes logs for any campaign.
            CampaignTrackUnsubscribe::model()->deleteAllByAttributes(array(
                'subscriber_id' => (int)$subscriber->subscriber_id,
            ));
        }

        if ($saved) {
            // raise event.
            $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                'subscriber'    => $subscriber,
                'list'          => $list,
                'action'        => 'subscribe-confirm',
                'do'            => $do,
            )));
        }

        // since 1.3.5.9
        $searchReplace = array();
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        if (!empty($searchReplace)) {
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

            // 1.5.3
            if (CampaignHelper::isTemplateEngineEnabled()) {
                $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            }
        }
        //

        $this->render('display_content', compact('content'));
    }

    /**
     * Allows a subscriber to update his profile
     */
    public function actionUpdate_profile($list_uid, $subscriber_uid)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }

        $pageType   = $this->loadPageTypeModel('update-profile');
        $page       = $this->loadPageModel($list->list_id, $pageType->type_id);
        $subscriber = $this->loadSubscriberModel($subscriber_uid, $list->list_id);

        if ($subscriber->status != ListSubscriber::STATUS_CONFIRMED) {
            if ($redirect = $list->getSubscriber404Redirect()) {
                $this->redirect($redirect);
            }
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $subscriber->list_id    = $list->list_id;
        $subscriber->ip_address = Yii::app()->request->getUserHostAddress();

        $options = Yii::app()->options;
        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);
        
        $searchReplace = array(
            '[LIST_NAME]'       => CHtml::encode($list->display_name),
            '[SUBMIT_BUTTON]'   => CHtml::button(Yii::t('lists', 'Update profile'), array('type' => 'submit', 'class' => 'btn btn-default')),
            '[UNSUBSCRIBE_URL]' => $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe/' . $subscriber->subscriber_uid,
        );
        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

        // load the list fields and bind the behavior.
        $listFields = ListField::model()->findAll(array(
            'condition' => 'list_id = :lid',
            'params'    => array(':lid' => $list->list_id),
            'order'     => 'sort_order asc'
        ));

        if (empty($listFields)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $hooks   = Yii::app()->hooks;

        $usedTypes = array();
        foreach ($listFields as $listField) {
            $usedTypes[] = $listField->type->type_id;
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition('type_id', $usedTypes);
        $fieldTypes = ListFieldType::model()->findAll($criteria);

        $instances = array();

        foreach ($fieldTypes as $fieldType) {

            if (empty($fieldType->identifier) || !is_file(Yii::getPathOfAlias($fieldType->class_alias).'.php')) {
                continue;
            }

            $component = Yii::app()->getWidgetFactory()->createWidget($this, $fieldType->class_alias, array(
                'fieldType'     => $fieldType,
                'list'          => $list,
                'subscriber'    => $subscriber,
            ));

            if (!($component instanceof FieldBuilderType)) {
                continue;
            }

            // run the component to hook into next events
            $component->run();

            $instances[] = $component;
        }

        $fields = array();

        // if the fields are saved
        if ($request->isPostRequest) {

            // since 1.3.5.8
            Yii::app()->hooks->doAction('frontend_list_update_profile_before_transaction');

            $transaction = Yii::app()->db->beginTransaction();

            try {

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_list_update_profile_at_transaction_start');

                if (!$subscriber->save()) {
                    if ($subscriber->hasErrors()) {
                        throw new Exception($subscriber->shortErrors->getAllAsString());
                    }
                    throw new Exception(Yii::t('app', 'Temporary error, please contact us if this happens too often!'));
                }

                // raise event
                $this->callbacks->onSubscriberSave(new CEvent($this->callbacks, array(
                    'fields' => &$fields,
                    'action' => 'update-profile',
                )));

                // if no exception thrown but still there are errors in any of the instances, stop.
                foreach ($instances as $instance) {
                    if (!empty($instance->errors)) {
                        throw new Exception(Yii::t('app', 'Your form has a few errors. Please fix them and try again!'));
                    }
                }

                // bind the default actions for sucess update
                $this->callbacks->onSubscriberSaveSuccess = array($this->callbacks, '_profileUpdatedSuccessfully');

                // raise event. at this point everything seems to be fine.
                $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                    'action'        => 'update-profile',
                )));
                
                if ($list->customer->getGroupOption('lists.subscriber_profile_update_optin_history', 'yes') == 'yes') {
                    $subscriber->createOptinHistory()->confirmOptinHistory();
                }

                $transaction->commit();

                // since 1.3.5.8
                Yii::app()->hooks->doAction('frontend_list_update_profile_at_transaction_end');

            } catch (Exception $e) {

                $transaction->rollback();
                Yii::app()->notify->addError($e->getMessage());

                // bind default save error event handler
                $this->callbacks->onSubscriberSaveError = array($this->callbacks, '_collectAndShowErrorMessages');

                // raise event
                $this->callbacks->onSubscriberSaveError(new CEvent($this->callbacks, array(
                    'instances'     => $instances,
                    'subscriber'    => $subscriber,
                    'list'          => $list,
                    'action'        => 'update-profile',
                )));
            }
        }

        // since 1.3.5.8
        Yii::app()->hooks->doAction('frontend_list_update_profile_after_transaction');

        // raise event. simply the fields are shown
        $this->callbacks->onSubscriberFieldsDisplay(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // add the default sorting of fields actions and raise the event
        $this->callbacks->onSubscriberFieldsSorting = array($this->callbacks, '_orderFields');
        $this->callbacks->onSubscriberFieldsSorting(new CEvent($this->callbacks, array(
            'fields' => &$fields,
        )));

        // and build the html for the fields.
        $fieldsHtml = '';
        foreach ($fields as $type => $field) {
            $fieldsHtml .= $field['field_html'];
        }

        // since 1.3.5.8
        $content = Yii::app()->hooks->applyFilters('frontend_list_update_profile_before_transform_list_fields', $content);

        // list fields transform and handling
        $content = preg_replace('/\[LIST_FIELDS\]/', $fieldsHtml, $content, 1, $count);

        // since 1.3.5.9
        $searchReplace = array();
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();

        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        if (!empty($searchReplace)) {
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
            
            // 1.5.3
            if (CampaignHelper::isTemplateEngineEnabled()) {
                $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            }
        }
        //

        // since 1.3.5.8
        $content = Yii::app()->hooks->applyFilters('frontend_list_update_profile_after_transform_list_fields', $content);

        $this->render('display_content', compact('content'));
    }

    /**
     * Allows a subscriber to unsubscribe from a list
     */
    public function actionUnsubscribe($list_uid, $subscriber_uid = null, $campaign_uid = null, $type = null)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }

        $pageType = $this->loadPageTypeModel('unsubscribe-form');
        $page     = $this->loadPageModel($list->list_id, $pageType->type_id);

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);

        // list name
        $content = str_replace('[LIST_NAME]', CHtml::encode($list->display_name), $content);

        // submit button
        $content = str_replace('[SUBMIT_BUTTON]', CHtml::button(Yii::t('lists', 'Unsubscribe'), array('type' => 'submit', 'class' => 'btn btn-default')), $content);

        $_subscriber = $_campaign = null;

        if (!empty($subscriber_uid)) {
            $_subscriber     = $this->loadSubscriberModel($subscriber_uid, $list->list_id);
            $allowedStatuses = array(ListSubscriber::STATUS_CONFIRMED, ListSubscriber::STATUS_UNSUBSCRIBED, ListSubscriber::STATUS_MOVED);
            if (!in_array($_subscriber->status, $allowedStatuses)) {
                $_subscriber = null;
            }
        }

        if (!empty($campaign_uid)) {
            $_campaign = Campaign::model()->findByAttributes(array(
                'campaign_uid'  => $campaign_uid,
                'list_id'       => (int)$list->list_id,
            ));
        }

        $subscriber       = new ListSubscriber();
        $trackUnsubscribe = new CampaignTrackUnsubscribe();
        
        $this->data->list             = $list;
        $this->data->subscriber       = $subscriber;
        $this->data->_subscriber      = $_subscriber;
        $this->data->_campaign        = $_campaign;
        $this->data->trackUnsubscribe = new CampaignTrackUnsubscribe();

        $subscriber->onRules = array($this->callbacks, '_addUnsubscribeEmailValidationRules');
        $subscriber->onAfterValidate = array($this->callbacks, '_unsubscribeAfterValidate');
        
        $request = Yii::app()->request;
        $hooks = Yii::app()->hooks;

        if ($request->isPostRequest && !isset($_POST[$subscriber->modelName]) && isset($_POST['EMAIL'])) {
            $_POST[$subscriber->modelName]['email'] = $request->getPost('EMAIL');
        }
        
        // since 1.3.6.2
        if ($request->isPostRequest && ($reason = $request->getPost('unsubscribe_reason'))) {
            $this->setUnsubscribeReason($reason);
        }

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($subscriber->modelName, array()))) {
            $subscriber->attributes = $attributes;
            $subscriber->validate();
        } elseif (!$request->isPostRequest && !empty($_subscriber)) {
            $subscriber->email = $_subscriber->email;
            // $subscriber->validate(); // do not auto validate for now
        }

        // since 1.3.4.7 - for usage in behavior to allow direct unsubscribe from a list
        // decide if we keep this as it raises multiple questions
        if (!$request->isPostRequest && !empty($subscriber->email) && $list->opt_out == Lists::OPT_OUT_SINGLE && !empty($type) && $type == 'unsubscribe-direct') {
            $this->setData('unsubscribeDirect', true);
            $subscriber->validate();
        }

        // input fields
        $reasonField = '';
        if (!empty($_campaign)) {
            $trackUnsubscribe->reason = $this->getUnsubscribeReason();
            $reasonField = $this->renderPartial('_unsubscribe-reason', compact('trackUnsubscribe'), true);
        }
        $inputField    = $this->renderPartial('_unsubscribe-input', compact('subscriber'), true);
        $searchReplace = array(
            '[UNSUBSCRIBE_EMAIL_FIELD]'  => $inputField,
            '[UNSUBSCRIBE_REASON_FIELD]' => $reasonField,
        );
        
        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
        }

        // avoid a nasty bug with model input array
        $content = preg_replace('/(ListSubscriber)(\[)([a-zA-Z0-9]+)(\])/', '$1_$3_', $content);

        // remove all remaining tags, if any of course.
        $content = preg_replace('/\[([^\]]?)+\]/six', '', $content);

        // put back the correct input array
        $content = preg_replace('/(ListSubscriber)(\_)([a-zA-Z0-9]+)(\_)/', '$1[$3]', $content);

        // embed output
        if ($request->getQuery('output') == 'embed') {
            $attributes = array(
                'width'     => (int)$request->getQuery('width', 400),
                'height'    => (int)$request->getQuery('height', 200),
            );
            $this->layout = 'embed';
            $this->setData('attributes', $attributes);
        }
        $this->render('display_content', compact('content'));
    }

    /**
     * This page is shown when the subscriber confirms his
     * unsubscription from email by clicking on the unsubscribe confirm link.
     */
    public function actionUnsubscribe_confirm($list_uid, $subscriber_uid, $campaign_uid = null)
    {
        $list = $this->loadListModel($list_uid);

        if (!empty($list->customer)) {
            $this->setCustomerLanguage($list->customer);
        }

        $pageType        = $this->loadPageTypeModel('unsubscribe-confirm');
        $page            = $this->loadPageModel($list->list_id, $pageType->type_id);
        $subscriber      = $this->loadSubscriberModel($subscriber_uid, $list->list_id);
        $options         = Yii::app()->options;
        $allowedStatuses = array(ListSubscriber::STATUS_CONFIRMED, ListSubscriber::STATUS_UNSUBSCRIBED, ListSubscriber::STATUS_MOVED);
        
        if (!in_array($subscriber->status, $allowedStatuses)) {
            if ($redirect = $list->getSubscriber404Redirect()) {
                $this->redirect($redirect);
            }
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $subscriber->status = ListSubscriber::STATUS_UNSUBSCRIBED;
        $saved = $subscriber->save(false);

        if ($saved && !empty($campaign_uid)) {
            $campaign = Campaign::model()->findByAttributes(array(
                'campaign_uid'  => $campaign_uid,
                'list_id'       => (int)$list->list_id,
            ));

            // add this subscriber to the list of campaign unsubscribers
            if (!empty($campaign)) {
                $track = CampaignTrackUnsubscribe::model()->findByAttributes(array(
                    'campaign_id'   => (int)$campaign->campaign_id,
                    'subscriber_id' => (int)$subscriber->subscriber_id,
                ));
                
                $saved = true;
                if (empty($track)) {
                    $track = new CampaignTrackUnsubscribe();
                    $track->campaign_id   = (int)$campaign->campaign_id;
                    $track->subscriber_id = (int)$subscriber->subscriber_id;
                    $track->ip_address    = Yii::app()->request->getUserHostAddress();
                    $track->user_agent    = substr(Yii::app()->request->getUserAgent(), 0, 255);
                    // since 1.3.6.2
                    $track->reason        = $this->getUnsubscribeReason();
                    //
                    $saved = $track->save();
                }

                if ($saved) {
                    // raise the action, hook added in 1.2
                    $this->setData('ipLocationSaved', false);
                    Yii::app()->hooks->doAction('frontend_lists_after_track_campaign_unsubscribe', $this, $track);
                }
            }
        }

        // since 1.3.5 - this should be expanded in future
        if ($saved) {
            $subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_UNSUBSCRIBE);
        }

        // 1.3.7.8 - Confirm optout history
        if ($saved) {
            $subscriber->confirmOptoutHistory();
        }

        $content = !empty($page->content) ? $page->content : $pageType->content;
        $content = CHtml::decode($content);

        // add the list name
        $content = str_replace('[LIST_NAME]', CHtml::encode($list->display_name), $content);

        // subscribe url
        $subscribeUrl = Yii::app()->apps->getAppUrl('frontend', sprintf('lists/%s/subscribe/%s', $list->list_uid, $subscriber->subscriber_uid), true);

        $content = str_replace('[SUBSCRIBE_URL]', $subscribeUrl, $content);

        // since 1.3.5.9
        $searchReplace = array();
        $subscriberCustomFields = $subscriber->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        if (!empty($searchReplace)) {
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

            // 1.5.3
            if (CampaignHelper::isTemplateEngineEnabled()) {
                $content = CampaignHelper::parseByTemplateEngine($content, $searchReplace);
            }
        }
        //
        
        if ($saved) {
            // raise event.
            $this->callbacks->onSubscriberSaveSuccess(new CEvent($this->callbacks, array(
                'subscriber'    => $subscriber,
                'list'          => $list,
                'action'        => 'unsubscribe-confirm',
            )));
        }

        // since 1.5.3
        $customer = Customer::model()->findByPk($list->customer_id);
        
        if (Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
        	if (!$customer->asa('logAction')) {
		        $customer->attachBehavior('logAction', array(
			        'class' => 'customer.components.behaviors.CustomerActionLogBehavior'
		        ));
	        }
            $customer->logAction->subscriberUnsubscribed($subscriber);
        }

        $dsParams = array('useFor' => DeliveryServer::USE_FOR_LIST_EMAILS);
        if ($list->customerNotification->unsubscribe == ListCustomerNotification::TEXT_YES && !empty($list->customerNotification->unsubscribe_to) && ($server = DeliveryServer::pickServer(0, $list, $dsParams))) {
        	
	        $params = CommonEmailTemplate::getAsParamsArrayBySlug('list-subscriber-unsubscribed',
		        array(
			        'fromName'  => $list->default->from_name,
			        'subject'   => Yii::t('lists', 'List subscriber unsubscribed!'),
		        ), array(
			        '[LIST_NAME]'        => $list->name,
			        '[SUBSCRIBER_EMAIL]' => $subscriber->getDisplayEmail(),
		        )
	        );

	        $recipients = explode(',', $list->customerNotification->unsubscribe_to);
	        $recipients = array_map('trim', $recipients);
	        
            foreach ($recipients as $recipient) {
                if (!FilterVarHelper::email($recipient)) {
                    continue;
                }
                $params['to'] = array($recipient => $customer->getFullName());
                $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($list)->sendEmail($params);
            }
        }

        $this->render('display_content', compact('content'));
    }

	/**
	 * @since 1.7.6
	 * @param $list_uid
	 *
	 * @throws CHttpException
	 */
	public function actionVcard($list_uid)
	{
		if (!MW_COMPOSER_SUPPORT) {
			return $this->redirect(array('site/index'));
		}

		$list = $this->loadListModel($list_uid);
		if (empty($list->customer) || empty($list->company)) {
			return $this->redirect(array('site/index'));
		}
		
		$company  = $list->company;
		$customer = $list->customer;
		$this->setCustomerLanguage($customer);

		$className = '\JeroenDesloovere\VCard\VCard';
		$vcard     = new $className();
		
		$vcard->addName($customer->last_name, $customer->first_name, '', '', '');
		$vcard->addCompany($company->name);

		$emailAddress = !empty($list->default->from_email) ? $list->default->from_email : $customer->email;
		$vcard->addEmail($emailAddress);
		
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
	 * Allows a subscriber to unsubscribe from a customer, from all lists
	 * 
	 * @param $customer_uid
	 * @param string $subscriber_uid
	 * @param string $campaign_uid
	 *
	 * @throws CException
	 * @throws CHttpException
	 * @since 1.7.4
	 */
    public function actionUnsubscribe_from_customer($customer_uid, $subscriber_uid = '', $campaign_uid = '')
    {
    	$customer = Customer::model()->findByAttributes(array(
    		'customer_uid' => $customer_uid,
	    ));
    	
    	if (empty($customer)) {
		    throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
	    }
    	
	    $subscriber = new ListSubscriber('search');
	    $request    = Yii::app()->request;
	    $notify     = Yii::app()->notify;
	    
	    if (!empty($subscriber_uid)) {
	    	if ($sub = ListSubscriber::model()->findByUid($subscriber_uid)) {
			    $subscriber->email = $sub->email;
		    }
	    }

	    // since 1.3.6.2
	    if ($request->isPostRequest && ($reason = $request->getPost('unsubscribe_reason'))) {
		    $this->setUnsubscribeReason($reason);
	    }

	    // input fields
	    $reasonField = '';
	    if (!empty($campaign_uid) && ($campaign = Campaign::model()->findByUid($campaign_uid))) {
		    $trackUnsubscribe = new CampaignTrackUnsubscribe();
		    $trackUnsubscribe->reason = $this->getUnsubscribeReason();
		    $reasonField = $this->renderPartial('_unsubscribe-reason', compact('trackUnsubscribe'), true);
	    }
	    
	    if ($request->isPostRequest) {
	    	
	    	$subscriber->attributes = (array)$request->getPost($subscriber->getModelName(), array());
	    	
	    	if (!FilterVarHelper::email($subscriber->email)) {
	    		
			    $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
	    		$subscriber->addError('email', Yii::t('lists', 'Please enter a valid email address!'));
		    
	    	} else {
	    		
	    		$lists = Lists::model()->findAllByAttributes(array(
	    			'customer_id' => $customer->customer_id,
			    ));
	    		
	    		foreach ($lists as $list) {
	    			
	    			$listSubscriber = ListSubscriber::model()->findByAttributes(array(
	    				'list_id' => $list->list_id,
	    				'email'   => $subscriber->email,
	    				'status'  => ListSubscriber::STATUS_CONFIRMED
				    ));
	    			
	    			if (empty($listSubscriber)) {
	    				continue;
				    }
	    			
	    			// capture the output 
				    ob_start();
				    ob_implicit_flush();
	    			
				    $this->actionUnsubscribe_confirm($list->list_uid, $listSubscriber->subscriber_uid, $campaign_uid);
				    
				    // and discard it
				    ob_get_clean();
			    }

			    $subscriber->email = '';
	    		$notify
				    ->clearAll()
				    ->addSuccess(Yii::t('lists', 'You have been successfully unsubscribed from all this customer lists!'));
		    }
	    }
    	
    	$this->render('unsubscribe-from-customer', compact('customer', 'subscriber', 'reasonField'));
    }
    
    /**
     * Allow guests to add their email address into the global blacklist
     * 
     * @since 1.3.7.3
     */
    public function actionBlock_address()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new BlockEmailRequest();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            $model->ip_address = $request->getUserHostAddress();
            $model->user_agent = StringHelper::truncateLength($request->getUserAgent(), 255);

            $success = $error = '';
            $transaction = Yii::app()->getDb()->beginTransaction();

            try {

                if (!$model->save()) {
                    throw new Exception($error = Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                }

                $detailsLink = CHtml::link(Yii::t('messages', 'here'), Yii::app()->options->get('system.urls.backend_absolute_url') . 'block-email-request/index');
                $message = new UserMessage();
                $message->title   = 'New block email request!';
                $message->message = 'Email "{email}" has requested to be blocked! You can see more details {here}!';
                $message->message_translation_params = array(
                    '{email}' => $model->email,
                    '{here}'  => $detailsLink,
                );
                $message->broadcast();

                $blocked = false;
                $sent    = false;
                $server  = DeliveryServer::pickServer();
                
                // send the confirmation email if we have a server
                if ($server) {

	                $params = CommonEmailTemplate::getAsParamsArrayBySlug('confirm-block-email-request',
		                array(
			                'to'      => array($model->email => $model->email),
			                'subject' => Yii::t('email_blacklist', 'Confirm the block email request!'),
		                ), array(
			                '[CONFIRMATION_URL]' => Yii::app()->apps->getAppUrl('frontend', sprintf('lists/block-address-confirmation/%s', $model->confirmation_key), true),
		                )
	                );

	                $sent = $server->sendEmail($params);
                }
                
                // if no server found or email could not be sent, then just proceed,
	            // subscriber does not have to suffer because of inability to send the email
                if (!$server || !$sent) {
	                $model->block();
	                $blocked = true;
                }

                $transaction->commit();
                
                if ($sent) {
	                $success = Yii::t('app', 'Please check your email in order to confirm the request!');
                }
                
                if ($blocked) {
	                $success = Yii::t('email_blacklist', 'The email address has been successfully blocked!');
                }

            } catch (Exception $e) {

                $transaction->rollback();
                
                if (empty($error)) {
                    $error = Yii::t('email_blacklist', 'Something went wrong, please try again!');
                }
            }

            if ($success) {
                $notify->addSuccess($success);
            } elseif ($error) {
                $notify->addError($error);
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('lists/block_address'));
            }
        }

        $appName = Yii::app()->options->get('system.common.site_name');
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Block email address'),
            'pageHeading'       => Yii::t('email_blacklist', 'Block email address'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_blacklist', 'Block email address') => $this->createUrl('email_blacklist/index'),
            )
        ));
        
        $this->render('block-address', compact('model', 'appName'));
    }

    /**
     * Confirm block address request
     *
     * @since 1.5.5
     */
    public function actionBlock_address_confirmation($key)
    {
        $notify = Yii::app()->notify;
        $model  = BlockEmailRequest::model()->findByAttributes(array(
            'confirmation_key' => $key,
        ));
        
        if (empty($model)) {
            $notify->addError(Yii::t('email_blacklist', 'The request has not been found!'));
            return $this->redirect(array('lists/block_address'));
        }

        if ($model->isConfirmed) {
            $notify->addError(Yii::t('email_blacklist', 'The request has already been confirmed!'));
            return $this->redirect(array('lists/block_address'));
        }
        
        $model->block();

        $notify->addSuccess(Yii::t('email_blacklist', 'The email address has been successfully blocked!'));
        return $this->redirect(array('lists/block_address'));
    }

    /**
     * Responds to the ajax calls from the country list fields
     */
    public function actionFields_country_states_by_country_name()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('site/index'));
        }

        $countryName = $request->getQuery('country');
        $country = Country::model()->findByAttributes(array('name' => $countryName));
        if (empty($country)) {
            return $this->renderJson(array());
        }

        $statesList = array();
        $states     = !empty($country->zones) ? $country->zones : array();

        foreach ($states as $state) {
            $statesList[$state->name] = $state->name;
        }

        return $this->renderJson($statesList);
    }

	/**
	 * Responds to the ajax calls from the state list fields
	 */
	public function actionFields_country_by_zone()
	{
		$request = Yii::app()->request;
		if (!$request->isAjaxRequest) {
			return $this->redirect(array('dashboard/index'));
		}

		$zone = Zone::model()->findByAttributes(array(
			'name' => $request->getQuery('zone')
		));

		if (empty($zone)) {
			return $this->renderJson(array());
		}

		return $this->renderJson(array(
			'country' => array(
				'name' => $zone->country->name,
				'code' => $zone->country->code,
			),
		));
	}

    /**
     * Helper method to load the list AR model
     */
    public function loadListModel($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        $model = Lists::model()->find($criteria);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to load the list page type AR model
     */
    public function loadPageTypeModel($slug)
    {
        $model = ListPageType::model()->findBySlug($slug);

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to load the list page AR model
     */
    public function loadPageModel($list_id, $type_id)
    {
        return ListPage::model()->findByAttributes(array(
            'list_id' => (int)$list_id,
            'type_id' => (int)$type_id,
        ));
    }

    /**
     * Helper method to load the list subscriber AR model
     */
    public function loadSubscriberModel($subscriber_uid, $list_id)
    {
        $model = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid'    => $subscriber_uid,
            'list_id'           => (int)$list_id
        ));

        if ($model === null) {
            if (($list = Lists::model()->findByPk((int)$list_id)) && ($redirect = $list->getSubscriber404Redirect())) {
                $this->redirect($redirect);
            }
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method to set the language for this customer.
     */
    public function setCustomerLanguage($customer)
    {
        if (empty($customer->language_id)) {
            return $this;
        }

        // 1.5.3 - language has been forced already at init
        if (($langCode = Yii::app()->request->getQuery('lang')) && strlen($langCode) <= 5) {
            return $this;
        }
        
        // multilanguage is available since 1.1 and the Language class does not exist prior to that version
        if (!version_compare(Yii::app()->options->get('system.common.version'), '1.1', '>=')) {
            return $this;
        }
        
        if (!empty($customer->language)) {
            Yii::app()->setLanguage($customer->language->getLanguageAndLocaleCode());
        }

        return $this;
    }

    /**
     * @param null
     */
    public function setUnsubscribeReason($reason)
    {
        if (empty($reason)) {
            return;
        }
        $session = Yii::app()->session;
        $session['unsubscribe_reason']    = StringHelper::truncateLength($reason, 250);
        $session['unsubscribe_reason_ts'] = time();
    }

    /**
     * @return mixed
     */
    public function getUnsubscribeReason()
    {
        $session     = Yii::app()->session;
        $unsubReason = null;
        if (isset($session['unsubscribe_reason'], $session['unsubscribe_reason_ts'])) {
            if ($session['unsubscribe_reason_ts'] + 600 > time()) {
                $unsubReason = $session['unsubscribe_reason'];
            }
            unset($session['unsubscribe_reason'], $session['unsubscribe_reason_ts']);
        }
        return $unsubReason;
    }

}
