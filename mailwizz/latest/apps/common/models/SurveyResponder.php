<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyResponder
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "{{survey_responder}}".
 *
 * The followings are the available columns in table '{{survey_responder}}':
 * @property integer $responder_id
 * @property string $responder_uid
 * @property integer $survey_id
 * @property integer $subscriber_id
 * @property string $ip_address
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property SurveyFieldValue[] $fieldValues
 * @property Survey $survey
 * @property ListSubscriber $subscriber
 */
class SurveyResponder extends ActiveRecord
{
    /**
     * @var int
     */
    public $counter = 0;

    /**
     * @var array
     */
    public $surveyIds = array();

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_responder}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $rules = array(
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            array('survey_id, responder_uid, ip_address, status', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'fieldValues' => array(self::HAS_MANY, 'SurveyFieldValue', 'responder_id'),
			'survey'      => array(self::BELONGS_TO, 'Survey', 'survey_id'),
			'subscriber'  => array(self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'responder_id'  => Yii::t('surveys', 'Responder'),
			'responder_uid' => Yii::t('surveys', 'Responder uid'),
			'survey_id'     => Yii::t('surveys', 'Survey'),
			'subscriber_id' => Yii::t('surveys', 'Subscriber'),
			'ip_address'    => Yii::t('surveys', 'Ip address'),
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

        if (!empty($this->survey_id)) {
            $criteria->compare('t.survey_id', (int)$this->survey_id);
        } elseif (!empty($this->surveyIds)) {
            $criteria->addInCondition('t.survey_id', array_map('intval', $this->surveyIds));
        }

        $criteria->compare('t.responder_uid', $this->responder_uid);
        $criteria->compare('t.ip_address', $this->ip_address, true);
        $criteria->compare('t.status', $this->status);

        $criteria->order = 't.responder_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.responder_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SurveyResponder the static model class
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
        if (empty($this->responder_uid)) {
            $this->responder_uid = $this->generateUid();
        }

        return parent::beforeSave();
    }

    /**
     * @param $responder_uid
     * @return mixed
     */
    public function findByUid($responder_uid)
    {
        return self::model()->findByAttributes(array(
            'responder_uid' => $responder_uid,
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
     * @return bool
     */
    public function getCanBeDeleted()
    {
        return $this->getRemovable();
    }

    /**
     * @return bool
     */
    public function getCanBeEdited()
    {
        return $this->getEditable();
    }

    /**
     * @return bool
     */
    public function getRemovable()
    {
        $removable = true;
        if (!empty($this->survey_id) && !empty($this->survey) && !empty($this->survey->customer_id) && !empty($this->survey->customer)) {
            $removable = $this->survey->customer->getGroupOption('surveys.can_delete_own_responders', 'yes') == 'yes';
        }
        return $removable;
    }

    /**
     * @return bool
     */
    public function getEditable()
    {
        $editable = true;
        if (!empty($this->survey_id) && !empty($this->survey) && !empty($this->survey->customer_id) && !empty($this->survey->customer)) {
            $editable = $this->survey->customer->getGroupOption('surveys.can_edit_own_responders', 'yes') == 'yes';
        }
        return $editable;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->responder_uid;
    }

    /**
     * @param null $status
     * @return bool|int
     */
    public function saveStatus($status = null)
    {
        if (empty($this->responder_id)) {
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
        return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'responder_id = :id', array(':id' => (int)$this->responder_id));
    }

    /**
     * @return array
     * @throws CException
     */
    public function loadAllCustomFieldsWithValues()
    {
        $fields = array();
        foreach (SurveyField::getAllBySurveyId($this->survey_id) as $field) {
            $values = Yii::app()->getDb()->createCommand()
                ->select('value')
                ->from('{{survey_field_value}}')
                ->where('responder_id = :sid AND field_id = :fid', array(
                    ':sid' => (int)$this->responder_id,
                    ':fid' => (int)$field['field_id']
                ))
                ->queryAll();

            $value = array();
            foreach ($values as $val) {
                $value[] = $val['value'];
            }
            $fields['['. $field['field_id'] .']'] = CHtml::encode(implode(', ', $value));
        }

        return $fields;
    }

    /**
     * @param bool $refresh
     * @return array|mixed|string
     * @throws CException
     */
    public function getAllCustomFieldsWithValues($refresh = false)
    {
        static $fields = array();

        if (empty($this->responder_id)) {
            return array();
        }

        if ($refresh && isset($fields[$this->responder_id])) {
            unset($fields[$this->responder_id]);
        }

        if (isset($fields[$this->responder_id])) {
            return $fields[$this->responder_id];
        }

        $fields[$this->responder_id] = array();

        return $fields[$this->responder_id] = $this->loadAllCustomFieldsWithValues();
    }

    /**
     * @param $field
     * @return mixed|null
     * @throws CException
     */
    public function getCustomFieldValue($field)
    {
        $field  = '['. strtoupper(str_replace(array('[', ']'), '', $field)) .']';
        $fields = $this->getAllCustomFieldsWithValues();
        $value  = isset($fields[$field]) || array_key_exists($field, $fields) ? $fields[$field] : null;
        unset($fields);
        return $value;
    }

    /**
     * @param null $ipAddress
     * @return bool
     */
    public function saveIpAddress($ipAddress = null)
    {
        if (empty($this->responder_id)) {
            return false;
        }
        if ($ipAddress && $ipAddress == $this->ip_address) {
            return true;
        }
        if ($ipAddress) {
            $this->ip_address = $ipAddress;
        }
        $attributes = array('ip_address' => $this->ip_address);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');
        return (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'responder_id = :id', array(':id' => (int)$this->responder_id));
    }
}
