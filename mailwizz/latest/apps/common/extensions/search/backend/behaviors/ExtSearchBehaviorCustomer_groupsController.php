<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCustomer_groupsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('customer group'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator')
			),
            'create' => array(
                'keywords'  => array('create customer group', 'customer group create', 'create group'),
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
        $criteria->order = 'group_id DESC';
        $criteria->limit = 5;
        
        $models = CustomerGroup::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('customer_groups/update', array('id' => $model->group_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	