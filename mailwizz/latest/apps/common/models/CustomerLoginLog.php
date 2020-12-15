<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerLoginLog
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

/**
 * This is the model class for table "{{customer_login_log}}".
 *
 * The followings are the available columns in table '{{customer_login_log}}':
 * @property string $log_id
 * @property integer $customer_id
 * @property string $location_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property IpLocation $location
 */
class CustomerLoginLog extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_login_log}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $rules = array(
			array('customer_id, ip_address', 'safe', 'on'=>'search'),
		);
        
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'location' => array(self::BELONGS_TO, 'IpLocation', 'location_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
        $labels = array(
			'log_id'      => Yii::t('customers', 'Log'),
			'customer_id' => Yii::t('customers', 'Customer'),
			'location_id' => Yii::t('customers', 'Location'),
			'ip_address'  => Yii::t('customers', 'Ip address'),
			'user_agent'  => Yii::t('customers', 'User agent'),
            
            'countryName' => Yii::t('customers', 'Country'),
            'zoneName'    => Yii::t('customers', 'Zone'),
            'cityName'    => Yii::t('customers', 'City'),
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

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        }
        
		$criteria->compare('t.ip_address', $this->ip_address, true);
        $criteria->order = 't.log_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.log_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerLoginLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param Customer $customer
     * @return bool
     */
    public static function addNew(Customer $customer)
    {
        if (MW_IS_CLI) {
            return false;
        }
        
        $model = new self();
        $model->customer_id = $customer->customer_id;
        $model->ip_address  = Yii::app()->request->getUserHostAddress();
        $model->user_agent  = substr(Yii::app()->request->getUserAgent(), 0, 255);
        
        $model->addRelatedRecord('customer', $customer, false);
        
        Yii::app()->hooks->doAction('customer_login_log_add_new_before_save', new CAttributeCollection(array(
            'model' => $model,
        )));

        $saved = $model->save();

        Yii::app()->hooks->doAction('customer_login_log_add_after_after_save', new CAttributeCollection(array(
            'model' => $model,
            'saved' => $saved,
        )));
        
        return $saved;
    }

    /**
     * @return null|string
     */
    public function getCountryName()
    {
        if (empty($this->location_id) || empty($this->location) || empty($this->location->country_name)) {
            return null;
        }
        return $this->location->country_name;
    }

    /**
     * @return null|string
     */
    public function getZoneName()
    {
        if (empty($this->location_id) || empty($this->location) || empty($this->location->zone_name)) {
            return null;
        }
        return $this->location->zone_name;
    }

    /**
     * @return null|string
     */
    public function getCityName()
    {
        if (empty($this->location_id) || empty($this->location) || empty($this->location->city_name)) {
            return null;
        }
        return $this->location->city_name;
    }
}
