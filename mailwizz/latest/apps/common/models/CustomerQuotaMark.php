<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerQuotaMark
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
/**
 * This is the model class for table "{{customer_quota_mark}}".
 *
 * The followings are the available columns in table '{{customer_quota_mark}}':
 * @property string $mark_id
 * @property integer $customer_id
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerQuotaMark extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_quota_mark}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
	   $rules = array();
	   return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'mark_id'     => Yii::t('customers', 'Mark'),
			'customer_id' => Yii::t('customers', 'Customer'),
		);
        
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerQuotaMark the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
