<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * MailerPHPMailer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.2
 */

class MailerPHPMailer extends MailerAbstract
{
    private $_transport;

    private $_message;

    private $_mailer;

    private $_params;

    /**
     * MailerPHPMailer::init()
     *
     * @return
     */
    public function init()
    {
	    require_once Yii::getPathOfAlias('common.vendors.PHPMailer-6x') . '/class.msmtp.php';
	    require_once Yii::getPathOfAlias('common.vendors.PHPMailer-6x') . '/class.mphpmailer.php';
	    
        parent::init();
    }

    /**
     * MailerPHPMailer::send()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return bool
     */
    public function send($params = array())
    {
        // params
        $params = new CMap($params);

        // since 1.3.6.7
        if ((int)$params->itemAt('maxConnectionMessages') > 1) {
            
            $serverId = (int)$params->itemAt('server_id');
 
            if ($serverId == 0 || $this->_deliveryServerId != $serverId) {
                $this->reset();
            } else {
                $this->resetMessage()->clearLogs();
            }

            $this->_deliveryServerId = $serverId;
        
        } else {
        
            $this->reset();
        
        }
        //
        
        // prepare
        $this->clearLogs()->setTransport($params)->setMessage($params);

        if (!$this->getTransport() || !$this->getMessage()) {
            return false;
        }

        $plugins = isset($params['mailerPlugins']) ? $params['mailerPlugins'] : array();
        $plugins['loggerPlugin'] = true;

        if (isset($plugins['antiFloodPlugin']) && is_array($plugins['antiFloodPlugin'])) {
            $data       = $plugins['antiFloodPlugin'];
            $sendAtOnce = isset($data['sendAtOnce']) && $data['sendAtOnce'] > 0 ? $data['sendAtOnce'] : 100;
            $pause      = isset($data['pause']) && $data['pause'] > 0 ? $data['pause'] : 30;

            if ($this->_sentCounter >= $sendAtOnce && (($this->_sentCounter % $sendAtOnce) == 0)) {
                sleep($pause);
            }
        }

        if (isset($plugins['throttlePlugin']) && is_array($plugins['throttlePlugin'])) {
            $data      = $plugins['throttlePlugin'];
            $perMinute = isset($data['perMinute']) && $data['perMinute'] > 0 ? $data['perMinute'] : 60;
            usleep(floor((60 / $perMinute) * 1000));
        }

        // since 1.3.5.3
        Yii::app()->hooks->doAction('mailer_before_send_email', $this, $params->toArray());
        if ($this->denySending === true) {
            return false;
        }

        try {
            if ($sent = (bool)$this->getMailer()->send()) {
                $this->addLog('OK');
            } else {
                $mailer = $this->getMailer();
                if ($mailer->SMTPDebug && $mailer->Debugoutput == 'logger' && ($log = $mailer->getLog())){
                    $this->addLog($log);
                } elseif (!empty($mailer->ErrorInfo)) {
                    $this->addLog($mailer->ErrorInfo);
                } else {
                    $this->addLog('NOT OK, UNKNOWN ERROR!');
                }
            }
        } catch (Exception $e) {
            $sent = false;
            $this->addLog($e->getMessage());
        }

        // since 1.3.5.3
        Yii::app()->hooks->doAction('mailer_after_send_email', $this, $params->toArray(), $sent);

        $this->_sentCounter++;

        // reset
        if ($this->_sentCounter >= (int)$params->itemAt('maxConnectionMessages')) {
            $this->reset(false);
        } else {
            $this->resetMessage();
        }

        return $sent;
    }

    /**
     * MailerPHPMailer::getEmailMessage()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return mixed
     */
    public function getEmailMessage($params = array())
    {
        $this->reset()->setMessage(new CMap($params))->getMailer()->preSend();
        if ($lastMessageId = $this->getMailer()->getLastMessageID()) {
            $this->_messageId = str_replace(array('<', '>'), '', $lastMessageId);
        }
        return $this->getMailer()->getSentMIMEMessage();
    }

    /**
     * MailerPHPMailer::reset()
     *
     * Implements the parent abstract method
     *
     * @return MailerPHPMailer
     */
    public function reset($resetLogs = true)
    {
        $this->resetTransport()->resetMessage()->resetMailer();

        if ($resetLogs) {
            $this->clearLogs();
        }

        return $this;
    }

    /**
     * MailerPHPMailer::getName()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getName()
    {
        return 'PHPMailer';
    }

    /**
     * MailerPHPMailer::getDescription()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getDescription()
    {
        return Yii::t('mailer', 'A very fast mailer.');
    }

    /**
     * MailerPHPMailer::setTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function setTransport(CMap $params)
    {
        if ($this->_transport !== null) {
            return $this;
        }

        $this->resetTransport()->resetMailer();

        if (!($transport = $this->buildTransport($params))) {
            return $this;
        }

        // since 1.3.5.3
        $this->_transport = Yii::app()->hooks->applyFilters('mailer_after_create_transport_instance', $transport, $params->toArray(), $this);

        return $this;
    }

    /**
     * MailerPHPMailer::setMessage()
     *
     * @param mixed $params
     * @return mixed
     */
    protected function setMessage(CMap $params)
    {
        if ($this->_params === null) {
            $this->_params =& $params;
        }
        
        $mailer = $this->getMailer();

        $this->resetMessage();
        $mailer->clearAllRecipients();
        $mailer->clearCustomHeaders();
        $mailer->clearReplyTos();
        $mailer->clearAttachments();

        $requiredKeys = array('to', 'from', 'subject');
        foreach ($requiredKeys as $key) {
            if (!$params->itemAt($key)) {
                return $this;
            }
        }

        if (!$params->itemAt('body') && !$params->itemAt('plainText')) {
            return $this;
        }
        
        list($fromEmail, $fromName)         = $this->findEmailAndName($params->itemAt('from'));
        list($toEmail, $toName)             = $this->findEmailAndName($params->itemAt('to'));
        list($replyToEmail, $replyToName)   = $this->findEmailAndName($params->itemAt('replyTo'));
        
        if ($params->itemAt('fromName') && is_string($params->itemAt('fromName'))) {
            $fromName = $params->itemAt('fromName');
        }

        if ($params->itemAt('toName') && is_string($params->itemAt('toName'))) {
            $toName = $params->itemAt('toName');
        }

        if ($params->itemAt('replyToName') && is_string($params->itemAt('replyToName'))) {
            $replyToName = $params->itemAt('replyToName');
        }

        // dmarc policy...
        if (!$this->isCustomFromDomainAllowed($this->getDomainFromEmail($fromEmail))) {
            $fromEmail = $params->itemAt('username');
        }
        
        if (!FilterVarHelper::email($fromEmail)) {
            $fromEmail = $params->itemAt('from_email');
        }
        
        $replyToName  = empty($replyToName)  ? $fromName   : $replyToName;
        $replyToEmail = empty($replyToEmail) ? $fromEmail  : $replyToEmail;
        $returnEmail  = FilterVarHelper::email($params->itemAt('returnPath')) ? $params->itemAt('returnPath') : $params->itemAt('from_email');
        $returnEmail  = FilterVarHelper::email($returnEmail) ? $returnEmail : $fromEmail;
        $returnDomain = $this->getDomainFromEmail($returnEmail, 'local.host');
        
        // since 1.3.4.7
        $dkimSign = $params->itemAt('signingEnabled') && $params->itemAt('dkimPrivateKey') && $params->itemAt('dkimDomain') && $params->itemAt('dkimSelector');
        if ($dkimSign) {
            $mailer->DKIM_domain         = $params->itemAt('dkimDomain');
            $mailer->DKIM_private_string = $params->itemAt('dkimPrivateKey');
            $mailer->DKIM_selector       = $params->itemAt('dkimSelector');
        }

        $this->_message    = true;
        $mailer->MessageID = sprintf('<%s@%s>', md5(StringHelper::uniqid() . StringHelper::uniqid() . StringHelper::uniqid()), $returnDomain);
        $this->_messageId  = str_replace(array('<', '>'), '', $mailer->MessageID);

        if ($params->itemAt('headers') && is_array($params->itemAt('headers'))) {
            foreach ($params->itemAt('headers') as $header) {
                if (!is_array($header) || !isset($header['name'], $header['value'])) {
                    continue;
                }
                $mailer->addCustomHeader($header['name'], $header['value']);
            }
        }
        
        $mailer->Subject    = $params->itemAt('subject');
        $mailer->From       = $fromEmail;
        $mailer->FromName   = $fromName;

	    // since 1.7.3
	    $addReturnPath = true;
	    if (isset(Yii::app()->params['email.custom.returnPath.enabled'])) {
	    	$addReturnPath = (bool)Yii::app()->params['email.custom.returnPath.enabled'];
	    }
	    if ($addReturnPath) {
		    $mailer->ReturnPath = $returnEmail;
		    $mailer->addCustomHeader('Return-Path', $returnEmail);
	    }
	    // 
        
        // 1.3.7.1
        if ($params->itemAt('forceSender')) {
            // $mailer->Sender = $returnEmail;
        }
        
        $mailer->addAddress($toEmail, $toName);
        $mailer->addReplyTo($replyToEmail, $replyToName);
        
        $mailer->addCustomHeader('X-Sender', $returnEmail);
        $mailer->addCustomHeader('X-Receiver', $toEmail);
        $mailer->addCustomHeader(sprintf('%sMailer', Yii::app()->params['email.custom.header.prefix']), 'PHPMailer - ' . $mailer->Version);

        $body           = $params->itemAt('body');
        $plainText      = $params->itemAt('plainText');
        $onlyPlainText  = $params->itemAt('onlyPlainText') === true;

        if (empty($plainText) && !empty($body)) {
            $plainText = CampaignHelper::htmlToText($body);
        }

        if (!empty($plainText) && empty($body)) {
            $body = $plainText;
        }

        if ($onlyPlainText) {
            $mailer->Body    = $plainText;
        } else {
            $mailer->Body    = $body;
            $mailer->AltBody = $plainText;
        }

        $attachments = $params->itemAt('attachments');
        if (!$onlyPlainText && !empty($attachments) && is_array($attachments)) {
            $attachments = array_unique($attachments);
            foreach ($attachments as $attachment) {
                if (is_file($attachment)) {
                    $mailer->addAttachment($attachment);
                }
            }
            unset($attachments);
        }

        $embedImages = $params->itemAt('embedImages');
        if (!$onlyPlainText && !empty($embedImages) && is_array($embedImages)) {
            foreach ($embedImages as $imageData) {
                if (!isset($imageData['path'], $imageData['cid'])) {
                    continue;
                }
                if (is_file($imageData['path'])) {
                    $imageData['name'] = empty($imageData['name']) ? basename($imageData['path']) : $imageData['name'];
                    $imageData['mime'] = empty($imageData['mime']) ? '' : $imageData['mime'];
                    $mailer->addEmbeddedImage($imageData['path'], $imageData['cid'], $imageData['name'], 'base64', $imageData['mime']);
                }
            }
            unset($embedImages);
        }

        $mailer->XMailer = ' ';
        $mailer->isHTML($onlyPlainText ? false : true);

        // since 1.3.5.3
        $this->_mailer = Yii::app()->hooks->applyFilters('mailer_after_create_message_instance', $mailer, $params->toArray(), $this);

        return $this;
    }

    /**
     * MailerPHPMailer::getTransport()
     *
     * @return mixed
     */
    protected function getTransport()
    {
        return $this->_transport;
    }

    /**
     * MailerPHPMailer::getMessage()
     *
     * @return mixed
     */
    protected function getMessage()
    {
        return $this->_message;
    }

    /**
     * MailerPHPMailer::getMailer()
     *
     * @return mixed
     */
    protected function getMailer()
    {
        if ($this->_mailer === null) {
            $this->_mailer = new MPHPMailer();
            $this->_mailer->WordWrap    = 900;
            $this->_mailer->CharSet     = Yii::app()->charset;
            $this->_mailer->SMTPDebug   = 1;
            $this->_mailer->Debugoutput = 'logger';
            $this->_mailer->Encoding    = 'quoted-printable'; // since 1.3.6.0
            // $this->_mailer->Encoding = '8bit'; // since 1.3.6.0
            
            // 1.3.7
            if (property_exists($this->_mailer, 'SMTPOptions')) {
                $this->_mailer->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true
                    )
                );
            }

            // since 1.3.5.3
            $this->_mailer = Yii::app()->hooks->applyFilters('mailer_after_create_mailer_instance', $this->_mailer, $this->_params, $this);
        }
        return $this->_mailer;
    }

    /**
     * MailerPHPMailer::resetTransport()
     *
     * @return MailerPHPMailer
     */
    protected function resetTransport()
    {
        $this->_sentCounter = 0;
        $this->_transport = null;
        return $this;
    }

    /**
     * MailerPHPMailer::resetMessage()
     *
     * @return MailerPHPMailer
     */
    protected function resetMessage()
    {
        $this->_message = null;
        return $this;
    }

    /**
     * MailerPHPMailer::resetMailer()
     *
     * @return MailerPHPMailer
     */
    protected function resetMailer()
    {
        if (!empty($this->_mailer)) {
            $this->_mailer->smtpClose();
        }
        $this->_mailer = null;
        return $this;
    }

    /**
     * MailerPHPMailer::buildTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildTransport(CMap $params)
    {
        if (!$params->itemAt('transport')) {
            $params->add('transport', 'smtp');
        }

        if ($params->itemAt('transport') == 'smtp') {
            return $this->buildSmtpTransport($params);
        }

        if ($params->itemAt('transport') == 'php-mail') {
            return $this->buildPhpMailTransport($params);
        }

        if ($params->itemAt('transport') == 'sendmail') {
            return $this->buildSendmailTransport($params);
        }

        return false;
    }

    /**
     * MailerPHPMailer::buildSmtpTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildSmtpTransport(CMap $params)
    {
        $requiredKeys = array('hostname');
        $hasRequiredKeys = true;

        foreach ($requiredKeys as $key) {
            if (!$params->itemAt($key)) {
                $hasRequiredKeys = false;
                break;
            }
        }

        if (!$hasRequiredKeys) {
            return false;
        }

        if (!$params->itemAt('port')) {
            $params->add('port', 25);
        }

        if (!$params->itemAt('timeout')) {
            $params->add('timeout', 30);
        }

        $mailer = $this->getMailer();
        $mailer->isSMTP();
        $mailer->Host           = $params->itemAt('hostname');
        $mailer->Port           = (int)$params->itemAt('port');
        $mailer->Timeout        = (int)$params->itemAt('timeout');
        $mailer->SMTPAuth       = $params->itemAt('username') && $params->itemAt('password');
        $mailer->SMTPKeepAlive  = (int)$params->itemAt('maxConnectionMessages') > 0;

        if ($params->itemAt('username')) {
            $mailer->Username = $params->itemAt('username');
        }

        if ($params->itemAt('password')) {
            $mailer->Password = $params->itemAt('password');
        }

        if ($params->itemAt('protocol')) {
            $mailer->SMTPSecure = $params->itemAt('protocol');
        }

        return $this->_transport = $params->itemAt('transport');
    }

    /**
     * MailerPHPMailer::buildSendmailTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildSendmailTransport(CMap $params)
    {
        if (!$params->itemAt('sendmailPath') || !CommonHelper::functionExists('popen')) {
            return false;
        }

        $mailer = $this->getMailer();
        $mailer->isSendmail();
        $mailer->Sendmail = $params->itemAt('sendmailPath');

        return $this->_transport = $params->itemAt('transport');
    }

    /**
     * MailerPHPMailer::buildPhpMailTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildPhpMailTransport(CMap $params)
    {
        if (!CommonHelper::functionExists('mail')) {
            return false;
        }

        $this->getMailer()->isMail();

        return $this->_transport = $params->itemAt('transport');
    }

    /**
     * MailerPHPMailer::clearLogs()
     *
     * @return
     */
    public function clearLogs()
    {
        if ($this->getMailer()) {
            $this->getMailer()->clearLogs();
        }
        return parent::clearLogs();
    }
}
