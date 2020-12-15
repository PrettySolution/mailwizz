<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CompanyType
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
/**
 * This is the model class for table "{{company_type}}".
 *
 * The followings are the available columns in table '{{company_type}}':
 * @property integer $type_id
 * @property string $name
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CustomerCompany[] $customerCompanies
 * @property ListCompany[] $listCompanies
 */
class CompanyType extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{company_type}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name', 'required'),
			array('name', 'length', 'max' => 255),
            array('name', 'unique'),
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
			'customerCompanies'  => array(self::HAS_MANY, 'CustomerCompany', 'type_id'),
			'listCompanies'      => array(self::HAS_MANY, 'ListCompany', 'type_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'type_id' => Yii::t('company_types', 'Type'),
			'name'    => Yii::t('company_types', 'Name'),
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
		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'name'     => CSort::SORT_ASC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CompanyType the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public static function getListForDropDown()
    {
        $types = array();
        
        $criteria = new CDbCriteria();
        $criteria->select = 'type_id, name';
        $criteria->order  = 'name ASC';
        $_types = self::model()->findAll($criteria);
        
        foreach ($_types as $type) {
            $types[$type->type_id] = $type->name;
        }
        
        return $types;
    }
}
