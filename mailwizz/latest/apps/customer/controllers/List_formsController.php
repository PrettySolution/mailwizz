<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_formsController
 * 
 * Handles the actions for list forms related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class List_formsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('list-forms.js')));
        parent::init();    
    }

    /**
     * @param $list_uid
     * @throws CHttpException
     */
    public function actionIndex($list_uid)
    {
        $list           = $this->loadListModel($list_uid);
        $subscribeUrl   = Yii::app()->apps->getAppUrl('frontend', 'lists/' . $list->list_uid . '/subscribe', true);
	    $subscribeHtml  = '';
	    $subscribeForm  = '';
	    
	    $response = AppInitHelper::simpleCurlGet($subscribeUrl);
        if ($response['status'] === 'success') {
	        $subscribeHtml = !empty($response['message']) ? $response['message'] : '';
        }
        
	    if (!CommonHelper::functionExists('qp')) {
		    require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
	    }

	    libxml_use_internal_errors(true);

	    try {

		    $query = qp($subscribeHtml, 'body', array(
			    'ignore_parser_warnings'    => true,
			    'convert_to_encoding'       => Yii::app()->charset,
			    'convert_from_encoding'     => Yii::app()->charset,
			    'use_parser'                => 'html',
		    ));

		    // to do: what action should we take here?
		    if (count(libxml_get_errors()) > 0) {}

		    $query->top()->find('form')->attr('action', $subscribeUrl);
		    $query->top()->find('form')->find('input[name="csrf_token"]')->remove();
		    $subscribeForm = $query->top()->find('form')->html();
		    
		    if (preg_match('#(<textarea[^>]+)/>#i', $subscribeForm)) {
			    $subscribeForm = preg_replace('#(<textarea[^>]+)/>#i', '$1></textarea>', $subscribeForm);
		    }
		    
		    $tidyEnabled = Yii::app()->params['email.templates.tidy.enabled'];
		    $tidyEnabled = $tidyEnabled && Yii::app()->options->get('system.common.use_tidy', 'yes') == 'yes';
		    if ($tidyEnabled && class_exists('tidy', false)) {
			    $tidy    = new tidy();
			    $options = Yii::app()->params['email.templates.tidy.options'];
			    $tidy->parseString($subscribeForm, $options, 'utf8');
			    if ($tidy->cleanRepair()) {
				    $_subscribeForm = $tidy->html()->value;
				    if (!empty($_subscribeForm) && preg_match('/<form[^>]+>(.*)<\/form>/six', $_subscribeForm, $matches)) {
					    $subscribeForm = $matches[0];
				    }
			    }
		    }

		    $subscribeForm = CHtml::encode($subscribeForm);
		    
	    } catch (Exception $e) {

	    }
	    
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('list_forms', 'Your mail list forms'),
            'pageHeading'       => Yii::t('list_forms', 'Embed list forms'), 
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_forms', 'Embed list forms')
            )
        ));

        $this->render('index', compact('list', 'subscribeForm'));
    }

    /**
     * Helper method to load the list AR model
     */
    public function loadListModel($list_uid)
    {
        $model = Lists::model()->findByAttributes(array(
            'list_uid'      => $list_uid,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
}