<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PagesController
 * 
 * Handles the actions for pages
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
 
class PagesController extends Controller
{
    /**
     * Redirect to home
     */
    public function actionIndex()
    {
        return $this->redirect(array('site/index'));
    }
    
    /**
     * View a single page details
     */
    public function actionView($slug)
    {
        $page = $this->loadPageModel($slug);
        
        if (!$page->isActive) {
            if (Yii::app()->user->getId()) {
                Yii::app()->notify->addInfo(Yii::t('pages', 'This page is inactive, only site admins can see it!'));
            } else {
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'       => $this->data->pageMetaTitle . ' | ' . $page->title,
            'pageMetaDescription' => StringHelper::truncateLength($page->content, 150),
        ));
        
        Yii::app()->clientScript->registerLinkTag('canonical', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        Yii::app()->clientScript->registerLinkTag('shortlink', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        
        $this->render('view', compact('page'));
    }

    /**
     * Helper method to load the page AR model
     * 
     * @param $slug
     * @return Page
     * @throws CHttpException
     */
    public function loadPageModel($slug)
    {
        $condition = array(
            'slug' => $slug,
        );

        $model = Page::model()->findByAttributes($condition);
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
    
}