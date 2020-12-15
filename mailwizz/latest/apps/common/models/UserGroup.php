<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserGroup
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
/**
 * This is the model class for table "user_group".
 *
 * The followings are the available columns in table 'user_group':
 * @property integer $group_id
 * @property string $name
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property User[] $users
 * @property User[] $usersCount
 * @property UserGroupRouteAccess[] $routeAccess
 */
class UserGroup extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{user_group}}';
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
			'users'       => array(self::HAS_MANY, 'User', 'group_id'),
            'usersCount'  => array(self::STAT, 'User', 'group_id'),
			'routeAccess' => array(self::HAS_MANY, 'UserGroupRouteAccess', 'group_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'group_id'   => Yii::t('user_groups', 'Group'),
			'name'       => Yii::t('user_groups', 'Name'),
            'usersCount' => Yii::t('user_groups', 'Users count'),
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
		$criteria->compare('name',$this->name,true);

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
	 * @return UserGroup the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getAllRoutesAccess()
    {
        return UserGroupRouteAccess::findAllByGroupId((int)$this->group_id);
    }
    
    public function hasRouteAccess($route)
    {
        return UserGroupRouteAccess::groupHasRouteAccess($this->group_id, $route);
    }
    
    public static function getAllAsOptions()
    {
        static $options;
        if ($options !== null) {
            return $options;
        }
        $options = array();
        $models  = self::model()->findAll();
        foreach ($models as $model) {
            $options[$model->group_id] = $model->name;
        }
        return $options;
    }
}
