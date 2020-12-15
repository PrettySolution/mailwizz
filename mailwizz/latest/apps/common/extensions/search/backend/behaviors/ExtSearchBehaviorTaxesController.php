<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorTaxesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('monetization', 'VAT', 'tax'),
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
		$criteria->addCondition('name LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'tax_id DESC';
        $criteria->limit = 5;
        
        $models = Tax::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('taxes/update', array('id' => $model->tax_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	