<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveySegment
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "survey_segment".
 *
 * The followings are the available columns in table 'survey_segment':
 * @property integer $segment_id
 * @property string $segment_uid
 * @property integer $survey_id
 * @property string $name
 * @property string $operator_match
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign[] $campaigns
 * @property Survey $survey
 * @property SurveySegmentCondition[] $segmentConditions
 */
class SurveySegment extends ActiveRecord
{
    const OPERATOR_MATCH_ANY = 'any';

    const OPERATOR_MATCH_ALL = 'all';

    const STATUS_PENDING_DELETE = 'pending-delete';

    private $_fieldConditions;

    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return '{{survey_segment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('name, operator_match', 'required'),

            array('name', 'length', 'max'=>255),
            array('operator_match', 'in', 'range'=>array_keys($this->getOperatorMatchArray())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'survey'            => array(self::BELONGS_TO, 'Survey', 'survey_id'),
            'segmentConditions' => array(self::HAS_MANY, 'SurveySegmentCondition', 'segment_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'segment_id'        => Yii::t('survey_segments', 'Segment'),
            'survey_id'         => Yii::t('survey_segments', 'Survey'),
            'name'              => Yii::t('survey_segments', 'Name'),
            'operator_match'    => Yii::t('survey_segments', 'Operator match'),
            'responders_count'  => Yii::t('survey_segments', 'Responders count'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a survey of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CSurveyView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $criteria->compare('t.survey_id', (int)$this->survey_id);

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
            'sort'=>array(
                'defaultOrder' => array(
                    'name'    => CSort::SORT_ASC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveySegment the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @param $surveyId
     * @return static[]
     */
    public function findAllBySurveyId($surveyId)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('survey_id', (int)$surveyId);
        $criteria->order = 'name ASC';
        return self::model()->findAll($criteria);
    }

    /**
     * @return array
     */
    public function getOperatorMatchArray()
    {
        return array(
            self::OPERATOR_MATCH_ANY => Yii::t('survey_segments', self::OPERATOR_MATCH_ANY),
            self::OPERATOR_MATCH_ALL => Yii::t('survey_segments', self::OPERATOR_MATCH_ALL),
        );
    }

    /**
     * @return array
     */
    public function getFieldsDropDownArray()
    {
        static $_options = array();
        if (isset($_options[$this->survey_id])) {
            return $_options[$this->survey_id];
        }

        if (empty($this->survey_id)) {
            return array();
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'field_id, label';
        $criteria->compare('survey_id', $this->survey_id);
        $criteria->order = 'sort_order ASC';
        $fields = SurveyField::model()->findAll($criteria);

        $options = array();

        foreach ($fields as $field) {
            $options[$field->field_id] = $field->label;
        }

        return $_options[$this->survey_id] = $options;
    }

    /**
     * @param null $extraCriteria
     * @param array $params
     * @return int
     * @throws CDbException
     */
    public function countResponders($extraCriteria = null, array $params = array())
    {
        $criteria = $this->_createCountFindRespondersCriteria($params);
        $this->_appendCountFindRespondersCriteria($criteria);

        // this is here so that we can hook when sending the campaign.
        if (!empty($extraCriteria) && $extraCriteria instanceof CDbCriteria) {
            $criteria->mergeWith($extraCriteria);
        }

        // since 1.3.4.9
        $criteria->select = 'COUNT(DISTINCT t.responder_id) as counter';
        $criteria->group  = '';

        return SurveyResponder::model()->count($criteria);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param null $extraCriteria
     * @param array $params
     * @return array
     * @throws CDbException
     */
    public function findResponders($offset = 0, $limit = 10, $extraCriteria = null, array $params = array())
    {
        $criteria = $this->_createCountFindRespondersCriteria($params);
        $this->_appendCountFindRespondersCriteria($criteria);

        // this is here so that we can hook when sending the campaign.
        if (!empty($extraCriteria) && $extraCriteria instanceof CDbCriteria) {
            $criteria->mergeWith($extraCriteria);
        }

        $criteria->offset = (int)$offset;
        $criteria->limit  = (int)$limit;
        return SurveyResponder::model()->findAll($criteria);
    }

    /**
     * @param array $params
     * @return CDbCriteria
     * @throws CDbException
     */
    protected function _createCountFindRespondersCriteria(array $params = array())
    {
        $segmentConditions = SurveySegmentCondition::model()->findAllByAttributes(array(
            'segment_id' => (int)$this->segment_id,
        ));

        $criteria = new CDbCriteria();
        $criteria->compare('t.survey_id', $this->survey_id);
        
        if (empty($params['status']) || !is_array($params['status'])) {
            $criteria->compare('t.status', SurveyResponder::STATUS_ACTIVE);
        } else {
            $criteria->addInCondition('t.status', $params['status']);
        }
        
        $criteria->group = 't.responder_id';
        $criteria->order = 't.responder_id DESC';

        $fieldConditions = array();
        foreach ($segmentConditions as $segmentCondition) {
            if (!isset($fieldConditions[$segmentCondition->field_id])) {
                $fieldConditions[$segmentCondition->field_id] = array();
            }
            $fieldConditions[$segmentCondition->field_id][] = $segmentCondition;
        }
        
        $responder = SurveyResponder::model();
        $md = $responder->getMetaData();
        foreach ($fieldConditions as $field_id => $conditions) {
            if ($md->hasRelation('fieldValues'.$field_id)) {
                continue;
            }
            $md->addRelation('fieldValues'.$field_id, array(SurveyResponder::HAS_MANY, 'SurveyFieldValue', 'responder_id'));
        }
        $this->_fieldConditions = $fieldConditions;

        unset($segmentConditions, $fieldConditions);
        return $criteria;
    }

    /**
     * @param CDbCriteria $criteria
     */
    protected function _appendCountFindRespondersCriteria(CDbCriteria $criteria)
    {
        $fieldConditions = $this->_fieldConditions;

        $with                       = array();
        $params                     = array();
        $appendCriteriaCondition    = array();

        foreach ($fieldConditions as $field_id => $conditions) {

            $addWith         = true;
            $relationName    = 'fieldValues'.$field_id;
            $valueColumnName = '`fieldValues'.$field_id.'`.`value`';

            if ($addWith) {
                $with[$relationName] = array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'LEFT JOIN',
                );
            }

            $conditionString = '1 = 1 AND (%s)';
            if ($addWith) {
                $conditionString = '(`fieldValues' . $field_id . '`.`field_id` = :field_id' . $field_id . ' AND (%s) )';
                $params[':field_id'.$field_id] = $field_id;
            }
            
            $injectCondition = array();

            // note: since 1.3.4.7, added the is_numeric() and is_float() checks and values casting if needed
            foreach ($conditions as $idx => $condition) {
                $index = '_' . $this->getUniqueIndexValue();
                $value = $condition->getParsedValue();

                if ($condition->operator->slug === SurveySegmentOperator::IS) {
                    if (is_numeric($value)) {
                        if (is_float($value)) {
                            $injectCondition[] = 'CAST('.$valueColumnName.' AS DECIMAL) = :value'.$index;
                            $params[':value'.$index] = (float)$value;
                        } else {
                            $injectCondition[] = 'CAST('.$valueColumnName.' AS UNSIGNED) = :value'.$index;
                            $params[':value'.$index] = (int)$value;
                        }
                    } else {
                        $injectCondition[] = $valueColumnName . ' = :value'.$index;
                        $params[':value'.$index] = $value;
                    }
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::IS_NOT) {
                    if (is_numeric($value)) {
                        if (is_float($value)) {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS DECIMAL) != :value'.$index;
                            $params[':value'.$index] = (float)$value;
                        } else {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS UNSIGNED) != :value'.$index;
                            $params[':value'.$index] = (int)$value;
                        }
                    } else {
                        $injectCondition[] =  $valueColumnName . ' != :value'.$index;
                        $params[':value'.$index] = $value;
                    }
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::CONTAINS) {
                    $injectCondition[] =  $valueColumnName . ' LIKE :value'.$index;
                    $params[':value'.$index] = '%'.$value.'%';
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::NOT_CONTAINS) {
                    $injectCondition[] =  $valueColumnName . ' NOT LIKE :value'.$index;
                    $params[':value'.$index] = '%'.$value.'%';
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::STARTS_WITH) {
                    $injectCondition[] = $valueColumnName . ' LIKE :value'.$index;
                    $params[':value'.$index] = $value.'%';
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::NOT_STARTS_WITH) {
                    $injectCondition[] = $valueColumnName . ' NOT LIKE :value'.$index;
                    $params[':value'.$index] = $value.'%';
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::ENDS_WITH) {
                    $injectCondition[] = $valueColumnName . ' LIKE :value'.$index;
                    $params[':value'.$index] = '%'.$value;
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::NOT_ENDS_WITH) {
                    $injectCondition[] = $valueColumnName . ' NOT LIKE :value'.$index;
                    $params[':value'.$index] = '%'.$value;
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::GREATER) {
                    if (is_numeric($value)) {
                        if (is_float($value)) {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS DECIMAL) > :value'.$index;
                            $params[':value'.$index] = (float)$value;
                        } else {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS UNSIGNED) > :value'.$index;
                            $params[':value'.$index] = (int)$value;
                        }
                    } else {
                        $injectCondition[] =  $valueColumnName . ' > :value'.$index;
                        $params[':value'.$index] = $value;
                    }
                    continue;
                }

                if ($condition->operator->slug === SurveySegmentOperator::LESS) {
                    if (is_numeric($value)) {
                        if (is_float($value)) {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS DECIMAL) < :value'.$index;
                            $params[':value'.$index] = (float)$value;
                        } else {
                            $injectCondition[] =  'CAST('.$valueColumnName.' AS UNSIGNED) < :value'.$index;
                            $params[':value'.$index] = (int)$value;
                        }
                    } else {
                        $injectCondition[] =  $valueColumnName . ' < :value'.$index;
                        $params[':value'.$index] = $value;
                    }
                    continue;
                }
            }

            if (!empty($injectCondition)) {
                if ($this->operator_match === SurveySegment::OPERATOR_MATCH_ANY) {
                    $injectCondition = implode(' OR ', $injectCondition);
                } else {
                    $injectCondition = implode(' AND ', $injectCondition);
                }
                $appendCriteriaCondition[] = sprintf($conditionString, $injectCondition);
            }
        }

        if (!empty($appendCriteriaCondition)) {
            $criteria->params = array_merge($criteria->params, $params);
            if ($this->operator_match === SurveySegment::OPERATOR_MATCH_ANY) {
                $appendCondition = ' AND ' . '( '. implode(' OR ', $appendCriteriaCondition) .' )';
            } else {
                $appendCondition = ' AND ' . implode(' AND ', $appendCriteriaCondition);
            }

            $criteria->with = $with;
            $criteria->condition .= $appendCondition;
        } else {
            // add a condition to return nothing as a result
            $criteria->compare('t.responder_id', -1);
        }
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        if ($this->isNewRecord || empty($this->segment_uid)) {
            $this->segment_uid = $this->generateUid();
        }

        return parent::beforeSave();
    }

    /**
     * @return bool
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
     * @param $segment_uid
     * @return static
     */
    public function findByUid($segment_uid)
    {
        return self::model()->findByAttributes(array(
            'segment_uid' => $segment_uid,
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
        return $this->segment_uid;
    }

	/**
	 * @return bool|SurveySegment
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
            $segment = clone $this;
            $segment->isNewRecord  = true;
            $segment->segment_id   = null;
            $segment->segment_uid  = $this->generateUid();
            $segment->date_added   = new CDbExpression('NOW()');
            $segment->last_updated = new CDbExpression('NOW()');

            if (preg_match('/\#(\d+)$/', $segment->name, $matches)) {
                $counter = (int)$matches[1];
                $counter++;
                $segment->name = preg_replace('/\#(\d+)$/', '#' . $counter, $segment->name);
            } else {
                $segment->name .= ' #1';
            }

            if (!$segment->save(false)) {
                throw new CException($segment->shortErrors->getAllAsString());
            }

            $conditions = !empty($this->segmentConditions) ? $this->segmentConditions : array();
            foreach ($conditions as $condition) {
                $condition = clone $condition;
                $condition->isNewRecord  = true;
                $condition->condition_id = null;
                $condition->segment_id   = $segment->segment_id;
                $condition->date_added   = new CDbExpression('NOW()');
                $condition->last_updated = new CDbExpression('NOW()');
                $condition->save(false);
            }

            $transaction->commit();
            $copied = $segment;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $copied;
    }

	/**
	 * @param $responder
	 *
	 * @return bool
	 * @throws CDbException
	 */
    public function hasResponder($responder)
    {
        if ($responder instanceof SurveyResponder) {
            $responderId = (int)$responder->responder_id;
        } else {
            $responderId = (int)$responder;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('t.responder_id', (int)$responderId);
        
        return $this->countResponders($criteria) > 0;
    }

    /**
     * @return bool
     */
    public function getIsPendingDelete()
    {
        return $this->status == self::STATUS_PENDING_DELETE;
    }

    /**
     * @deprecated since 1.3.8.9
     */
    public function getPendingDelete()
    {
        trigger_error('Please call getIsPendingDelete() / isPendingDelete instead!', E_USER_NOTICE);
        return $this->getIsPendingDelete();
    }

    /**
     * @return string
     */
    public function getUniqueIndexValue()
    {
        static $values = array();
        $value = StringHelper::random(6, true);
        while (isset($values[$value])) {
            $value = StringHelper::random(6, true);
        }
        $values[$value] = true;
        return $value;
    }
}
