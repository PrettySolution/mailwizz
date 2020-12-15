<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Customer_messagesController
 *
 * Handles the actions for customer messages related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 */

class Customer_messagesController extends Controller
{
    public function init()
    {
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List customer messages
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $message = new CustomerMessage('search');

        $message->unsetAttributes();
        $message->attributes = (array)$request->getQuery($message->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('messages', 'View messages'),
            'pageHeading'     => Yii::t('messages', 'View messages'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('messages', 'Messages')   => $this->createUrl('customer_messages/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('message'));
    }

    /**
     * Create a new message
     */
    public function actionCreate()
    {
        $message = new CustomerMessage();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($message->modelName, array()))) {
            $message->attributes = $attributes;
            $message->message    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$message->modelName]['message']);

            if (!$message->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $message,
            )));

            if ($collection->success) {
                $this->redirect(array('customer_messages/index'));
            }
        }

        $message->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('messages', 'Create new message'),
            'pageHeading'     => Yii::t('messages', 'Create new message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('messages', 'Messages') => $this->createUrl('customer_messages/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('message'));
    }

    /**
     * Update existing message
     */
    public function actionUpdate($id)
    {
        $message = CustomerMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($message->modelName, array()))) {
            $message->attributes = $attributes;
            $message->message    = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$message->modelName]['message']);

            if (!$message->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $message,
            )));

            if ($collection->success) {
                $this->redirect(array('customer_messages/index'));
            }
        }

        $message->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('messages', 'Update message'),
            'pageHeading'     => Yii::t('messages', 'Update message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('messages', 'Messages')   => $this->createUrl('customer_messages/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('message'));
    }

    /**
     * View message
     */
    public function actionView($id)
    {
        $message = CustomerMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('messages', 'View message'),
            'pageHeading'     => Yii::t('messages', 'View message'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers') => $this->createUrl('customers/index'),
                Yii::t('messages', 'Messages')   => $this->createUrl('customer_messages/index'),
                Yii::t('app', 'View'),
            )
        ));

        $this->render('view', compact('message'));
    }

    /**
     * Delete existing customer message
     */
    public function actionDelete($id)
    {
        $message = CustomerMessage::model()->findByPk((int)$id);

        if (empty($message)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $message->delete();

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $redirect = $request->getPost('returnUrl', array('customer_messages/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $message,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Callback method to set the editor options for email footer in campaigns
     */
    public function _setEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('message'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);

        if ($event->params['attribute'] == 'notification_message') {
            $options['height'] = 100;
        }

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('create', 'update'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
