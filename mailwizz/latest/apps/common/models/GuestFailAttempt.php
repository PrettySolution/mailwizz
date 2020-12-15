<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * GuestFailAttempt
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
/**
 * This is the model class for table "{{guest_fail_attempt}}".
 *
 * The followings are the available columns in table '{{guest_fail_attempt}}':
 * @property string $attempt_id
 * @property string $ip_address
 * @property string $ip_address_hash
 * @property string $place
 * @property string $date_added
 */
class GuestFailAttempt extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{guest_fail_attempt}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('ip_address, place', 'safe', 'on' => 'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array();
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'attempt_id'      => Yii::t('guest_fail_attempt', 'Attempt'),
			'ip_address'      => Yii::t('guest_fail_attempt', 'Ip address'),
			'ip_address_hash' => Yii::t('guest_fail_attempt', 'Ip address hash'),
            'user_agent'      => Yii::t('guest_fail_attempt', 'User agent'),
			'place'           => Yii::t('guest_fail_attempt', 'Place'),
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
		$criteria=new CDbCriteria;

		$criteria->compare('ip_address', $this->ip_address, true);
		$criteria->compare('place', $this->place, true);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder'     => array(
                    'attempt_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GuestFailAttempt the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->setBaseInfo();
    }
    
    protected function beforeSave()
    {
        $this->setBaseInfo();
        return parent::beforeSave();
    }
    
    public function setBaseInfo()
    {
        if (empty($this->ip_address) && !MW_IS_CLI) {
            $this->ip_address = Yii::app()->request->userHostAddress;
        }
        if (empty($this->ip_address_hash)) {
            $this->ip_address_hash = md5($this->ip_address);
        }
        if (empty($this->user_agent) && !MW_IS_CLI) {
            $this->user_agent = Yii::app()->request->userAgent;
        }
        return $this;
    }
    
    public function setPlace($place)
    {
        $this->place = $place;
        return $this;    
    }
    
    public function getHasTooManyFailures()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.ip_address_hash', $this->ip_address_hash);
        $criteria->addCondition('t.date_added >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)');
        $count = self::model()->count($criteria);

        if ($count >= 10) {
            return true;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('ip_address_hash', $this->ip_address_hash);
        $criteria->addCondition('date_added <= DATE_SUB(NOW(), INTERVAL 20 MINUTE)');
        $this->deleteAll($criteria);

        return false;
    }
    
    public static function registerByPlace($place)
    {
        $model = new self();
        $model->setBaseInfo();
        $model->setPlace($place);
        return $model->save();
    }
}
