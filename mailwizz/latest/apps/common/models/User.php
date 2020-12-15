<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * User
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $user_id
 * @property string $user_uid
 * @property integer $group_id
 * @property integer $language_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $timezone
 * @property string $avatar
 * @property string $removable
 * @property string $twofa_enabled
 * @property string $twofa_secret
 * @property integer $twofa_timestamp
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 * @property string $last_login
 *
 * The followings are the available model relations:
 * @property Language $language
 * @property UserGroup $group
 * @property UserAutoLoginToken[] $autoLoginTokens
 * @property PricePlanOrderNote[] $pricePlanOrderNotes
 * @property UserMessage[] $messages
 */
class User extends ActiveRecord
{
	/**
	 * @var string 
	 */
    public $fake_password;

	/**
	 * @var string
	 */
    public $confirm_password;

	/**
	 * @var string
	 */
    public $confirm_email;

	/**
	 * @var string
	 */
    public $new_avatar;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{user}}';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $avatarMimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $avatarMimes = Yii::app()->extensionMimes->get(array('png', 'jpg', 'jpeg', 'gif'))->toArray();
        }

        $rules = array(
            // when new user is created .
            array('first_name, last_name, email, confirm_email, fake_password, confirm_password, timezone, status', 'required', 'on' => 'insert'),
            // when a user is updated
            array('first_name, last_name, email, confirm_email, timezone, status', 'required', 'on' => 'update'),
            //
            array('language_id, group_id', 'numerical', 'integerOnly' => true),
            array('group_id', 'exist', 'className' => 'UserGroup'),
            array('language_id', 'exist', 'className' => 'Language'),
            array('first_name, last_name', 'length', 'min' => 1, 'max' => 100),
            array('email, confirm_email', 'length', 'min' => 4, 'max' => 100),
            array('email, confirm_email', 'email', 'validateIDN' => true),
            array('timezone', 'in', 'range' => array_keys(DateTimeHelper::getTimeZones())),
            array('fake_password, confirm_password', 'length', 'min' => 6, 'max' => 100),
            array('confirm_password', 'compare', 'compareAttribute' => 'fake_password'),
            array('confirm_email', 'compare', 'compareAttribute' => 'email'),
            array('email', 'unique', 'criteria' => array('condition' => 'user_id != :uid', 'params' => array(':uid' => (int)$this->user_id) )),

            // avatar
            array('new_avatar', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $avatarMimes, 'allowEmpty' => true),

            // mark them as safe for search
            array('first_name, last_name, email, status, group_id', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function relations()
    {
        $relations = array(
            'language'              => array(self::BELONGS_TO, 'Language', 'language_id'),
            'group'                 => array(self::BELONGS_TO, 'UserGroup', 'group_id'),
            'autoLoginTokens'       => array(self::HAS_MANY, 'UserAutoLoginToken', 'user_id'),
            'pricePlanOrderNotes'   => array(self::HAS_MANY, 'PricePlanOrderNote', 'user_id'),
            'messages'              => array(self::HAS_MANY, 'UserMessage', 'user_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = array(
            'user_id'       => Yii::t('users', 'User'),
            'language_id'   => Yii::t('users', 'Language'),
            'group_id'      => Yii::t('users', 'Group'),
            'first_name'    => Yii::t('users', 'First name'),
            'last_name'     => Yii::t('users', 'Last name'),
            'email'         => Yii::t('users', 'Email'),
            'password'      => Yii::t('users', 'Password'),
            'timezone'      => Yii::t('users', 'Timezone'),
            'avatar'        => Yii::t('users', 'Avatar'),
            'new_avatar'    => Yii::t('users', 'New avatar'),
            'removable'     => Yii::t('users', 'Removable'),

            'confirm_email'     => Yii::t('users', 'Confirm email'),
            'fake_password'     => Yii::t('users', 'Password'),
            'confirm_password'  => Yii::t('users', 'Confirm password'),

            'twofa_enabled' => Yii::t('users', '2FA enabled'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
	public function attributeHelpTexts()
	{
		$texts = array(
			'twofa_enabled' => Yii::t('users', 'Please make sure you scan the QR code in your authenticator application before enabling this feature, otherwise you will be locked out from your account'),
		);

		return CMap::mergeArray($texts, parent::attributeHelpTexts());
	}

    /**
    * Retrieves a list of models based on the current search/filter conditions.
    * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
    */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('first_name', $this->first_name, true);
        $criteria->compare('last_name', $this->last_name, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('group_id', $this->group_id);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'user_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @inheritdoc
	 */
    protected function afterValidate()
    {
        parent::afterValidate();
        $this->handleUploadedAvatar();
    }

	/**
	 * @inheritdoc
	 */
    protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if (empty($this->user_uid)) {
            $this->user_uid = $this->generateUid();
        }

        if (!empty($this->fake_password)) {
            $this->password = Yii::app()->passwordHasher->hash($this->fake_password);
        }

        if ($this->removable === self::TEXT_NO) {
            $this->status = self::STATUS_ACTIVE;
            $this->group_id = null;
        }

        return true;
    }

	/**
	 * @return bool
	 */
    protected function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        return $this->removable === self::TEXT_YES;
    }

	/**
	 * @return string
	 */
    public function getFullName()
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name.' '.$this->last_name;
        }
    }

	/**
	 * @return array
	 */
    public function getStatusesArray()
    {
        return array(
            self::STATUS_ACTIVE     => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE   => Yii::t('app', 'Inactive'),
        );
    }

	/**
	 * @return array
	 */
    public function getTimeZonesArray()
    {
        return DateTimeHelper::getTimeZones();
    }

	/**
	 * @param $user_uid
	 *
	 * @return null|User
	 */
    public function findByUid($user_uid)
    {
        return self::model()->findByAttributes(array(
            'user_uid' => $user_uid,
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
        return $this->user_uid;
    }

	/**
	 * For compatibility with the User component
	 *
	 * @return int
	 */
	public function getId()
	{
		return $this->user_id;
	}

	/**
	 * @param int $size
	 *
	 * @return mixed
	 */
    public function getGravatarUrl($size = 50)
    {
        $gravatar = sprintf('//www.gravatar.com/avatar/%s?s=%d', md5(strtolower(trim($this->email))), (int)$size);
        return Yii::app()->hooks->applyFilters('user_get_gravatar_url', $gravatar, $this, $size);
    }

	/**
	 * @param int $width
	 * @param int $height
	 * @param bool $forceSize
	 *
	 * @return mixed
	 */
    public function getAvatarUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->avatar)) {
            return $this->getGravatarUrl($width);
        }
        return ImageHelper::resize($this->avatar, $width, $height, $forceSize);
    }

	/**
	 * @param $route
	 *
	 * @return bool
	 */
    public function hasRouteAccess($route)
    {
        if (empty($this->group_id)) {
            return true;
        }
        return $this->group->hasRouteAccess($route);
    }

	/**
	 * @return $this
	 */
    public function updateLastLogin()
    {
        if (!array_key_exists('last_login', $this->getAttributes())) {
            return $this;
        }
        $columns = array('last_login' => new CDbExpression('NOW()'));
        $params  = array(':id' => $this->user_id);
        Yii::app()->getDb()->createCommand()->update($this->tableName(), $columns, 'user_id = :id', $params);
        $this->last_login = date('Y-m-d H:i:s');
        return $this;
    }

	/**
	 * @return void
	 */
    protected function handleUploadedAvatar()
    {
        if ($this->hasErrors()) {
            return;
        }

        if (!($avatar = CUploadedFile::getInstance($this, 'new_avatar'))) {
            return;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.avatars');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError('new_avatar', Yii::t('users', 'The avatars storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return;
            }
        }

        $newAvatarName = uniqid(rand(0, time())) . '-' . $avatar->getName();
        if (!$avatar->saveAs($storagePath . '/' . $newAvatarName)) {
            $this->addError('new_avatar', Yii::t('users', 'Cannot move the avatar into the correct storage folder!'));
            return;
        }

        $this->avatar = '/frontend/assets/files/avatars/' . $newAvatarName;
    }

	/**
	 * @return bool
	 */
	public function getTwoFaEnabled()
	{
		return $this->twofa_enabled === self::TEXT_YES;
	}
}
