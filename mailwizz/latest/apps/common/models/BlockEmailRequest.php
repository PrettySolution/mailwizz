<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BlockEmailRequest
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

/**
 * This is the model class for table "block_email_request".
 *
 * The followings are the available columns in table 'block_email_request':
 * @property integer $email_id
 * @property string $email
 * @property string $ip_address
 * @property string $user_agent
 * @property string $confirmation_key
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 */
class BlockEmailRequest extends ActiveRecord
{
    /**
     * Flag
     */
    const STATUS_CONFIRMED = 'confirmed';

    /**
     * Flag
     */
    const STATUS_UNCONFIRMED = 'unconfirmed';

	/**
	 * Flag
	 */
    const BULK_ACTION_CONFIRM = 'confirm-block';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{block_email_request}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('email', 'required'),
            array('email', 'length', 'max' => 150),
            array('email', 'email', 'validateIDN' => true),
            
            array('email, ip_address, user_agent', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'email_id'      => Yii::t('email_blacklist', 'Email'),
            'email'         => Yii::t('email_blacklist', 'Email'),
            'ip_address'    => Yii::t('email_blacklist', 'Ip address'),
            'user_agent'    => Yii::t('email_blacklist', 'User agent'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $criteria->compare('email', $this->email, true);
        $criteria->compare('ip_address', $this->ip_address, true);
        $criteria->compare('user_agent', $this->user_agent, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'email_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EmailBlacklist the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (empty($this->confirmation_key)) {
            $this->confirmation_key = sha1(StringHelper::random(40));
        }
        return parent::beforeSave();
    }

    /**
     * @param null $status
     * @return bool
     */
    public function saveStatus($status = null)
    {
        if (empty($this->email_id)) {
            return false;
        }

        if ($status && $status == $this->status) {
            return true;
        }

        if ($status) {
            $this->status = $status;
        }

        $attributes = array('status' => $this->status);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'before_savestatus')), $this);
	    //
	    
	    $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'email_id = :id', array(':id' => (int)$this->email_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
	    
	    return $result;
    }

    /**
     * @return $this
     * @throws CDbException
     */
    public function block()
    {
        if (empty($this->email)) {
            return $this;
        }
        
        $blacklist = EmailBlacklist::model()->findByAttributes(array('email' => $this->email));
        if (empty($blacklist)) {
            EmailBlacklist::addToBlacklist($this->email, 'Block email request!');
        }
        
        $this->saveStatus(self::STATUS_CONFIRMED);
        
        Yii::app()->getDb()
            ->createCommand('UPDATE {{list_subscriber}} SET `status` = :st1 WHERE email = :em AND `status` = :st2')
            ->execute(array(
                ':st1' => ListSubscriber::STATUS_BLACKLISTED,
                ':st2' => ListSubscriber::STATUS_CONFIRMED,
                ':em'  => $this->email,
            ));
        
        return $this;
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_UNCONFIRMED => Yii::t('email_blacklist', 'Unconfirmed'),
            self::STATUS_CONFIRMED   => Yii::t('email_blacklist', 'Confirmed'),
        );
    }

    /**
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

	/**
	 * @return array
	 */
    public function getBulkActionsList()
    {
    	return array(
    		self::BULK_ACTION_CONFIRM => Yii::t('email_blacklist', 'Confirm'),
	    );
    }
}
