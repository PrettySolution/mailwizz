<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklistMonitor
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.9
 */

/**
 * This is the model class for table "email_blacklist_monitor".
 *
 * The followings are the available columns in table 'email_blacklist_monitor':
 * @property integer $monitor_id
 * @property string $name
 * @property string $email_condition
 * @property string $email
 * @property string $reason_condition
 * @property string $reason
 * @property string $condition_operator
 * @property string $notifications_to
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 */
class EmailBlacklistMonitor extends ActiveRecord
{
    const CONDITION_EQUALS = 'equals';
    
    const CONDITION_CONTAINS = 'contains';
    
    const CONDITION_STARTS_WITH = 'starts with';
    
    const CONDITION_ENDS_WITH = 'ends with';
    
    const OPERATOR_AND = 'and';
    
    const OPERATOR_OR = 'or';

    /**
     * @var $file - the uploaded file for import
     */
    public $file;
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{email_blacklist_monitor}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }
        
        $rules = array(
            array('name, condition_operator, status', 'required', 'on' => 'insert, update'),
            
            array('email_condition, reason_condition', 'in', 'range' => array_keys($this->getConditionsList())),
            array('name, email, reason, notifications_to', 'length', 'max' => 255),
            array('condition_operator', 'in', 'range' => array_keys($this->getConditionOperatorsList())),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            array('file', 'required', 'on' => 'import'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => 512000000, 'allowEmpty' => true),
            
            // search
            array('name, email_condition, reason_condition, email, reason, notifications_to, condition_operator, status', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'monitor_id'         => Yii::t('email_blacklist', 'Monitor'),
            'name'               => Yii::t('email_blacklist', 'Name'),
            'email_condition'    => Yii::t('email_blacklist', 'Email condition'),
            'email'              => Yii::t('email_blacklist', 'Email match'),
            'reason_condition'   => Yii::t('email_blacklist', 'Reason condition'),
            'reason'             => Yii::t('email_blacklist', 'Reason match'),
            'condition_operator' => Yii::t('email_blacklist', 'Condition operator'),
            'notifications_to'   => Yii::t('email_blacklist', 'Notifications to'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'name'               => Yii::t('email_blacklist', 'Name your monitor for easier identification'),
            'email_condition'    => Yii::t('email_blacklist', 'How to match against the blacklisted email address'),
            'email'              => Yii::t('email_blacklist', 'The text to match against the email address'),
            'reason_condition'   => Yii::t('email_blacklist', 'How to match against the blacklisted reason'),
            'reason'             => Yii::t('email_blacklist', 'The text to match against the blacklist reason. Use the [EMPTY] tag to match empty content'),
            'condition_operator' => Yii::t('email_blacklist', 'What operator to use between the conditions'),
            'notifications_to'   => Yii::t('email_blacklist', 'Where to send notifications when the conditions are met. Separate multiple email addresses with a comma'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'email'              => 'yahoo.com',
            'reason'             => Yii::t('email_blacklist', 'Greylisted'),
            'notifications_to'   => 'a@domain.com, b@domain.com, c@domain.com',
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
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
        $criteria->compare('monitor_id',$this->monitor_id);
        $criteria->compare('email_condition', $this->email_condition);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('reason_condition', $this->reason_condition);
        $criteria->compare('reason', $this->reason, true);
        $criteria->compare('condition_operator', $this->condition_operator);
        $criteria->compare('notifications_to', $this->notifications_to, true);
        $criteria->compare('status', $this->status);

	    $criteria->order = 'name ASC';
	    
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    'monitor_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        if (!empty($this->notifications_to)) {
            $notificationsTo = CommonHelper::getArrayFromString($this->notifications_to, ',');
            foreach ($notificationsTo as $index => $email) {
                if (!FilterVarHelper::email($email)) {
                    unset($notificationsTo[$index]);
                }
            }
            $notificationsTo = array_values($notificationsTo); // reset
            $this->notifications_to = CommonHelper::getStringFromArray($notificationsTo, ',');
        }
        
        if (in_array($this->scenario, array('create', 'update'))) {

            if (empty($this->email) && empty($this->reason)) {
                $this->addError('email', Yii::t('emails_blacklist', 'Please specify at least the email and/or the reason!'));
                $this->addError('reason', Yii::t('emails_blacklist', 'Please specify at least the email and/or the reason!'));
            }

            if (!empty($this->email) && empty($this->email_condition)) {
                $this->addError('email_condition', Yii::t('emails_blacklist', 'Please specify the condition!'));
            }

            if (!empty($this->reason) && empty($this->reason_condition)) {
                $this->addError('reason_condition', Yii::t('emails_blacklist', 'Please specify the condition!'));
            }
            
        }
        
        parent::afterValidate();
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EmailBlacklistMonitor the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function getConditionsList()
    {
        return array(
            self::CONDITION_EQUALS      => Yii::t('email_blacklist', ucfirst(self::CONDITION_EQUALS)),
            self::CONDITION_CONTAINS    => Yii::t('email_blacklist', ucfirst(self::CONDITION_CONTAINS)),
            self::CONDITION_STARTS_WITH => Yii::t('email_blacklist', ucfirst(self::CONDITION_STARTS_WITH)),
            self::CONDITION_ENDS_WITH   => Yii::t('email_blacklist', ucfirst(self::CONDITION_ENDS_WITH)),
        );
    }

    /**
     * @return array
     */
    public function getConditionOperatorsList()
    {
        return array(
            self::OPERATOR_AND => Yii::t('email_blacklist', ucfirst(self::OPERATOR_AND)),
            self::OPERATOR_OR  => Yii::t('email_blacklist', ucfirst(self::OPERATOR_OR)),
        );
    }
}