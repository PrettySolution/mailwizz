<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

class MPHPMailer extends \PHPMailer\PHPMailer\PHPMailer
{
	/**
	 * @var string 
	 */
	public $Version = '6.x';

	/**
	 * @var array 
	 */
    protected $_logData = array();

    /**
     * MPHPMailer::addLog()
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
        $this->_logData[] = $log;
        return $this;
    }
    
    /**
     * MPHPMailer::getLogs()
     * 
     * @param bool $clear
     * @return
     */
    public function getLogs($clear = true)
    {
        // maybe this gets too verbose ?
        if (is_object($this->smtp) && count($this->smtp->getLogs(false)) > 0) {
            $this->addLog($this->smtp->getLogs());
        }
            
        $logs = $this->_logData = array_unique($this->_logData);
        
        if ($clear) {
            $this->clearLogs();
        }
        
        return $logs;
    }
    
    /**
     * MPHPMailer::getLog()
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
     * MPHPMailer::clearLogs()
     * 
     * @return
     */
    public function clearLogs()
    {
        $this->_logData = array();
        if (is_object($this->smtp)) {
            $this->smtp->clearLogs();
        }
        return $this;
    }
    
    /**
     * PHPMailer::edebug()
     * Override parent implementation
     * 
     * Output debugging info via user-defined method.
     * Only if debug output is enabled.
     * @see PHPMailer::$Debugoutput
     * @see PHPMailer::$SMTPDebug
     * @param string $str
     */
    protected function edebug($str)
    {
        if (!$this->SMTPDebug) {
            return;
        }
        
        if ($this->Debugoutput == 'logger') {
            $this->addLog(preg_replace('/[\r\n]+/', '', strip_tags($str))); 
        } else {
            parent::edebug($str);
        }
    }
    
    /**
     * PHPMailer::getSMTPInstance()
     * Override parent implementation
     * 
     * Get an instance to use for SMTP operations.
     * Override this function to load your own SMTP implementation
     * @return MSMTP
     */
    public function getSMTPInstance()
    {
        if (!is_object($this->smtp)) {
            $this->smtp = new MSMTP;
        }
        return $this->smtp;
    }
}