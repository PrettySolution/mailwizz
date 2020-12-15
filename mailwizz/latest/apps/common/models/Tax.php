<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Tax
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

/**
 * This is the model class for table "{{price_plan_tax}}".
 *
 * The followings are the available columns in table '{{price_plan_tax}}':
 * @property integer $tax_id
 * @property integer $country_id
 * @property integer $zone_id
 * @property string $name
 * @property string $percent
 * @property string $is_global
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property PricePlanOrder[] $pricePlanOrders
 * @property Country $country
 * @property Zone $zone
 */
class Tax extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{tax}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name, percent, is_global, status', 'required'),
			array('country_id, zone_id', 'numerical', 'integerOnly' => true),
			array('name', 'length', 'max' => 100),
            array('percent', 'numerical'),
            array('percent', 'type', 'type' => 'float'),
			array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            array('is_global', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('country_id', 'exist', 'className' => 'Country'),
            array('zone_id', 'exist', 'className' => 'Zone'),
            
			// The following rule is used by search().
			array('country_id, zone_id, name, percent, is_global, status', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'pricePlanOrders'    => array(self::HAS_MANY, 'PricePlanOrder', 'tax_id'),
			'country'            => array(self::BELONGS_TO, 'Country', 'country_id'),
			'zone'               => array(self::BELONGS_TO, 'Zone', 'zone_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'tax_id'     => Yii::t('taxes', 'Tax'),
			'country_id' => Yii::t('taxes', 'Country'),
			'zone_id'    => Yii::t('taxes', 'Zone'),
			'name'       => Yii::t('taxes', 'Name'),
			'percent'    => Yii::t('taxes', 'Percent'),
            'is_global'  => Yii::t('taxes', 'Is global'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    /**
     * @return array help text for attributes
     */
    public function attributeHelpTexts()
    {
        $texts = array(
			'country_id' => Yii::t('taxes', 'The country for which this tax applies'),
			'zone_id'    => Yii::t('taxes', 'The zone/state for which this tax applies'),
			'name'       => Yii::t('taxes', 'The name of this tax'),
			'percent'    => Yii::t('taxes', 'How much from the total amount of the order this max means, use a number'),
            'is_global'  => Yii::t('taxes', 'Whether this tax is global, i.e: applies for customers that don\'t match other taxes'),
		);
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    protected function afterSave()
    {
        if ($this->is_global == self::TEXT_YES) {
            $criteria = new CDbCriteria();
            $criteria->addCondition('tax_id != :tid');
            $criteria->params = array(
                ':tid' => (int)$this->tax_id
            );

            $criteria = Yii::app()->hooks->applyFilters('model_tax_after_save_before_update_all_criteria', $criteria);

            self::model()->updateAll(array('is_global' => self::TEXT_NO), $criteria);
        }
        
        return parent::afterSave();
    }

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria=new CDbCriteria;

        if ($this->country_id) {
            if (is_string($this->country_id)) {
                $criteria->with['country'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> '(country.name LIKE :c01 OR country.code LIKE :c01)',
                    'params'   => array(':c01' => '%'. $this->country_id .'%')
                );
            } else {
                $criteria->compare('t.country_id', (int)$this->country_id);
            }
        }
        
        if ($this->zone_id) {
            if (is_string($this->zone_id)) {
                $criteria->with['zone'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> '(zone.name LIKE :z01 OR zone.code LIKE :z01)',
                    'params'   => array(':z01' => '%'. $this->zone_id .'%')
                );
            } else {
                $criteria->compare('t.zone_id', (int)$this->zone_id);
            }
        }
        
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.percent',$this->percent,true);
        $criteria->compare('t.is_global',$this->is_global);
		$criteria->compare('t.status',$this->status);
        
        $criteria->order = 't.tax_id DESC';
        
		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.tax_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanTax the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getFormattedPercent()
    {
        return Yii::app()->format->formatNumber($this->percent) . '%';
    }
    
    public static function getAsDropdownOptions()
    {
        static $options;
        if ($options !== null) {
            return $options;
        }
        $options = array();
        $taxes   = self::model()->findAll(array('select' => 'tax_id, name, percent', 'order' => 'name ASC'));
        foreach ($taxes as $tax) {
            $options[$tax->tax_id] = $tax->name . '('. $tax->getFormattedPercent() .')';
        }
        return $options;
    }
}
