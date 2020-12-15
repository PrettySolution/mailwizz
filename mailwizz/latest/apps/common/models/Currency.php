<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Currency
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */

/**
 * This is the model class for table "{{currency}}".
 *
 * The followings are the available columns in table '{{currency}}':
 * @property integer $currency_id
 * @property string $name
 * @property string $code
 * @property string $value
 * @property string $is_default
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property PricePlanOrder[] $pricePlanOrders
 */
class Currency extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{currency}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('name, code, is_default, status', 'required'),
            array('name', 'length', 'max' => 100),
            array('code', 'length', 'is' => 3),
            array('code', 'match', 'pattern' => '/[A-Z]{3}/'),
            array('code', 'unique'),
            array('is_default', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            array('name, code, is_default, status', 'safe', 'on' => 'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'pricePlanOrders' => array(self::HAS_MANY, 'PricePlanOrder', 'currency_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'currency_id'    => Yii::t('currencies', 'Currency'),
			'name'           => Yii::t('currencies', 'Name'),
			'code'           => Yii::t('currencies', 'Code'),
			'value'          => Yii::t('currencies', 'Value'),
			'is_default'     => Yii::t('currencies', 'Is default'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    protected function beforeValidate()
    {
        if ($this->code !== null) {
            try {
                Yii::app()->numberFormatter->formatCurrency(10.00, $this->code);
            } catch (Exception $e) {
                $this->addError('code', Yii::t('currencies', 'Unrecognized currecy code!'));
            }
        }
        if ($this->is_default == self::TEXT_NO) {
            $hasDefault = self::model()->countByAttributes(array('is_default' => self::TEXT_YES));
            if (empty($hasDefault)) {
                $this->is_default = self::TEXT_YES;
            }
        }
        $this->value = '1.00000000';
        return parent::beforeValidate();
    }
    
    protected function afterSave()
    {
        if ($this->is_default == self::TEXT_YES) {
            self::model()->updateAll(array('is_default' => self::TEXT_NO), array('condition' => 'currency_id != :cid', 'params' => array(':cid' => (int)$this->currency_id)));
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
		$criteria->compare('name', $this->name, true);
		$criteria->compare('code', $this->code, true);
		$criteria->compare('is_default', $this->is_default);
		$criteria->compare('status', $this->status);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'currency_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Currency the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function findByCode($code)
    {
        return self::model()->findByAttributes(array('code' => $code));
    }
    
    public function findDefault()
    {
        $currency = self::model()->findByAttributes(array(
            'is_default' => self::TEXT_YES,
        ));
        
        if (!empty($currency)) {
            return $currency;
        }

        $currency = self::model()->findByAttributes(array(
            'code' => 'USD',
        ));
        
        if (empty($currency)) {
            $currency = new self();
            $currency->code = 'USD';
            $currency->name = 'US Dollar';
        }
        
        $currency->is_default = self::TEXT_YES;
        if ($currency->save()) {
            return $currency;
        }
        
        return false;
    }
    
    public function getIsRemovable()
    {
        return $this->is_default != self::TEXT_YES;
    }
}
