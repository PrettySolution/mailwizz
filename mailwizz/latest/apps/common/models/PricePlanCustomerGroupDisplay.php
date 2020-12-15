<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PricePlanCustomerGroupDisplay
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.2
 */

/**
 * This is the model class for table "{{price_plan_customer_group_display}}".
 *
 * The followings are the available columns in table '{{price_plan_customer_group_display}}':
 * @property integer $plan_id
 * @property integer $group_id
 */
class PricePlanCustomerGroupDisplay extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{price_plan_customer_group_display}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('plan_id', 'exist', 'className' => 'PricePlan'),
			array('group_id', 'exist', 'className' => 'Customergroup'),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function relations()
	{
		$relations = array(
			'plan'  => array(self::BELONGS_TO, 'PricePlan', 'plan_id'),
			'group' => array(self::BELONGS_TO, 'CustomerGroup', 'group_id'),
		);

		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'group_id' => Yii::t('customers', 'Customer group(s) visibility'),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHelpTexts()
	{
		$texts = array(
			'group_id'    => Yii::t('customers', 'If no group is selected, all customers will see this plan. If one or more groups are selected, then just customers within these groups will see the plan.'),
		);

		return CMap::mergeArray($texts, parent::attributeHelpTexts());
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanCustomerGroupDisplay the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return array
	 */
	public function getCustomerGroupsList()
	{
		$list   = array();
		$groups = CustomerGroup::model()->findAll();
		foreach ($groups as $group) {
			$list[$group->group_id] = $group->name;
		}
		return $list;
	}
}
