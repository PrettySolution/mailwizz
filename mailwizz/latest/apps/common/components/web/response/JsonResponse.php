<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * JsonResponse
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

class JsonResponse extends Response
{
    /**
     * @var array the data to be json encoded
     */
    private $_data = array();
    
    /**
     * @var string the callback for jsonp, if any
     */
    private $_callback;
    
    /**
     * @var bool if the json should be prety printed
     */
    private $_prettyPrint = false;
    
    /**
     * JsonResponse::setData()
     * 
     * @param mixed $data
     * @return JsonResponse
     */
    public function setData($data = array())
    {
        if ($this->_data instanceof CMap) {
            $this->_data->mergeWith($data);
        } else {
            $this->_data = new CMap($data);
        }
        return $this;
    }
    
    /**
     * JsonResponse::getData()
     * 
     * @return CMap
     */
    public function getData()
    {
        if (!($this->_data) instanceof CMap) {
            $this->_data = new CMap();
        }
        return $this->_data;
    }
    
    /**
     * JsonResponse::addData()
     * 
     * @return
     */
    public function addData($key, $value) 
    {
        return $this->getData()->add($key, $value);
    }
    
    /**
     * JsonResponse::removeData()
     * 
     * @return
     */
    public function removeData($key) 
    {
        return $this->getData()->remove($key);
    }
    
    /**
     * JsonResponse::setCallback()
     * 
     * @param mixed $callback
     * @return JsonResponse
     * @throw InvalidArgumentException
     */
    public function setCallback($callback = null)
    {
        if ($callback !== null) {
            // taken from http://www.geekality.net/2011/08/03/valid-javascript-identifier/
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*+$/u';
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part)) {
                    throw new InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $this->_callback = $callback;
        
        return $this;
    }
    
    /**
     * JsonResponse::getCallback()
     * 
     * @return string
     */
    public function getCallback()
    {
        return $this->_callback;
    }
    
    public function setPrettyPrint($bool)
    {
        $this->_prettyPrint = $bool;
        return $this;
    }
    
    public function getPrettyPrint()
    {
        return $this->_prettyPrint;
    }
    
    public function send()
    {
        if ($this->getCallback() !== null) {
            $this->getHeaders()->add('Content-Type', 'text/javascript');
            $this->setContent(sprintf('%s(%s);', $this->getCallback(), CJSON::encode($this->getData()->toArray())));
        } else {
            $this->getHeaders()->add('Content-Type', 'application/json');
            $json = CJSON::encode($this->getData()->toArray());
            if ($this->getPrettyPrint()) {
                $json = $this->pretty($json);
            }
            $this->setContent($json);
        }
        
        return parent::send();
    }
    
    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param string $json The original JSON string to process.
     * @return string Indented version of the original JSON string.
     * @author http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
     * @link http://www.daveperrett.com/articles/2008/03/11/format-json-with-php/
     */
    public function pretty($json) 
    {
    
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;
    
        for ($i=0; $i<=$strLen; $i++) {
    
            // Grab the next character in the string.
            $char = substr($json, $i, 1);
    
            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
    
            // If this character is the end of an element,
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }
    
            // Add the character to the result string.
            $result .= $char;
    
            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
    
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
    
            $prevChar = $char;
        }
    
        return $result;
    }
}