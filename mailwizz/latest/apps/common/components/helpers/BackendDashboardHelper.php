<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BackendDashboardHelper
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */
 
class BackendDashboardHelper 
{
	/**
	 * @return array
	 */
    public static function getGlanceStats()
    {
    	$options    = Yii::app()->options;
    	$backendUrl = $options->get('system.urls.backend_absolute_url');
    	
    	// so we can call it multiple times under different language
    	static $counters;
    	if ($counters === null) {
    		$counters = array(
    			'customers'     => Customer::model()->count(),
			    'campaigns'     => Campaign::model()->count(),
			    'lists'         => Lists::model()->count(),
			    'subscribers'   => ListSubscriber::model()->count(),
			    'segments'      => ListSegment::model()->count(),
			    'servers'       => DeliveryServer::model()->count(),
		    );
	    }
    	
	    return array(
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['customers']),
			    'heading'   => Yii::t('dashboard', 'Customers'),
			    'icon'      => IconHelper::make('ion-person-add'),
			    'url'       => $backendUrl . 'customers/index',
		    ),
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['campaigns']),
			    'heading'   => Yii::t('dashboard', 'Campaigns'),
			    'icon'      => IconHelper::make('ion-ios-email-outline'),
			    'url'       => $backendUrl . 'campaigns/index',
		    ),
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['lists']),
			    'heading'   => Yii::t('dashboard', 'Lists'),
			    'icon'      => IconHelper::make('ion ion-clipboard'),
			    'url'       => $backendUrl . 'lists/index',
		    ),
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['subscribers']),
			    'heading'   => Yii::t('dashboard', 'Subscribers'),
			    'icon'      => IconHelper::make('ion-ios-people'),
			    'url'       => 'javascript:;',
		    ),
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['segments']),
			    'heading'   => Yii::t('dashboard', 'Segments'),
			    'icon'      => IconHelper::make('ion-gear-b'),
			    'url'       => 'javascript:;',
		    ),
		    array(
			    'count'     => Yii::app()->format->formatNumber($counters['servers']),
			    'heading'   => Yii::t('dashboard', 'Delivery servers'),
			    'icon'      => IconHelper::make('ion-paper-airplane'),
			    'url'       => $backendUrl . 'delivery-servers/index',
		    ),
	    );
    }

	/**
	 * @return array
	 */
    public static function getTimelineItems()
    {
	    $options    = Yii::app()->options;
	    $backendUrl = $options->get('system.urls.backend_absolute_url');

	    $criteria = new CDbCriteria();
	    $criteria->select    = 'DISTINCT(DATE(t.date_added)) as date_added';
	    $criteria->condition = 'DATE(t.date_added) >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
	    $criteria->group     = 'DATE(t.date_added)';
	    $criteria->order     = 't.date_added DESC';
	    $criteria->limit     = 3;
	    $models = CustomerActionLog::model()->findAll($criteria);

	    $items = array();
	    foreach ($models as $model) {
		    $_item = array(
			    'date'  => $model->dateTimeFormatter->formatLocalizedDate($model->date_added),
			    'items' => array(),
		    );
		    $criteria = new CDbCriteria();
		    $criteria->select    = 't.log_id, t.customer_id, t.message, t.date_added';
		    $criteria->condition = 'DATE(t.date_added) = :date';
		    $criteria->params    = array(':date' => $model->date_added);
		    $criteria->limit     = 5;
		    $criteria->order     = 't.date_added DESC';
		    $criteria->with      = array(
			    'customer' => array(
				    'select'   => 'customer.customer_id, customer.first_name, customer.last_name',
				    'together' => true,
				    'joinType' => 'INNER JOIN',
			    ),
		    );
		    $records = CustomerActionLog::model()->findAll($criteria);
		    foreach ($records as $record) {
			    $customer = $record->customer;
			    $time     = $record->dateTimeFormatter->formatLocalizedTime($record->date_added);
			    $_item['items'][] = array(
				    'time'         => $time,
				    'customerName' => $customer->getFullName(),
				    'customerUrl'  => $backendUrl . 'customers/update/id/' . $customer->customer_id,
				    'message'      => strip_tags($record->message),
			    );
		    }
		    $items[] = $_item;
	    }
	    
	    return $items;
    }
}