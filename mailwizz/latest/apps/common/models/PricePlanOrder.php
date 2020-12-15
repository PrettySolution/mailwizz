<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PricePlanOrder
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */

/**
 * This is the model class for table "{{price_plan_order}}".
 *
 * The followings are the available columns in table '{{price_plan_order}}':
 * @property integer $order_id
 * @property string $order_uid
 * @property integer $customer_id
 * @property integer $plan_id
 * @property integer $promo_code_id
 * @property integer $tax_id
 * @property integer $currency_id
 * @property string $subtotal
 * @property string $tax_percent
 * @property string $tax_value
 * @property string $discount
 * @property string $total
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Tax $tax
 * @property PricePlan $plan
 * @property Customer $customer
 * @property PricePlanPromoCode $promoCode
 * @property Currency $currency
 * @property PricePlanOrderNote[] $notes
 * @property PricePlanOrderTransaction[] $transactions
 */
class PricePlanOrder extends ActiveRecord
{
    const STATUS_INCOMPLETE = 'incomplete';
    
    const STATUS_COMPLETE = 'complete';
    
    const STATUS_PENDING = 'pending';
    
    const STATUS_FAILED = 'failed';
    
    const STATUS_REFUNDED = 'refunded';
    
    const STATUS_DUE = 'due';
    
    protected $_initStatus;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{price_plan_order}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('customer_id, plan_id, currency_id', 'required'),
			array('customer_id, plan_id, promo_code_id, currency_id, tax_id', 'numerical', 'integerOnly' => true),
			array('subtotal, discount, total, tax_value, tax_percent', 'numerical'),
            array('subtotal, discount, total, tax_value, tax_percent', 'type', 'type' => 'float'),
			array('status', 'in', 'range' => array_keys($this->getStatusesList())),
			
            // The following rule is used by search().
			array('order_uid, customer_id, plan_id, promo_code_id, currency_id, tax_id, subtotal, tax_value, tax_percent, discount, total, status', 'safe', 'on'=>'search'),
            array('subtotal, tax_value, discount, total', 'safe', 'on'=>'customer-search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
            'tax'            => array(self::BELONGS_TO, 'Tax', 'tax_id'),
			'plan'           => array(self::BELONGS_TO, 'PricePlan', 'plan_id'),
			'customer'       => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'promoCode'      => array(self::BELONGS_TO, 'PricePlanPromoCode', 'promo_code_id'),
			'currency'       => array(self::BELONGS_TO, 'Currency', 'currency_id'),
            'notes'          => array(self::HAS_MANY, 'PricePlanOrderNote', 'order_id'),
            'transactions'   => array(self::HAS_MANY, 'PricePlanOrderTransaction', 'order_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'order_id'       => Yii::t('orders', 'Order'),
			'order_uid'      => Yii::t('orders', 'Order no.'),
			'customer_id'    => Yii::t('orders', 'Customer'),
			'plan_id'        => Yii::t('orders', 'Plan'),
			'promo_code_id'  => Yii::t('orders', 'Promo code'),
            'tax_id'         => Yii::t('orders', 'Tax'),
			'currency_id'    => Yii::t('orders', 'Currency'),
			'subtotal'       => Yii::t('orders', 'Subtotal'),
            'tax_percent'    => Yii::t('orders', 'Tax percent'),
            'tax_value'      => Yii::t('orders', 'Tax value'),
			'discount'       => Yii::t('orders', 'Discount'),
			'total'          => Yii::t('orders', 'Total'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    /**
     * @return array help text for attributes
     */
    public function attributeHelpTexts()
    {
        $texts = array(
			'customer_id'    => Yii::t('orders', 'The customer this order applies to, autocomplete enabled'),
			'plan_id'        => Yii::t('orders', 'The price plan included in this order, autocomplete enabled'),
			'promo_code_id'  => Yii::t('orders', 'The promo code applied to this order, autocomplete enabled'),
		);
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    /**
     * @return array attribute placeholders
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'customer_id'    => Yii::t('orders', 'Customer, autocomplete enabled'),
			'plan_id'        => Yii::t('orders', 'Plan, autocomplete enabled'),
			'promo_code_id'  => Yii::t('orders', 'Promo code, autocomplete enabled'),
			'currency_id'    => '',
			'subtotal'       => '0.0000',
			'discount'       => '0.0000',
			'total'          => '0.0000',
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
		$criteria = new CDbCriteria;

        if ($this->customer_id) {
            if (is_string($this->customer_id)) {
                $criteria->with['customer'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> '(CONCAT(customer.first_name, " ", customer.last_name) LIKE :c01 OR customer.email LIKE :c01)',
                    'params'   => array(':c01' => '%'. $this->customer_id .'%')
                );
            } else {
                $criteria->compare('t.customer_id', (int)$this->customer_id);
            }
        }
        
        if ($this->plan_id) {
            if (is_string($this->plan_id)) {
                $criteria->with['plan'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> 'plan.name LIKE :p01',
                    'params'   => array(':p01' => '%'. $this->plan_id .'%')
                );
            } else {
                $criteria->compare('t.plan_id', (int)$this->plan_id);
            }
        }
        
        if ($this->promo_code_id) {
            if (is_string($this->promo_code_id)) {
                $criteria->with['promoCode'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> 'promoCode.code LIKE :pc01',
                    'params'   => array(':pc01' => '%'. $this->promo_code_id .'%')
                );
            } else {
                $criteria->compare('t.promo_code_id', (int)$this->promo_code_id);
            }
        }
        
        if ($this->currency_id) {
            if (is_string($this->currency_id)) {
                $criteria->with['currency'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> 'currency.code LIKE :cr01',
                    'params'   => array(':cr01' => '%'. $this->currency_id .'%')
                );
            } else {
                $criteria->compare('t.currency_id', (int)$this->currency_id);
            }
        }
        
        if ($this->tax_id) {
            if (is_string($this->tax_id)) {
                $criteria->with['tax'] = array(
                    'together' => true,
                    'joinType' => 'INNER JOIN',
                    'condition'=> 'currency.code LIKE :t01',
                    'params'   => array(':t01' => '%'. $this->tax_id .'%')
                );
            } else {
                $criteria->compare('t.tax_id', (int)$this->tax_id);
            }
        }
        
        $criteria->compare('t.order_uid', $this->order_uid, true);
		$criteria->compare('t.subtotal', $this->subtotal, true);
        $criteria->compare('t.tax_value', $this->tax_value, true);
        $criteria->compare('t.tax_percent', $this->tax_percent, true);
		$criteria->compare('t.discount', $this->discount, true);
		$criteria->compare('t.total', $this->total, true);
		$criteria->compare('t.status', $this->status);
        
        $criteria->order = 't.order_id DESC';
        
		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.order_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanOrder the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getStatusesList()
    {
        return array(
            self::STATUS_INCOMPLETE => Yii::t('app', 'Incomplete'),
            self::STATUS_COMPLETE   => Yii::t('app', 'Complete'),
            self::STATUS_PENDING    => Yii::t('app', 'Pending'),
            self::STATUS_DUE        => Yii::t('app', 'Due'),
            self::STATUS_FAILED     => Yii::t('app', 'Failed'),
            self::STATUS_REFUNDED   => Yii::t('app', 'Refunded'),
        );
    }

    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }
        
        if (empty($this->order_uid)) {
            $this->order_uid = $this->generateUid();
        }

        return true;
    }

    protected function afterConstruct()
    {
        $this->_initStatus = $this->status;
        if (empty($this->currency_id)) {
            $currency = Currency::model()->findDefault();
            $this->addRelatedRecord('currency', $currency, false);
            $this->currency_id = $currency->currency_id;
        }
        parent::afterConstruct();
    }

    protected function afterFind()
    {
        $this->_initStatus = $this->status;
        parent::afterFind();
    }

    protected function afterSave()
    {
        if (in_array($this->_initStatus, array(self::STATUS_INCOMPLETE, self::STATUS_PENDING, self::STATUS_DUE)) && $this->status == self::STATUS_COMPLETE) {
            $this->customer->group_id = $this->plan->group_id;
            $this->customer->save(false);
            $this->customer->createQuotaMark();
        }
        parent::afterSave();
    }
    
    public function calculate()
    {
        if (empty($this->plan_id)) {
            return $this;
        }

	    // since 1.7.6
	    Yii::app()->hooks->applyFilters('price_plan_order_before_calculate_totals', $this);
        
        $this->subtotal = $this->plan->price;
        $this->total    = $this->plan->price;
        
        if (!empty($this->promo_code_id) && !empty($this->promoCode)) {
            $this->discount = 0;
            
            if ($this->promoCode->type == PricePlanPromoCode::TYPE_FIXED_AMOUNT) {
                $this->discount += (float)$this->promoCode->discount;
            } else {
                $this->discount += (float)(($this->promoCode->discount / 100) * $this->total);
            }
            
            $this->total -= $this->discount;
            if ($this->total < 0) {
                $this->total = 0;
            }
        }
        
        $this->applyTaxes();
        
        // since 1.7.6
        Yii::app()->hooks->applyFilters('price_plan_order_after_calculate_totals', $this);
 
        return $this;
    }
    
    public function getFormattedSubtotal()
    {
        return Yii::app()->numberFormatter->formatCurrency($this->subtotal, $this->currency->code);
    }
    
    public function getFormattedTaxPercent()
    {
        return Yii::app()->format->formatNumber($this->tax_percent) . '%';
    }
    
    public function getFormattedTaxValue()
    {
        return Yii::app()->numberFormatter->formatCurrency($this->tax_value, $this->currency->code);
    }
    
    public function getFormattedDiscount()
    {
        return Yii::app()->numberFormatter->formatCurrency($this->discount, $this->currency->code);
    }
    
    public function getFormattedTotal()
    {
        return Yii::app()->numberFormatter->formatCurrency($this->total, $this->currency->code);
    }
    
    public function findByUid($order_uid)
    {
        return self::model()->findByAttributes(array(
            'order_uid' => $order_uid,
        ));    
    }
    
    public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);
        
        if (!empty($exists)) {
            return $this->generateUid();
        }
        
        return $unique;
    }

    public function getUid()
    {
        return $this->order_uid;
    }
    
    public function applyTaxes()
    {
        if (empty($this->customer_id)) {
            return $this;
        }
        
        if ($this->tax_id !== null && $this->tax_percent > 0 && $this->tax_value > 0) {
            return $this;
        }
        
        if (empty($this->tax_id) || empty($this->tax)) {
            $tax = $zoneTax = $countryTax = null;
            $globalTax = Tax::model()->findByAttributes(array('is_global' => Tax::TEXT_YES));
            if (!empty($this->customer) && !empty($this->customer->company)) {
                $company  = $this->customer->company;
                $zoneTax  = Tax::model()->findByAttributes(array('zone_id' => (int)$company->zone_id));
                if (empty($zoneTax)) {
                    $countryTax = Tax::model()->findByAttributes(array('country_id' => (int)$company->country_id));
                }
            }
            
            if (!empty($zoneTax)) {
                $tax = $zoneTax;
            } elseif (!empty($countryTax)) {
                $tax = $countryTax;
            } elseif (!empty($globalTax)) {
                $tax = $globalTax;
            } else {
                return $this;
            }
            
            if ($tax->percent < 0.1) {
                return $this;
            }
            
            $this->tax_id = $tax->tax_id;
            $this->addRelatedRecord('tax', $tax, false);    
        }
        
        
        $this->tax_percent = $this->tax->percent;
        $this->tax_value   = ($this->tax->percent / 100) * $this->total;
        $this->total += $this->tax_value;
        
        return $this;
    }

    /**
     * @param string $headingTag
     * @param string $separator
     * @return string
     */
    public function getHtmlPaymentFrom($headingTag = 'strong', $separator = '<br />')
    {
        if (empty($this->customer_id)) {
            return '';
        }
        
        $customer    = $this->customer;
        $paymentFrom = array();
        
        if ($headingTag !== null && $headingTag != "\n") {
            $paymentFrom[] = CHtml::tag($headingTag, array(), $customer->getFullName());
        } else {
            $paymentFrom[] = $customer->getFullName();
        }
        
        if (!empty($customer->company)) {
            $paymentFrom[] = $customer->company->name;
            $paymentFrom[] = $customer->company->address_1;
            $paymentFrom[] = $customer->company->address_2;
            
            $location = array();
            $location[] = !empty($customer->company->country_id) ? $customer->company->country->name : '';
            $location[] = !empty($customer->company->zone_id) ? $customer->company->zone_name : '';
            $location[] = $customer->company->city;
            $location[] = $customer->company->zip_code;
            
            foreach ($location as $index => $info) {
                if (empty($info)) {
                    unset($location[$index]);
                }
            }
            
            $paymentFrom[] = implode(', ', $location);
            $paymentFrom[] = $customer->company->phone;
            
            if (!empty($customer->company->vat_number)) {
                $paymentFrom[] = Yii::t('orders', 'VAT Number: {vat_number}', array('{vat_number}' => $customer->company->vat_number));
            }
            
            foreach ($paymentFrom as $index => $info) {
                if (empty($info)) {
                    unset($paymentFrom[$index]);
                }
            }
        }
        
        $paymentFrom[] = $customer->email;
        
        $html = implode($separator, $paymentFrom);
        
        // 1.5.0
        $html = Yii::app()->hooks->applyFilters('price_plan_order_get_html_payment_from', $html, $customer);
        
        return $html;
    }

    /**
     * @param string $headingTag
     * @param string $separator
     * @return string
     */
    public function getHtmlPaymentTo($headingTag = 'strong', $separator = '<br />')
    {
        if (empty($this->customer_id)) {
            return '';
        }
        
        $customer  = $this->customer;
        $paymentTo = array();
        
        if ($headingTag !== null && $headingTag != "\n") {
            $paymentTo[] = CHtml::tag($headingTag, array(), Yii::app()->options->get('system.common.site_name'));
        } else {
            $paymentTo[] = Yii::app()->options->get('system.common.site_name');
        }
        
        if ($separator !== null && $separator != "\n") {
            $paymentTo[] = nl2br(Yii::app()->options->get('system.common.company_info'));
        } else {
            $paymentTo[] = Yii::app()->options->get('system.common.company_info');
        }
        
        $html = implode($separator, $paymentTo);

        // 1.5.0
        $html = Yii::app()->hooks->applyFilters('price_plan_order_get_html_payment_to', $html, $customer);
        
        return $html;
    }
    
    public function getIsComplete()
    {
        return $this->status == self::STATUS_COMPLETE;
    }
}
