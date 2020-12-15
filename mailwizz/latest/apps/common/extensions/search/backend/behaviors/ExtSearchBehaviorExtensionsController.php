<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorExtensionsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('extend', 'extension'),
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
		$extensions = Yii::app()->extensionsManager->getAllExtensions();
        $items      = array();
        
        foreach ($extensions as $extension) {
            if ((stripos($extension->name, $term) !== false) || (stripos($extension->description, $term) !== false)) {
            	
            	$url = Yii::app()->createUrl('extensions/index');
            	if ($extension->isEnabled && $extension->pageUrl) {
            		$url = $extension->pageUrl;
	            }
            	
                $item        = new SearchExtSearchItem();
                $item->title = $extension->name;
                $item->url   = $url;
                $item->score++;
                $items[] = $item->fields;
            }
        }
		return $items;
	}
}
	