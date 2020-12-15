<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerSuppressionList
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

/**
 * This is the model class for table "{{customer_suppression_list}}".
 *
 * The followings are the available columns in table '{{customer_suppression_list}}':
 * @property integer $list_id
 * @property string $list_uid
 * @property integer $customer_id
 * @property string $name
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property CustomerSuppressionListEmail[] $emails
 */
class CustomerSuppressionList extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{customer_suppression_list}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $rules = array(
			array('name', 'required'),
			array('name', 'length', 'max' => 255),
			
			// The following rule is used by search().
			array('name', 'safe', 'on' => 'search'),
		);

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer'    => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'emails'      => array(self::HAS_MANY, 'CustomerSuppressionListEmail', 'list_id'),
            'emailsCount' => array(self::STAT, 'CustomerSuppressionListEmail', 'list_id'),
		);

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
        $labels = array(
			'list_id'       => Yii::t('suppression_lists', 'List'),
			'list_uid'      => Yii::t('suppression_lists', 'List'),
			'customer_id'   => Yii::t('suppression_lists', 'Customer'),
			'name'          => Yii::t('suppression_lists', 'Name'),
            
            'emailsCount'   => Yii::t('suppression_lists', 'Emails count'),
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
        
		$criteria->compare('customer_id', (int)$this->customer_id);
		$criteria->compare('name', $this->name, true);
		$criteria->order = 't.list_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    't.list_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerSuppressionList the static model class
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
        if (!parent::beforeSave()) {
            return false;
        }

        if ($this->isNewRecord) {
            $this->list_uid = $this->generateUid();
        }

        return true;
    }

    /**
     * @param $list_uid
     * @return static
     */
    public function findByUid($list_uid)
    {
        return self::model()->findByAttributes(array(
            'list_uid' => $list_uid,
        ));
    }

    /**
     * @return string
     */
    public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);

        if (!empty($exists)) {
            return $this->generateUid();
        }

        return $unique;
    }

	/**
	 * @since 1.7.9
	 * @return bool
	 */
    public function touchLastUpdated()
    {
	    $attributes = array();
	    $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');

	    return (bool)Yii::app()
           ->getDb()
           ->createCommand()
           ->update($this->tableName(), $attributes, 'list_id = :id', array(':id' => (int)$this->list_id));
    }
}
