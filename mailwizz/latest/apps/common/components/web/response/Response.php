<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Response
 * 
 * This class is inspired a bit from Sympfony's HttpFoundation package.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class Response extends CComponent
{
    /**
     * @var CMap
     */
    private $_headers;
    
    /**
     * @var string the content to be shown 
     */
    private $_content;
    
    /**
     * @var HTTP Protocol version
     */
    private $_version = '1.0';
    
    /**
     * @var HTTP status code
     */
    private $_statusCode = 200;
    
    /**
     * @var HTTP status text
     */
    private $_statusText;
    
    /**
     * @var the response charset
     */
    private $_charset = 'UTF-8';
    
    /**
     * @var the content type of the response
     */
    private $_contentType = 'text/html';
    
    /**
     * @var list of HTTP codes and their text messages
     */
    public static $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC-reschke-http-status-308-07
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    );

    /**
     * Response::sendHeaders()
     * 
     * @return Response
     */
    protected function sendHeaders()
    {
        // if headers already sent, stop
        if (headers_sent()) {
            return $this;
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->getVersion(), $this->getStatusCode(), $this->getStatusText()));
        
        $headers = $this->getHeaders();

        if(!$headers->contains('Date')) {
            $headers->add('Date', date('D, d M Y H:i:s', time()) . 'GMT');
        }
        
        if (!$headers->contains('Content-Type')) {
            $headers->add('Content-Type', $this->getContentType());
        }
        
        if (strpos($headers->itemAt('Content-Type'), 'charset') === false) {
            $headers->add('Content-Type', $headers->itemAt('Content-Type').'; charset='.$this->getCharset());
        }

        // fix Content-Length
        if ($headers->contains('Transfer-Encoding')) {
            $headers->remove('Content-Length');
        }
        
        // set all headers
        foreach ($headers as $name => $value) {
            header($name.': '.$value, false);
        }
        
        return $this;
    }
    
    /**
     * Response::sendContent()
     * 
     * @return Response
     */
    protected function sendContent()
    {
        echo $this->getContent();
        return $this;
    }
    
    /**
     * Response::send()
     * 
     * @return
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
        
        if (CommonHelper::functionExists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            $previous = null;
            $obStatus = ob_get_status(1);
            while (($level = ob_get_level()) > 0 && $level !== $previous) {
                $previous = $level;
                if ($obStatus[$level - 1] && isset($obStatus[$level - 1]['del']) && $obStatus[$level - 1]['del']) {
                    ob_end_flush();
                }
            }
            flush();
        }

        return $this;
    }
    
    /**
     * Response::setHeaders()
     * 
     * @param mixed $headers
     * @return Response
     */
    public function setHeaders(array $headers = array())
    {
        if ($this->_headers instanceof CMap) {
            $headers = CMap::mergeArray($this->_headers->toArray(), $headers);
        }
        $this->_headers = new CMap($headers);
        return $this;
    }
    
    /**
     * Response::getHeaders()
     * 
     * @return CMap
     */
    public function getHeaders()
    {
        if (!($this->_headers instanceof CMap)) {
            $this->_headers = new CMap();
        }
        return $this->_headers;
    }
    
    /**
     * Response::resetHeaders()
     * 
     * @return CMap
     */
    public function resetHeaders()
    {
        $this->_headers = new CMap();
    }
    
    /**
     * Response::setContent()
     * 
     * @param mixed $content
     * @return Response
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }
    
    /**
     * Response::getContent()
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }
    
    /**
     * Response::setVersion()
     * 
     * @param mixed $version
     * @return Response
     */
    public function setVersion($version)
    {
        $this->_version = $version;
        return $this;
    }
    
    /**
     * Response::getVersion()
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }
    
    /**
     * Response::setStatusCode()
     * 
     * @param mixed $code
     * @param mixed $text
     * @return Response
     */
    public function setStatusCode($code, $text = null)
    {
        $this->_statusCode = $code = (int)$code;

        if ($text === null) {
            $this->setStatusText(isset(self::$statusTexts[$code]) ? self::$statusTexts[$code] : '');
            return $this;
        }

        if ($text === false) {
            $this->setStatusText('');
            return $this;
        }

        $this->setStatusText($text);
        
        return $this;
    }
    
    /**
     * Response::getStatusCode()
     * 
     * @return
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
    
    /**
     * Response::setStatusText()
     * 
     * @param mixed $text
     * @return Response
     */
    public function setStatusText($text)
    {
        $this->_statusText = $text;
        return $this;
    }
    
    /**
     * Response::getStatusText()
     * 
     * @return string
     */
    public function getStatusText()
    {
        return $this->_statusText;
    }
    
    /**
     * Response::setCharset()
     * 
     * @param mixed $charset
     * @return Response
     */
    public function setCharset($charset)
    {
        $this->_charset = strtoupper($charset);
        return $this;
    }
    
    /**
     * Response::getCharset()
     * 
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }
    
    /**
     * Response::setContentType()
     * 
     * @param mixed $contentType
     * @return Response
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;
        return $this;
    }
    
    /**
     * Response::getContentType()
     * 
     * @return string
     */
    public function getContentType()
    {
        return $this->_contentType;
    }
}