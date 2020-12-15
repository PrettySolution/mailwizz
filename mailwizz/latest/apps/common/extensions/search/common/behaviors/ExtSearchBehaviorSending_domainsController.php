<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorSending_domainsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('domain sending'),
				'skip'              => array($this, '_skip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator'),
			),
			'create' => array(
				'keywords'  => array('domain sending create'),
				'skip'      => array($this, '_skip'),
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
            return Yii::app()->customer->getModel()->getGroupOption('sending_domains.can_manage_sending_domains', 'no') != 'yes';
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

		$criteria->addCondition('(name LIKE :term)');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'domain_id DESC';
		$criteria->limit = 5;

		$models = SendingDomain::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('sending_domains/update', array('id' => $model->domain_id));
			$item->score++;
			
			$items[] = $item->fields;
		}
		return $items;
	}
}
	