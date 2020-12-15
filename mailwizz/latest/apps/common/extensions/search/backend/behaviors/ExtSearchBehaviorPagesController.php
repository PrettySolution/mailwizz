<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorPagesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('custom pages', 'custom content'),
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
		$criteria->addCondition('title LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'page_id DESC';
        $criteria->limit = 5;

        $models = Page::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->title;
			$item->url   = Yii::app()->createUrl('pages/update', array('id' => $model->page_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	