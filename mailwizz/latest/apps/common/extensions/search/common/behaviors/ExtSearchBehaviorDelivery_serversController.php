<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorDelivery_serversController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('delivery server', 'create delivery server', 'server', 'delivery'),
				'skip'              => array($this, '_indexSkip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator'),
			),
            'create' => array(
                'keywords'          => array('delivery server create', 'create delivery server', 'server', 'delivery'),
                'skip'              => array($this, '_createSkip'),
            ),
		);
	}

	/**
	 * @param SearchExtSearchItem $item
	 *
	 * @return bool
	 */
	public function _indexSkip(SearchExtSearchItem $item)
	{
		if (MW_APP_NAME == 'customer') {
			$customer = Yii::app()->customer->getModel();
			return (int)$customer->getGroupOption('servers.max_delivery_servers', 0) == 0;
		}
		return !Yii::app()->user->getModel()->hasRouteAccess($item->route);
	}

	/**
	 * @param $term
	 * @param SearchExtSearchItem|null $parent
	 *
	 * @return array
	 */
	public function _indexChildrenGenerator($term, SearchExtSearchItem $parent = null)
	{
		$criteria = new CDbCriteria();

		if (MW_APP_NAME == 'customer') {
			$criteria->addCondition('customer_id = :cid');
			$criteria->params[':cid'] = (int)Yii::app()->customer->getId();
		}

		$criteria->addCondition('(name LIKE :term OR hostname LIKE :term OR username LIKE :term OR from_email LIKE :term)');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'server_id DESC';
		$criteria->limit = 5;
		
		$models = DeliveryServer::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = !empty($model->name) ? $model->name : $model->hostname;
			$item->url   = Yii::app()->createUrl('delivery_servers/update', array('id' => $model->server_id, 'type' => $model->type));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}

    /**
     * @return bool
     */
    public function _createSkip()
    {
        return true;
    }
}
	