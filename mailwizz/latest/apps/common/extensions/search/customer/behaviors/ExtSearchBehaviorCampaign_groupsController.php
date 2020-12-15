<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCampaign_groupsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('campaigns groups'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator')
            ),
            'create' => array(
                'keywords' => array('create groups', 'campaigns groups create'),
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
	    $criteria->addCondition('customer_id = :cid');
        $criteria->addCondition('name LIKE :term');
        $criteria->params[':term'] = '%'. $term .'%';
	    $criteria->params[':cid']  = (int)Yii::app()->customer->getId();
        $criteria->order = 'group_id DESC';
        $criteria->limit = 5;

        $models = CampaignGroup::model()->findAll($criteria);
        $items  = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->name;
            $item->url   = Yii::app()->createUrl('campaign_groups/update', array('group_uid' => $model->group_uid));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }
}
	