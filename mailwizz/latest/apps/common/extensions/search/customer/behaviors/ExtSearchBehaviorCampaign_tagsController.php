<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCampaign_tagsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('campaigns tags'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator')
            ),
            'create' => array(
                'keywords' => array('create tags', 'campaigns tags create'),
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
        $criteria->addCondition('tag LIKE :term');
	    $criteria->params[':cid']  = (int)Yii::app()->customer->getId();
        $criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'tag_id DESC';
        $criteria->limit = 5;
        
        $models = CustomerCampaignTag::model()->findAll($criteria);
        $items  = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->tag;
            $item->url   = Yii::app()->createUrl('campaign_tags/update', array('tag_uid' => $model->tag_uid));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }
}
	