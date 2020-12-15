<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Class CustomerForTwoFactorAuth
 */
class CustomerForTwoFactorAuth extends Customer
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('twofa_enabled', 'required'),
			array('twofa_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
		);

		return $rules;
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Article the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
}
