<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArticlesController
 * 
 * Handles the actions for artciles related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ArticlesController extends Controller
{
    /**
     * List available published articles 
     */
    public function actionIndex()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('status', Article::STATUS_PUBLISHED);
        $criteria->order = 'article_id DESC';
        
        $count = Article::model()->count($criteria);
        
        $pages = new CPagination($count);
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        
        $articles = Article::model()->findAll($criteria);
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('articles', 'Helpful articles'), 
            'pageBreadcrumbs'   => array()
        ));

        $this->render('index', compact('articles', 'pages'));
    }
    
    /**
     * List available published articles belonging to a category
     */
    public function actionCategory($slug)
    {
        $category = $this->loadCategoryModel($slug);
        
        $criteria = new CDbCriteria();
        $criteria->compare('t.status', Article::STATUS_PUBLISHED);
        $criteria->with = array(
            'activeCategories' => array(
                'select'    => 'activeCategories.category_id',
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'activeCategories.category_id = :cid',
                'params'    => array(':cid' => $category->category_id),
            )
        );
        $criteria->order = 't.article_id DESC';
        
        $count = Article::model()->count($criteria);
        
        $pages = new CPagination($count);
        $pages->pageSize = 10;
        $pages->applyLimit($criteria);
        
        $articles = Article::model()->findAll($criteria);

        $this->setData(array(
            'pageMetaTitle'         => $this->data->pageMetaTitle . ' | ' . $category->name,
            'pageMetaDescription'   => StringHelper::truncateLength($category->description, 150),
        ));
        
        Yii::app()->clientScript->registerLinkTag('canonical', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        Yii::app()->clientScript->registerLinkTag('shortlink', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        
        $this->render('category', compact('category', 'articles', 'pages'));
    }
    
    /**
     * View a single article details
     */
    public function actionView($slug)
    {
        $article = $this->loadArticleModel($slug);
        if ($article->status != Article::STATUS_PUBLISHED) {
            if (Yii::app()->user->getId()) {
                Yii::app()->notify->addInfo(Yii::t('articles', 'This article is unpublished, only site admins can see it!'));
            } else {
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'         => $this->data->pageMetaTitle . ' | ' . $article->title,
            'pageMetaDescription'   => StringHelper::truncateLength($article->content, 150),
        ));
        
        Yii::app()->clientScript->registerLinkTag('canonical', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        Yii::app()->clientScript->registerLinkTag('shortlink', null, $this->createAbsoluteUrl($this->route, array('slug' => $slug)));
        
        $this->render('view', compact('article'));
    }
    
    /**
     * Helper method to load the category AR model
     */
    public function loadCategoryModel($slug)
    {
        $model = ArticleCategory::model()->findByAttributes(array(
            'slug'      => $slug,
            'status'    => Article::STATUS_ACTIVE
        ));
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
    
    /**
     * Helper method to load the article AR model
     */
    public function loadArticleModel($slug)
    {
        $condition = array(
            'slug' => $slug,
        );

        $model = Article::model()->findByAttributes($condition);
        
        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        return $model;
    }
    
}