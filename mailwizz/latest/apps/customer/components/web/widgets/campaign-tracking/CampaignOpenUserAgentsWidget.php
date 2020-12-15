<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignOpenUserAgentsWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.4
 */
 
class CampaignOpenUserAgentsWidget extends CWidget 
{
    /**
     * @var $campaign Campaign|null
     */
    public $campaign;

	/**
	 * @return string
	 * @throws CException
	 */
    public function run() 
    {
    	if (empty($this->campaign) || !version_compare(PHP_VERSION, '5.4', '>=')) {
    		return '';
	    }

	    // 1.7.9
	    if ($this->campaign->option->open_tracking != CampaignOption::TEXT_YES) {
		    return '';
	    }

	    // 1.7.9 - static counters
	    if ($this->campaign->option->opens_count >= 0) {
		    return '';
	    }
	    
        $cacheKey = __METHOD__;
        if ($this->campaign) {
            $cacheKey .= '::' . $this->campaign->campaign_uid;
        }
        $cacheKey = sha1($cacheKey);
        
        if (($data = Yii::app()->cache->get($cacheKey)) === false) {
            $data = $this->getData();
            Yii::app()->cache->set($cacheKey, $data, 300);
        }
        
        if (empty($data)) {
            return '';
        }
        
        $chartData = array(
	        'os'     => array(),
	        'device' => array(),
	        'browser'=> array(),
        );
        
        $allEmpty = true;
        foreach ($chartData as $key => $_) {

	        if (empty($data[$key])) {
	            continue;
            }
            $allEmpty = false;

	        foreach ($data[$key] as $row) {
		        $chartData[$key][] = array(
			        'label'           => $row['name'],
			        'data'            => $row['count'],
			        'count'           => $row['count'],
			        'count_formatted' => Yii::app()->numberFormatter->formatDecimal($row['count']),
		        );
	        }
        }
        
        if ($allEmpty) {
        	return '';
        }
        
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.pie.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/campaign-open-user-agents.js'));
        
        $this->render('campaign-open-user-agents', compact('chartData', 'data'));
    }

    /**
     * @return array
     */
    protected function getData()
    {
    	$limit  = 5000;
    	$offset = 0;
    	
    	$detector = '\WhichBrowser\Parser';
    	$data     = array(
		    'os'       => array(),
    		'device'   => array(),
		    'browser'  => array(),
	    );
    	
    	while (($models = $this->getModels($limit, $offset))) {
		    $offset = $offset + $limit;
		    
		    foreach ($models as $model) {
		    	
		    	if (strlen($model['user_agent']) < 10) {
		    		continue;
			    }
		    	$result = new $detector($model['user_agent'], array('detectBots' => false));

		    	if (empty($result->os->name) || empty($result->device->type) || empty($result->browser->name)) {
		    		continue;
			    }
		    	
		    	// OS
			    if (!isset($data['os'][$result->os->name])) {
				    $data['os'][$result->os->name] = array(
					    'name'  => ucwords($result->os->name),
					    'count' => 0,
				    );
			    }
			    $data['os'][$result->os->name]['count'] += $model['counter'];
			    
			    // Device
		    	if (!isset($data['device'][$result->device->type])) {
				    $data['device'][$result->device->type] = array(
				    	'name'  => ucwords($result->device->type),
					    'count' => 0,
				    );
			    }
			    $data['device'][$result->device->type]['count'] += $model['counter'];
		    	
		    	// Browser
			    $name = $result->browser->name;
			    if (!empty($result->browser->version)) {
			    	$version = explode('.', $result->browser->version->value);
			    	$version = array_slice($version, 0, 2);
			    	$version = implode('.', $version);
				    $name .= sprintf('(v.%s)', $version);
			    }
			    if (!isset($data['browser'][$name])) {
				    $data['browser'][$name] = array(
					    'name'  => ucwords($name),
					    'count' => 0,
				    );
			    }
			    $data['browser'][$name]['count'] += $model['counter'];
		    }
	    }
	    
	    foreach ($data as $key => $contents) {
	    	$counts = array();
	    	foreach ($contents as $content) {
	    		$counts[] = $content['count'];
		    }
		    $items = $data[$key];
		    array_multisort($counts, SORT_NUMERIC | SORT_DESC, $items);
		    $data[$key] = array_slice($items, 0, 50);
	    }
	    
        return $data;
    }

	/**
	 * @param $limit
	 * @param $offset
	 *
	 * @return array
	 */
	protected function getModels($limit, $offset)
	{
		try {

			$rows = Yii::app()->db->createCommand()
				->select('user_agent, count(user_agent) as counter')
				->from(CampaignTrackOpen::model()->tableName())
				->where('campaign_id = :campaign_id', array(':campaign_id' => (int)$this->campaign->campaign_id))
				->group('user_agent')
				->limit($limit)
				->offset($offset)
				->queryAll();
			
		} catch (Exception $e) {

			$rows = array();
		}
		
		return $rows;
	}
}