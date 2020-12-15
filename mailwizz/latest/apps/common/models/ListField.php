<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListField
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "list_field".
 *
 * The followings are the available columns in table 'list_field':
 * @property integer $field_id
 * @property integer $type_id
 * @property integer $list_id
 * @property string $label
 * @property string $tag
 * @property string $default_value
 * @property string $help_text
 * @property string $description
 * @property string $required
 * @property string $visibility
 * @property integer $sort_order
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignOpenActionListField[] $campaignOpenActionListFields
 * @property CampaignSentActionListField[] $campaignSentActionListFields
 * @property CampaignTemplateUrlActionListField[] $campaignTemplateUrlActionListFields
 * @property Lists $list
 * @property ListFieldType $type
 * @property ListFieldDefaultValue[] $defaultValues
 * @property ListFieldDefaultValue $defaultValue
 * @property ListFieldOption[] $options
 * @property ListFieldOption $option
 * @property ListFieldValue[] $values
 * @property ListFieldValue[] $value
 * @property ListSegmentCondition[] $segmentConditions
 */
class ListField extends ActiveRecord
{
    /**
     * Flag
     */
    const VISIBILITY_VISIBLE = 'visible';

    /**
     * Flag
     */
    const VISIBILITY_HIDDEN = 'hidden';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_field}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('type_id, label, tag, required, visibility, sort_order', 'required'),

            array('type_id', 'numerical', 'integerOnly' => true, 'min' => 1),
            array('type_id', 'exist', 'className' => 'ListFieldType'),
            array('label, help_text, description, default_value', 'length', 'min' => 1, 'max' => 255),
            array('tag', 'length', 'min' => 1, 'max' => 50),
            array('tag', 'match', 'pattern' => '#^(([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}]+)([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9\_]+)?([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9]+)?)$#u'),
            array('tag', '_checkIfAttributeUniqueInList'),
            array('tag', '_checkIfTagReserved'),
            array('required', 'in', 'range' => array_keys($this->getRequiredOptionsArray())),
            array('visibility', 'in', 'range' => array_keys($this->getVisibilityOptionsArray())),
            array('sort_order', 'numerical', 'min' => -100, 'max' => 100),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'campaignOpenActionListFields'          => array(self::HAS_MANY, 'CampaignOpenActionListField', 'field_id'),
            'campaignSentActionListFields'          => array(self::HAS_MANY, 'CampaignSentActionListField', 'field_id'),
            'campaignTemplateUrlActionListFields'   => array(self::HAS_MANY, 'CampaignTemplateUrlActionListField', 'field_id'),
            'list'                                  => array(self::BELONGS_TO, 'Lists', 'list_id'),
            'type'                                  => array(self::BELONGS_TO, 'ListFieldType', 'type_id'),
            'defaultValues'                         => array(self::HAS_MANY, 'ListFieldDefaultValue', 'field_id'),
            'defaultValue'                          => array(self::HAS_ONE, 'ListFieldDefaultValue', 'field_id'),
            'options'                               => array(self::HAS_MANY, 'ListFieldOption', 'field_id'),
            'option'                                => array(self::HAS_ONE, 'ListFieldOption', 'field_id'),
            'values'                                => array(self::HAS_MANY, 'ListFieldValue', 'field_id'),
            'value'                                 => array(self::HAS_ONE, 'ListFieldValue', 'field_id'),
            'segmentConditions'                     => array(self::HAS_MANY, 'ListSegmentCondition', 'field_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'field_id'      => Yii::t('list_fields', 'Field'),
            'type_id'       => Yii::t('list_fields', 'Type'),
            'list_id'       => Yii::t('list_fields', 'List'),
            'label'         => Yii::t('list_fields', 'Label'),
            'tag'           => Yii::t('list_fields', 'Tag'),
            'default_value' => Yii::t('list_fields', 'Default value'),
            'help_text'     => Yii::t('list_fields', 'Help text'),
            'Description'   => Yii::t('list_fields', 'Description'),
            'required'      => Yii::t('list_fields', 'Required'),
            'visibility'    => Yii::t('list_fields', 'Visibility'),
            'sort_order'    => Yii::t('list_fields', 'Sort order'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListField the static model class
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
        // make sure we uppercase the tags
        $this->tag = strtoupper($this->tag);
        return parent::beforeValidate();
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _checkIfAttributeUniqueInList($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare($attribute, $this->$attribute);
        $criteria->addNotInCondition('field_id', array((int)$this->field_id));

        $exists = self::model()->find($criteria);

        if (!empty($exists)) {
            $this->addError($attribute, Yii::t('list_fields', 'The {attribute} attribute must be unique in the mail list!', array(
                '{attribute}' => $attribute,
            )));
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _checkIfTagReserved($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $exists = TagRegistry::model()->findByAttributes(array('tag' => '['.$this->$attribute.']'));
        if (!empty($exists)) {
            $this->addError($attribute, Yii::t('list_fields', '"{tagName}" is reserved!', array(
                '{tagName}' => CHtml::encode($this->$attribute),
            )));
        }

        // since 1.3.5.9
        if (strpos($this->$attribute, CustomerCampaignTag::getTagPrefix()) === 0) {
            $this->addError($attribute, Yii::t('list_fields', '"{tagName}" is reserved!', array(
                '{tagName}' => CHtml::encode($this->$attribute),
            )));
        }
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $tags  = implode(', ', array_map(array('CHtml', 'encode'), array_keys(self::getDefaultValueTags())));
        $texts = array(
            'label'         => Yii::t('list_fields', 'This is what your subscribers will see above the input field.'),
            'tag'           => Yii::t('list_fields', 'The tag must be unique amoung the list tags. It must start with a letter, end with a letter or number and contain only alpha-numeric chars and underscores, all uppercased. The tag can be used in your templates like: [TAGNAME]'),
            'default_value' => Yii::t('list_fields', 'In case this field is not required and you need a default value for it. Following tags are recognized: {tags}', array('{tags}' => $tags)),
            'help_text'     => Yii::t('list_fields', 'If you need to describe this field to your subscribers.'),
            'description'   => Yii::t('list_fields', 'Additional description for this field to show to your subscribers.'),
            'required'      => Yii::t('list_fields', 'Whether this field must be filled in in order to submit the subscription form.'),
            'visibility'    => Yii::t('list_fields', 'Hidden fields are not shown to subscribers.'),
            'sort_order'    => Yii::t('list_fields', 'Decide the order of the fields shown in the form.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getRequiredOptionsArray()
    {
        return array(
            self::TEXT_YES   => Yii::t('app', 'Yes'),
            self::TEXT_NO    => Yii::t('app', 'No'),
        );
    }

    /**
     * @return array
     */
    public function getVisibilityOptionsArray()
    {
        return array(
            self::VISIBILITY_VISIBLE    => Yii::t('app', 'Visible'),
            self::VISIBILITY_HIDDEN     => Yii::t('app', 'Hidden'),
        );
    }

    /**
     * @return array
     */
    public function getSortOrderOptionsArray()
    {
        static $_opts = array();
        if (!empty($_opts)) {
            return $_opts;
        }

        for ($i = -100; $i <= 100; ++$i) {
            $_opts[$i] = $i;
        }

        return $_opts;
    }

    /**
     * @since 1.3.6.2
     * @param $listId
     * @return mixed
     */
    public static function getAllByListId($listId)
    {
        static $fields = array();
        if (!isset($fields[$listId])) {
            $fields[$listId] = array();
            $criteria = new CDbCriteria();
            $criteria->select = 't.field_id, t.tag';
            $criteria->compare('t.list_id', $listId);
            $models = self::model()->findAll($criteria);
            foreach ($models as $model) {
                $fields[$listId][] = $model->getAttributes(array('field_id', 'tag'));
            }
        }
        return $fields[$listId];
    }

    /**
     * @since 1.4.4
     * 
     * @param ListSubscriber|null $subscriber
     * @return mixed
     */
    public static function getDefaultValueTags(ListSubscriber $subscriber = null)
    {
        $ip = $userAgent = '';
        
        if (!MW_IS_CLI) {
            $ip        = Yii::app()->request->getUserHostAddress();
            $userAgent = StringHelper::truncateLength(Yii::app()->request->getUserAgent(), 255);
        }

        $geoCountry = $geoCity = $geoState = '';
        if (!empty($subscriber) && !empty($subscriber->ip_address) && ($location = IpLocation::findByIp($subscriber->ip_address))) {
            $geoCountry = $location->country_name;
            $geoCity    = $location->city_name;
            $geoState   = $location->zone_name;
        }
        
        $tags = array(
            '[DATETIME]'              => date('Y-m-d H:i:s'),
            '[DATE]'                  => date('Y-m-d'),
            '[SUBSCRIBER_IP]'         => $ip,
            '[SUBSCRIBER_USER_AGENT]' => $userAgent,
            '[SUBSCRIBER_GEO_COUNTRY]'=> $geoCountry,
            '[SUBSCRIBER_GEO_STATE]'  => $geoState,
            '[SUBSCRIBER_GEO_CITY]'   => $geoCity,
        );

        return Yii::app()->hooks->applyFilters('list_field_get_default_value_tags', $tags);
    }

    /**
     * @since 1.4.4
     * @param $value
     * @param ListSubscriber|null $subscriber
     * @return mixed
     */
    public static function parseDefaultValueTags($value, ListSubscriber $subscriber = null)
    {
        if (empty($value)) {
            return $value;
        }
        $tags = self::getDefaultValueTags($subscriber);

        return str_replace(array_keys($tags), array_values($tags), $value);
    }
}
