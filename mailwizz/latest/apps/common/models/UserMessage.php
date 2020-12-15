<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserMessage
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

/**
 * This is the model class for table "user_message".
 *
 * The followings are the available columns in table 'user_message':
 * @property integer $message_id
 * @property string $message_uid
 * @property integer $user_id
 * @property string $title
 * @property string $message
 * @property string $title_translation_params
 * @property string $message_translation_params
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property User $user
 */
class UserMessage extends ActiveRecord
{
	const STATUS_UNSEEN = 'unseen';

	const STATUS_SEEN = 'seen';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{user_message}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('user_id, message', 'required'),
			array('user_id', 'exist', 'className' => 'User'),
			array('title', 'length', 'max' => 255),
			array('message', 'length', 'min' => 5),
			array('status', 'in', 'range' => array_keys($this->getStatusesList())),

			// The following rule is used by search().
			array('user_id, title, message, status', 'safe', 'on'=>'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'message_id'  => Yii::t('messages', 'Message'),
			'message_uid' => Yii::t('messages', 'Message'),
			'user_id'     => Yii::t('messages', 'User'),
			'title'		  => Yii::t('messages', 'Title'),
			'message' 	  => Yii::t('messages', 'Message'),
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

		if (!empty($this->user_id)) {
            if (is_numeric($this->user_id)) {
                $criteria->compare('t.user_id', $this->user_id);
            } else {
                $criteria->with['user'] = array(
                    'condition' => 'user.email LIKE :name OR user.first_name LIKE :name OR user.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->user_id . '%')
                );
            }
        }

		$criteria->compare('t.title', $this->title, true);
		$criteria->compare('t.message', $this->message, true);
		$criteria->compare('t.status', $this->status);

		$criteria->order = 't.message_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.message_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return UserMessage the static model class
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
        if (!parent::beforeSave()) {
            return false;
        }

        if ($this->isNewRecord) {
            $this->message_uid = $this->generateUid();
        }
        
        if (!empty($this->title_translation_params)) {
            $this->title_translation_params = serialize($this->title_translation_params);
        }

        if (!empty($this->message_translation_params)) {
            $this->message_translation_params = serialize($this->message_translation_params);
        }
        
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        parent::afterFind();

        if (!empty($this->title_translation_params)) {
            $this->title_translation_params = @unserialize($this->title_translation_params);
        }

        if (!empty($this->message_translation_params)) {
            $this->message_translation_params = @unserialize($this->message_translation_params);
        }
    }

    /**
     * @return string
     */
    public function getTranslatedTitle()
    {
        if (!empty($this->title_translation_params) && is_array($this->title_translation_params)) {
            return Yii::t('messages', $this->title, $this->title_translation_params);
        }
        
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTranslatedMessage()
    {
        if (!empty($this->message_translation_params) && is_array($this->message_translation_params)) {
            return Yii::t('messages', $this->message, $this->message_translation_params);
        }

        return $this->message;
    }

    /**
     * @param $message_uid
     * @return static
     */
	public function findByUid($message_uid)
    {
        return self::model()->findByAttributes(array(
            'message_uid' => $message_uid,
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
        return $this->message_uid;
    }

    /**
     * @return array
     */
	public function getStatusesList()
    {
        return array(
            self::STATUS_UNSEEN => Yii::t('messages', 'Unseen'),
            self::STATUS_SEEN   => Yii::t('messages', 'Seen'),
        );
    }

    /**
     * @param int $length
     * @return string
     */
	public function getShortMessage($length = 45)
	{
		return StringHelper::truncateLength($this->getTranslatedMessage(), $length);
	}

    /**
     * @param int $length
     * @return string
     */
	public function getShortTitle($length = 25)
	{
		return StringHelper::truncateLength($this->getTranslatedTitle(), $length);
	}

    /**
     * @return bool
     */
	public function getIsUnseen()
	{
		return $this->status == self::STATUS_UNSEEN;
	}

    /**
     * @return bool
     */
	public function getIsSeen()
	{
		return $this->status == self::STATUS_SEEN;
	}

    /**
     * @param null $status
     * @return bool
     */
	public function saveStatus($status = null)
    {
        if (empty($this->message_id)) {
            return false;
        }

        if ($status) {
            $this->status = $status;
        }

		$attributes = array('status' => $this->status);

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'before_savestatus')), $this);
	    //
	    
	    $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'message_id = :id', array(':id' => (int)$this->message_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
	    
	    return $result;
    }

    /**
     * @param $userId
     * @return int
     */
	public static function markAllAsSeenForUser($userId)
	{
		$attributes = array('status' => self::STATUS_SEEN);
		$instance   = new self();
		return Yii::app()->getDb()->createCommand()->update($instance->tableName(), $attributes, 'user_id = :id', array(':id' => (int)$userId));
	}

    /**
     * @return $this
     */
	public function broadcast()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'user_id';
        $criteria->compare('status', User::STATUS_ACTIVE);
        $users = User::model()->findAll($criteria);
        
        foreach ($users as $user) {
            $message = clone $this;
            $message->user_id       = $user->user_id;
            $message->date_added    = null;
            $message->last_updated  = null;
            $message->save();
        }
        
        return $this;
    }
}
