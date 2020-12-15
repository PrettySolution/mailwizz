<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class Ext_tour_settingsController extends Controller
{
    // the extension instance
    public $extension;

    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-tour.backend.views.settings');
    }

    /**
     * Common settings
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new TourExtCommon();
        $model->populate();

        if ($request->isPostRequest) {
            $model->attributes = (array)$request->getPost($model->modelName, array());
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save();
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'    => $this->data->pageMetaTitle . ' | '. $this->extension->t('Tour'),
            'pageHeading'      => $this->extension->t('Tour'),
            'pageBreadcrumbs'  => array(
                Yii::t('app', 'Extensions') => $this->createUrl('extensions/index'),
                $this->extension->t('Tour') => $this->createUrl('ext_tour_settings/index'),
            )
        ));

        $this->render('index', compact('model'));
    }
}
