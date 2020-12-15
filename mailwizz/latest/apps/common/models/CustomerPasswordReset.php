<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerPasswordReset
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "customer_password_reset".
 *
 * The followings are the available columns in table 'customer_password_reset':
 * @property integer $request_id
 * @property integer $customer_id
 * @property string $reset_key
 * @property string $ip_address
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerPasswordReset extends ActiveRecord
{
    const STATUS_USED = 'used';

    public $email;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_password_reset}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive customer inputs.
        $rules = array(
            array('email', 'required'),
            array('email', 'email', 'validateIDN' => true),
            array('email', 'exist', 'className' => 'Customer', 'criteria' => array('condition' => 'status = :st', 'params' => array(':st' => Customer::STATUS_ACTIVE))),
        );

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
            'request_id'    => Yii::t('customers', 'Request'),
            'customer_id'   => Yii::t('customers', 'Customer'),
            'reset_key'     => Yii::t('customers', 'Reset key'),
            'ip_address'    => Yii::t('customers', 'Ip address'),
            'email'         => Yii::t('customers', 'Email'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerPasswordReset the static model class
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
            self::model()->updateAll(array('status' => self::STATUS_USED), 'customer_id = :uid', array(':uid' => (int)$this->customer_id));
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
