<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BackendDashboardCacheCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */
 
class BackendDashboardCacheCommand extends ConsoleCommand 
{
    /**
     * @return int
     */
    public function actionIndex() 
    {
        $this
	        ->rebuildGlanceStatsCache()
            ->rebuildTimelineItemsCache();
        
        return 0;
    }

	/**
	 * @return $this
	 */
    protected function rebuildGlanceStatsCache()
    {
	    // hold default app language
    	$lang = Yii::app()->language;
    	
    	foreach ($this->getUsersLanguages() as $languageId => $languageCode) {

		    Yii::app()->language = $languageCode;
		    
		    $cacheKey = sha1('backend.dashboard.glanceStats.' . $languageId);
		    Yii::app()->cache->set($cacheKey, BackendDashboardHelper::getGlanceStats(), 600);
	    }

    	// restore app language
	    Yii::app()->language = $lang;
    	
    	return $this;
    }

	/**
	 * @return $this
	 */
    protected function rebuildTimelineItemsCache()
    {
    	// hold default app language
	    $lang = Yii::app()->language;

	    foreach ($this->getUsersLanguages() as $languageId => $languageCode) {

		    Yii::app()->language = $languageCode;

		    $cacheKey = sha1('backend.dashboard.timelineItems.' . $languageId);
		    Yii::app()->cache->set($cacheKey, BackendDashboardHelper::getTimelineItems(), 600);
	    }

	    // restore app language
	    Yii::app()->language = $lang;
	    
	    return $this;
    }

	/**
	 * @return User[]
	 */
    protected function getUsers()
    {
    	static $users;
    	if ($users === null) {
		    $users = User::model()->findAll();
	    }
    	return $users;
    }

	/**
	 * @return array
	 */
    protected function getUsersLanguages()
    {
	    $usersLanguages = array(
		    // default 
		    0 => Yii::app()->language
	    );

	    foreach ($this->getUsers() as $user) {
		    if (empty($user->language_id) || empty($user->language)) {
			    continue;
		    }
		    $usersLanguages[$user->language_id] = $user->language->getLanguageAndLocaleCode();
	    }
	    
	    return $usersLanguages;
    }
}
