<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_subscribersController
 *
 * Handles the actions for list subscribers related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */

class List_subscribersController extends Controller
{

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete, subscribe, unsubscribe, disable',
        ), parent::filters());
    }

    /**
     * Campaigns sent to this subscriber
     */
    public function actionCampaigns($list_uid, $subscriber_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $subscriber = $this->loadSubscriberModel($subscriber_uid);
        $request    = Yii::app()->request;

        $model = new CampaignDeliveryLog('search');
        $model->campaign_id   = -1;
        $model->subscriber_id = (int)$subscriber->subscriber_id;
        $model->status        = null;

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('list_subscribers', 'Subscriber campaigns'),
            'pageHeading'     => Yii::t('list_subscribers', 'Subscriber campaigns'),
            'pageBreadcrumbs' => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Subscribers') => $this->createUrl('list_subscribers/index', array('list_uid' => $list->list_uid)),
                Yii::t('list_subscribers', 'Campaigns') => $this->createUrl('list_subscribers/campaigns', array('list_uid' => $list_uid, 'subscriber_uid' => $subscriber_uid)),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('campaigns', compact('model', 'list', 'subscriber'));
    }
    
    /**
     * Delete existing list subscriber
     */
    public function actionDelete($subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $subscriber = $this->loadSubscriberModel($subscriber_uid);

        if ($subscriber->canBeDeleted) {
            $subscriber->delete();
            if ($logAction = $subscriber->list->customer->asa('logAction')) {
                $logAction->subscriberDeleted($subscriber);
            }
        }

        $redirect = null;
        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('lists/all_subscribers'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'subscriber' => $subscriber,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Disable existing list subscriber
     */
    public function actionDisable($subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $subscriber = $this->loadSubscriberModel($subscriber_uid);

        if ($subscriber->getCanBeDisabled()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_DISABLED);
        }

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully disabled!'));
            $this->redirect($request->getPost('returnUrl', array('lists/all_subscribers')));
        }
    }
    
    /**
     * Unsubscribe existing list subscriber
     */
    public function actionUnsubscribe($subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $subscriber = $this->loadSubscriberModel($subscriber_uid);

        if ($subscriber->getCanBeUnsubscribed()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
        }

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber was successfully unsubscribed!'));
            $this->redirect($request->getPost('returnUrl', array('list/all_subscribers')));
        }
    }

    /**
     * Subscribe existing list subscriber
     */
    public function actionSubscribe($subscriber_uid)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $subscriber = $this->loadSubscriberModel($subscriber_uid);
        $oldStatus  = $subscriber->status;

        if ($subscriber->getCanBeApproved()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);
            $subscriber->handleApprove(true)->handleWelcome(true);
        } elseif ($subscriber->getCanBeConfirmed()) {
            $subscriber->saveStatus(ListSubscriber::STATUS_CONFIRMED);
        }

        if (!$request->isAjaxRequest) {
            if ($oldStatus == ListSubscriber::STATUS_UNSUBSCRIBED) {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list unsubscriber was successfully subscribed back!'));
            } elseif ($oldStatus == ListSubscriber::STATUS_UNAPPROVED) {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber has been approved and notified!'));
            } else {
                $notify->addSuccess(Yii::t('list_subscribers', 'Your list subscriber has been confirmed!'));
            }
            $this->redirect($request->getPost('returnUrl', array('list/all_subscribers')));
        }
    }
    
    /**
     * Return profile info
     */
    public function actionProfile($subscriber_uid)
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('lists/all_subscribers'));
        }

        $subscriber = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
        ));
        
        if (empty($subscriber)) {
            return '';
        }
        
        return $this->renderPartial('_profile-in-modal', array(
            'list'          => $subscriber->list,
            'subscriber'    => $subscriber,
            'subscriberName'=> $subscriber->getFullName(),
            'optinHistory'  => !empty($subscriber->optinHistory) ? $subscriber->optinHistory : null,
            'optoutHistory' => $subscriber->status == ListSubscriber::STATUS_UNSUBSCRIBED && !empty($subscriber->optoutHistory) ? $subscriber->optoutHistory : null,
        ));
    }

    /**
     * Export profile info
     */
    public function actionProfile_export($subscriber_uid)
    {
        $notify     = Yii::app()->notify;
        $subscriber = $this->loadSubscriberModel($subscriber_uid);
        $data       = $subscriber->getFullData();
        
        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('subscriber-profile.csv');

        @fputcsv($fp, array_keys($data), ',', '"');

        @fputcsv($fp, array_values($data), ',', '"');

        @fclose($fp);

        Yii::app()->end();
    }

	/**
	 * Helper method to load the list AR model
	 */
	public function loadListModel($list_uid)
	{
		$model = Lists::model()->findByAttributes(array(
			'list_uid' => $list_uid,
		));

		if ($model === null) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		return $model;
	}
	
    /**
     * Helper method to load the list subscriber AR model
     */
    public function loadSubscriberModel($subscriber_uid)
    {
        $model = ListSubscriber::model()->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }
}
