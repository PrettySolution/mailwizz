<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * GuestController
 * 
 * Handles the actions for guest related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class GuestController extends Controller
{
	/**
	 * @var string 
	 */
    public $layout = 'guest';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->getData('bodyClasses')->add('hold-transition login-page');
    }

	/**
	 * Display the login form so that a guest can login and become an administrator
	 * 
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionIndex()
    {
        $model   = new UserLogin();
        $request = Yii::app()->request;
        $options = Yii::app()->options;
        
        if (version_compare($options->get('system.common.version'), '1.3.5', '>=') && GuestFailAttempt::model()->setBaseInfo()->hasTooManyFailures) {
            throw new CHttpException(403, Yii::t('app', 'Your access to this resource is forbidden.'));
        }

	    // since 1.5.1
	    $customize    = new OptionCustomization();
	    $loginBgImage = $customize->getBackendLoginBgUrl(5000, 5000);
	    
	    if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {

		    $model->attributes = $attributes;

		    /* mark the initial status, not logged in */
		    $loggedIn = false;

		    if ($model->validate()) {

			    $twoFaSettings = new OptionTwoFactorAuth();

			    /* when 2FA is disabled system wide or per account */
			    if (!$twoFaSettings->getIsEnabled() || !$model->getModel()->getTwoFaEnabled()) {

				    $loggedIn = $model->authenticate();

			    } else {

				    /* set the right scenario */
				    $model->scenario = 'twofa-login';

				    /* when the 2FA code has been posted */
				    if ($model->twofa_code) {

					    $managerClass = '\Da\TwoFA\Manager';
					    $manager      = new $managerClass();

					    if (!$model->getModel()->twofa_timestamp) {
						    $model->getModel()->twofa_timestamp = $manager->getTimestamp();
						    $model->getModel()->save(false);
					    }
					    $previousTs = $model->getModel()->twofa_timestamp;

					    $timestamp = $manager
						    ->setCycles(5)
						    ->verify($model->twofa_code, $model->getModel()->twofa_secret, $previousTs);

					    if ($timestamp && ($loggedIn = $model->authenticate())) {
						    $model->getModel()->twofa_timestamp = $timestamp;
						    $model->getModel()->save(false);
					    } else {
						    $model->addError('twofa_code', Yii::t('customers', 'The 2FA code you have provided is not valid!'));
					    }
				    }

				    /* render the form to enter the 2FA only if not logged in */
				    if (!$loggedIn) {

					    return $this->render('login-2fa', array(
						    'model'         => $model,
						    'loginBgImage'  => $loginBgImage,
					    ));
				    }
			    }
		    }

		    Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
			    'controller'=> $this,
			    'success'   => $loggedIn,
			    'model'     => $model,
		    )));

		    if ($collection->success) {
			    $this->redirect(Yii::app()->user->returnUrl);
		    }

		    if (version_compare($options->get('system.common.version'), '1.3.5', '>=')) {
			    GuestFailAttempt::registerByPlace('Backend login');
		    }
	    }
        
        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Please login'), 
            'pageHeading'   => Yii::t('users', 'Please login'),
        ));
        
        $this->render('login', compact('model', 'loginBgImage'));
    }

    /**
     * Display the form to retrieve a forgotten password.
     * 
     * @throws CException
     * @throws CHttpException
     */
    public function actionForgot_password()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $options = Yii::app()->options;
        $model = new UserPasswordReset();
        
        if (version_compare($options->get('system.common.version'), '1.3.5', '>=') && GuestFailAttempt::model()->setBaseInfo()->hasTooManyFailures) {
            throw new CHttpException(403, Yii::t('app', 'Your access to this resource is forbidden.'));
        }
        
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $model->validate(),
                'model'     => $model,
            )));

            if (!$collection->success) {
                
                if (version_compare($options->get('system.common.version'), '1.3.5', '>=')) {
                    GuestFailAttempt::registerByPlace('Backend forgot password');
                }
            
            } else {
                
                $user = User::model()->findByAttributes(array('email' => $model->email));
                $model->user_id = $user->user_id;
                $model->save(false);
                
	            $params = CommonEmailTemplate::getAsParamsArrayBySlug('password-reset-request',
		            array(
			            'subject' => Yii::t('customers', 'Password reset request!'),
		            ), array(
			            '[CONFIRMATION_URL]' => Yii::app()->createAbsoluteUrl('guest/reset_password', array('reset_key' => $model->reset_key)),
		            )
	            );
	            
                $email = new TransactionalEmail();
                $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
                $email->to_name      = $user->getFullName();
                $email->to_email     = $user->email;
                $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
                $email->subject      = $params['subject'];
                $email->body         = $params['body'];
                $email->save();

                $notify->addSuccess(Yii::t('app', 'Please check your email address.'));
                $model->unsetAttributes();
                $model->email = null;
                
            }
        }
        
        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Retrieve a new password for your account.'), 
        ));

        $this->render('forgot_password', compact('model'));
    }

    /**
     * Reached from email, will reset the password for given user and send a new one via email.
     * 
     * @param $reset_key
     * @throws CException
     * @throws CHttpException
     */
    public function actionReset_password($reset_key)
    {
        $model = UserPasswordReset::model()->findByAttributes(array(
            'reset_key' => $reset_key,
            'status'    => UserPasswordReset::STATUS_ACTIVE,
        ));
        
        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        $randPassword   = StringHelper::random();
        $hashedPassword = Yii::app()->passwordHasher->hash($randPassword);
        
        User::model()->updateByPk((int)$model->user_id, array('password' => $hashedPassword));
        $model->status = UserPasswordReset::STATUS_USED;
        $model->save();
        
        $options = Yii::app()->options;
        $notify  = Yii::app()->notify;
        $user    = User::model()->findByPk($model->user_id);
        
        // since 1.3.9.3
        Yii::app()->hooks->doAction('backend_controller_guest_reset_password', $collection = new CAttributeCollection(array(
            'user'          => $user,
            'passwordReset' => $model,
            'randPassword'  => $randPassword,
            'hashedPassword'=> $hashedPassword,
            'sendEmail'     => true,
            'redirect'      => array('guest/index'),
        )));
        
        if (!empty($collection->sendEmail)) {

	        $params = CommonEmailTemplate::getAsParamsArrayBySlug('new-login-info',
		        array(
			        'subject' => Yii::t('app', 'Your new login info!'),
		        ), array(
			        '[LOGIN_EMAIL]'     => $user->email,
			        '[LOGIN_PASSWORD]'  => $randPassword,
			        '[LOGIN_URL]'       => Yii::app()->createAbsoluteUrl('guest/index'),
		        )
	        );
	        
            $email               = new TransactionalEmail();
            $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
            $email->to_name      = $user->getFullName();
            $email->to_email     = $user->email;
            $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
            $email->subject      = $params['subject'];
            $email->body         = $params['body'];
            $email->save();
        }
        
        if (!empty($collection->redirect)) {
            $notify->addSuccess(Yii::t('app', 'Your new login has been successfully sent to your email address.'));
            $this->redirect($collection->redirect);
        }
    }
    
    /**
     * The error handler
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo CHtml::encode($error['message']);
            } else {
                $this->setData(array(
                    'pageMetaTitle' => Yii::t('app', 'Error {code}!', array('{code}' => (int)$error['code'])), 
                ));
                $this->render('error', $error) ;
            }    
        }
    }
}