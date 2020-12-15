<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * LanguagesController
 *
 * Handles the actions for languages related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */

class LanguagesController extends Controller
{
    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete', // we only allow deletion via POST request
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available languages
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $language = new Language('search');
        $language->unsetAttributes();
        $languageUpload = new LanguageUploadForm();

        // for filters.
        $language->attributes = (array)$request->getQuery($language->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('languages', 'View available languages'),
            'pageHeading'       => Yii::t('languages', 'View available languages'),
            'pageBreadcrumbs'   => array(
                Yii::t('languages', 'Languages') => $this->createUrl('languages/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('language', 'languageUpload'));
    }

    /**
     * Create a new language
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $language   = new Language();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($language->modelName, array()))) {
            $language->attributes = $attributes;
            if (!$language->validate()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                try {
                    $locale = Yii::app()->getLocale($language->getLanguageAndLocaleCode());
                    $language->save(false);
                    $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                } catch (Exception $e) {
                    $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                    $notify->addError($e->getMessage());
                }
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'language'  => $language,
            )));

            if ($collection->success) {
                $this->redirect(array('languages/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('languages', 'Create new language'),
            'pageHeading'       => Yii::t('languages', 'Create new language'),
            'pageBreadcrumbs'   => array(
                Yii::t('languages', 'Languages') => $this->createUrl('languages/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('language'));
    }

    /**
     * Update existing language
     */
    public function actionUpdate($id)
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $language   = Language::model()->findByPk((int)$id);

        if (empty($language)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($language->modelName, array()))) {
            $language->attributes = $attributes;
            if (!$language->validate()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                try {
                    $locale = Yii::app()->getLocale($language->getLanguageAndLocaleCode());
                    $language->save(false);
                    $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                } catch (Exception $e) {
                    $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                    $notify->addError($e->getMessage());
                }
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'language'  => $language,
            )));

            if ($collection->success) {

            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('languages', 'Update language'),
            'pageHeading'       => Yii::t('languages', 'Update language'),
            'pageBreadcrumbs'   => array(
                Yii::t('languages', 'Languages') => $this->createUrl('languages/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('language'));
    }

    /**
     * Upload language pack
     */
    public function actionUpload()
    {
        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $model = new LanguageUploadForm();

        if ($request->isPostRequest && $request->getPost($model->modelName)) {
            $model->archive = CUploadedFile::getInstance($model, 'archive');
               if (!$model->upload()) {
                   $notify->addError($model->shortErrors->getAllAsString());
               } else {
                   $notify->addSuccess(Yii::t('languages', 'Your language pack has been successfully uploaded!'));
               }
               $this->redirect(array('languages/index'));
          }

          $notify->addError(Yii::t('languages', 'Please select a language pack archive for upload!'));
          $this->redirect(array('languages/index'));
    }

    /**
     * Delete existing language
     */
    public function actionDelete($id)
    {
        $language = Language::model()->findByPk((int)$id);

        if (empty($language)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($language->is_default != Language::TEXT_YES) {
            $language->delete();
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('languages/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $language,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

}
