<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SiteController
 * 
 * Default api application controller
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class SiteController extends Controller
{
    // access rules for this controller
    public function accessRules()
    {
        return array(
            // allow all users on all actions
            array('allow'),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     * 
     * By default we don't return any information from this action.
     */
    public function actionIndex()
    {
        $this->renderJson();
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if ($error['code'] === 404) {
                $error['message'] = Yii::t('app', 'Page not found.');
            }
            return $this->renderJson(array(
                'status'    => 'error',
                'error'        => CHtml::encode($error['message']),
            ), $error['code']);
        }
    }

}
