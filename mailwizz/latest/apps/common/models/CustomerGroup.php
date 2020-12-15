<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerGroup
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

/**
 * This is the model class for table "customer_group".
 *
 * The followings are the available columns in table 'customer_group':
 * @property integer $group_id
 * @property string $name
 * @property string $is_default
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer[] $customers
 * @property CustomerGroupOption[] $options
 * @property Customer[] $customersCount
 * @property DeliveryServer[] $deliveryServers
 * @property PricePlan[] $pricePlans
 */
class CustomerGroup extends ActiveRecord
{
    public $preDeleteCheckDone = false;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_group}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name', 'required'),
			array('name', 'length', 'max' => 255),
            // The following rule is used by search().
			array('name', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customers'       => array(self::HAS_MANY, 'Customer', 'group_id'),
			'options'         => array(self::HAS_MANY, 'CustomerGroupOption', 'group_id'),
            'customersCount'  => array(self::STAT, 'Customer', 'group_id'),
            'deliveryServers' => array(self::MANY_MANY, 'DeliveryServer', 'delivery_server_to_customer_group(group_id, server_id)'),
            'pricePlans'      => array(self::HAS_MANY, 'PricePlan', 'group_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'group_id'   => Yii::t('customers', 'Group'),
			'name'       => Yii::t('customers', 'Name'),
			'is_default' => Yii::t('customers', 'Is default'),
            
            'customersCount' => Yii::t('customers', 'Customers count'),
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
		$criteria->compare('name', $this->name, true);
		$criteria->order = 'name ASC';
		
		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'group_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

    protected function beforeDelete()
    {
        if (!$this->preDeleteCheckDone) {
            $this->preDeleteCheckDone = true;
            $denyOptions  = array('system.customer_registration.default_group', 'system.customer_sending.move_to_group_id');
            foreach ($denyOptions as $option) {
                if ((int)$this->group_id == (int)Yii::app()->options->get($option)) {
                    return $this->preDeleteCheckDone = false;
                }
            }
            
            $criteria = new CDbCriteria();
            $criteria->compare('t.code', 'system.customer_sending.move_to_group_id');
            $criteria->compare('t.value', $this->group_id);
            $criteria->addCondition('t.group_id != :gid');
            $criteria->params[':gid'] = $this->group_id;
            $model = CustomerGroupOption::model()->find($criteria);
            if (!empty($model)) {
                return $this->preDeleteCheckDone = false;
            }
        }

        return parent::beforeDelete();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerGroup the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function copy()
    {
        $copied = false;
        
        if ($this->isNewRecord) {
            return $copied;
        }

        $transaction = Yii::app()->db->beginTransaction();
        
        try {
            
            $group = clone $this;
            $group->isNewRecord  = true;
            $group->group_id     = null;
            $group->date_added   = new CDbExpression('NOW()');
            $group->last_updated = new CDbExpression('NOW()');

            if (preg_match('/\#(\d+)$/', $group->name, $matches)) {
                $counter = (int)$matches[1];
                $counter++;
                $group->name = preg_replace('/\#(\d+)$/', '#' . $counter, $group->name);
            } else {
                $group->name .= ' #1';
            }

            if (!$group->save(false)) {
                throw new CException($group->shortErrors->getAllAsString());
            }
            
            $options = CustomerGroupOption::model()->findAllByAttributes(array(
                'group_id' => $this->group_id,
            ));
            
            foreach ($options as $option) {
                $option = clone $option;
                $option->isNewRecord  = true;
                $option->option_id    = null;
                $option->group_id     = $group->group_id;
                $option->date_added   = new CDbExpression('NOW()');
                $option->last_updated = new CDbExpression('NOW()');
                if (!$option->save()) {
                    throw new Exception($option->shortErrors->getAllAsString());
                }
            }
            
            $deliveryServers = DeliveryServerToCustomerGroup::model()->findAllByAttributes(array(
                'group_id' => $this->group_id,
            ));
            
            foreach ($deliveryServers as $server) {
                $_server = new DeliveryServerToCustomerGroup();
                $_server->group_id  = $group->group_id;
                $_server->server_id = $server->server_id;
                if (!$_server->save()) {
                    throw new Exception($_server->shortErrors->getAllAsString());
                }
            }
            
            $transaction->commit();
            $copied = $group;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $copied;
    }
    
    public function getOptionValue($optionCode, $defaultValue = null)
    {
        static $loaded = array();
        if (!isset($loaded[$this->group_id])) {
            $loaded[$this->group_id] = array();
        }
        
        if (array_key_exists($optionCode, $loaded[$this->group_id])) {
            return $loaded[$this->group_id][$optionCode];
        }
        $criteria = new CDbCriteria();
        $criteria->select = 't.value, t.is_serialized';
        $criteria->compare('t.group_id', (int)$this->group_id);
        $criteria->compare('t.code', $optionCode);
        $model = CustomerGroupOption::model()->find($criteria);
        return $loaded[$this->group_id][$optionCode] = !empty($model) ? $model->value : $defaultValue;
    }
    
    public static function getGroupsList()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.group_id, t.name';
        $criteria->order = 't.name ASC';
        return self::model()->findAll($criteria);
    }
    
    public static function getGroupsArray()
    {
        static $_options;
        if ($_options !== null) {
            return $_options;
        }
        $_options = array();
        
        $groups = self::getGroupsList();
        if (empty($groups)) {
            return $_options;
        }
        
        foreach ($groups as $group) {
            $_options[$group->group_id] = $group->name;
        }
        
        return $_options;
    }
    
    public function resetSendingQuota()
    {
        Yii::app()->getDb()->createCommand('
            DELETE qm FROM {{customer_quota_mark}} qm 
                INNER JOIN {{customer}} c ON c.customer_id = qm.customer_id 
                INNER JOIN {{customer_group}} g ON g.group_id = c.group_id
            WHERE g.group_id = :gid
        ')->execute(array(':gid' => (int)$this->group_id));
        return $this;
    }
}
