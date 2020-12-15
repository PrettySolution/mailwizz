<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArticlesController
 *
 * Handles the actions for articles related tasks
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
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('articles.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, slug',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available articles
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $article = new Article('search');
        $article->unsetAttributes();

        // for filters.
        $article->attributes = (array)$request->getQuery($article->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'View articles'),
            'pageHeading'       => Yii::t('articles', 'View articles'),
            'pageBreadcrumbs'   => array(
                Yii::t('articles', 'Articles') => $this->createUrl('articles/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('article'));
    }

    /**
     * Create a new article
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $article    = new Article();
        $articleToCategory = new ArticleToCategory();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($article->modelName, array()))) {
            $article->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$article->modelName]['content'])) {
                $article->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$article->modelName]['content']);
            }
            if (!$article->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if ($categories = (array)$request->getPost($articleToCategory->modelName, array())) {
                    foreach ($categories as $category_id) {
                        $articleToCategory = new ArticleToCategory();
                        $articleToCategory->article_id = $article->article_id;
                        $articleToCategory->category_id = (int)$category_id;
                        $articleToCategory->save();
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'article'   => $article,
            )));

            if ($collection->success) {
                $this->redirect(array('articles/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'Create new article'),
            'pageHeading'       => Yii::t('articles', 'Create new article'),
            'pageBreadcrumbs'   => array(
                Yii::t('articles', 'Articles') => $this->createUrl('articles/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('article', 'articleToCategory'));
    }

    /**
     * Update existing article
     */
    public function actionUpdate($id)
    {
        $article = Article::model()->findByPk((int)$id);

        if (empty($article)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $articleToCategory = new ArticleToCategory();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($article->modelName, array()))) {
            $article->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$article->modelName]['content'])) {
                $article->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$article->modelName]['content']);
            }
            if (!$article->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                ArticleToCategory::model()->deleteAllByAttributes(array('article_id' => $article->article_id));
                if ($categories = (array)$request->getPost($articleToCategory->modelName, array())) {
                    foreach ($categories as $category_id) {
                        $articleToCategory = new ArticleToCategory();
                        $articleToCategory->article_id = $article->article_id;
                        $articleToCategory->category_id = (int)$category_id;
                        $articleToCategory->save();
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'article'   => $article,
            )));

            if ($collection->success) {
                $this->redirect(array('articles/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'Update article'),
            'pageHeading'       => Yii::t('articles', 'Update article'),
            'pageBreadcrumbs'   => array(
                Yii::t('articles', 'Articles') => $this->createUrl('articles/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('article', 'articleToCategory'));
    }

    /**
     * Delete an existing article
     */
    public function actionDelete($id)
    {
        $article = Article::model()->findByPk((int)$id);

        if (empty($article)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $article->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('articles/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $article,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Generate the slug for an article based on the article title
     */
    public function actionSlug()
    {
        $request = Yii::app()->request;

        if (!$request->isAjaxRequest) {
            $this->redirect(array('articles/index'));
        }

        $article = new Article();
        $article->article_id = (int)$request->getPost('article_id');
        $article->slug = $request->getPost('string');

        $category = new ArticleCategory();
        $category->slug = $article->slug;

        $article->slug = $category->generateSlug();
        $article->slug = $article->generateSlug();

        return $this->renderJson(array('result' => 'success', 'slug' => $article->slug));
    }
}
