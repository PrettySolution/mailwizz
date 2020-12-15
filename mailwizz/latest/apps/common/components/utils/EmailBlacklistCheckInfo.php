<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklistCheckInfo
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class EmailBlacklistCheckInfo extends CMap
{
    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->contains('email') ? (string)$this->itemAt('email') : '';
    }

    /**
     * @param $value
     * @return $this
     * @throws CException
     */
    public function setEmail($value)
    {
        $this->add('email', (string)$value);
        return $this;
    }
    
    /**
     * @return string
     */
    public function getReason()
    {
        return $this->contains('reason') ? $this->itemAt('reason') : null;
    }

    /**
     * @param $value
     * @return $this
     * @throws CException
     */
    public function setReason($value)
    {
        $this->add('reason', $value);
        return $this;
    }

    /**
     * @return bool
     */
    public function getBlacklisted()
    {
        return $this->contains('blacklisted') && $this->itemAt('blacklisted') !== false;
    }

    /**
     * @param $value
     * @return $this
     * @throws CException
     */
    public function setBlacklisted($value)
    {
        $this->add('blacklisted', (bool)$value);
        return $this;
    }

    /**
     * @return bool
     */
    public function getCustomerBlacklist()
    {
        return $this->contains('customerBlacklist') && $this->itemAt('customerBlacklist') !== false; 
    }

    /**
     * @param $value
     * @return $this
     */
    public function setCustomerBlacklist($value)
    {
        $this->add('customerBlacklist', (bool)$value);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBlacklisted() && $this->getReason() ? $this->getReason() : '';
    }
}