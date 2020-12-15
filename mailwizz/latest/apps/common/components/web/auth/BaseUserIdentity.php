<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BaseUserIdentity
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BaseUserIdentity extends CBaseUserIdentity
{
    /**
     * @var string the email to be checked against the database
     */
    public $email;
    
    /**
     * @var string the plain text password to be checked against the database
     */
    public $password;
    
    /**
     * @var int the user id
     */
    private $_id;
    
    /**
     * Constructor
     * 
     * @param string $email the email to check against database
     * @param string $password the plain text password to check against database
     */
    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
    
    /**
     * Set the user id
     * 
     * @param int $id the user id
     * @return BaseUserIdentity
     */
    public function setId($id)
    {
        $this->_id = (int)$id;
        return $this;
    }
    
    /**
     * Get the user id
     * 
     * @return int the user id
     */
    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * Get the user name
     * 
     * @return string the user name (the user email will be returned)
     */
    public function getName()
    {
        return $this->email;
    }

    /**
     * Child classes need to implement this method for checking if the email/password are valid
     * 
     */
    public function authenticate()
    {
        return parent::authenticate();
    }

}