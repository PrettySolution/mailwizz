<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Language
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */

/**
 * This is the model class for table "language".
 *
 * The followings are the available columns in table 'language':
 * @property integer $language_id
 * @property string $name
 * @property string $language_code
 * @property string $region_code
 * @property string $is_default
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer[] $customers
 * @property User[] $users
 */
class Language extends ActiveRecord
{
    // reference constant, never change this!
    const DEFAULT_LANGUAGE_CODE = 'en';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{language}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name, language_code, is_default', 'required'),
			array('name', 'length', 'max' => 255),
			array('language_code, region_code', 'length', 'is' => 2),
            array('language_code, region_code', 'match', 'pattern' => '/^[a-z]+$/'),
			array('is_default', 'in', 'range' => array_keys($this->getIsDefaultOptionsArray())),
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
			'customers' => array(self::HAS_MANY, 'Customer', 'language_id'),
			'users' => array(self::HAS_MANY, 'User', 'language_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'language_id'    => Yii::t('languages', 'Language'),
			'name'           => Yii::t('languages', 'Name'),
			'language_code'  => Yii::t('languages', 'Language code'),
			'region_code'    => Yii::t('languages', 'Region code'),
            'is_default'     => Yii::t('languages', 'Is default language?'),
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

		$criteria->compare('name', $this->name, true);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'name'   => CSort::SORT_ASC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Language the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeValidate()
    {
        if ($this->is_default != self::TEXT_YES) {
            $defaultLanguage = self::getDefaultLanguage();
            if (empty($defaultLanguage)) {
                $this->is_default = self::TEXT_YES;
            }
        }
        
        return parent::beforeValidate();
    }
    
    protected function beforeSave()
    {
        if ($this->is_default == self::TEXT_YES) {
            self::model()->updateAll(array('is_default' => self::TEXT_NO), 'language_id != :lid AND is_default = :default', array(':lid' => (int)$this->language_id, ':default' => self::TEXT_YES));
        }
        
        return parent::beforeSave();
    }

    protected function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        
        return $this->is_default != Language::TEXT_YES;
    }
    
    protected function afterDelete()
    {
        if (Yii::app()->hasComponent('messages') && (Yii::app()->messages instanceof CPhpMessageSource)) {
            $languageDir = Yii::app()->messages->basePath . '/' . $this->getLanguageAndLocaleCode();
            if (file_exists($languageDir) && is_dir($languageDir)) {
                FileSystemHelper::deleteDirectoryContents($languageDir, true, 1);
            }
        }

        parent::afterDelete();
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'name'          => Yii::t('languages', 'The visible language name to distinct between same language but distinct regions (i.e: between English US and English GB)'),
            'language_code' => Yii::t('languages', '2 letter language code, i.e: en'),
            'region_code'   => Yii::t('languages', '2 letter region code, i.e: us. Please do not fill this field unless necessary. For most of the cases, the language code is enough'),
            'is_default'    => Yii::t('languages', 'Whether this language is the default language for users/customers that have not set a language'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'name'          => Yii::t('languages', 'i.e: English - United States'),
            'language_code' => Yii::t('languages', 'i.e: en'),
            'region_code'   => Yii::t('languages', 'i.e: us'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function getIsDefaultOptionsArray()
    {
        return array(
            self::TEXT_NO  => Yii::t('app', ucfirst(self::TEXT_NO)),
            self::TEXT_YES => Yii::t('app', ucfirst(self::TEXT_YES)),
        );
    }
    
    public static function getDefaultLanguage()
    {
        return self::model()->findByAttributes(array('is_default' => self::TEXT_YES));
    }
    
    public function getLanguageAndLocaleCode()
    {
        if (empty($this->region_code)) {
            return $this->language_code;
        }
        return $this->language_code . '_' . $this->region_code;
    }
    
    public static function getLanguagesList()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.language_id, t.name';
        $criteria->order = 't.name ASC';
        return self::model()->findAll($criteria);
    }
    
    public static function getLanguagesArray()
    {
        static $_options;
        if ($_options !== null) {
            return $_options;
        }
        $_options = array();
        
        $languages = self::getLanguagesList();
        if (empty($languages)) {
            return $_options;
        }
        
        foreach ($languages as $language) {
            $_options[$language->language_id] = $language->name;
        }
        
        return $_options;
    }
}
