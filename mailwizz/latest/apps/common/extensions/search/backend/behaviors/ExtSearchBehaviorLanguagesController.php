<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorLanguagesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('translate', 'translation', 'i18n', 'internationalisation'),
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
		$criteria->addCondition('name LIKE :term OR language_code LIKE :term OR region_code LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'language_id DESC';
        $criteria->limit = 5;
        
        $models = Language::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('languages/update', array('id' => $model->language_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	