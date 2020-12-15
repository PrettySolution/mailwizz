<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCampaignsController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('campaign', 'campaigns list', 'autoresponders'),
				'skip'              => array($this, '_indexSkip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator'),
			),
			'create' => array(
				'keywords'  => array('campaign', 'regular campaign', 'autoresponders'),
				'skip'      => array($this, '_createSkip'),
			),
			'regular' => array(
				'keywords'  => array('regular campaign', 'regular campaigns'),
				'skip'      => array($this, '_indexSkip'),
			),
			'autoresponder' => array(
				'keywords'  => array('autoresponder campaign', 'autoresponder campaigns', 'autoresponders'),
				'skip'      => array($this, '_indexSkip'),
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
			return false;
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

		$criteria->addCondition('(name LIKE :term OR subject LIKE :term)');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'campaign_id DESC';
		$criteria->limit = 5;
		
		$models = Campaign::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('campaigns/overview', array('campaign_uid' => $model->campaign_uid));
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
		return MW_APP_NAME != 'customer';
	}
}
	