<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerGroup
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 */

/**
 * This is the model class for table "delivery_server_to_customer_group".
 *
 * The followings are the available columns in table 'delivery_server_to_customer_group':
 * @property integer $server_id
 * @property integer $group_id
 */
class DeliveryServerToCustomerGroup extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{delivery_server_to_customer_group}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('server_id, group_id', 'required'),
			array('server_id, group_id', 'numerical', 'integerOnly'=>true),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array();
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'server_id' => Yii::t('servers', 'Server'),
			'group_id'  => Yii::t('servers', 'Customer group'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DeliveryServerToCustomerGroup the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
