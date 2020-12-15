<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Customer_login_logsController
 *
 * Handles the actions for customer login logs
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class Customer_login_logsController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		set_time_limit(0);

		$this->getData('pageScripts')->add(array('src' => AssetsUrl::js('customers-login-logs.js')));
		parent::init();
	}
	
    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, delete_all',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List customer login logs
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model   = new CustomerLoginLog('search');

        $model->unsetAttributes();
        $model->attributes = (array)$request->getQuery($model->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('customers', 'View login logs'),
            'pageHeading'     => Yii::t('customers', 'View login logs'),
            'pageBreadcrumbs' => array(
                Yii::t('customers', 'Customers')  => $this->createUrl('customers/index'),
                Yii::t('customers', 'Login logs') => $this->createUrl('customer_login_logs/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('model'));
    }

    /**
     * Delete existing customer login log
     */
    public function actionDelete($id)
    {
        $model = CustomerLoginLog::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
   
        $model->delete();

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $redirect = $request->getPost('returnUrl', array('customer_login_logs/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $model,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

	/**
	 * Delete all login logs
	 */
	public function actionDelete_all()
	{
		CustomerLoginLog::model()->deleteAll();
		
		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		if (!$request->getQuery('ajax')) {
			$notify->addSuccess(Yii::t('app', 'Your items have been successfully deleted!'));
			$this->redirect($request->getPost('returnUrl', array('customer_login_logs/index')));
		}
	}
}
