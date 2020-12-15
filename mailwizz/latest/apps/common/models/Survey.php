<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Survey
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "{{survey}}".
 *
 * The followings are the available columns in table '{{survey}}':
 * @property integer $survey_id
 * @property string $survey_uid
 * @property integer $customer_id
 * @property string $name
 * @property string $display_name
 * @property string $description
 * @property string $start_at
 * @property string $end_at
 * @property string $finish_redirect
 * @property string $meta_data
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property SurveyField[] $fields
 * @property SurveyField $fieldsCount
 * @property SurveyResponder[] $responders
 * @property SurveyResponder[] $respondersCount
 * @property SurveySegment[] $segments
 * @property SurveySegment[] $segmentsCount
 */
class Survey extends ActiveRecord
{
    /**
     * Flag for pending-delete
     */
    const STATUS_PENDING_DELETE = 'pending-delete';

    /**
     * Flag for draft
     */
    const STATUS_DRAFT = 'draft';

    /**
     * @var array
     */
    public $copySurveyFieldsMap = array();

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name, status', 'required'),
			array('customer_id', 'numerical', 'integerOnly' => true),
			array('name, display_name', 'length', 'min' => 2, 'max' => 255),
            array('description', 'length', 'min' => 2, 'max' => 65535),
            array('start_at, end_at', 'date', 'format' => 'yyyy-mm-dd hh:mm:ss'),
            array('finish_redirect', 'url'),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),

            array('survey_id, survey_uid, customer_id, name, display_name, description, start_at, end_at, status', 'safe', 'on'=>'search'),
		);

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer'            => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'fields'              => array(self::HAS_MANY, 'SurveyField', 'survey_id'),
            'fieldsCount'         => array(self::STAT, 'SurveyField', 'survey_id'),
            'responders'          => array(self::HAS_MANY, 'SurveyResponder', 'survey_id'),
			'respondersCount'     => array(self::STAT, 'SurveyResponder', 'survey_id'),
            'segments'            => array(self::HAS_MANY, 'SurveySegment', 'survey_id'),
            'segmentsCount'       => array(self::STAT, 'SurveySegment', 'survey_id'),
            'activeSegmentsCount' => array(self::STAT, 'SurveySegment', 'survey_id', 'condition' => 't.status = :s', 'params' => array(':s' => SurveySegment::STATUS_ACTIVE)),
        );

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'survey_id'       => Yii::t('surveys', 'Survey'),
			'survey_uid'      => Yii::t('surveys', 'Survey'),
			'customer_id'     => Yii::t('surveys', 'Customer'),
			'name'            => Yii::t('surveys', 'Name'),
			'display_name'    => Yii::t('surveys', 'Display name'),
			'description'     => Yii::t('surveys', 'Description'),
			'start_at'        => Yii::t('surveys', 'Start at'),
			'end_at'          => Yii::t('surveys', 'End at'),
            'finish_redirect' => Yii::t('surveys', 'Finish redirect'),
            'meta_data'       => Yii::t('surveys', 'Meta data'),

            'responders_count' => Yii::t('surveys','Responders count'),
		);

        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @return array customized attribute help texts (name=>text)
     */
    public function attributeHelpTexts()
    {
        $text = array(
            'survey_id'       => Yii::t('surveys', 'Survey'),
            'survey_uid'      => Yii::t('surveys', 'Survey'),
            'customer_id'     => Yii::t('surveys', 'Customer'),
            'name'            => Yii::t('surveys', 'The name of the survey'),
            'display_name'    => Yii::t('surveys', 'The display name of the survey which will be shown to responders. If this is left blank, the name of the survey is shown instead'),
            'description'     => Yii::t('surveys', 'The survey description shown to your responders'),
            'start_at'        => Yii::t('surveys', 'The start date since this survey will be available'),
            'end_at'          => Yii::t('surveys', 'The date when this survey will not be available anymore'),
            'finish_redirect' => Yii::t('surveys', 'Url where to redirect when the responder is reaching the survey end'),
        );

        return CMap::mergeArray($text, parent::attributeHelpTexts());
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
        $criteria->with = array();

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        }

        $criteria->compare('t.survey_uid', $this->survey_uid);
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.display_name', $this->display_name, true);

        if (empty($this->status)) {
            $criteria->compare('t.status', '<>' . self::STATUS_PENDING_DELETE);
        } else {
            $criteria->compare('t.status', $this->status);
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'attributes' => array(
                    'survey_id',
                    'customer_id',
                    'survey_uid',
                    'name',
                    'display_name',
                    'status',
                    'date_added',
                    'last_updated',
                ),
                'defaultOrder'  => array(
                    'survey_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Survey the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @inheritdoc
     */
    protected function beforeValidate()
    {
        if (empty($this->start_at) || $this->start_at === '0000-00-00 00:00:00') {
            $this->start_at = null;
        }

        if (empty($this->end_at) || $this->end_at === '0000-00-00 00:00:00') {
            $this->end_at = null;
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if ($this->isNewRecord && empty($this->survey_uid)) {
            $this->survey_uid = $this->generateUid();
        }

        if (empty($this->display_name)) {
            $this->display_name = $this->name;
        }

        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function beforeDelete()
    {
        if (!$this->getIsPendingDelete()) {
            $this->status = self::STATUS_PENDING_DELETE;
            $this->save(false);

            return false;
        }
        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'name'            => Yii::t('surveys', 'Survey name, i.e: Customer satisfaction survey.'),
            'description'     => Yii::t('surveys', 'Survey detailed description, something your responders will easily recognize.'),
            'finish_redirect' => Yii::t('surveys', 'i.e: https://www.google.com')
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @param $survey_uid
     * @return array|mixed|null
     */
    public function findByUid($survey_uid)
    {
        return self::model()->findByAttributes(array(
            'survey_uid' => $survey_uid,
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
     * @return string
     */
    public function getUid()
    {
        return $this->survey_uid;
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_DRAFT    => ucfirst(Yii::t('surveys', self::STATUS_DRAFT)),
            self::STATUS_ACTIVE   => ucfirst(Yii::t('surveys', self::STATUS_ACTIVE)),
            self::STATUS_INACTIVE => ucfirst(Yii::t('surveys', self::STATUS_INACTIVE)),
        );
    }

    /**
     * @return bool
     */
    public function getCanBeDeleted()
    {
        return $this->getIsRemovable();
    }

    /**
     * @return bool
     */
    public function getIsRemovable()
    {
        if ($this->getIsPendingDelete()) {
            return false;
        }

        $removable = true;
        if (!empty($this->customer_id) && !empty($this->customer)) {
            $removable = $this->customer->getGroupOption('surveys.can_delete_own_surveys', 'yes') == 'yes';
        }
        return $removable;
    }

    /**
     * @return bool
     */
    public function getEditable()
    {
        return in_array($this->status, array(self::STATUS_ACTIVE, self::STATUS_DRAFT));
    }

    /**
     * @return bool
     */
    public function getIsPendingDelete()
    {
        return $this->status == self::STATUS_PENDING_DELETE;
    }

    /**
     * @return bool
     */
    public function getIsDraft()
    {
        return $this->status == self::STATUS_DRAFT;
    }

	/**
	 * @return bool|Survey
	 * @throws CException
	 */
    public function copy()
    {
        $copied = false;

        if ($this->isNewRecord) {
            return $copied;
        }

        $transaction = Yii::app()->db->beginTransaction();

        try {

            $survey = clone $this;
            $survey->isNewRecord  = true;
            $survey->survey_id    = null;
            $survey->survey_uid   = $this->generateUid();
            $survey->date_added   = new CDbExpression('NOW()');
            $survey->last_updated = new CDbExpression('NOW()');

            if (preg_match('/\#(\d+)$/', $survey->name, $matches)) {
                $counter = (int)$matches[1];
                $counter++;
                $survey->name = preg_replace('/\#(\d+)$/', '#' . $counter, $survey->name);
            } else {
                $survey->name .= ' #1';
            }

            if (!$survey->save(false)) {
                throw new CException($survey->shortErrors->getAllAsString());
            }

            $fields = !empty($this->fields) ? $this->fields : array();
            foreach ($fields as $field) {
                $oldFieldId = $field->field_id;

                $fieldOptions = !empty($field->options) ? $field->options : array();
                $field = clone $field;
                $field->isNewRecord  = true;
                $field->field_id     = null;
                $field->survey_id      = $survey->survey_id;
                $field->date_added   = new CDbExpression('NOW()');
                $field->last_updated = new CDbExpression('NOW()');
                if (!$field->save(false)) {
                    continue;
                }

                $newFieldId = $field->field_id;
                $this->copySurveyFieldsMap[$oldFieldId] = $newFieldId;

                foreach ($fieldOptions as $option) {
                    $option = clone $option;
                    $option->isNewRecord  = true;
                    $option->option_id    = null;
                    $option->field_id     = $field->field_id;
                    $option->date_added   = new CDbExpression('NOW()');
                    $option->last_updated = new CDbExpression('NOW()');
                    $option->save(false);
                }
            }

            $segments = !empty($this->segments) ? $this->segments : array();
            foreach ($segments as $_segment) {

                if ($_segment->getIsPendingDelete()) {
                    continue;
                }

                $segment = clone $_segment;
                $segment->isNewRecord  = true;
                $segment->survey_id    = $survey->survey_id;
                $segment->segment_id   = null;
                $segment->segment_uid  = null;
                $segment->date_added   = new CDbExpression('NOW()');
                $segment->last_updated = new CDbExpression('NOW()');
                if (!$segment->save(false)) {
                    continue;
                }

                $conditions = !empty($_segment->segmentConditions) ? $_segment->segmentConditions : array();
                foreach ($conditions as $_condition) {
                    if (!isset($this->copySurveyFieldsMap[$_condition->field_id])) {
                        continue;
                    }
                    $condition = clone $_condition;
                    $condition->isNewRecord  = true;
                    $condition->condition_id = null;
                    $condition->segment_id   = $segment->segment_id;
                    $condition->field_id     = $this->copySurveyFieldsMap[$_condition->field_id];
                    $condition->date_added   = new CDbExpression('NOW()');
                    $condition->last_updated = new CDbExpression('NOW()');
                    $condition->save(false);
                }
            }

            $transaction->commit();
            $copied = $survey;
            $copied->copySurveyFieldsMap = $this->copySurveyFieldsMap;
        } catch (Exception $e) {
            $transaction->rollback();
            $this->copySurveyFieldsMap = array();
        }

        return Yii::app()->hooks->applyFilters('models_survey_after_copy_survey', $copied, $this);
    }

    /**
     * @return mixed
     */
    public function getStartAt()
    {
        if (empty($this->start_at) || $this->start_at == '0000-00-00 00:00:00') {
            return null;
        }
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->start_at);
    }

    /**
     * @return mixed
     */
    public function getEndAt()
    {
        if (empty($this->end_at) || $this->end_at == '0000-00-00 00:00:00') {
            return null;
        }
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->end_at);
    }

    /**
     * @return mixed
     */
    public function getViewUrl()
    {
        return Yii::app()->apps->getAppUrl('frontend', 'surveys/' . $this->survey_uid, true);
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return !empty($this->display_name) ? $this->display_name : $this->name;
    }

    /**
     * @return bool
     */
    public function getIsStarted()
    {
        if (empty($this->start_at) || $this->start_at === '0000-00-00 00:00:00') {
            return true;
        }
        return strtotime($this->start_at) < time();
    }

    /**
     * @return bool
     */
    public function getIsEnded()
    {
        if (empty($this->end_at) || $this->end_at === '0000-00-00 00:00:00') {
            return false;
        }
        return strtotime($this->end_at) < time();
    }
}
