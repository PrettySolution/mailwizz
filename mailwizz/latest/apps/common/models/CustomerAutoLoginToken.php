<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerAutoLoginToken
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "customer_auto_login_token".
 *
 * The followings are the available columns in table 'customer_auto_login_token':
 * @property integer $token_id
 * @property integer $customer_id
 * @property string $token
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerAutoLoginToken extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_auto_login_token}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
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
            'token_id'      => Yii::t('customers', 'Token'),
            'customer_id'   => Yii::t('customers', 'Customer'),
            'token'         => Yii::t('customers', 'Token'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerAutoLoginToken the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
