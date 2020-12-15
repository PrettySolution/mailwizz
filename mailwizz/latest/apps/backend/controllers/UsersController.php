<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UsersController
 *
 * Handles the actions for users related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class UsersController extends Controller
{
    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete', // we only allow deletion via POST request
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available users
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $user = new User('search');
        $user->unsetAttributes();

        // for filters.
        $user->attributes = (array)$request->getQuery($user->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'View users'),
            'pageHeading'       => Yii::t('users', 'View users'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('user'));
    }

    /**
     * Create a new user
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $user = new User();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($user->modelName, array()))) {
            $user->attributes = $attributes;
            if (!$user->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'user'       => $user,
            )));

            if ($collection->success) {
                $this->redirect(array('users/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Create new user'),
            'pageHeading'       => Yii::t('users', 'Create new user'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('user'));
    }

	/**
	 * Update existing user
	 * 
	 * @param $id
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionUpdate($id)
    {
        $user = User::model()->findByPk((int)$id);

        if (empty($user)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($user->user_id == Yii::app()->user->getId()) {
            $this->redirect(array('account/index'));
        }

        if ($user->removable === User::TEXT_NO && $user->user_id != Yii::app()->user->getId())  {
            Yii::app()->notify->addWarning(Yii::t('users', 'You are not allowed to update the master administrator!'));
            $this->redirect(array('users/index'));
        }

        $user->confirm_email = $user->email;
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

	    $twoFaSettings = new OptionTwoFactorAuth();
	    
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($user->modelName, array()))) {
            $user->attributes = $attributes;
            if (!$user->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'user'       => $user,
            )));
            
	        if ($collection->success) {
		        $this->redirect(array('users/update', 'id' => $user->user_id));
	        }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Update user'),
            'pageHeading'       => Yii::t('users', 'Update user'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('user', 'twoFaSettings'));
    }

	/**
	 * 2FA for existing user
	 *
	 * @param $id
	 * @throws CException
	 * @throws CHttpException
	 */
	public function action2fa($id)
	{
		$request       = Yii::app()->request;
		$notify        = Yii::app()->notify;
		$twoFaSettings = new OptionTwoFactorAuth();

		$user = UserForTwoFactorAuth::model()->findByPk((int)$id);
		if (empty($user)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}
		
		/* make sure 2FA is enabled */
		if (!$twoFaSettings->isEnabled) {
			$notify->addWarning(Yii::t('app', '2FA is not enabled in this system!'));
			return $this->redirect(array('update', 'id' => $user->user_id));
		}

		if ($request->isPostRequest && ($attributes = (array)$request->getPost($user->modelName, array()))) {
			$user->attributes = $attributes;
			if (!$user->save()) {
				$notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
			} else {
				$notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller'=> $this,
				'success'   => $notify->hasSuccess,
				'customer'  => $user,
			)));

			if ($collection->success) {
				$this->redirect(array('users/2fa', 'id' => $user->user_id));
			}
		}

		$managerClass = '\Da\TwoFA\Manager';
		$totpClass    = '\Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService';
		$qrCodeClass  = '\Da\TwoFA\Service\QrCodeDataUriGeneratorService';

		/* make sure we have the secret */
		if (empty($user->twofa_secret)) {
			$manager = new $managerClass;
			$user->twofa_secret = $manager->generateSecretKey(64);
			$user->save(false);
		}

		/* we need to create our time-based one time password secret uri */
		$company   = $twoFaSettings->companyName . ' / Backend';
		$totp      = new $totpClass($company, $user->email, $user->twofa_secret);
		$qrCode    = new $qrCodeClass($totp->run());
		$qrCodeUri = $qrCode->run();

		$this->setData(array(
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Update user'),
			'pageHeading'       => Yii::t('users', 'Update user'),
			'pageBreadcrumbs'   => array(
				Yii::t('users', 'Users') => $this->createUrl('users/index'),
				Yii::t('app', 'Update'),
			)
		));

		$this->render('2fa', compact('user', 'qrCodeUri'));
	}

    /**
     * Delete existing user
     */
    public function actionDelete($id)
    {
        $user = User::model()->findByPk((int)$id);

        if (empty($user)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($user->removable == User::TEXT_YES) {
            $user->delete();
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('users/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $user,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

}
