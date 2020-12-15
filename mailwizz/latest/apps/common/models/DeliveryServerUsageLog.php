<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerUsageLog
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
/**
 * This is the model class for table "{{delivery_server_usage_log}}".
 *
 * The followings are the available columns in table '{{delivery_server_usage_log}}':
 * @property string $log_id
 * @property integer $server_id
 * @property integer $customer_id
 * @property string $delivery_for
 * @property string $customer_countable
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property DeliveryServer $server
 * @property Customer $customer
 */
class DeliveryServerUsageLog extends ActiveRecord
{
    const TEXT_NO = 'no';
    
    const TEXT_YES = 'yes';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{delivery_server_usage_log}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('customer_id, server_id', 'safe', 'on' => 'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'server'     => array(self::BELONGS_TO, 'DeliveryServer', 'server_id'),
			'customer'   => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'log_id'                => Yii::t('servers', 'Log'),
			'server_id'             => Yii::t('servers', 'Server'),
			'customer_id'           => Yii::t('servers', 'Customer'),
            'delivery_for'          => Yii::t('servers', 'Delivery for'),
            'customer_countable'    => Yii::t('servers', 'Countable for customer')
		);
        
        return CMap::mergeArray(parent::attributeLabels(), $labels);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DeliveryServerUsageLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;
        
        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with = array(
                    'customer' => array(
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => array(
                            ':name'    => '%' . $this->customer_id . '%',
                        ),
                    )
                );
            }
        }
        $criteria->compare('t.server_id', $this->server_id);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => (int)$this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder'  => array(
                    'log_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }
    
    public static function getDeliveryServersAsOptions()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'server_id, name, hostname';
        $servers = DeliveryServer::model()->findAll($criteria);
        $options = array();
        foreach ($servers as $server) {
            $options[$server->server_id] = $server->name . '('. $server->hostname .')';
        }
        return $options;
    }
}
