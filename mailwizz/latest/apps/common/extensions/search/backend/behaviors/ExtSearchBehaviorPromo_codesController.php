<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorPromo_codesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('monetization', 'promotions', 'promos', 'discount'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator')
			),
		);
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
		$criteria->addCondition('code LIKE :term OR type LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'promo_code_id DESC';
        $criteria->limit = 5;
        
        $models = PricePlanPromoCode::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->code;
			$item->url   = Yii::app()->createUrl('promo_codes/update', array('id' => $model->promo_code_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	