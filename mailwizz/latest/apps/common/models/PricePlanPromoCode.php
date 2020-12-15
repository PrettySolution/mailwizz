<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PricePlanPromoCode
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */

/**
 * This is the model class for table "{{price_plan_promo_code}}".
 *
 * The followings are the available columns in table '{{price_plan_promo_code}}':
 * @property integer $promo_code_id
 * @property string $code
 * @property string $type
 * @property string $discount
 * @property string $total_amount
 * @property integer $total_usage
 * @property integer $customer_usage
 * @property string $date_start
 * @property string $date_end
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property PricePlanOrder[] $pricePlanOrders
 */
class PricePlanPromoCode extends ActiveRecord
{
    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_FIXED_AMOUNT = 'fixed amount';

    public $pickerDateStartComparisonSign;

    public $pickerDateEndComparisonSign;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{price_plan_promo_code}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('code, type, discount, total_amount, total_usage, customer_usage, date_start, date_end, status', 'required'),

            array('code', 'length', 'min' => 1, 'max' => 15),
            array('code', 'unique'),
            array('type', 'in', 'range' => array_keys($this->getTypesList())),
            array('discount, total_amount', 'numerical'),
            array('discount, total_amount', 'type', 'type' => 'float'),
            array('total_usage, customer_usage', 'length', 'min' => 1),
            array('total_usage, customer_usage', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 9999),
            array('date_start, date_end', 'date', 'format' => 'yyyy-MM-dd'),

            array('pickerDateStartComparisonSign, pickerDateEndComparisonSign', 'in', 'range' => array_keys($this->getComparisonSignsList())),
			array('code, type, discount, total_amount, total_usage, customer_usage, date_start, date_end, status', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
            'pricePlanOrders' => array(self::HAS_MANY, 'PricePlanOrder', 'promo_code_id'),
        );
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'code_id'        => Yii::t('promo_codes', 'Code'),
			'code'           => Yii::t('promo_codes', 'Code'),
			'type'           => Yii::t('promo_codes', 'Type'),
			'discount'       => Yii::t('promo_codes', 'Discount'),
			'total_amount'   => Yii::t('promo_codes', 'Total amount'),
			'total_usage'    => Yii::t('promo_codes', 'Total usage'),
			'customer_usage' => Yii::t('promo_codes', 'Customer usage'),
			'date_start'     => Yii::t('promo_codes', 'Date start'),
			'date_end'       => Yii::t('promo_codes', 'Date end'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @return array help text for attributes
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'code_id'        => Yii::t('promo_codes', 'Code'),
			'code'           => Yii::t('promo_codes', 'The promotional code'),
			'type'           => Yii::t('promo_codes', 'The type of the promotional code'),
			'discount'       => Yii::t('promo_codes', 'The discount received after applying this promotional code'),
			'total_amount'   => Yii::t('promo_codes', 'The amount of the price plan in order for this promotional code to apply'),
			'total_usage'    => Yii::t('promo_codes', 'The maximum number of usages for this promotional code. Set it to 0 for unlimited'),
			'customer_usage' => Yii::t('promo_codes', 'How many times a customer can use this promotional code. Set it to 0 for unlimited'),
			'date_start'     => Yii::t('promo_codes', 'The start date for this promotional code'),
			'date_end'       => Yii::t('promo_codes', 'The end date for this promotional code'),
		);

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array attribute placeholders
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'code_id'        => '',
			'code'           => Yii::t('promo_codes', 'i.e: FREE100'),
			'type'           => '',
			'discount'       => Yii::t('promo_codes', 'i.e: 10'),
			'total_amount'   => Yii::t('promo_codes', 'i.e: 30'),
			'total_usage'    => Yii::t('promo_codes', 'i.e: 10'),
			'customer_usage' => Yii::t('promo_codes', 'i.e: 1'),
			'date_start'     => Yii::t('promo_codes', Yii::t('promo_codes', 'i.e: {date}', array('{date}' => date('Y-m-d')))),
			'date_end'       => Yii::t('promo_codes', Yii::t('promo_codes', 'i.e: {date}', array('{date}' => date('Y-m-d', strtotime('+30 days'))))),
		);

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
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

        $comparisonSigns   = $this->getComparisonSignsList();
        $originalDateStart = $this->date_start;
        $originalDateEnd   = $this->date_end;
        if (!empty($this->pickerDateStartComparisonSign) && in_array($this->pickerDateStartComparisonSign, array_keys($comparisonSigns))) {
            $this->date_start = $comparisonSigns[$this->pickerDateStartComparisonSign] . $this->date_start;
        }
        if (!empty($this->pickerDateEndComparisonSign) && in_array($this->pickerDateEndComparisonSign, array_keys($comparisonSigns))) {
            $this->date_end = $comparisonSigns[$this->pickerDateEndComparisonSign] . $this->date_end;
        }

		$criteria->compare('code', $this->code, true);
		$criteria->compare('type', $this->type);
		$criteria->compare('discount', $this->discount);
		$criteria->compare('total_amount', $this->total_amount);
		$criteria->compare('total_usage', $this->total_usage);
		$criteria->compare('customer_usage', $this->customer_usage);
		$criteria->compare('date_start', $this->date_start);
		$criteria->compare('date_end', $this->date_end);
		$criteria->compare('status', $this->status);

        $this->date_start = $originalDateStart;
        $this->date_end   = $originalDateEnd;

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'promo_code_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanPromoCode the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getTypesList()
    {
        return array(
            self::TYPE_FIXED_AMOUNT => ucfirst(Yii::t('promo_codes', self::TYPE_FIXED_AMOUNT)),
            self::TYPE_PERCENTAGE   => ucfirst(Yii::t('promo_codes', self::TYPE_PERCENTAGE)),
        );
    }

    public function getTypeName($type = null)
    {
        if ($type === null) {
            $type = $this->type;
        }
        $types = $this->getTypesList();
        return isset($types[$type]) ? $types[$type] : null;
    }

    public function getCurrency()
    {
        return Currency::model()->findDefault();
    }

    public function getFormattedDiscount()
    {
        if ($this->type == self::TYPE_FIXED_AMOUNT) {
            return Yii::app()->numberFormatter->formatCurrency($this->discount, $this->getCurrency()->code);
        }
        return Yii::app()->numberFormatter->formatDecimal($this->discount) . '%';
    }

    public function getFormattedTotalAmount()
    {
        return Yii::app()->numberFormatter->formatCurrency($this->total_amount, $this->getCurrency()->code);
    }

    public function getDateStart()
    {
        return $this->dateTimeFormatter->formatLocalizedDate($this->date_start);
    }

    public function getDateEnd()
    {
        return $this->dateTimeFormatter->formatLocalizedDate($this->date_end);
    }

    public function getDatePickerFormat()
    {
        return 'yy-mm-dd';
    }

    public function getDatePickerLanguage()
    {
        $language = Yii::app()->getLanguage();
        if (strpos($language, '_') === false) {
            return $language;
        }
        $language = explode('_', $language);

        // commented since 1.3.5.9
        // return $language[0] . '-' . strtoupper($language[1]);
        return $language[0];
    }
}
