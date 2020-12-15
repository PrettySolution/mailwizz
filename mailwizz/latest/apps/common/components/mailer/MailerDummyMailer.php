<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * MailerDummyMailer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.2
 */

class MailerDummyMailer extends MailerAbstract
{
    /**
     * MailerDummyMailer::send()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return bool
     */
    public function send($params = array())
    {
        $this->reset();
        
        $this->addLog('OK');
        $this->_messageId = md5(StringHelper::uniqid());
        $this->_sentCounter++;

        $this->reset(false);

        return true;
    }

    /**
     * MailerDummyMailer::getEmailMessage()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return mixed
     */
    public function getEmailMessage($params = array())
    {
        return StringHelper::random(rand(0, 1000));
    }

    /**
     * MailerDummyMailer::reset()
     *
     * Implements the parent abstract method
     *
     * @return MailerDummyMailer
     */
    public function reset($resetLogs = true)
    {
        if ($resetLogs) {
            $this->clearLogs();
        }
        return $this;
    }

    /**
     * MailerDummyMailer::getName()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getName()
    {
        return 'DummyMailer';
    }

    /**
     * MailerDummyMailer::getDescription()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getDescription()
    {
        return Yii::t('mailer', 'System testing mailer, only simulate sending.');
    }
}
