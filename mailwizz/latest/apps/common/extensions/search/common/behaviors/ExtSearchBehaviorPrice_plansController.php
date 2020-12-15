<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorPrice_plansController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('monetization'),
				'skip'              => array($this, '_indexSkip'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator')
			),
			'orders' => array(
				'keywords' => array('order', 'orders', 'my order', 'my orders'),
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
            if (Yii::app()->options->get('system.monetization.monetization.enabled', 'no') == 'no') {
                return true;
            }
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
        if (MW_APP_NAME == 'customer') {
            return array();
        }

	    $criteria = new CDbCriteria();
		$criteria->addCondition('name LIKE :term OR description LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'plan_id DESC';
		$criteria->limit = 5;
		
		$models = PricePlan::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('price_plans/update', array('id' => $model->plan_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	