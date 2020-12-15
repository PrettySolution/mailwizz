<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerGroupOption
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

/**
 * This is the model class for table "customer_group_option".
 *
 * The followings are the available columns in table 'customer_group_option':
 * @property integer $option_id
 * @property integer $group_id
 * @property string $code
 * @property string $is_serialized
 * @property string $value
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CustomerGroup $group
 */
class CustomerGroupOption extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_group_option}}';
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
			'group' => array(self::BELONGS_TO, 'CustomerGroup', 'group_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'option_id'      => Yii::t('customers', 'Option'),
			'code'           => Yii::t('customers', 'Code'),
            'is_serialized'  => Yii::t('customers', 'Is serialized'),
            'value'          => Yii::t('customers', 'Value'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    protected function beforeSave()
    {
        $this->is_serialized = 0;
        if ($this->value !== null && !is_string($this->value)) {
            $this->value = @serialize($this->value);
            $this->is_serialized = 1;
        }
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if ($this->is_serialized) {
            $this->value = @unserialize($this->value);
        }
        return parent::afterSave();
    }
    
    protected function afterFind()
    {
        if ($this->is_serialized) {
            $this->value = @unserialize($this->value);
        }
        return parent::afterFind();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerGroupOption the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
