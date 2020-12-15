<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * MailerAbstract
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.2
 */
 
abstract class MailerAbstract extends CApplicationComponent
{
    protected $_messageId;
    
    protected $_logs = array();

    protected $_sentCounter = 0;
    
    protected $_deliveryServerId = 0;
    
    public $denySending = false;
    
    /**
     * MailerAbstract::init()
     * 
     */
    public function init()
    {
        $this->setLocalServerNameIfMissing();
        parent::init();
    }
    
    /**
     * MailerAbstract::send()
     * 
     * @param mixed $params
     * @return
     */
    abstract public function send($params = array());
    
    /**
     * MailerAbstract::getEmailMessage()
     * 
     * @param mixed $params
     * @return
     */
    abstract public function getEmailMessage($params = array());
    
    /**
     * MailerAbstract::reset()
     * 
     * @return
     */
    abstract public function reset($resetLogs = true);
    
    /**
     * MailerAbstract::getName()
     * 
     * @return string
     */
    abstract public function getName();
    
    /**
     * MailerAbstract::getDescription()
     * 
     * @return string
     */
    abstract public function getDescription();
    
    /**
     * MailerAbstract::getEmailMessageId()
     * 
     * @return
     */
    public function getEmailMessageId()
    {
        return $this->_messageId;
    }
    
    /**
     * MailerAbstract::addLog()
     * 
     * @param mixed $log
     * @return
     */
    public function addLog($log)
    {
        if (is_array($log)) {
            foreach ($log as $l) {
                $this->addLog($l);
            }
            return $this;
        }
        $this->_logs[] = $log;
        return $this;
    }
    
    /**
     * MailerAbstract::getLogs()
     * 
     * @param bool $clear
     * @return
     */
    public function getLogs($clear = true)
    {
        $logs = $this->_logs = array_unique($this->_logs);
        if ($clear) {
            $this->clearLogs();
        }
        return $logs;
    }
    
    /**
     * MailerAbstract::getLog()
     * 
     * @param string $glue
     * @param bool $clear
     * @return
     */
    public function getLog($glue = "\n", $clear = true)
    {
        return implode($glue, $this->getLogs($clear));
    }
    
    /**
     * MailerAbstract::clearLogs()
     * 
     * @return
     */
    public function clearLogs()
    {
        $this->_logs = array();
        return $this;
    }
    
    /**
     * MailerAbstract::setLocalServerNameIfMissing()
     * 
     * @return
     */
    protected function setLocalServerNameIfMissing()
    {
        if (!empty($_SERVER) && !empty($_SERVER['SERVER_NAME'])) {
            return $this;
        }
        
        if (empty($_SERVER)) {
            $_SERVER = array();
        }
        
        $options  = Yii::app()->options;
        $hostname = $options->get('system.urls.frontend_absolute_url', $options->get('system.urls.backend_absolute_url'));
        if (!empty($hostname)) {
            $hostname = @parse_url($hostname, PHP_URL_HOST);
            if (!empty($hostname)) {
                $_SERVER['SERVER_NAME'] = $hostname;
            }
        }

        if (empty($_SERVER['SERVER_NAME']) && php_uname('n') !== false) {
            $_SERVER['SERVER_NAME'] = php_uname('n');
        }
        
        if (empty($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'localhost.localdomain';
        }

        return $this;
    }
    
    /**
     * MailerAbstract::findEmailAndName()
     * 
     * @return array
     */
    public function findEmailAndName($data)
    {
        if (empty($data)) {
            return array(null, null);
        }
        if (is_string($data)) {
            return array($data, null);
        }
        if (!is_array($data)) {
            return array(null, null);
        }
        foreach ($data as $email => $name) {
            return array($email, $name);
        }
        return array(null, null);
    }
    
    /**
     * MailerAbstract::getDomainFromEmail()
     * 
     * @return string
     */
    protected function getDomainFromEmail($email, $default = null)
    {
        if (strpos($email, '@') === false) {
            return $default;
        }
        $parts = explode('@', $email);
        return isset($parts[1]) ? $parts[1] : $default;
    }
    
    /**
     * MailerAbstract::isCustomFromDomainAllowed()
     * 
     * This should be something temporary i believe.
     * See: http://www.socketlabs.com/blog/yahoo-com-changes-dmarc-policy/
     * 
     * @param string $domain
     * @return bool
     */
    protected function isCustomFromDomainAllowed($domain)
    {
        static $patterns = array();
        static $domains  = array();
        
        if(isset($domains[$domain])) {
            return $domains[$domain];
        }
        
        if (empty($patterns)) {
            $patterns = array('/^yahoo/i', '/^aol/i');
            $patterns = (array)Yii::app()->hooks->applyFilters('mailer_not_allowed_custom_from_domain_patterns', $patterns);
            $patterns = array_unique($patterns);
        }

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $domain)) {
                return $domains[$domain] = false;
            }
        }
        
        return $domains[$domain] = true;
    }
    
    /**
     * MailerAbstract::appendDomainNameIfMissing()
     * 
     * @return string
     */
    protected function appendDomainNameIfMissing($email)
    {
        if (strpos($email, '@') !== false) {
            return $email;
        }
        
        if (empty($_SERVER['SERVER_NAME'])) {
            $this->setLocalServerNameIfMissing();
        }
        $searchReplace = array(
            '/^(www\.)/i' => '',
        );
        $thisDomainName = preg_replace(array_keys($searchReplace), array_values($searchReplace), $_SERVER['SERVER_NAME']);
        return $email . '@' . $thisDomainName;
    }
}