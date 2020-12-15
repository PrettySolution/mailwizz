<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Article_categoriesController
 *
 * Handles the actions for articles categories related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class Article_categoriesController extends Controller
{
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
     * List all the available article categories
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
        $category   = new ArticleCategory('search');
        $category->unsetAttributes();

        // for filters.
        $category->attributes = (array)$request->getQuery($category->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'View article categories'),
            'pageHeading'       => Yii::t('articles', 'View article categories'),
            'pageBreadcrumbs'   => array(
                Yii::t('articles', 'Articles')      => $this->createUrl('articles/index'),
                Yii::t('articles', 'Categories')    => $this->createUrl('article_categories/index'),
                Yii::t('app', 'View all'),
            )
        ));

        $this->render('list', compact('category'));
    }

    /**
     * Create a new article category
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $category   = new ArticleCategory();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($category->modelName, array()))) {
            $category->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$category->modelName]['description'])) {
                $category->description = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$category->modelName]['description']);
            }
            if (!$category->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'category'  => $category,
            )));

            if ($collection->success) {
                $this->redirect(array('article_categories/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'Create new article category'),
            'pageHeading'       => Yii::t('articles', 'Create new article category'),
            'pageBreadcrumbs'   => array(
                Yii::t('articles', 'Articles')      => $this->createUrl('articles/index'),
                Yii::t('articles', 'Categories')    => $this->createUrl('article_categories/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('category'));
    }

    /**
     * Update existing article category
     */
    public function actionUpdate($id)
    {
        $category = ArticleCategory::model()->findByPk((int)$id);

        if (empty($category)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($category->modelName, array()))) {
            $category->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$category->modelName]['description'])) {
                $category->description = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$category->modelName]['description']);
            }
            if (!$category->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'category'  => $category,
            )));

            if ($collection->success) {
                $this->redirect(array('article_categories/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'         => $this->data->pageMetaTitle . ' | '. Yii::t('articles', 'Update article category'),
            'pageHeading'           => Yii::t('articles', 'Update article category'),
            'pageBreadcrumbs'       => array(
                Yii::t('articles', 'Articles')      => $this->createUrl('articles/index'),
                Yii::t('articles', 'Categories')   => $this->createUrl('article_categories/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('category'));
    }

    /**
     * Delete exiting article category
     */
    public function actionDelete($id)
    {
        $category = ArticleCategory::model()->findByPk((int)$id);

        if (empty($category)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $category->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('article_categories/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $category,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Generate the slug of the article category based on the article category title
     */
    public function actionSlug()
    {
        $request = Yii::app()->request;

        if (!$request->isAjaxRequest) {
            $this->redirect(array('article_categories/index'));
        }

        $category = new ArticleCategory();
        $category->category_id = (int)$request->getPost('category_id');
        $category->slug = $request->getPost('string');

        $article = new Article();
        $article->slug = $category->slug;

        $category->slug = $article->generateSlug();
        $category->slug = $category->generateSlug();

        return $this->renderJson(array('result' => 'success', 'slug' => $category->slug));
    }
}
