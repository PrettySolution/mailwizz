<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

class MSMTP extends \PHPMailer\PHPMailer\SMTP
{
    protected $_logData = array();

    /**
     * MSMTP::addLog()
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
     * MSMTP::getLogs()
     * 
     * @param bool $clear
     * @return
     */
    public function getLogs($clear = true)
    {
        $logs = $this->_logData = array_unique($this->_logData);
        if ($clear) {
            $this->clearLogs();
        }
        return $logs;
    }
    
    /**
     * MSMTP::getLog()
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
     * MSMTP::clearLogs()
     * 
     * @return
     */
    public function clearLogs()
    {
        $this->_logData = array();
        return $this;
    }
    
    /**
     * Output debugging info via a user-selected method.
     * @param string $str Debug string to output
     * @return void
     */
    protected function edebug($str, $level = 0)
    {
        if ($this->Debugoutput == 'logger') {
            $this->addLog(preg_replace('/[\r\n]+/', '', $str));   
        } else {
            parent::edebug($str, $level = 0);
        }
    }
}