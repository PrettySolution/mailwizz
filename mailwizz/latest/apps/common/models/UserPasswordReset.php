<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserPasswordReset
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "user_password_reset".
 *
 * The followings are the available columns in table 'user_password_reset':
 * @property integer $request_id
 * @property integer $user_id
 * @property string $reset_key
 * @property string $ip_address
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property User $user
 */
class UserPasswordReset extends ActiveRecord
{
    const STATUS_USED = 'used';

    public $email;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{user_password_reset}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('email', 'required'),
            array('email', 'email', 'validateIDN' => true),
            array('email', 'exist', 'className' => 'User', 'criteria' => array('condition' => 'status = :st', 'params' => array(':st' => User::STATUS_ACTIVE))),
        );

        return CMap::mergeArray($rules, parent::rules());
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
            'request_id'    => Yii::t('users', 'Request'),
            'user_id'       => Yii::t('users', 'User'),
            'reset_key'     => Yii::t('users', 'Reset key'),
            'ip_address'    => Yii::t('users', 'Ip address'),
            'email'         => Yii::t('users', 'Email'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return UserPasswordReset the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeSave()
    {
        if ($this->isNewRecord) {
            $this->reset_key = sha1(uniqid(rand(0, time()), true));
            $this->ip_address = Yii::app()->request->userHostAddress;
            self::model()->updateAll(array('status' => self::STATUS_USED), 'user_id = :uid', array(':uid' => (int)$this->user_id));
        }

        return parent::beforeSave();
    }

    public function sendEmail(array $params = array())
    {
        if (!($server = DeliveryServer::pickServer())) {
            return $this->sendEmailFallback($params);
        }

        $params['from'] = array($server->getFromEmail() => Yii::app()->options->get('system.common.site_name'));

        $sent = false;
        for ($i = 0; $i < 3; ++$i) {
            if ($server->sendEmail($params)) {
                $sent = true;
                break;
            }
            $server = DeliveryServer::pickServer($server->server_id);
        }

        if (!$sent) {
            $sent = $this->sendEmailFallback($params);
        }

        return (bool)$sent;
    }

    public function sendEmailFallback(array $params = array())
    {
        $request             = Yii::app()->request;
        $options             = Yii::app()->options;
        $email               = 'noreply@' . $request->getServer('HTTP_HOST', $request->getServer('SERVER_NAME', 'domain.com'));
        $params['from']      = array($email => $options->get('system.common.site_name'));
        $params['transport'] = 'php-mail';

        return Yii::app()->mailer->send($params);
    }
}
