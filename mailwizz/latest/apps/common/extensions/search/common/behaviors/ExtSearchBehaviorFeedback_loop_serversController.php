<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorFeedback_loop_serversController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('feedback loop server', 'create feedback loop server', 'server', 'feedback'),
				'skip'              => array($this, '_skip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator')
			),
            'create' => array(
                'keywords'          => array('feedback loop server', 'create feedback loop server', 'server', 'feedback'),
                'skip'              => array($this, '_skip'),
            ),
		);
	}

	/**
	 * @param SearchExtSearchItem $item
	 *
	 * @return bool
	 */
	public function _skip(SearchExtSearchItem $item)
	{
		if (MW_APP_NAME == 'customer') {
			$customer = Yii::app()->customer->getModel();
			return (int)$customer->getGroupOption('servers.max_fbl_servers', 0) == 0;
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
		
		$criteria->addCondition('(hostname LIKE :term OR email LIKE :term OR username LIKE :term)');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'server_id DESC';
		$criteria->limit = 5;
		
		$models = FeedbackLoopServer::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->hostname;
			$item->url   = Yii::app()->createUrl('feedback_loop_servers/update', array('id' => $model->server_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	