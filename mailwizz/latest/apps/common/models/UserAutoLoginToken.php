<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * 
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "user_auto_login_token".
 *
 * The followings are the available columns in table 'user_auto_login_token':
 * @property integer $token_id
 * @property integer $user_id
 * @property string $token
 *
 * The followings are the available model relations:
 * @property User $user
 */
class UserAutoLoginToken extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{user_auto_login_token}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'token_id'  => Yii::t('users', 'Token'),
            'user_id'   => Yii::t('users', 'User'),
            'token'     => Yii::t('users', 'Token'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return UserAutoLoginToken the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
