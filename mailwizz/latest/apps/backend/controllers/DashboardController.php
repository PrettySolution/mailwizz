<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DashboardController
 *
 * Handles the actions for dashboard related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class DashboardController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->mergeWith(array(
            array('src' => AssetsUrl::js('dashboard.js'))
        ));
        parent::init();
    }
    
    /**
     * Display dashboard informations
     */
    public function actionIndex()
    {
        $options = Yii::app()->options;
        $notify  = Yii::app()->notify;
        
        if (file_exists(Yii::getPathOfAlias('root.install')) && is_dir($dir = Yii::getPathOfAlias('root.install'))) {
            $notify->addWarning(Yii::t('app', 'Please remove the install directory({dir}) from your application!', array(
                '{dir}' => $dir,
            )));
        }
        
        // since 1.7.4
	    $minPhpVersion = '7.2';
        if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
	        $notify->addWarning(Yii::t('app', 'You are using an outdated version of PHP({v1}) which will not be supported in the near future! Please upgrade PHP to at least version {v2}!', array(
		        '{v1}' => PHP_VERSION,
		        '{v2}' => $minPhpVersion,
	        )));
        }

        // since 1.3.6.3
        if ($options->get('system.installer.freshinstallextensionscheck', 0) == 0) {
            $options->set('system.installer.freshinstallextensionscheck', 1);
            
            $notify->clearAll()->addInfo(Yii::t('extensions', 'Conducting extensions checks for the fresh install...'));
            
            $manager    = Yii::app()->extensionsManager;
            $extensions = $manager->getCoreExtensions();
            $errors     = array();
            foreach ($extensions as $id => $instance) {
                if ($manager->extensionMustUpdate($id) && !$manager->updateExtension($id)) {
                    $errors[] = Yii::t('extensions', 'The extension "{name}" has failed to update!', array(
                        '{name}' => CHtml::encode($instance->name),
                    ));
                    $errors = CMap::mergeArray($errors, (array)$manager->getErrors());
                    $manager->resetErrors();
                }
            }
            
            if (!empty($errors)) {
                $notify->addError($errors);
            } else {
                $notify->addSuccess(Yii::t('extensions', 'All extension checks were conducted successfully.'));
            }
            
            // enable extensions
            $manager          = Yii::app()->extensionsManager; 
            $enableExtensions = array('tour', 'email-template-builder', 'search');
            foreach ($enableExtensions as $ext) {
                if ($manager->enableExtension($ext)) {
                    $manager->getExtensionInstance($ext)->setOption('enabled', 'yes');
                }
            }
            //
            
            $this->redirect(array('dashboard/index'));
        }

	    // since 1.6.2
	    if ($options->get('system.installer.freshinstallcommonemailtemplates', 0) == 0) {
		    $options->set('system.installer.freshinstallcommonemailtemplates', 1);
		    CommonEmailTemplate::reinstallCoreTemplates();
	    }
        
        //
        $checkVersionUpdate = $options->get('system.common.check_version_update', 'yes') == 'yes';
        
        // stats
        $timelineItems = $this->getTimelineItems();

        // 1.4.5
        $appName     = Yii::app()->apps->getCurrentAppName();
        $glanceStats = Yii::app()->hooks->applyFilters($appName . '_dashboard_glance_stats_list', array(), $this);
        if (empty($glanceStats)) {
            $glanceStats = $this->getGlanceStats();
        }
        $keys = array('count', 'heading', 'icon', 'url');
        foreach ($glanceStats as $index => $stat) {
            foreach ($keys as $key) {
                if (!array_key_exists($key, $stat)) {
                    unset($glanceStats[$index]);
                }
            }
        }
        //

        $renderItems = false;
        foreach ($glanceStats as $stat) {
            if (!empty($stat['count'])) {
                $renderItems = true;
                break;
            }
        }
        
        //
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('dashboard', 'Dashboard'),
            'pageHeading'       => Yii::t('dashboard', 'Dashboard'),
            'pageBreadcrumbs'   => array(
                Yii::t('dashboard', 'Dashboard'),
            ),
        ));
        
        $this->render('index', compact('checkVersionUpdate', 'glanceStats', 'timelineItems', 'renderItems'));
    }

    /**
     * Check for updates
     */
    public function actionCheck_update()
    {
        ignore_user_abort(true);

        if (!Yii::app()->request->isAjaxRequest) {
            $this->redirect(array('dashboard/index'));
        }

        $options = Yii::app()->options;
        if ($options->get('system.common.enable_version_update_check', 'yes') == 'no') {
            Yii::app()->end();
        }

        $now        = time();
        $lastCheck  = (int)$options->get('system.common.version_update.last_check', 0);
        $interval   = 60 * 60 * 24; // once at 24 hours should be enough

        if ($lastCheck + $interval > $now) {
            Yii::app()->end();
        }

        $options->set('system.common.version_update.last_check', $now);

        $response = AppInitHelper::simpleCurlGet('https://www.mailwizz.com/api/site/version');
        if (empty($response) || $response['status'] == 'error') {
            Yii::app()->end();
        }

        $json = CJSON::decode($response['message']);
        if (empty($json['current_version'])) {
            Yii::app()->end();
        }

        $dbVersion = $options->get('system.common.version', '1.0');
        if (version_compare($json['current_version'], $dbVersion, '>')) {
            $options->set('system.common.version_update.current_version', $json['current_version']);
        }

        Yii::app()->end();
    }

    /**
     * Campaigns list
     */
    public function actionCampaigns()
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            return $this->redirect(array('dashboard/index'));
        }

        $listId     = (int)$request->getPost('list_id');
        $campaignId = (int)$request->getPost('campaign_id');

        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id, name';
        $criteria->compare('status', Campaign::STATUS_SENT);
        $criteria->compare('list_id', $listId);
        $criteria->order = 'campaign_id DESC';
        $criteria->limit = 50;

        $latestCampaigns = Campaign::model()->findAll($criteria);
        $campaignsList   = array();
        foreach ($latestCampaigns as $cmp) {
            $campaignsList[$cmp->campaign_id] = $cmp->name;
        }

        if (empty($campaignId) && !empty($latestCampaigns)) {
            $campaignId = $latestCampaigns[0]->campaign_id;
        }

        $campaign = Campaign::model()->findByAttributes(array(
            'campaign_id' => $campaignId,
            'status'      => Campaign::STATUS_SENT,
        ));

        if (empty($campaign)) {
            return $this->renderJson(array(
                'html'  => '',
            ));
        }
        
        return $this->renderJson(array(
            'html'  => $this->renderPartial('_campaigns', compact('campaign', 'campaignsList'), true),
        ));
    }


    /**
     * @return array
     */
    public function getGlanceStats()
    {
        $user       = Yii::app()->user->getModel();
        $languageId = (int)$user->language_id;
        $cacheKey   = sha1('backend.dashboard.glanceStats.' . $languageId);
        $cache      = Yii::app()->cache;

        if (($items = $cache->get($cacheKey))) {
            return $items;
        }
        
        // since 1.7.6
        $items = BackendDashboardHelper::getGlanceStats();

        $cache->set($cacheKey, $items, 600);
        
        return $items;
    }

    /**
     * @return array
     */
    public function getTimelineItems()
    {
        $user       = Yii::app()->user->getModel();
        $languageId = (int)$user->language_id;
        $cacheKey   = sha1('backend.dashboard.timelineItems.' . $languageId);
        $cache      = Yii::app()->cache;
        
        if (($items = $cache->get($cacheKey))) {
            return $items;
        }

	    // since 1.7.6
        $items = BackendDashboardHelper::getTimelineItems();
        
        $cache->set($cacheKey, $items, 600);
        
        return $items;
    }
}
