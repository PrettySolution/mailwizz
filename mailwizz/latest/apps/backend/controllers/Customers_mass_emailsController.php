<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Customers_mass_emailsController
 * 
 * Handles the actions for sending mass emails to customers
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
class Customers_mass_emailsController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('customers-mass-emails.js')));
        parent::init();
    }
    
    /**
     * Send mass emails to customers 
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $options = Yii::app()->options;
        $model   = new CustomerMassEmail();
        
        if (empty($model->message)) {
            $model->message = $options->get('system.email_templates.common');
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('customers', 'Mass emails'),
            'pageHeading'       => Yii::t('customers', 'Mass emails'),
            'pageBreadcrumbs'   => array(
                Yii::t('customers', 'Customers')  => $this->createUrl('customers/index'),
                Yii::t('customers', 'Mass emails') => $this->createUrl('customers_mass_emails/index'),
            )
        ));
        
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        
        if ($request->isPostRequest && ($attributes = $request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if (!$request->isAjaxRequest) {
                $model->message = Yii::app()->params['POST'][$model->modelName]['message'];
                if ($model->validate()) {
                    $jsonAttributes = CJSON::encode(array(
                        'attributes'           => $model->attributes,
                        'formatted_attributes' => $model->getFormattedAttributes(),
                    ));
                    return $this->render('index-ajax', compact('model', 'jsonAttributes'));
                }
            } else {
                if (empty($model->message_id) || !is_file(Yii::getPathOfAlias(CustomerMassEmail::STORAGE_ALIAS) . '/' . $model->message_id)) {
                    return $this->renderJson(array(
                        'result'  => 'error',
                        'message' => Yii::t('customers', 'Unable to load the message from written source!'),
                    ));
                }
                $model->loadCustomers();
                if (empty($model->customers)) {
                    if (is_file($file = Yii::getPathOfAlias(CustomerMassEmail::STORAGE_ALIAS) . '/' . $model->message_id)) {
                        unlink($file);
                    }
                    $model->finished = true;
                    $model->progress_text = Yii::t('customers', 'All emails were queued successfully!');
                    return $this->renderJson(array(
                        'result'               => 'success',
                        'message'              => $model->progress_text,
                        'attributes'           => $model->attributes,
                        'formatted_attributes' => $model->getFormattedAttributes(),
                    ));
                }
                $message = file_get_contents(Yii::getPathOfAlias(CustomerMassEmail::STORAGE_ALIAS) . '/' . $model->message_id);
                foreach ($model->customers as $customer) {
                    $searchReplace = array(
                        '[FULL_NAME]'  => $customer->getFullName(),
                        '[FIRST_NAME]' => $customer->first_name,
                        '[LAST_NAME]'  => $customer->last_name,
                        '[EMAIL]'      => $customer->email,
                    );
                    $body    = str_replace(array_keys($searchReplace), array_values($searchReplace), $message);
                    $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $model->subject);
                    $email   = new TransactionalEmail();
                    $email->to_name   = $customer->getFullName();
                    $email->to_email  = $customer->email;
                    $email->from_name = $options->get('system.common.site_name', 'Marketing website');
                    $email->subject   = $subject;
                    $email->body      = $body;
                    $email->save();
                    
                    $model->processed++;
                }
                
                $model->customers     = array();
                $model->page          = $model->page + 1;
                $model->percentage    = ($model->processed * 100) / $model->total; 
                $model->progress_text = Yii::t('customers', 'Please wait, queueing messages...');
                
                return $this->renderJson(array(
                    'result'               => 'success',
                    'message'              => $model->progress_text,
                    'attributes'           => $model->attributes,
                    'formatted_attributes' => $model->getFormattedAttributes(),
                ));
            }
        }

        $this->render('index', compact('model'));
    }
    
    /**
     * Callback method to set the editor options for email footer in campaigns
     */
    public function _setupEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('message'))) {
            return;
        }
        
        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        
        $options['id']              = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $options['fullPage']        = true;
        $options['allowedContent']  = true;
        $options['height']          = 500;
        
        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}