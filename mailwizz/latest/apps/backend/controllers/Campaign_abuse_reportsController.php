<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign_abuse_reportsController
 *
 * Handles the actions for campaign abuse reports tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */

class Campaign_abuse_reportsController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('campaign-abuse-reports.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, blacklist',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all abuse reports for all campaigns
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $reports = new CampaignAbuseReport('search');
        $reports->unsetAttributes();

        $reports->attributes = (array)$request->getQuery($reports->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'View campaign abuse reports'),
            'pageHeading'       => Yii::t('campaigns', 'View campaign abuse reports'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaign abuse reports'),
            )
        ));

        $this->render('index', compact('reports'));
    }

    /**
     * Delete an existing article
     */
    public function actionDelete($id)
    {
        $report = CampaignAbuseReport::model()->findByPk((int)$id);

        if (empty($report)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $report->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('campaign_abuse_reports/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $report,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Blacklist campaign abuse email
     */
    public function actionBlacklist($id)
    {
        $report = CampaignAbuseReport::model()->findByPk((int)$id);

        if (empty($report)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $reason = Yii::t('campaigns', 'Campaign abuse report') . '#' . $report->report_id . ': ' . $report->reason;
        EmailBlacklist::addToBlacklist($report->subscriber_info, $reason);

        $report->addLog(Yii::t('campaigns', 'Subscriber email has been blacklisted!'))->save(false);

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('campaigns', 'The email has been successfully blacklisted!'));
            $this->redirect($request->getPost('returnUrl', array('campaign_abuse_reports/index')));
        }

        return $this->renderJson(array(
            'status'  => 'success',
            'message' => Yii::t('campaigns', 'The email has been successfully blacklisted!'),
        ));
    }
}
