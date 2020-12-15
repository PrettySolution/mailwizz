<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserLogin
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class UserLogin extends User
{
	/**
	 * @var bool
	 */
	public $remember_me = true;

	/**
	 * @var string
	 */
	public $twofa_code = '';

	/**
	 * @var null
	 */
	protected $_model = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);

        $rules = array(
            array('email, password', 'required'),
	        array('twofa_code', 'required', 'on' => 'twofa-login'),

            array('email', 'length', 'min' => 7, 'max' => 100),
            array('email', 'email', 'validateIDN' => true),
            array('password', 'length', 'min' => 6, 'max' => 100),
	        array('password', '_preAuthenticate'),

	        array('remember_me', 'safe'),
	        array('twofa_code', 'length', 'min' => 3, 'max' => 100),
        );

        $rules = $hooks->applyFilters($filter, new CList($rules));
        $this->onRules(new CModelEvent($this, array(
            'rules' => $rules,
        )));

        return $rules->toArray();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'remember_me' => Yii::t('users', 'Remember me'),
            'twofa_code'  => Yii::t('users', '2FA code'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
	public function attributePlaceholders()
	{
		$placeholders = array(
			'twofa_code'  => '',
		);

		return CMap::mergeArray($placeholders, parent::attributePlaceholders());
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
	 * @param $attribute
	 * @param $params
	 *
	 * @return bool
	 * @throws CException
	 */
	public function _preAuthenticate($attribute, $params)
	{
		if ($this->hasErrors()) {
			return false;
		}

		$identity = new UserIdentity($this->email, $this->password);
		if (!$identity->authenticate()) {
			$this->addError($attribute, $identity->errorCode);
			return false;
		}


		if (!($model = $this->getModel())) {
			$this->addError($attribute, Yii::t('users', 'Invalid login credentials.'));
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws CException
	 */
    public function authenticate()
    {
        if ($this->hasErrors()) {
	        return false;
        }

        $identity = new UserIdentity($this->email, $this->password);
        if (!$identity->authenticate()) {
            $this->addError('password', $identity->errorCode);
	        return false;
        }

        if (!Yii::app()->user->login($identity, $this->remember_me ? 3600 * 24 * 30 : 0)) {
            $this->addError('password', Yii::t('users', 'Unable to login with the given identity!'));
	        return false;
        }

	    return true;
    }

	/**
	 * @return User|null
	 */
	public function getModel()
	{
		if ($this->_model === null) {
			$this->_model = User::model()->findByAttributes(array(
				'email' => $this->email,
			));
		}
		return $this->_model;
	}
}
