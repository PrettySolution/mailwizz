<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BaseActiveRecord
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BaseActiveRecord extends CActiveRecord
{
	/**
	 * Flag for active status
	 */
    const STATUS_ACTIVE = 'active';

	/**
	 * Flag for inactive status
	 */
    const STATUS_INACTIVE = 'inactive';

	/**
	 * Flag for deleted status
	 */
    const STATUS_DELETED = 'deleted';

	/**
	 * Flag for bulk delete
	 */
    const BULK_ACTION_DELETE = 'delete';

	/**
	 * Flag for bulk copy
	 */
    const BULK_ACTION_COPY = 'copy';

	/**
	 * Flag for confirmation
	 */
    const TEXT_YES = 'yes';

	/**
	 * Flag for confirmation
	 */
    const TEXT_NO = 'no';

    /**
     * @var 
     */
    private $_modelName;

    /**
     * @var array 
     */
    private static $_relatedCached = array();

    /**
     * @var bool 
     */
    protected $validationHasBeenMade = false;

	/**
	 * @since 1.6.6
	 * @var string
	 */
	public $afterFindStatus = '';

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $hooks = Yii::app()->hooks;
        
        $rules = new CList();
        $rules = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $rules);
        $rules = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $rules);
        
        $this->onRules(new CModelEvent($this, array(
            'rules' => $rules,
        )));
        
        return $rules->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onRules(CModelEvent $event)
    {
        $this->raiseEvent('onRules', $event);
    }

	/**
	 * @inheritdoc
	 */
    public function behaviors()
    {
        $behaviors = CMap::mergeArray(parent::behaviors(), array(
            'shortErrors' => array(
                'class' => 'common.components.behaviors.AttributesShortErrorsBehavior'
            ),
            'fieldDecorator' => array(
                'class' => 'common.components.behaviors.AttributeFieldDecoratorBehavior'
            ),
            'modelMetaData' => array(
                'class' => 'common.components.db.behaviors.ModelMetaDataBehavior'
            ),
            'paginationOptions' => array(
                'class' => 'common.components.behaviors.PaginationOptionsBehavior'
            ),
            'stickySearchFilters' => array(
                'class' => 'common.components.behaviors.StickySearchFiltersBehavior'
            ),
        ));
        
        if ($this->hasAttribute('date_added') || $this->hasAttribute('last_updated')) {
            $behaviors['CTimestampBehavior'] = array(
                'class'           => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => null,
                'updateAttribute' => null,
            );
            
            if ($this->hasAttribute('date_added')) {
                $behaviors['CTimestampBehavior']['createAttribute'] = 'date_added';
            }
            
            if ($this->hasAttribute('last_updated')) {
                $behaviors['CTimestampBehavior']['updateAttribute'] = 'last_updated';
                $behaviors['CTimestampBehavior']['setUpdateOnCreate'] = true;
            }
        }
        
        $behaviors['dateTimeFormatter'] = array(
                'class'                 => 'common.components.db.behaviors.DateTimeFormatterBehavior',
                'dateAddedAttribute'    => 'date_added',
                'lastUpdatedAttribute'  => 'last_updated',
                'timeZone'              => null,
        );
        
	    $hooks = Yii::app()->hooks;
	    
        $behaviors  = new CMap($behaviors);
        $behaviors  = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $behaviors);
        $behaviors  = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $behaviors);
        
        $this->onBehaviors(new CModelEvent($this, array(
            'behaviors' => $behaviors,
        )));
        
        return $behaviors->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onBehaviors(CModelEvent $event)
    {
        $this->raiseEvent('onBehaviors', $event);
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = new CMap(array(
            'status'        => Yii::t('app', 'Status'),
            'date_added'    => Yii::t('app', 'Date added'),
            'last_updated'  => Yii::t('app', 'Last updated'),
        ));
        
        $hooks  = Yii::app()->hooks;
        
        $labels = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $labels);
        $labels = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $labels);
        
        $this->onAttributeLabels(new CModelEvent($this, array(
            'labels' => $labels,
        )));
        
        return $labels->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onAttributeLabels(CModelEvent $event)
    {
        $this->raiseEvent('onAttributeLabels', $event);
    }

	/**
	 * @inheritdoc
	 * @since 1.6.6
	 */
	protected function afterFind()
	{
		parent::afterFind();
		
		if ($this->hasAttribute('status') && !empty($this->status)) {
			$this->afterFindStatus = $this->status;
		}
	}

	/**
	 * @inheritdoc
	 */
    protected function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        
        $this->validationHasBeenMade = true;
        
        return true;
    }

    /**
     * @inheritdoc
     * @since 1.3.8.6
     */
    protected function afterValidate()
    {
        parent::afterValidate();
        Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $this);
    }

    /**
     * @inheritdoc
     * @since 1.3.8.6
     */
    protected function afterSave()
    {
        parent::afterSave();

        Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $this);
    }

	/**
	 * @inheritdoc
	 */
    public function relations()
    {
        $hooks = Yii::app()->hooks;
        
        $relations = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), new CMap());
        
        $this->onRelations(new CModelEvent($this, array(
            'relations' => $relations,
        )));
        
        return $relations->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onRelations(CModelEvent $event)
    {
        $this->raiseEvent('onRelations', $event);
    }

	/**
	 * @inheritdoc
	 */
    public function scopes()
    {
        $scopes = new CMap(array(
            'active' => array(
                'condition' => $this->getTableAlias(false, false).'`status` = :st',
                'params' => array(':st' => self::STATUS_ACTIVE),
            ),
            'inactive' => array(
                'condition' => $this->getTableAlias(false, false).'`status` = :st',
                'params' => array(':st' => self::STATUS_INACTIVE),
            ),
            'deleted' => array(
                'condition' => $this->getTableAlias(false, false).'`status` = :st',
                'params' => array(':st' => self::STATUS_DELETED),
            ),
        ));
        
        $hooks  = Yii::app()->hooks;
        
        $scopes = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $scopes);
        $scopes = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $scopes);
        
        $this->onScopes(new CModelEvent($this, array(
            'scopes' => $scopes,
        )));
        
        return $scopes->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onScopes(CModelEvent $event)
    {
        $this->raiseEvent('onScopes', $event);
    }

	/**
	 * @inheritdoc
	 */
    public function attributeHelpTexts()
    {
        $hooks  = Yii::app()->hooks;

        $texts  = new CMap();
        $texts  = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $texts);
        $texts  = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $texts);
        
        $this->onAttributeHelpTexts(new CModelEvent($this, array(
            'texts' => $texts,
        )));
        
        return $texts->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onAttributeHelpTexts(CModelEvent $event)
    {
        $this->raiseEvent('onAttributeHelpTexts', $event);
    }

	/**
	 * @inheritdoc
	 */
    public function attributePlaceholders()
    {
        $hooks = Yii::app()->hooks;
  
        $placeholders = new CMap();
        $placeholders = $hooks->applyFilters($this->buildHookName(array('class' => false, 'suffix' => strtolower(__FUNCTION__))), $placeholders);
        $placeholders = $hooks->applyFilters($this->buildHookName(array('suffix' => strtolower(__FUNCTION__))), $placeholders);
        
        $this->onAttributePlaceholders(new CModelEvent($this, array(
            'placeholders' => $placeholders,
        )));
        
        return $placeholders->toArray();
    }

	/**
	 * @param CModelEvent $event
	 *
	 * @throws CException
	 */
    public function onAttributePlaceholders(CModelEvent $event)
    {
        $this->raiseEvent('onAttributePlaceholders', $event);
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        if ($this->_modelName === null) {
            $this->_modelName = get_class($this);
        }
        return $this->_modelName;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function statusIs($status = self::STATUS_ACTIVE)
    {
        if (!is_array($status)) {
            $status = array($status);
        }
        $criteria = new CDbCriteria();
        $criteria->addInCondition($this->getTableAlias(false, false).'status', $status);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function statusIsNot($status = self::STATUS_ACTIVE)
    {
        if (!is_array($status)) {
            $status = array($status);
        }
        $criteria = new CDbCriteria();
        $criteria->addNotInCondition($this->getTableAlias(false, false).'status', $status);
        $this->getDbCriteria()->mergeWith($criteria);
        return $this;
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_ACTIVE     => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE   => Yii::t('app', 'Inactive'),
            // self::STATUS_DELETED    => Yii::t('app', 'Deleted'),
        );
    }

    /**
     * @return array
     */
    public function getBulkActionsList()
    {
        return array(
            self::BULK_ACTION_DELETE => Yii::t('app', 'Delete'),
        );
    }

    /**
     * @param null $status
     * @return string
     */
    public function getStatusName($status = null)
    {
        if (!$status && $this->hasAttribute('status')) {
            $status = $this->status;
        }
        if (!$status) {
            return '';
        }
        $list = $this->getStatusesList();
        return isset($list[$status]) ? $list[$status] : Yii::t('app', ucfirst(preg_replace('/[^a-z]/', ' ', strtolower($status))));
    }

    /**
     * @return array
     */
    public function getYesNoOptions()
    {
        return array(
            self::TEXT_YES  => ucfirst(Yii::t('app', self::TEXT_YES)),
            self::TEXT_NO   => ucfirst(Yii::t('app', self::TEXT_NO)),
        );
    }

    /**
     * @return array
     */
    public function getComparisonSignsList()
    {
        return array(
            '='  => '=',
            '>'  => '>',
            '>=' => '>=',
            '<'  => '<',
            '<=' => '<=',
            '<>' => '<>',
        );
    }

    /**
     * @since 1.3.6.2
     * @return array
     */
    public function getSortOrderList()
    {
        return array_combine(range(-100, 100), range(-100, 100));
    }
    
    /**
     * Since 1.3.4.6
     * Override parent implementation to add global in memory cache, in testing for now...
     * This can become a memory hog containing unused models...
     */
    public function getRelated($name,$refresh=false,$params=array())
    {
        $cache = false;
        if (($md = $this->getMetaData()) && isset($md->relations[$name]) && is_object($md->relations[$name]) && is_string($md->relations[$name]->foreignKey) && $this->hasAttribute($md->relations[$name]->foreignKey)) {
            $relationKey = $md->relations[$name]->foreignKey;
            $cacheKey    = $name . '_' . $md->relations[$name]->className . '_' . get_class($this);
            $relationKey = $this->$relationKey;
            $cache       = true;
        }
        
        if (($refresh || !empty($params)) && $cache && (isset(self::$_relatedCached[$cacheKey][$relationKey]) || array_key_exists($relationKey, self::$_relatedCached[$cacheKey]))) {
            unset(self::$_relatedCached[$cacheKey][$relationKey]);
        }
        
        if ($cache && !isset(self::$_relatedCached[$cacheKey])) {
            self::$_relatedCached[$cacheKey] = array();
        }
        
        $related = -1;
        if ($cache && (isset(self::$_relatedCached[$cacheKey][$relationKey]) || array_key_exists($relationKey, self::$_relatedCached[$cacheKey]))) {
            $related = self::$_relatedCached[$cacheKey][$relationKey];
        } 
        
        if ($related === -1) {
            $related = parent::getRelated($name,$refresh,$params);
            if ($cache) {
                self::$_relatedCached[$cacheKey][$relationKey] =& $related;
            }
        }
        
        return $related;
    }

	/**
	 * @since 1.7.9
	 * 
	 * @param array $options
	 *
	 * @return string
	 * @throws Exception
	 */
    final protected function buildHookName(array $options)
    {
    	$options = CMap::mergeArray(array(
    		'suffix' => '',
    		'app'    => true,
		    'class'  => true,
	    ), $options);
    	
    	if (empty($options['suffix'])) {
    		throw new Exception(Yii::t('app', 'Please provide a suffix when building the hook name!'));
	    }
    	
    	$hookParts = array();
    	
    	if ($options['app']) {
		    $hookParts[] = Yii::app()->apps->getCurrentAppName();
	    }
    	
    	$hookParts[] = 'model';
    	
    	if ($options['class']) {
    		$hookParts[] = strtolower(get_class($this));
	    }
    	
    	$hookParts[] = $options['suffix'];
    	
    	return implode('_', array_filter($hookParts));
    }
}