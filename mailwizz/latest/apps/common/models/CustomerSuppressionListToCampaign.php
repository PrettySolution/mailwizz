<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerSuppressionListToCampaign
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

/**
 * This is the model class for table "{{customer_suppression_list_to_campaign}}".
 *
 * The followings are the available columns in table '{{customer_suppression_list_to_campaign}}':
 * @property integer $list_id
 * @property integer $campaign_id
 */
class CustomerSuppressionListToCampaign extends ActiveRecord 
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_suppression_list_to_campaign}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array();
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
        $relations = array(
            'campaign'        => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
            'suppressionList' => array(self::BELONGS_TO, 'CustomerSuppressionList', 'list_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerSuppressionListToCampaign the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
