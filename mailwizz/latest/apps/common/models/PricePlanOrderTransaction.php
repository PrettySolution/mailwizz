<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PricePlanOrderTransaction
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */

/**
 * This is the model class for table "{{price_plan_order_transaction}}".
 *
 * The followings are the available columns in table '{{price_plan_order_transaction}}':
 * @property integer $transaction_id
 * @property string $transaction_uid
 * @property integer $order_id
 * @property string $payment_gateway_name
 * @property string $payment_gateway_transaction_id
 * @property string $payment_gateway_response
 * @property string $status
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property PricePlanOrder $order
 */
class PricePlanOrderTransaction extends ActiveRecord
{
    const STATUS_FAILED = 'failed';
    
    const STATUS_SUCCESS = 'success';
    
    const STATUS_PENDING_RETRY = 'pending-retry';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{price_plan_order_transaction}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
        
			// The following rule is used by search().
			array('payment_gateway_name, payment_gateway_transaction_id, status', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'order' => array(self::BELONGS_TO, 'PricePlanOrder', 'order_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'transaction_id'                 => Yii::t('orders', 'Transaction'),
			'transaction_uid'                => Yii::t('orders', 'Transaction uid'),
			'order_id'                       => Yii::t('orders', 'Order'),
			'payment_gateway_name'           => Yii::t('orders', 'Payment gateway name'),
			'payment_gateway_transaction_id' => Yii::t('orders', 'Payment gateway transaction'),
			'payment_gateway_response'       => Yii::t('orders', 'Payment gateway response'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
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

		$criteria->compare('order_id', $this->order_id);
		$criteria->compare('payment_gateway_name', $this->payment_gateway_name, true);
		$criteria->compare('payment_gateway_transaction_id', $this->payment_gateway_transaction_id, true);
		$criteria->compare('status', $this->status);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'transaction_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanOrderTransaction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }
        
        if (empty($this->transaction_uid)) {
            $this->transaction_uid = $this->generateUid();
        }

        return true;
    }
    
    public function findByUid($transaction_uid)
    {
        return self::model()->findByAttributes(array(
            'transaction_uid' => $transaction_uid,
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
        return $this->transaction_uid;
    }
}
