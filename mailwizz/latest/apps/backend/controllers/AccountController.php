<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AccountController
 * 
 * Handles the actions for account related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class AccountController extends Controller
{
	/**
	 * Default action, allowing to update the account
	 * 
	 * @throws CException
	 */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $user    = Yii::app()->user->getModel();
        $user->confirm_email = $user->email;
        
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
                'user'      => $user,
            )));
            
            if ($collection->success) {
                $this->redirect(array('account/index'));
            }
        }

	    $twoFaSettings = new OptionTwoFactorAuth();
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Update account'),
            'pageHeading'       => Yii::t('users', 'Update account'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('users', 'Update account'),
            )
        ));

        $this->render('index', compact('user', 'twoFaSettings'));
    }

	/**
	 * Update the account 2fa settings
	 * 
	 * @throws CException
	 */
	public function action2fa()
	{
		$request       = Yii::app()->request;
		$notify        = Yii::app()->notify;
		$twoFaSettings = new OptionTwoFactorAuth();

		/* make sure 2FA is enabled */
		if (!$twoFaSettings->isEnabled) {
			$notify->addWarning(Yii::t('app', '2FA is not enabled in this system!'));
			return $this->redirect(array('index'));
		}

		$user = UserForTwoFactorAuth::model()->findByPk((int)Yii::app()->user->getId());

		if ($request->isPostRequest && ($attributes = $request->getPost($user->modelName))) {
			$user->attributes = $attributes;
			
			if ($user->save()) {
				$notify->addSuccess(Yii::t('users', 'User info successfully updated!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller'=> $this,
				'success'   => $notify->hasSuccess,
				'user'      => $user,
			)));
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
			'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', '2FA'),
			'pageHeading'       => Yii::t('users', '2FA'),
			'pageBreadcrumbs'   => array(
				Yii::t('customers', 'Account') => $this->createUrl('account/index'),
				Yii::t('customers', '2FA') => $this->createUrl('account/2fa'),
				Yii::t('app', 'Update')
			)
		));

		$this->render('2fa', compact('user', 'qrCodeUri'));
	}
    
    /**
     * Log the user out from the application
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->user->loginUrl);    
    }

    /**
     * Save the grid view columns for this user
     */
    public function actionSave_grid_view_columns()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        $model      = $request->getPost('model');
        $controller = $request->getPost('controller');
        $action     = $request->getPost('action');
        $columns    = $request->getPost('columns', array());
        
        if (!($redirect = $request->getServer('HTTP_REFERER'))) {
            $redirect = array('dashboard/index');
        }

        if (!$request->getIsPostRequest()) {
            $this->redirect($redirect);
        }
        
        if (empty($model) || empty($controller) || empty($action) || empty($columns) || !is_array($columns)) {
            $this->redirect($redirect);
        }

        $optionKey = sprintf('%s:%s:%s', (string)$model, (string)$controller, (string)$action);
        $userId    = (int)Yii::app()->user->getId();
        $optionKey = sprintf('system.views.grid_view_columns.users.%d.%s', $userId, $optionKey);
        Yii::app()->options->set($optionKey, (array)$columns);

        $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
        $this->redirect($redirect);
    }
}