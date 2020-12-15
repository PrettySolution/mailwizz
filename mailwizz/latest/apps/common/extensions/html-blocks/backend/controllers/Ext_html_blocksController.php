<?php defined('MW_PATH') || exit('No direct script access allowed');

/** 
 * Controller file for html blocks.
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Stripe
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class Ext_html_blocksController extends Controller
{
    // the extension instance
    public $extension;
    
    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-html-blocks.backend.views');
    }
    
    /**
     * Default action.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = $this->extension->getExtModel();

        if ($request->isPostRequest) {
            $model->attributes = Yii::app()->ioFilter->purify($request->getOriginalPost($model->modelName, array()));
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save();
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('ext_html_blocks', 'Html blocks'),
            'pageHeading'       => Yii::t('ext_html_blocks', 'Html blocks'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Extensions') => $this->createUrl('extensions/index'),
                Yii::t('ext_html_blocks', 'Html blocks') => $this->createUrl('ext_html_blocks/index'),
            )
        ));
        
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addEditorOptions');
        
        $this->render('settings', compact('model'));
    }
    
    /**
     * Callback method to set the editor options for email footer in campaigns
     */
    public function _addEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('customer_footer'))) {
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