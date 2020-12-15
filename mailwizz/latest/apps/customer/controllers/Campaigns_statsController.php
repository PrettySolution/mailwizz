<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaigns_statsController
 *
 * Handles the actions for campaigns Campaigns stats related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

class Campaigns_statsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('campaigns-stats.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    /**
     * List available campaigns stats
     */
    public function actionIndex()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);

        $customerId = (int)Yii::app()->customer->getId(); 
        $request    = Yii::app()->request;
        $filter     = new CampaignStatsFilter();
        $filter->unsetAttributes();

        $filter->attributes  = (array)$request->getQuery($filter->modelName, array());
        $filter->customer_id = $customerId;
        $filter->addRelatedRecord('customer', Yii::app()->customer->getModel(), false);
        
        $canExport = $filter->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes';
        if ($canExport && $filter->isExportAction) {

            /* Set the download headers */
            HeaderHelper::setDownloadHeaders('campaigns-stats.csv');
            
            $attributes = array(
                'name', 'subject', 'listName', 'subscribersCount', 'deliverySuccess', 'uniqueOpens', 
                'allOpens', 'uniqueClicks', 'allClicks', 
                'unsubscribes', 'bounces', 'softBounces', 
                'hardBounces'
            );

            $columns = array();
            foreach ($attributes as $name) {
                $columns[] = sprintf('"%s"', $filter->getAttributeLabel($name));
            }
            
            echo implode(",", $columns) . PHP_EOL;

            $criteria = $filter->search()->getCriteria();
            $criteria->limit  = 10;
            $criteria->offset = 0;

            $models = CampaignStatsFilter::model()->findAll($criteria);
            while (!empty($models)) {
                foreach ($models as $model) {
                    $out = array();
                    foreach ($attributes as $attribute) {
                        $out[] = sprintf('"%s"', $model->$attribute);
                    }
                    echo implode(",", $out) . PHP_EOL;
                }
                $criteria->offset = $criteria->offset + $criteria->limit;
                $models = CampaignStatsFilter::model()->findAll($criteria);
            }

            Yii::app()->end();
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Campaigns'),
            'pageHeading'       => Yii::t('campaigns', 'Stats'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Stats') => $this->createUrl('campaigns_stats/index'),
                Yii::t('app', 'View all')
            )
        ));
        
        $this->render('index', compact('filter', 'customerId'));
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('index'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
