<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorList_page_typeController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('page types', 'list pages types'),
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
		$criteria->addCondition('name LIKE :term OR description LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'type_id DESC';
        $criteria->limit = 5;
        
        $models = ListPageType::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('list_page_type/update', array('id' => $model->type_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	