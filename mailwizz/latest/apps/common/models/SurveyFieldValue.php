<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldValue
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "{{survey_field_value}}".
 *
 * The followings are the available columns in table '{{survey_field_value}}':
 * @property integer $value_id
 * @property integer $field_id
 * @property integer $responder_id
 * @property string $value
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property SurveyField $field
 * @property SurveyResponder $responder
 */
class SurveyFieldValue extends ActiveRecord
{
    /**
     * @var int
     */
    public $counter = 0;

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_field_value}}';
	}

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('value', 'length', 'max'=>255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'field'     => array(self::BELONGS_TO, 'SurveyField', 'field_id'),
			'responder' => array(self::BELONGS_TO, 'SurveyResponder', 'responder_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'value_id'      => Yii::t('survey_fields', 'Value'),
            'field_id'      => Yii::t('survey_fields', 'Field'),
            'responder_id'  => Yii::t('survey_fields', 'Responder'),
            'value'         => Yii::t('survey_fields', 'Value')
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

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'value_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SurveyFieldValue the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
