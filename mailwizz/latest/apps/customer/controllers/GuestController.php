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
        $this->onBeforeAction = array($this, '_registerJuiBs');
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('guest.js')));
        $this->getData('bodyClasses')->add('hold-transition login-page');
    }

	/**
	 * Display the login form
	 * 
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionIndex()
    {
        $model   = new CustomerLogin();
        $request = Yii::app()->request;
        $options = Yii::app()->options;

        if (GuestFailAttempt::model()->setBaseInfo()->hasTooManyFailures) {
            throw new CHttpException(403, Yii::t('app', 'Your access to this resource is forbidden.'));
        }

	    // since 1.5.1
	    $customize    = new OptionCustomization();
	    $loginBgImage = $customize->getCustomerLoginBgUrl(5000, 5000);
	    
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
	        	
		        // since 1.3.6.2
		        CustomerLoginLog::addNew(Yii::app()->customer->getModel());

		        $this->redirect(Yii::app()->customer->returnUrl);
	        }
	        
	        GuestFailAttempt::registerByPlace('Customer login');
        }

        $registrationEnabled = $options->get('system.customer_registration.enabled', 'no') == 'yes';
        $facebookEnabled     = $options->get('system.customer_registration.facebook_enabled', 'no') == 'yes';
        $twitterEnabled      = $options->get('system.customer_registration.twitter_enabled', 'no') == 'yes';
        
        // twitter library requires php >= 5.6
        if ($twitterEnabled && version_compare(PHP_VERSION, '5.6', '<')) {
            $twitterEnabled = false;
        }

        // facebook library requires php >= 5.4
        if ($facebookEnabled && version_compare(PHP_VERSION, '5.4', '<')) {
            $facebookEnabled = false;
        }
        
        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('customers', 'Please login'),
            'pageHeading'   => Yii::t('customers', 'Please login'),
        ));

        $this->render('login', compact('model', 'registrationEnabled', 'facebookEnabled', 'twitterEnabled', 'loginBgImage'));
    }

    /**
     * Display the registration form
     * 
     * @throws CException
     * @throws CHttpException
     */
    public function actionRegister()
    {
        $options = Yii::app()->options;
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new Customer('register');
        $company = new CustomerCompany('register');

        if ($options->get('system.customer_registration.enabled', 'no') != 'yes') {
            $this->redirect(array('guest/index'));
        }

        if (GuestFailAttempt::model()->setBaseInfo()->hasTooManyFailures) {
            throw new CHttpException(403, Yii::t('app', 'Your access to this resource is forbidden.'));
        }

        $facebookEnabled    = $options->get('system.customer_registration.facebook_enabled', 'no') == 'yes';
        $twitterEnabled     = $options->get('system.customer_registration.twitter_enabled', 'no') == 'yes';
        $companyRequired    = $options->get('system.customer_registration.company_required', 'no') == 'yes';
        
        // 1.3.6.3
        $mustConfirmEmail   = $options->get('system.customer_registration.require_email_confirmation', 'yes') == 'yes';
        $requireApproval    = $options->get('system.customer_registration.require_approval', 'no') == 'yes';
        $defaultCountry     = $options->get('system.customer_registration.default_country', '');
        $defaultTimezone    = $options->get('system.customer_registration.default_timezone', '');
        
        if (!empty($defaultCountry)) {
            $company->country_id = $defaultCountry;
        }
        
        if (!empty($defaultTimezone)) {
            $model->timezone = $defaultTimezone;
        }
        //
        
        // 1.5.5
        $newsletterApiEnabled     = $options->get('system.customer_registration.api_enabled', 'no') == 'yes';
        $newsletterApiConsentText = $options->get('system.customer_registration.api_consent_text', '');
        
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            $model->status     = Customer::STATUS_PENDING_CONFIRM;
            
            Yii::app()->hooks->addAction('controller_action_save_data', array($this, '_checkEmailDomainForbidden'), 100);
            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => true,
                'model'     => $model,
            )));
            
            if ($collection->success) {
                
                $transaction = Yii::app()->getDb()->beginTransaction();

                try {
                    
                    if ($model->hasErrors() || !$model->save()) {
                        throw new Exception(CHtml::errorSummary($model));
                    }
                    
                    if (EmailBlacklist::isBlacklisted($model->email)) {
                        throw new Exception(Yii::t('customers', 'This email address is blacklisted!'));
                    }
                    
                    if ($companyRequired) {
                        $company->attributes  = (array)$request->getPost($company->modelName, array());
                        $company->customer_id = $model->customer_id;
                        if (!$company->save()) {
                            throw new Exception(CHtml::errorSummary($company));
                        }
                    }
                    
                    // 1.3.6.3
                    if ($mustConfirmEmail) {
                        $this->_sendRegistrationConfirmationEmail($model, $company);
                    }
                    
                    $this->_sendNewCustomerNotifications($model, $company);
                    
                    // 1.3.7
                    $this->_subscribeToEmailList($model);
                    
                    // 1.3.6.3
                    if (!$mustConfirmEmail) {
                        $transaction->commit();
                        $this->redirect(array('guest/confirm_registration', 'key' => $model->confirmation_key));
                    }
                    
                    if ($notify->isEmpty) {
                        if ($mustConfirmEmail) {
                            $notify->addSuccess(Yii::t('customers', 'Congratulations, your account has been created, check your inbox for email confirmation! Please note that sometimes the email might land in spam/junk!'));
                        } else {
                            $notify->addSuccess(Yii::t('customers', 'Congratulations, your account has been created, you can login now!'));
                        }
                    }
                    $transaction->commit();
                    $this->redirect(array('guest/index'));
                } catch (Exception $e) {
                    $transaction->rollback();
                    GuestFailAttempt::registerByPlace('Customer register');
                }    
            }
        }

        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('customers', 'Please register'),
            'pageHeading'   => Yii::t('customers', 'Please register'),
        ));

        $this->render('register', compact('model', 'company', 'companyRequired', 'facebookEnabled', 'twitterEnabled', 'newsletterApiEnabled', 'newsletterApiConsentText'));
    }

    public function actionConfirm_registration($key)
    {
        $options = Yii::app()->options;
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = Customer::model()->findByAttributes(array(
            'confirmation_key' => $key,
            'status'           => Customer::STATUS_PENDING_CONFIRM,
        ));

        if (empty($model)) {
            $this->redirect(array('guest/index'));
        }

        if (($defaultGroup = (int)$options->get('system.customer_registration.default_group')) > 0) {
            $group = CustomerGroup::model()->findByPk((int)$defaultGroup);
            if (!empty($group)) {
                $model->group_id = $group->group_id;
            }
        }

        $requireApproval = $options->get('system.customer_registration.require_approval', 'no') == 'yes';
        $model->status   = !$requireApproval ? Customer::STATUS_ACTIVE : Customer::STATUS_PENDING_ACTIVE;
        if (!$model->save(false)) {
            $this->redirect(array('guest/index'));
        }

        if ($requireApproval) {
            $notify->addSuccess(Yii::t('customers', 'Congratulations, you have successfully confirmed your account.'));
            $notify->addSuccess(Yii::t('customers', 'You will be able to login once an administrator will approve it.'));
            $this->redirect(array('guest/index'));
        }

        // send welcome email if needed
        $sendWelcome        = $options->get('system.customer_registration.welcome_email', 'no') == 'yes';
        $sendWelcomeSubject = $options->get('system.customer_registration.welcome_email_subject', '');
        $sendWelcomeContent = $options->get('system.customer_registration.welcome_email_content', '');
        if (!empty($sendWelcome) && !empty($sendWelcomeSubject) && !empty($sendWelcomeContent)) {
            $searchReplace = array(
                '[FIRST_NAME]' => $model->first_name,
                '[LAST_NAME]'  => $model->last_name,
                '[FULL_NAME]'  => $model->fullName,
                '[EMAIL]'      => $model->email,
            );
            $sendWelcomeSubject = str_replace(array_keys($searchReplace), array_values($searchReplace), $sendWelcomeSubject);
            $sendWelcomeContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $sendWelcomeContent);
            $emailTemplate = $options->get('system.email_templates.common');
            $emailTemplate = str_replace('[CONTENT]', $sendWelcomeContent, $emailTemplate);

            $email = new TransactionalEmail();
            $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
            $email->to_name      = $model->getFullName();
            $email->to_email     = $model->email;
            $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
            $email->subject      = $sendWelcomeSubject;
            $email->body         = $emailTemplate;
            $email->save();
        }

        $identity = new CustomerIdentity($model->email, $model->password);
        $identity->setId($model->customer_id)->setAutoLoginToken($model);

        if (!Yii::app()->customer->login($identity, 3600 * 24 * 30)) {
            $this->redirect(array('guest/index'));
        }

        $notify->addSuccess(Yii::t('customers', 'Congratulations, your account is now ready to use.'));
        $notify->addSuccess(Yii::t('customers', 'Please start by filling your account and company info.'));
        $this->redirect(array('account/index'));
    }

    /**
     * Display the "Forgot password" form
     * 
     * @throws CException
     * @throws CHttpException
     */
    public function actionForgot_password()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new CustomerPasswordReset();

        if (GuestFailAttempt::model()->setBaseInfo()->hasTooManyFailures) {
            throw new CHttpException(403, Yii::t('app', 'Your access to this resource is forbidden.'));
        }

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            
            $model->attributes = $attributes;

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $model->validate(),
                'model'     => $model,
            )));

            if ($collection->success) {
            	
                $options  = Yii::app()->options;
                $customer = Customer::model()->findByAttributes(array('email' => $model->email));
                $model->customer_id = $customer->customer_id;
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
                $email->customer_id  = $customer->customer_id;
                $email->to_name      = $customer->getFullName();
                $email->to_email     = $customer->email;
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
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('customers', 'Retrieve a new password for your account.'),
            'pageHeading'   => Yii::t('customers', 'Retrieve a new password for your account.'),
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
        $model = CustomerPasswordReset::model()->findByAttributes(array(
            'reset_key' => $reset_key,
            'status'    => CustomerPasswordReset::STATUS_ACTIVE,
        ));

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $randPassword   = StringHelper::random();
        $hashedPassword = Yii::app()->passwordHasher->hash($randPassword);

        Customer::model()->updateByPk((int)$model->customer_id, array('password' => $hashedPassword));
        $model->status = CustomerPasswordReset::STATUS_USED;
        $model->save();

        $options  = Yii::app()->options;
        $notify   = Yii::app()->notify;
        $customer = Customer::model()->findByPk($model->customer_id);

        // since 1.3.9.3
        Yii::app()->hooks->doAction('customer_controller_guest_reset_password', $collection = new CAttributeCollection(array(
            'customer'      => $customer,
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
			        '[LOGIN_EMAIL]'     => $customer->email,
			        '[LOGIN_PASSWORD]'  => $randPassword,
			        '[LOGIN_URL]'       => Yii::app()->createAbsoluteUrl('guest/index'),
		        )
	        );
	        
            $email               = new TransactionalEmail();
            $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
	        $email->customer_id  = $customer->customer_id;
            $email->to_name      = $customer->getFullName();
            $email->to_email     = $customer->email;
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
     * @throws CException
     */
    public function actionFacebook()
    {
        $options = Yii::app()->options;

        if ($options->get('system.customer_registration.enabled', 'no') != 'yes') {
            $this->redirect(array('guest/index'));
        }

        if ($options->get('system.customer_registration.facebook_enabled', 'no') != 'yes') {
            $this->redirect(array('guest/index'));
        }
        
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->redirect(array('guest/index'));
        }

        $appID     = $options->get('system.customer_registration.facebook_app_id');
        $appSecret = $options->get('system.customer_registration.facebook_app_secret');

        if (strlen($appID) < 15 || strlen($appSecret) < 32) {
            $this->redirect(array('guest/index'));
        }

        $notify   = Yii::app()->notify;
        $request  = Yii::app()->request;

        $className = '\Facebook\Facebook';
        $facebook  = new $className(array(
            'app_id'                => $appID,
            'app_secret'            => $appSecret,
            'default_graph_version' => 'v2.7',
        ));

        $helper = $facebook->getRedirectLoginHelper();

        if (!$request->getQuery('code')) {
            $this->redirect($helper->getLoginUrl($this->createAbsoluteUrl('guest/facebook'), array('email','public_profile')));
        }

        if (!($token = $helper->getAccessToken())) {
            $this->redirect(array('guest/index'));
        }

        // let's see if the user is logged into facebook and he has approved our app.
        try {
            $response  = $facebook->get('/me?locale=en_US&fields=first_name,last_name,email', $token);
            $user      = $response->getGraphUser();
        }
        catch(Exception $e){}

        // the user needs to approve our application.
        if(empty($user)) {
            $this->redirect(array('guest/index'));
        }

        // if we are here, means the customer approved the app
        // create the default attributes.
        $attributes = array(
            'oauth_uid'     => $user->getId(),
            'oauth_provider'=> 'facebook',
            'first_name'    => $user->getFirstName(),
            'last_name'     => $user->getLastName(),
            'email'         => $user->getEmail(),
        );
        $attributes = Yii::app()->ioFilter->stripClean($attributes);
        
        // DO NOT $customer->attributes = $attributes because most of them will not be assigned
        $customer = new Customer();
        foreach($attributes AS $key => $value) {
            if (empty($value)) {
                $notify->addError(Yii::t('customers', 'Unable to retrieve all your account data!'));
                $this->redirect(array('guest/index'));
            }
            $customer->setAttribute($key, $value);
        }

        $exists = Customer::model()->findByAttributes(array(
            'oauth_uid'         => $customer->oauth_uid,
            'oauth_provider'    => 'facebook',
        ));

        if(!empty($exists)) {
            if ($exists->status == Customer::STATUS_ACTIVE) {
                $identity = new CustomerIdentity($exists->email, $exists->password);
                $identity->setId($exists->customer_id)->setAutoLoginToken($exists);
                Yii::app()->customer->login($identity, 3600 * 24 * 30);
                $this->redirect(array('dashboard/index'));
            }
            $notify->addError(Yii::t('customers', 'Your account is not active!'));
            $this->redirect(array('guest/index'));
        }

        // if another customer with same email address, do nothing
        $exists = Customer::model()->findByAttributes(array('email' => $customer->email));
        if (!empty($exists)) {
            $notify->addError(Yii::t('customers', 'There is another account using this email address, please fill in the form to recover your password!'));
            $this->redirect(array('guest/forgot_password'));
        }

        $requireApproval         = $options->get('system.customer_registration.require_approval', 'no') == 'yes';
        $randPassword            = StringHelper::random(8);
        $customer->fake_password = $randPassword;
        $customer->status        = !$requireApproval ? Customer::STATUS_ACTIVE : Customer::STATUS_PENDING_ACTIVE;
        $customer->avatar        = $this->fetchCustomerRemoteImage('https://graph.facebook.com/'.$customer->oauth_uid.'/picture?height=400&type=large&width=400');

        if (($defaultGroup = (int)$options->get('system.customer_registration.default_group')) > 0) {
            $group = CustomerGroup::model()->findByPk((int)$defaultGroup);
            if (!empty($group)) {
                $customer->group_id = $group->group_id;
            }
        }

        // finally try to save the customer.
        if(!$customer->save(false)) {
            $notify->addError(Yii::t('customers', 'Unable to save your account, please contact us if this error persists!'));
            $this->redirect(array('guest/index'));
        }

        // create the email for customer
	    $params = CommonEmailTemplate::getAsParamsArrayBySlug('new-login-info',
		    array(
			    'subject' => Yii::t('app', 'Your new login info!'),
		    ), array(
			    '[LOGIN_EMAIL]'     => $customer->email,
			    '[LOGIN_PASSWORD]'  => $randPassword,
			    '[LOGIN_URL]'       => Yii::app()->createAbsoluteUrl('guest/index'),
		    )
	    );
	    
        $email = new TransactionalEmail();
        $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
        $email->to_name      = $customer->getFullName();
        $email->to_email     = $customer->email;
        $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
        $email->subject      = $params['subject'];
        $email->body         = $params['body'];
        $email->save();

        // notify admins
        $this->_sendNewCustomerNotifications($customer, new CustomerCompany());
        
        if ($requireApproval) {
            $notify->addSuccess(Yii::t('customers', 'Congratulations, your account has been successfully created.'));
            $notify->addSuccess(Yii::t('customers', 'You will be able to login once an administrator will approve it.'));
            $this->redirect(array('guest/index'));
        }

        // the customer has been saved, we need to log him in, should work okay...
        $identity = new CustomerIdentity($customer->email, $customer->password);
        $identity->setId($customer->customer_id)->setAutoLoginToken($customer);
        Yii::app()->customer->login($identity, 3600 * 24 * 30);
        $this->redirect(array('dashboard/index'));
    }

    /**
     * @throws CException
     */
    public function actionTwitter()
    {
        // the twitter library works only with php >= 5.5
        if (version_compare(PHP_VERSION, '5.5', '<')) {
            $this->redirect(array('guest/index'));
        }
        
        $options = Yii::app()->options;
        
        // avoid parsing errors on php < 5.3
        $twitterLibClassName = '\Abraham\TwitterOAuth\TwitterOAuth';
        
        if ($options->get('system.customer_registration.enabled', 'no') != 'yes') {
            $this->redirect(array('guest/index'));
        }

        if ($options->get('system.customer_registration.twitter_enabled', 'no') != 'yes') {
            $this->redirect(array('guest/index'));
        }

        $appConsumerKey     = $options->get('system.customer_registration.twitter_app_consumer_key');
        $appConsumerSecret  = $options->get('system.customer_registration.twitter_app_consumer_secret');
        $requireApproval    = $options->get('system.customer_registration.require_approval', 'no') == 'yes';

        if (strlen($appConsumerKey) < 20 || strlen($appConsumerSecret) < 40) {
            $this->redirect(array('guest/index'));
        }

        $notify   = Yii::app()->notify;
        $request  = Yii::app()->request;
        $session  = Yii::app()->session;
        
        // only if not done already.
        if (!isset($session['access_token'])) {
            // when the app is not approved.
            if ($request->getQuery('do') != 'get-request-token') {
                $twitterOauth = new $twitterLibClassName($appConsumerKey, $appConsumerSecret);
                $requestToken = $twitterOauth->oauth('oauth/request_token', array('oauth_callback' => $this->createAbsoluteUrl('guest/twitter',array('do'=>'get-request-token'))));

                if(empty($requestToken)) {
                    $this->redirect(array('guest/index'));
                }

                $session['oauth_token']        = $requestToken['oauth_token'];
                $session['oauth_token_secret'] = $requestToken['oauth_token_secret'];

                $this->redirect($twitterOauth->url('oauth/authorize', array('oauth_token' => $requestToken['oauth_token'])));
            }
            
            //when the request is made...
            if (!$request->getQuery('oauth_verifier') || empty($session['oauth_token']) || empty($session['oauth_token_secret'])) {
                $this->redirect(array('guest/index'));
            }

            $twitterOauth = new $twitterLibClassName($appConsumerKey, $appConsumerSecret, $session['oauth_token'], $session['oauth_token_secret']);
            $accessToken  = $twitterOauth->oauth("oauth/access_token", array("oauth_verifier" => $request->getQuery('oauth_verifier')));

            if (empty($accessToken)) {
                $this->redirect(array('guest/index'));
            }

            $session['access_token'] = $accessToken;
        }

        $accessToken = $session['access_token'];
        $twitterOauth = new $twitterLibClassName($appConsumerKey, $appConsumerSecret, $accessToken['oauth_token'], $accessToken['oauth_token_secret']);
        $_user = $twitterOauth->get('account/verify_credentials');
        
        if (empty($_user) || !empty($_user->errors)) {
            $this->redirect(array('guest/index'));
        }
        
        $firstName = $lastName = trim($_user->name);
        if (strpos($_user->name, ' ') !== false) {
            $names = explode(' ', $_user->name);
            if (count($names) >= 2) {
                $firstName = array_shift($names);
                $lastName  = implode(' ', $names);
            }
        }

        $attributes = array(
            'oauth_uid'      => !empty($_user->id) ? $_user->id : null,
            'oauth_provider' => 'twitter',
            'first_name'     => $firstName,
            'last_name'      => $lastName,
        );
        $attributes = Yii::app()->ioFilter->stripClean($attributes);

        $customer = new Customer();
        foreach($attributes AS $key => $value) {
            if (empty($value)) {
                $notify->addError(Yii::t('customers', 'Unable to retrieve all your account data!'));
                $this->redirect(array('guest/index'));
            }
            $customer->setAttribute($key, $value);
        }

        $exists = Customer::model()->findByAttributes(array(
            'oauth_uid'         => $customer->oauth_uid,
            'oauth_provider'    => 'twitter',
        ));

        if(!empty($exists)) {
            if ($exists->status == Customer::STATUS_ACTIVE) {
                $identity = new CustomerIdentity($exists->email, $exists->password);
                $identity->setId($exists->customer_id)->setAutoLoginToken($exists);
                Yii::app()->customer->login($identity, 3600 * 24 * 30);
                $this->redirect(array('dashboard/index'));
            }
            $notify->addError(Yii::t('customers', 'Your account is not active!'));
            $this->redirect(array('guest/index'));
        }

        if (!$request->isPostRequest) {
            $this->setData('pageHeading', Yii::t('customers', 'Enter your email address'));
            return $this->render('twitter-email', compact('customer'));
        }

        if (($attributes = $request->getPost($customer->modelName, array()))) {
            $customer->email = isset($attributes['email']) ? $attributes['email'] : null;
        }

        if (!FilterVarHelper::email($customer->email)) {
            $notify->addError(Yii::t('customers', 'Invalid email address provided!'));
            $this->setData('pageHeading', Yii::t('customers', 'Enter your email address'));
            return $this->render('twitter-email', compact('customer'));
        }

        // if another customer with same email address, do nothing
        $exists = Customer::model()->findByAttributes(array('email' => $customer->email));
        if (!empty($exists)) {
            $notify->addError(Yii::t('customers', 'There is another account using this email address, please fill in the form to recover your password!'));
            $this->redirect(array('guest/forgot_password'));
        }

        // create a random 8 chars password for the customer, and assign the active status.
        $randPassword            = StringHelper::random(8);
        $customer->fake_password = $randPassword;
        $customer->status        = !$requireApproval ? Customer::STATUS_ACTIVE : Customer::STATUS_PENDING_ACTIVE;
        $customer->avatar        = $this->fetchCustomerRemoteImage($_user->profile_image_url);

        if (($defaultGroup = (int)$options->get('system.customer_registration.default_group')) > 0) {
            $group = CustomerGroup::model()->findByPk((int)$defaultGroup);
            if (!empty($group)) {
                $customer->group_id = $group->group_id;
            }
        }

        // finally try to save the customer.
        if(!$customer->save(false)) {
            $notify->addError(Yii::t('customers', 'Unable to save your account, please contact us if this error persists!'));
            $this->redirect(array('guest/index'));
        }

        // create the email for customer
	    $params = CommonEmailTemplate::getAsParamsArrayBySlug('new-login-info',
		    array(
			    'subject' => Yii::t('app', 'Your new login info!'),
		    ), array(
			    '[LOGIN_EMAIL]'     => $customer->email,
			    '[LOGIN_PASSWORD]'  => $randPassword,
			    '[LOGIN_URL]'       => Yii::app()->createAbsoluteUrl('guest/index'),
		    )
	    );
	    
        $email = new TransactionalEmail();
        $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
        $email->to_name      = $customer->getFullName();
        $email->to_email     = $customer->email;
        $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
        $email->subject      = $params['subject'];
        $email->body         = $params['body'];
        $email->save();

        // notify admins
        $this->_sendNewCustomerNotifications($customer, new CustomerCompany());
        
        if ($requireApproval) {
            $notify->addSuccess(Yii::t('customers', 'Congratulations, your account has been successfully created.'));
            $notify->addSuccess(Yii::t('customers', 'You will be able to login once an administrator will approve it.'));
            $this->redirect(array('guest/index'));
        }

        // the customer has been saved, we need to log him in, should work okay...
        $identity = new CustomerIdentity($customer->email, $customer->password);
        $identity->setId($customer->customer_id)->setAutoLoginToken($customer);
        Yii::app()->customer->login($identity, 3600 * 24 * 30);
        $this->redirect(array('dashboard/index'));
    }

    /**
     * Display country zones
     */
    public function actionZones_by_country()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            $this->redirect(array('guest/index'));
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'zone_id, name';
        $criteria->compare('country_id', (int)$request->getQuery('country_id'));
	    $criteria->order = 'name ASC';
        $models = Zone::model()->findAll($criteria);

        $zones = array();
        foreach ($models as $model) {
            $zones[] = array(
                'zone_id'  => $model->zone_id,
                'name'     => $model->name
            );
        }
        return $this->renderJson(array('zones' => $zones));
    }

    /**
     * Callback for controller_action_save_data action hook
     * @param $collection
     */
    public function _checkEmailDomainForbidden($collection)
    {
        if (!$collection->success) {
            return;
        }
        
        $email = $collection->model->email;
        if (empty($email) || !FilterVarHelper::email($email)) {
            return;
        }
        
        $options = Yii::app()->options;
        if (!($domains = $options->get('system.customer_registration.forbidden_domains', ''))) {
            return;
        }
        
        $domains = explode(',', $domains);
        $domains = array_map('strtolower', array_map('trim', $domains));
        $domains = array_unique($domains);
        
        $emailDomain = explode('@', $email);
        $emailDomain = strtolower($emailDomain[1]);
        
        foreach ($domains as $domain) {
            if (strpos($emailDomain, $domain) === 0) {
                Yii::app()->notify->addError(Yii::t('customers', 'We\'re sorry, but we don\'t accept registrations from {domain}. <br />Please use another email address, preferably not from a free service!', array(
                    '{domain}' => $emailDomain,
                )));
                $collection->success = false;
                break;
            }
        }
    }

    /**
     * Callback after success registration to send the confirmation email
     */
    protected function _sendRegistrationConfirmationEmail(Customer $customer, CustomerCompany $company)
    {
        $options = Yii::app()->options;
	    $params  = CommonEmailTemplate::getAsParamsArrayBySlug('customer-confirm-registration',
		    array(
			    'subject' => Yii::t('customers', 'Please confirm your account!'),
		    ), array(
			    '[CONFIRMATION_URL]' => $options->get('system.urls.customer_absolute_url') . 'guest/confirm-registration/' . $customer->confirmation_key,
		    )
	    );
	    
        $email = new TransactionalEmail();
        $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
        $email->to_name      = $customer->getFullName();
        $email->to_email     = $customer->email;
        $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
        $email->subject      = $params['subject'];
        $email->body         = $params['body'];
        $email->save();
    }

    /**
     * Callback after success registration to send the notification emails to admin users
     */
    protected function _sendNewCustomerNotifications(Customer $customer, CustomerCompany $company)
    {
        $options    = Yii::app()->options;
        $recipients = $options->get('system.customer_registration.new_customer_registration_notification_to');

        if (empty($recipients)) {
            return;
        }

	    $recipients = explode(',', $recipients);
	    $recipients = array_map('trim', $recipients);
	    
	    $customerInfo = array();
	    foreach ($customer->getAttributes(array('first_name', 'last_name', 'email')) as $attributeName => $attributeValue) {
		    $customerInfo[] = $customer->getAttributeLabel($attributeName) . ': ' . $attributeValue;
	    }
	    $customerInfo = implode('<br />', $customerInfo);
	    
	    $params = CommonEmailTemplate::getAsParamsArrayBySlug('new-customer-registration',
		    array(
			    'subject' => Yii::t('customers', 'New customer registration!'),
		    ), array(
			    '[CUSTOMER_URL]' => Yii::app()->options->get('system.urls.backend_absolute_url') . 'customers/update/id/' . $customer->customer_id,
			    '[CUSTOMER_INFO]'=> $customerInfo
		    )
	    );
	    
        foreach ($recipients as $recipient) {
            if (!FilterVarHelper::email($recipient)) {
                continue;
            }
            $email = new TransactionalEmail();
            $email->sendDirectly = (bool)($options->get('system.customer_registration.send_email_method', 'transactional') == 'direct');
            $email->to_name      = $recipient;
            $email->to_email     = $recipient;
            $email->from_name    = $options->get('system.common.site_name', 'Marketing website');
            $email->subject      = $params['subject'];
            $email->body         = $params['body'];
            $email->save();
        }
    }

    /**
     * @param $url
     * @return null|string
     */
    protected function fetchCustomerRemoteImage($url)
    {
        if (empty($url)) {
            return null;
        }

        $imageRequest = AppInitHelper::simpleCurlGet($url);
        if ($imageRequest['status'] != 'success' || empty($imageRequest['message'])) {
            return null;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.avatars');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            return null;
        }

        $tempDir = FileSystemHelper::getTmpDirectory();
        $name    = StringHelper::random(20);

        if (!file_exists($tempDir) || !is_dir($tempDir)) {
            return null;
        }

        if (!file_put_contents($tempDir . '/' . $name, $imageRequest['message'])) {
            return null;
        }

        if (($info = getimagesize($tempDir . '/' . $name)) === false) {
            unlink($tempDir . '/' . $name);
            return null;
        }

        if (empty($info[0]) || empty($info[1]) || empty($info['mime'])) {
            unlink($tempDir . '/' . $name);
            return null;
        }

        $mimes = array();
        $mimes['jpg'] = Yii::app()->extensionMimes->get('jpg')->toArray();
        $mimes['png'] = Yii::app()->extensionMimes->get('png')->toArray();
        $mimes['gif'] = Yii::app()->extensionMimes->get('gif')->toArray();

        $extension = null;
        foreach ($mimes as $_extension => $_mimes) {
            if (in_array($info['mime'], $_mimes)) {
                $extension = $_extension;
                break;
            }
        }

        if ($extension === null) {
            unlink($tempDir . '/' . $name);
            return null;
        }

        if (!copy($tempDir . '/' . $name, $storagePath . '/' . $name . '.' . $extension)) {
            unlink($tempDir . '/' . $name);
            return null;
        }

        return '/frontend/assets/files/avatars/' . $name . '.' . $extension;
    }

    /**
     * @since 1.3.7
     * @param Customer $customer
     */
    protected function _subscribeToEmailList(Customer $customer)
    {
        // 1.5.5
        $apiEnabled     = Yii::app()->options->get('system.customer_registration.api_enabled', 'no') == 'yes';
        $apiUrl         = Yii::app()->options->get('system.customer_registration.api_url');
        $privateKey     = Yii::app()->options->get('system.customer_registration.api_private_key');
        $publicKey      = Yii::app()->options->get('system.customer_registration.api_public_key');
        $listUids       = Yii::app()->options->get('system.customer_registration.api_list_uid');
        $consentText    = Yii::app()->options->get('system.customer_registration.api_consent_text', '');
        
        if (empty($apiEnabled) || empty($apiUrl) || empty($privateKey) || empty($publicKey) || empty($listUids)) {
            return;
        }
        
        if (!empty($consentText) && (empty($customer->newsletter_consent) || $consentText != $customer->newsletter_consent)) {
            return;
        }
        
        require_once Yii::getPathOfAlias('common.vendors.MailWizzApi.Autoloader') . '.php';
        Yii::registerAutoloader(array('MailWizzApi_Autoloader', 'autoloader'));
        
        MailWizzApi_Base::setConfig(new MailWizzApi_Config(array(
            'apiUrl'        => $apiUrl,
            'publicKey'     => $publicKey,
            'privateKey'    => $privateKey,
        )));
        
        $lists    = CommonHelper::getArrayFromString($listUids, ',');
        $endpoint = new MailWizzApi_Endpoint_ListSubscribers();
        
        foreach ($lists as $list) {
            $endpoint->create($list, array(
                'EMAIL'    => $customer->email,
                'FNAME'    => $customer->first_name,
                'LNAME'    => $customer->last_name,
                'CONSENT'  => $customer->newsletter_consent,
                'details'  => array(
                    'ip_address' => Yii::app()->request->getUserHostAddress(),
                    'user_agent' => StringHelper::truncateLength(Yii::app()->request->getUserAgent(), 255)
                ),
            ));
        }
    }

    /**
     * Called when the application is offline
     */
    public function actionOffline()
    {
        if (Yii::app()->options->get('system.common.site_status') !== 'offline') {
            $this->redirect(array('dashboard/index'));
        }

        throw new CHttpException(503, Yii::app()->options->get('system.common.site_offline_message'));
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

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('register'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
