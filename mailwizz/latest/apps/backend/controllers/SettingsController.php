<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SettingsController
 *
 * Handles the settings for the application
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('settings.js')));
        parent::init();
    }

    /**
     * Handle the common settings page
     */
    public function actionIndex()
    {
        $request     = Yii::app()->request;
        $notify      = Yii::app()->notify;
        $commonModel = new OptionCommon();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($commonModel->modelName, array()))) {
            $commonModel->attributes = $attributes;
            if (!$commonModel->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'success'       => $notify->hasSuccess,
                'commonModel'   => $commonModel,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/index'));
            }
        }
        
        // since 1.5.1
        Yii::app()->hooks->addFilter('common_settings_auto_update_warning_message', array($this, '_commonSettingsAutoUpdateWarningMessage'), 5);

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Common settings')
            )
        ));

        $this->render('index', compact('commonModel'));
    }

    /**
     * @param string $out
     * @return string
     */
    public function _commonSettingsAutoUpdateWarningMessage($out)
    {
        $out = array();
        $out[] = CHtml::tag('strong', array(), Yii::t('settings', 'Warning!'));
        $out[] = Yii::t('settings', 'While this feature should be very safe for use, please make sure you also understand the downsides of enabling it.');
        $out[] = Yii::t('settings', 'Since this is an automated process, there are chances for an update to break your app.');
        $out[] = Yii::t('settings', 'Please make sure you have some sort of backup process in place so that you can restore your app in case things go wrong.');
        $out[] = Yii::t('settings', 'If you have the {ext} extension enabled, when an auto-update runs, it will also create a backup for you automatically!', array(
            '{ext}' => CHtml::link(Yii::t('settings', 'Backup manager'), 'https://codecanyon.net/item/backup-manager-for-mailwizz-ema/8184361?ref=twisted1919', array(
                'target' => '_blank',
                'style'  => 'color: #fff',
            )),
        ));
        $out[] = Yii::t('settings', 'Also note that we expect the following functions to be enabled on your server: {funcs} and your server must also have following binaries installed: {binaries}', array(
            '{funcs}'    => sprintf('<strong>%s</strong>', 'exec'),
            '{binaries}' => sprintf('<strong>%s</strong>', 'curl, unzip, cp'),
        ));
        
        return implode('<br />', $out);
    }

    /**
     * Handle the settings for system urls
     */
    public function actionSystem_urls()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $options = Yii::app()->options;
        $apps    = Yii::app()->apps->getWebApps();

        if ($request->isPostRequest) {
            foreach ($apps as $appName) {
                $options->set('system.urls.'.$appName.'_absolute_url', '');
            }

            $scheme = 'http';
            if ($request->getPost('scheme', 'http') == 'https') {
                $scheme = 'https';
            }
            $options->set('system.urls.scheme', $scheme);

            $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/system_urls'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'System urls')
            )
        ));

        // the scheme
        $scheme = Yii::app()->options->get('system.urls.scheme', 'http');

        $this->render('system-urls', compact('apps', 'options', 'scheme'));
    }

    /**
     * Handle the settings for importer/exporter
     */
    public function actionImport_export()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        $importModel = new OptionImporter();
        $exportModel = new OptionExporter();

        if ($request->isPostRequest) {
            $importModel->attributes = (array)$request->getPost($importModel->modelName, array());
            $exportModel->attributes = (array)$request->getPost($exportModel->modelName, array());

            if (!$importModel->save() || !$exportModel->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'   => $this,
                'success'      => $notify->hasSuccess,
                'importModel'  => $importModel,
                'exportModel'  => $exportModel
            )));

            if ($collection->success) {
                $this->redirect(array('settings/import_export'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Import/Export settings')
            )
        ));

        $this->render('import-export', compact('importModel', 'exportModel'));
    }

    /**
     * Handle the settings for console commands
     */
    public function actionCron()
    {
        $request                = Yii::app()->request;
        $notify                 = Yii::app()->notify;
        $cronDeliveryModel      = new OptionCronDelivery();
        $cronLogsModel          = new OptionCronProcessDeliveryBounce();
        $cronSubscribersModel   = new OptionCronProcessSubscribers();
        $cronRespondersModel    = new OptionCronProcessResponders();
        $cronBouncesModel       = new OptionCronProcessBounceServers();
        $cronFeedbackModel      = new OptionCronProcessFeedbackLoopServers();
        $cronEmailBoxModel      = new OptionCronProcessEmailBoxMonitors();
        $cronTransEmailsModel   = new OptionCronProcessTransactionalEmails();
	    $cronDeleteLogsModel    = new OptionCronDeleteLogs();

        if ($request->isPostRequest) {

            $cronDeliveryModel->attributes      = (array)$request->getPost($cronDeliveryModel->modelName, array());
            $cronLogsModel->attributes          = (array)$request->getPost($cronLogsModel->modelName, array());
            $cronSubscribersModel->attributes   = (array)$request->getPost($cronSubscribersModel->modelName, array());
            $cronRespondersModel->attributes    = (array)$request->getPost($cronRespondersModel->modelName, array());
            $cronBouncesModel->attributes       = (array)$request->getPost($cronBouncesModel->modelName, array());
            $cronFeedbackModel->attributes      = (array)$request->getPost($cronFeedbackModel->modelName, array());
            $cronEmailBoxModel->attributes      = (array)$request->getPost($cronEmailBoxModel->modelName, array());
            $cronTransEmailsModel->attributes   = (array)$request->getPost($cronTransEmailsModel->modelName, array());
	        $cronDeleteLogsModel->attributes    = (array)$request->getPost($cronDeleteLogsModel->modelName, array());

            $models = array(
                $cronDeliveryModel, $cronLogsModel, $cronSubscribersModel, $cronRespondersModel,
                $cronBouncesModel, $cronFeedbackModel, $cronEmailBoxModel, $cronTransEmailsModel,
                $cronDeleteLogsModel
            );
            
            $saved = true;
            foreach ($models as $model) {
                if (!$model->save()) {
                    $saved = false;
                }
            }
            
            if (!$saved) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'            => $this,
                'success'               => $notify->hasSuccess,
                'cronDeliveryModel'     => $cronDeliveryModel,
                'cronLogsModel'         => $cronLogsModel,
                'cronSubscribersModel'  => $cronSubscribersModel,
                'cronRespondersModel'   => $cronRespondersModel,
                'cronBouncesModel'      => $cronBouncesModel,
                'cronFeedbackModel'     => $cronFeedbackModel,
                'cronEmailBoxModel'     => $cronEmailBoxModel,
                'cronTransEmailsModel'  => $cronTransEmailsModel,
	            'cronDeleteLogsModel'   => $cronDeleteLogsModel,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/cron'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Cron jobs settings')
            )
        ));

        $this->render('cron', compact('cronDeliveryModel', 'cronLogsModel', 'cronSubscribersModel', 'cronRespondersModel', 'cronBouncesModel', 'cronFeedbackModel', 'cronEmailBoxModel', 'cronTransEmailsModel', 'cronDeleteLogsModel'));
    }

    /**
     * Handle the settings for email templates
     */
    public function actionEmail_templates($type = 'common')
    {
    	$types   = OptionEmailTemplate::getTypesList();
        $type    = OptionEmailTemplate::getTypeById($type);
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new OptionEmailTemplate($type['id']);
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        if ($request->isPostRequest) {
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (isset(Yii::app()->params['POST'][$model->modelName][$type['id']])) {
                $model->{$type['id']} = Yii::app()->params['POST'][$model->modelName][$type['id']];
            }

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/email_templates'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Email templates')
            )
        ));
        
        $this->render('email-templates', compact('model', 'types', 'type'));
    }

    /**
     * Handle the settings for email blacklist checks
     */
    public function actionEmail_blacklist()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $blacklistModel = new OptionEmailBlacklist();

        if ($request->isPostRequest) {

            $blacklistModel->unsetAttributes();
            $blacklistModel->attributes = (array)$request->getPost($blacklistModel->modelName, array());

            if (!$blacklistModel->save()) {
            	
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            
            } else {

	            $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
	            
            	// since 1.6.4
                if ($request->getPost('regex_test_email')) {
                	$notify->clearAll();
                	
                	$regexes = CommonHelper::getArrayFromString($blacklistModel->regular_expressions, "\n");
                	$emails  = CommonHelper::getArrayFromString($request->getPost('regex_test_email'));
                	foreach ($emails as $email) {
                		if (!FilterVarHelper::email($email)) {
                			$notify->addError(Yii::t('settings', '{email} is invalid!', array(
                				'{email}' => CHtml::encode($email)
			                )));
                			continue;
		                }
		                foreach ($regexes as $regex) {
		                	if (preg_match($regex, $email)) {
				                $notify->addError(Yii::t('settings', '{email} has been matched by {regex}!', array(
				                	'{email}'   => sprintf('<strong>%s</strong>', $email),
					                '{regex}'   => sprintf('<strong>%s</strong>', CHtml::encode($regex)),
				                )));
				                break;
			                }
		                }
	                }
	                
	                if (!$notify->hasError) {
	                	$notify->addSuccess(Yii::t('settings', 'No regex matched given email addresses!'));
	                }
                
                }
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'        => $this,
                'success'           => $notify->hasSuccess,
                'blacklistModel'    => $blacklistModel,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/email_blacklist'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Email blacklist settings')
            )
        ));

        $this->render('email-blacklist', compact('blacklistModel'));
    }

    /**
     * Handle the settings for api common
     */
    public function actionApi()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionApi();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/api'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Api settings'),
            )
        ));

        $this->render('api', compact('model'));
    }
    
    /**
     * Handle the settings for api ip access
     */
    public function actionApi_ip_access()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionApiIpAccess();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/api_ip_access'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Api settings') => $this->createUrl('settings/api'),
                Yii::t('settings', 'IP access'),
            )
        ));

        $this->render('api-ip-access', compact('model'));
    }

    /**
     * Handle the common settings for customers options
     */
    public function actionCustomer_common()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCustomerCommon();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (isset(Yii::app()->params['POST'][$model->modelName]['notification_message'])) {
                $model->notification_message = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['notification_message']);
            }

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_common'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Common')
            )
        ));

        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->render('customer-common', compact('model'));
    }

    /**
     * Handle the settings for customer server options
     */
    public function actionCustomer_servers()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerServers();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_servers'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Servers')
            )
        ));

        $this->render('customer-servers', compact('model'));
    }

    /**
     * Handle the settings for customer domains options
     */
    public function actionCustomer_domains()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $tracking = new OptionCustomerTrackingDomains();
        $sending  = new OptionCustomerSendingDomains();

        if ($request->isPostRequest) {

            $tracking->attributes = (array)$request->getPost($tracking->modelName, array());
            $sending->attributes  = (array)$request->getPost($sending->modelName, array());

            if (!$tracking->save() || !$sending->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'models'     => compact('tracking', 'sending'),
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_domains'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Domains')
            )
        ));

        $this->render('customer-domains', compact('tracking', 'sending'));
    }

    /**
     * Handle the settings for customer lists options
     */
    public function actionCustomer_lists()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerLists();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_lists'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Lists')
            )
        ));

        $this->render('customer-lists', compact('model'));
    }

    /**
     * Handle the settings for customer registration options
     */
    public function actionCustomer_registration()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new OptionCustomerRegistration();
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (isset(Yii::app()->params['POST'][$model->modelName]['welcome_email_content'])) {
                $model->welcome_email_content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['welcome_email_content']);
            }

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_registration'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Registration')
            )
        ));

        $this->render('customer-registration', compact('model'));
    }

    /**
     * Handle the settings for customer api options
     */
    public function actionCustomer_api()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new OptionCustomerApi();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());
            
            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_api'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'API')
            )
        ));

        $this->render('customer-api', compact('model'));
    }

    /**
     * Handle the settings for customer sending options
     */
    public function actionCustomer_sending()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerSending();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (isset(Yii::app()->params['POST'][$model->modelName]['quota_notify_email_content'])) {
                $model->quota_notify_email_content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['quota_notify_email_content']);
            }
            
            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_sending'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Sending')
            )
        ));

        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->render('customer-sending', compact('model'));
    }

    /**
     * Handle the settings for customer quota counters options
     */
    public function actionCustomer_quota_counters()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerQuotaCounters();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_quota_counters'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Quota counters')
            )
        ));

        $this->render('customer-quota-counters', compact('model'));
    }

    /**
     * Handle the settings for customer campaigns options
     */
    public function actionCustomer_campaigns()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerCampaigns();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (isset(Yii::app()->params['POST'][$model->modelName]['email_header'])) {
                $model->email_header = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['email_header']);
            }
            
            if (isset(Yii::app()->params['POST'][$model->modelName]['email_footer'])) {
                $model->email_footer = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['email_footer']);
            }

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_campaigns'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Campaigns')
            )
        ));

        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addCustomerCampaignEmailFooterEditor');

        $this->render('customer-campaigns', compact('model'));
    }

    /**
     * Handle the settings for customer surveys options
     */
    public function actionCustomer_surveys()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCustomerSurveys();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_surveys'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'Surveys')
            )
        ));

        $this->render('customer-surveys', compact('model'));
    }

    /**
     * Handle the settings for customer cdn options
     */
    public function actionCustomer_cdn()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCustomerCdn();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customer_cdn'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customers') => $this->createUrl('settings/customer_common'),
                Yii::t('settings', 'CDN')
            )
        ));

        $this->render('customer-cdn', compact('model'));
    }

    /**
     * Handle the settings for campaign attachments
     */
    public function actionCampaign_attachments()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCampaignAttachment();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_attachments'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Attachments')
            )
        ));

        $this->render('campaign-attachments', compact('model'));
    }

    /**
     * Handle the settings for campaign available tags
     */
    public function actionCampaign_template_tags()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCampaignTemplateTag();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_template_tags'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Template tags')
            )
        ));

        $this->render('campaign-template-tags', compact('model'));
    }

    /**
     * Handle the settings for campaigns to exclude various ips from tracking(opens/clicks)
     */
    public function actionCampaign_exclude_ips_from_tracking()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCampaignExcludeIpsFromTracking();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_exclude_ips_from_tracking'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Exclude IPs from tracking')
            )
        ));

        $this->render('campaign-exclude-ips-from-tracking', compact('model'));
    }

    /**
     * Handle the settings for campaigns to blacklist various words from subject and/or content
     */
    public function actionCampaign_blacklist_words()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCampaignBlacklistWords();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_blacklist_words'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Blacklist words')
            )
        ));

        $this->render('campaign-blacklist-words', compact('model'));
    }

    /**
     * Handle the settings for campaign template engine options
     */
    public function actionCampaign_template_engine()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCampaignTemplateEngine();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_template_engine'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Template engine')
            )
        ));

        $this->render('campaign-template-engine', compact('model'));
    }

	/**
	 * Handle the settings for campaign webhooks options
	 */
	public function actionCampaign_webhooks()
	{
		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;
		$model   = new OptionCampaignWebhooks();

		if ($request->isPostRequest) {

			$model->unsetAttributes();
			$model->attributes = (array)$request->getPost($model->modelName, array());

			if (!$model->save()) {
				$notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
			} else {
				$notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller' => $this,
				'success'    => $notify->hasSuccess,
				'model'      => $model,
			)));

			if ($collection->success) {
				$this->redirect(array('settings/campaign_webhooks'));
			}
		}

		$this->setData(array(
			'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
			'pageHeading'       => Yii::t('settings', 'Settings'),
			'pageBreadcrumbs'   => array(
				Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
				Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
				Yii::t('settings', 'Webhooks')
			)
		));

		$this->render('campaign-webhooks', compact('model'));
	}
    
    /**
     * Handle the settings for misc campaign options
     */
    public function actionCampaign_misc()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCampaignMisc();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_misc'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaigns') => $this->createUrl('settings/campaign_attachments'),
                Yii::t('settings', 'Miscellaneous')
            )
        ));

        $this->render('campaign-misc', compact('model'));
    }

    /**
     * Handle the settings for campaign options
     */
    public function actionCampaign_options()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new OptionCampaignOptions();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/campaign_options'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
                Yii::t('settings', 'Campaign options')
            )
        ));

        $this->render('campaign-options', compact('model'));
    }

    /**
     * Handle the settings for monetization options
     */
    public function actionMonetization()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionMonetizationMonetization();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/monetization'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Monetization') => $this->createUrl('settings/monetization'),
            )
        ));

        $this->render('monetization', compact('model'));
    }

    /**
     * Handle the settings for monetization orders
     */
    public function actionMonetization_orders()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionMonetizationOrders();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/monetization_orders'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Monetization') => $this->createUrl('settings/monetization'),
                Yii::t('settings', 'Orders')
            )
        ));

        $this->render('monetization-orders', compact('model'));
    }

    /**
     * Handle the settings for monetization invoices
     */
    public function actionMonetization_invoices()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionMonetizationInvoices();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/monetization_invoices'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Monetization') => $this->createUrl('settings/monetization'),
                Yii::t('settings', 'Invoices')
            )
        ));

        $this->render('monetization-invoices', compact('model'));
    }
    
    /**
     * Handle the settings for license options
     */
    public function actionLicense()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $options = Yii::app()->options;
        $model   = new OptionLicense();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());
                
            $request = LicenseHelper::verifyLicense($model);
            $error   = '';
            
            if ($request['status'] == 'error') {
                $error = $request['message'];
            } else {
                $response = CJSON::decode($request['message'], true);
                if (empty($response['status'])) {
                    $error = Yii::t('settings', 'Invalid response, please try again later!');
                } elseif ($response['status'] != 'success') {
                    $error = $response['message'];
                    $options->set('system.license.error_message', $error);
                }
            }

            if (empty($error)) {
                if (!$model->save()) {
                    $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
                } else {
                    $notify->clearAll();
                    $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                    $options->set('system.license.error_message', '');
                    $options->set("system.common.site_status", "online");
                    $options->set("system.common.api_status", "online");
                }
            } else {
                $notify->addError($error);
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/license'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'License')
            )
        ));

        $this->render('license', compact('model'));
    }

    /**
     * Handle the settings for social links options
     */
    public function actionSocial_links()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionSocialLinks();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/social_links'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Social links')
            )
        ));

        $this->render('social-links', compact('model'));
    }

    /**
     * Handle the settings for CDN options
     */
    public function actionCdn()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCdn();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/cdn'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'CDN')
            )
        ));

        $this->render('cdn', compact('model'));
    }

    /**
     * Handle the settings for CDN options
     */
    public function actionSpf_dkim()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionSpfDkim();

        if ($request->isPostRequest) {
            
            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            $disabledDomains = array();
            
            // 1.4.9
            if ($model->update_sending_domains == OptionSpfDkim::TEXT_YES) {
                $keys = array('dkim_private_key', 'dkim_public_key');
                $domains = SendingDomain::model()->findAllByAttributes(array('verified' => SendingDomain::TEXT_YES));
                foreach ($domains as $domain) {
                    foreach ($keys as $key) {
                        if ($domain->$key != $model->$key) {
                            $domain->dkim_private_key = $model->dkim_private_key;
                            $domain->dkim_public_key = $model->dkim_public_key;
                            $domain->verified = SendingDomain::TEXT_NO;
                            $domain->save(false);
                            $disabledDomains[] = $domain->name;
                            break;
                        }
                    }
                }
                $disabledDomains = array_filter(array_unique($disabledDomains));
            }
            
            if ($disabledDomains) {
                $notify->addWarning(Yii::t('app', 'Please note that following sending domains have been disabled because their dkim signature is not valid anymore: {domains}', array(
                    '{domains}' => implode(', ', $disabledDomains)
                )));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/spf_dkim'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Spf/Dkim')
            )
        ));

        $this->render('spf-dkim', compact('model'));
    }

    /**
     * Handle the settings for customization options
     */
    public function actionCustomization()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new OptionCustomization();

        if ($request->isPostRequest) {

            $model->unsetAttributes();
            $model->attributes = (array)$request->getPost($model->modelName, array());

            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'model'      => $model,
            )));

            if ($collection->success) {
                $this->redirect(array('settings/customization'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
            'pageHeading'       => Yii::t('settings', 'Settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('settings', 'Settings')  => $this->createUrl('settings/index'),
                Yii::t('settings', 'Customization')
            )
        ));

        $this->render('customization', compact('model'));
    }

	/**
	 * Handle the settings for 2FA
	 */
	public function action2fa()
	{
		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;
		$model   = new OptionTwoFactorAuth();

		if ($request->isPostRequest) {

			$model->unsetAttributes();
			$model->attributes = (array)$request->getPost($model->modelName, array());

			if (!$model->save()) {
				$notify->addError(Yii::t('app', 'Your form contains a few errors, please fix them and try again!'));
			} else {
				$notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
			}

			Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
				'controller' => $this,
				'success'    => $notify->hasSuccess,
				'model'      => $model,
			)));

			if ($collection->success) {
				$this->redirect(array('settings/2fa'));
			}
		}
		
		if (!version_compare(PHP_VERSION, '5.6', '>=')) {
			$notify->addWarning(Yii::t('settings', 'PHP >= {v1} is required in order for this feature to work, but your PHP version is just {v2}!', array(
				'{v1}' => '5.6',
				'{v2}' => PHP_VERSION
			)));
		}

		$this->setData(array(
			'pageMetaTitle'     => $this->getData('pageMetaTitle') . ' | ' . Yii::t('settings', 'Settings'),
			'pageHeading'       => Yii::t('settings', 'Settings'),
			'pageBreadcrumbs'   => array(
				Yii::t('settings', 'Settings') => $this->createUrl('settings/index'),
				Yii::t('settings', '2FA settings'),
			)
		));

		$this->render('2fa', compact('model'));
	}

    /**
     * Display the modal window with for htaccess
     */
    public function actionHtaccess_modal()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('settings/index'));
        }
        $this->renderPartial('_htaccess_modal');
    }

    /**
     * Tries to write the contents of the htaccess file
     */
    public function actionWrite_htaccess()
    {
        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('settings/index'));
        }

        if (!AppInitHelper::isModRewriteEnabled()) {
            return $this->renderJson(array('result' => 'error', 'message' => Yii::t('settings', 'Mod rewrite is not enabled on this host. Please enable it in order to use clean urls!')));
        }

        if (!is_file($file = Yii::getPathOfAlias('root') . '/.htaccess')) {
            if (!@touch($file)) {
                return $this->renderJson(array('result' => 'error', 'message' => Yii::t('settings', 'Unable to create the file: {file}. Please create the file manually and paste the htaccess contents into it.', array('{file}' => $file))));
            }
        }

        if (!@file_put_contents($file, $this->getHtaccessContent())) {
            return $this->renderJson(array('result' => 'error', 'message' => Yii::t('settings', 'Unable to write htaccess contents into the file: {file}. Please create the file manually and paste the htaccess contents into it.', array('{file}' => $file))));
        }

        return $this->renderJson(array('result' => 'success', 'message' => Yii::t('settings', 'The htaccess file has been successfully created. Do not forget to save the changes!')));
    }

    /**
     * Will generate the contents of the htaccess file which later
     * should be written in the document root of the application
     */
    protected function getHtaccessContent()
    {
        $apps       = Yii::app()->apps;
        $webApps    = $apps->getWebApps();
        $baseUrl    = '/' . trim($apps->getAppUrl('frontend', null, false, true), '/') . '/';
        $baseUrl    = str_replace('//', '/', $baseUrl);

        if (($index = array_search('frontend', $webApps)) !== false) {
            unset($webApps[$index]);
        }

        return $this->renderPartial('_htaccess', compact('webApps', 'baseUrl'), true);
    }

    /**
     * Callback method to set the editor options for email settings
     */
    public function _setupEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('common', 'notification_message', 'welcome_email_content', 'quota_notify_email_content'))) {
            return;
        }
		
        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }

        $options['id']     = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $options['height'] = 500;

        if ($event->params['attribute'] == 'common') {
            $options['fullPage'] = true;
            $options['allowedContent'] = true;
        }

        if ($event->params['attribute'] == 'notification_message') {
            $options['height'] = 100;
        }

        if ($event->params['attribute'] == 'welcome_email_content') {
            $options['height'] = 300;
        }
        
        if ($event->params['attribute'] == 'quota_notify_email_content') {
            $options['height'] = 200;
        }

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }

    /**
     * Callback method to set the editor options for email footer in campaigns
     */
    public function _addCustomerCampaignEmailFooterEditor(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('email_header', 'email_footer'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
