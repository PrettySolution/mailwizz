<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignGroup
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
/**
 * This is the model class for table "{{campaign_group}}".
 *
 * The followings are the available columns in table '{{campaign_group}}':
 * @property integer $group_id
 * @property string $group_uid
 * @property integer $customer_id
 * @property string $name
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign[] $campaigns
 * @property integer Campaign $campaignsCount
 * @property Customer $customer
 */
class CampaignGroup extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_group}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name', 'required'),
			array('name', 'length', 'max'=>255),
            
			// The following rule is used by search().
			array('name', 'safe', 'on'=>'search'),
		);
        
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaigns'       => array(self::HAS_MANY, 'Campaign', 'group_id'),
            'campaignsCount'  => array(self::STAT, 'Campaign', 'group_id'),
			'customer'        => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'group_id'       => Yii::t('campaigns', 'Group'),
			'group_uid'      => Yii::t('campaigns', 'Group uid'),
			'customer_id'    => Yii::t('campaigns', 'Customer'),
			'name'           => Yii::t('campaigns', 'Name'),
            
            'campaignsCount' => Yii::t('campaigns', 'Campaigns count'),
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
		$criteria->compare('customer_id', (int)$this->customer_id);
		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'group_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignGroup the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    protected function beforeSave()
    {
        if ($this->isNewRecord && empty($this->group_uid)) {
            $this->group_uid = $this->generateUid();
        }

        return parent::beforeSave();
    }
    
    public function getUid()
    {
        return $this->group_uid;
    }
    
    public function findByUid($group_uid)
    {
        return self::model()->findByAttributes(array(
            'group_uid' => $group_uid,
        ));    
    }
    
    public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);
        
        if (!empty($exists)) {
            return $this->generateUid();
        }
        
        return $unique;
    }
    
    public static function getForDropDown()
    {
        static $list;
        if ($list !== null) {
            return $list;
        }
        $list = array();
        $models = self::model()->findAll(array('select' => 'group_id, name', 'limit' => 100));
        foreach ($models as $model) {
            $list[$model->group_id] = $model->name;
        }
        return $list;
    }
}
