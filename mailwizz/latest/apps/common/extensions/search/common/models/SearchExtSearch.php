<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SearchExt
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class SearchExtSearch extends FormModel
{
	/**
	 * @var string 
	 */
	public $term = '';

	/**
	 * @var array 
	 */
	protected $commonKeywords = array('index', 'status', 'date added', 'last updated');

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	public function getResults()
	{
		return $this->getItems();
	}
	
	/**
	 * @return array
	 * @throws ReflectionException
	 */
	protected function getItems()
	{
		$term = trim((string)$this->term);
		if (strlen($term) < 3 || strlen($term) > 100) {
			return array();
		}
		
		$items   = $this->prepareItemsFromParsedFiles();
		$results = array();
		
		foreach ($items as $item) {
			
			if (stripos($item->title, $term) !== false) {
				$item->score += 2;
			}
			
			foreach ($item->keywords as $keyword) {
				if (stripos($keyword, $term) !== false) {
					$item->score++;
				}
			}

			if (!empty($item->childrenGenerator) && is_callable($item->childrenGenerator, true)) {
				if ($item->children = call_user_func($item->childrenGenerator, $term, $item)) {
					$item->score++;
				}
			}
			
			if ($item->score) {
				$results[] = $item->fields;
			}
		}
		
		$sort = array(
			'score' => array(),
			'title' => array()
		);
		
		foreach ($results as $index => $result) {
			$sort['title'][$index] = $result['title'];
			$sort['score'][$index] = $result['score'];
		}

		array_multisort($sort['score'], SORT_DESC, $sort['title'], SORT_ASC, $results);
		
		return $results;
	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	protected function prepareItemsFromParsedFiles()
	{
		$items = $this->getItemsFromParsedFiles();
		$items = (array)Yii::app()->hooks->applyFilters('ext_search_searchable_items_list', (array)$items);
		foreach ($items as $index => $item) {
			if (!($item instanceof SearchExtSearchItem)) {
				unset($items[$index]);
			}
		}
		$items = array_values($items);
		
		foreach ($items as $index => $item) {

			if ($item->skip === null) {
				$item->skip = array($this, '_defaultSkipLogic');
			}

			if (!empty($item->skip) && is_callable($item->skip, true) && call_user_func($item->skip, $item)) {
				unset($items[$index]);
				continue;
			}

			if (!empty($item->keywordsGenerator) && is_callable($item->keywordsGenerator, true)) {
				$item->keywords = CMap::mergeArray($item->keywords, (array)call_user_func($item->keywordsGenerator));
			}

			$item->keywords = array_map('strtolower', $item->keywords);
			$item->keywords = array_filter(array_unique($item->keywords));
			$item->keywords = array_values(array_diff($item->keywords, $this->commonKeywords));
		}

		$items = array_values($items);
		
		return $items;
	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	protected function getItemsFromParsedFiles()
	{
		$paths = array(
			Yii::getPathOfAlias('root.apps.' . MW_APP_NAME . '.controllers'),
		);
		
		$excludeFiles      = $this->getExcludedFilesFromIndexing();
		$enabledExtensions = false;

		// this allows us to look inside the controllers of active extensions
		$autoSearchDisabledExtensions = array();
		$autoSearchDisabledExtensions = Yii::app()->hooks->applyFilters('ext_search_autosearch_disabled_extensions', $autoSearchDisabledExtensions);
		$autoSearchDisabledExtensions = array_unique($autoSearchDisabledExtensions);
		
		$em         = Yii::app()->extensionsManager;
		$extensions = $em->getAllExtensions();
		foreach ($extensions as $extension) {
			
			if (!$extension->isEnabled || in_array($extension->getDirName(), $autoSearchDisabledExtensions))	{
				continue;
			}
			
			$refl    = new ReflectionClass($extension);
			$paths[] = dirname($refl->getFileName()) . '/' . MW_APP_NAME . '/controllers';
			$enabledExtensions = true;
		}
		// end active extensions lookup
		
		$extension     = Yii::app()->extensionsManager->getExtensionInstance('search');
		$behaviorPaths = array(
			$extension->getPathOfAlias(MW_APP_NAME . '.behaviors.ExtSearchBehavior'),
			$extension->getPathOfAlias('common.behaviors.ExtSearchBehavior'),
		);
		
		$items = array();
		foreach ($paths as $path) {

			$controllerFiles = glob($path . "/*Controller.php");
			foreach ($controllerFiles as $controllerFile) {
				
				if (in_array($controllerFile, $excludeFiles)) {
					continue;
				}

				$className = basename($controllerFile, '.php');
				if (class_exists($className, false)) {
					continue;
				}

				require_once $controllerFile;

				$controllerId   = strtolower(substr($className, 0, -10));
				$instance       = new $className($controllerId);
				$reflection     = new ReflectionClass($instance);

				$searchableActions = array();
				if (method_exists($instance, 'actionIndex')) {
					$searchableActions['index'] = array();
				}
				
				if (method_exists($instance, 'actionCreate')) {
					$searchableActions['create'] = array();
				}
				
				foreach ($behaviorPaths as $behaviorPath) {
					$behaviorFile = $behaviorPath . $className . '.php';
					if (is_file($behaviorFile)) {
						require_once $behaviorFile;
						$behaviorClassName = basename($behaviorFile, '.php');
						$instance->attachBehavior($behaviorClassName, array(
							'class' => $behaviorClassName,
						));
						$searchableActions = CMap::mergeArray($searchableActions, $instance->searchableActions());
						break;
					}
				}
				
				if (empty($searchableActions)) {
					continue;
				}

				foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
					if (strpos($method->name, 'action') !== 0 || $method->name == 'actions') {
						continue;
					}

					$actionId = strtolower(substr($method->name, 6));
					if (!isset($searchableActions[$actionId]) || !is_array($searchableActions[$actionId])) {
						continue;
					}
					
					$skipActions        = array('index');
					$route              = $controllerId . '/' . $actionId;
					$controllerIdParsed = $controllerId;
					if ($enabledExtensions) {
						$controllerIdParsed = str_replace(array('_ext', 'ext_'), '', $controllerIdParsed);
					}
					$controllerIdParsed = str_replace('_', ' ', $controllerIdParsed);
					$actionIdParsed     = str_replace('_', ' ', $actionId);
					$item               = new SearchExtSearchItem();
					
					$item->title = ucfirst(strtolower($controllerIdParsed));
					if (!in_array($actionId, $skipActions)) {
						$item->title .= ' / ' . ucfirst(strtolower($actionIdParsed));;
					}
					
					$item->url   = Yii::app()->createUrl($route);
					$item->route = $route;

					if (!in_array($actionId, $skipActions)) {
						$item->keywords[] = $controllerIdParsed . ' ' . $actionIdParsed;
						$item->keywords[] = $actionIdParsed . ' ' . $controllerIdParsed;
					} else {
						$item->keywords[] = $controllerIdParsed;
					}
					
					$item->mergeAttributes($searchableActions[$actionId]);

					$items[] = $item;
				}
			}
		}
		
		return $items;
	}

	/**
	 * @return mixed
	 */
	protected function getExcludedFilesFromIndexing()
	{
		$backendControllers  = Yii::getPathOfAlias('root.apps.backend.controllers') . DIRECTORY_SEPARATOR;
		$customerControllers = Yii::getPathOfAlias('root.apps.customer.controllers') . DIRECTORY_SEPARATOR;
		$excludeFiles = array(
			$backendControllers  . 'UpdateController.php',
			$customerControllers . 'List_exportController.php',
			$customerControllers . 'List_fieldsController.php',
			$customerControllers . 'List_formsController.php',
			$customerControllers . 'List_importController.php',
			$customerControllers . 'List_pageController.php',
			$customerControllers . 'List_segments_exportController.php',
			$customerControllers . 'List_segmentsController.php',
			$customerControllers . 'List_subscribersController.php',
			$customerControllers . 'List_toolsController.php',
			$customerControllers . 'Survey_fieldsController.php',
			$customerControllers . 'Survey_segmentsController.php',
			$customerControllers . 'Survey_respondersController.php',
			$customerControllers . 'Survey_segments_exportController.php',
			$customerControllers . 'Suppression_list_emailsController.php',
			$customerControllers . 'Campaign_reportsController.php',
			$customerControllers . 'Campaign_exportController.php',
		);
		
		return Yii::app()->hooks->applyFilters('ext_search_exclude_files_from_indexing', $excludeFiles);
	}

	/**
	 * @param $item
	 *
	 * @return bool
	 */
	protected function _defaultSkipLogic($item)
	{
		if (MW_APP_NAME == 'backend') {
			return !Yii::app()->user->getModel()->hasRouteAccess($item->route);
		}
		return false;
	}
}
