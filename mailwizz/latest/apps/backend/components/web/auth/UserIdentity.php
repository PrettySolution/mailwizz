<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserIdentity
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class UserIdentity extends BaseUserIdentity
{
	/**
	 * @return bool
	 */
    public function authenticate()
    {
        $user = User::model()->findByAttributes(array(
            'email'  => $this->email,
            'status' => User::STATUS_ACTIVE,
        ));

        if (empty($user) || !Yii::app()->passwordHasher->check($this->password, $user->password)) {
            $this->errorCode = Yii::t('users', 'Invalid login credentials.');
            return !$this->errorCode;
        }

        $this->setId($user->user_id);
        $this->setAutoLoginToken($user);

        $this->errorCode = self::ERROR_NONE;
        return !$this->errorCode;
    }

	/**
	 * @param User $user
	 *
	 * @return $this
	 */
    public function setAutoLoginToken(User $user)
    {
        $token = sha1(uniqid(rand(0, time()), true));
        $this->setState('__user_auto_login_token', $token);

        UserAutoLoginToken::model()->deleteAllByAttributes(array(
            'user_id' => (int)$user->user_id,
        ));

        $autologinToken          = new UserAutoLoginToken();
        $autologinToken->user_id = (int)$user->user_id;
        $autologinToken->token   = $token;
        $autologinToken->save();

        return $this;
    }

}
