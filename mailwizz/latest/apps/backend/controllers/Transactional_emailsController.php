<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Transactional_emailsController
 *
 * Handles the actions for transactional emails related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */

class Transactional_emailsController extends Controller
{
    public function init()
    {
        parent::init();
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('transactional-emails.js')));
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
     * List all available emails
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $email   = new TransactionalEmail('search');
        $email->unsetAttributes();

        $email->attributes = (array)$request->getQuery($email->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('transactional_emails', 'View transactional emails'),
            'pageHeading'       => Yii::t('transactional_emails', 'View transactional emails'),
            'pageBreadcrumbs'   => array(
                Yii::t('transactional_emails', 'Transactional emails') => $this->createUrl('transactional_emails/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('email'));
    }

    /**
     * Preview transactional email
     */
    public function actionPreview($id)
    {
        $request = Yii::app()->request;
        $email   = TransactionalEmail::model()->findByPk((int)$id);

        if (empty($email)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $this->renderPartial('preview', compact('email'), false, true);
    }

    /**
     * resend transactional email
     */
    public function actionResend($id)
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $email   = TransactionalEmail::model()->findByPk((int)$id);

        if (empty($email) || $email->status != TransactionalEmail::STATUS_SENT) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $email->status       = TransactionalEmail::STATUS_UNSENT;
        $email->sendDirectly = true;

        if ($email->save(false)) {
            $notify->addSuccess(Yii::t('app', 'The email has been successfully resent!'));
        }

        $this->redirect($request->getPost('returnUrl', array('transactional_emails/index')));
    }

    /**
     * Delete existing email
     */
    public function actionDelete($id)
    {
        $email = TransactionalEmail::model()->findByPk((int)$id);

        if (empty($email)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $email->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('transactional_emails/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $email,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }
}
