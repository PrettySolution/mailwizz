<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * StringHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class StringHelper
{
    /**
     * StringHelper::simpleCamelCase()
     *
     * @param string $str
     * @param bool $startUppercase
     * @return string
     */
    public static function simpleCamelCase($str, $startUppercase = true)
    {
        $str = str_replace(array('_','-'), ' ', $str);
        $str = ucwords($str);
        $str = str_replace(' ', '', $str);
        $str[0] = $startUppercase ? strtoupper($str[0]) : strtolower($str[0]);

        return $str;
    }

    /**
     * StringHelper::isEqual()
     *
     * @param string $compare
     * @param string $against
     * @return bool
     */
    public static function isEqual($compare, $against)
    {
        if ($compare !== $against) {
            return false;
        }

        $lengthFunction = CommonHelper::functionExists('mb_strlen') ? 'mb_strlen' : 'strlen';

        if ($lengthFunction($compare) !== $lengthFunction($against)) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $lengthFunction($compare); $i++) {
            $result |= ord($compare[$i]) ^ ord($against[$i]);
        }

        return $result == 0;
    }

    /**
     * StringHelper::random()
     *
     * @param integer $length
     * @param bool $lowerCaseOnly
     * @param bool $lettersOnly
     * @param bool $numbersOnly
     * @return string
     */
    public static function random($length = 13, $lowerCaseOnly = false, $lettersOnly = false, $numbersOnly = false)
    {
        $pool = '';

        if (!$lettersOnly || $numbersOnly) {
            $pool .= '0123456789';
        }

        if (!$numbersOnly) {
            $pool .= 'abcdefghjklmnopqrstvwxyz';
        }

        if (!$lowerCaseOnly && !$numbersOnly) {
            $pool .= 'ABCDEFGHJKLMNOPQRSTVWXYZ';
        }

        if (empty($pool)) {
            $pool = '0123456789abcdefghjklmnopqrstvwxyzABCDEFGHJKLMNOPQRSTVWXYZ';
        }

        $str = '';
        for ($i=0; $i < $length; $i++) {
            $str .= substr($pool, rand(0, strlen($pool) -1), 1);
        }
        return $str;
    }

    /**
     * StringHelper::truncateLength()
     *
     * @param string $string
     * @param integer $length
     * @param string $elipse
     * @return string
     */
    public static function truncateLength($string, $length = 100, $elipse = '...')
    {
        $length = (int)$length;
        $string = strip_tags(CHtml::decode($string));
        $strlen = CommonHelper::functionExists('mb_strlen') ? 'mb_strlen' : 'strlen';
        $substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
        if ($strlen($string) - $strlen($elipse) >= $length) {
            return $substr($string, 0, $length) . $elipse;
        }
        return $string;
    }

    /**
     * StringHelper::getTagFromString()
     *
     * @param string $string
     * @return string
     */
    public static function getTagFromString($string)
    {
        $substr = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
        $tagName = $substr($string, 0, 50);
        $tagName = preg_replace('/[^a-z0-9\s_]/six', '', $string);
        $tagName = preg_replace('/\s{2,}/', ' ', $tagName);
        $tagName = preg_replace('/_{2,}/', ' ', $tagName);
        $tagName = strtoupper($tagName);
        $tagName = str_replace('  ', ' ', $tagName);
        $tagName = str_replace(' ', '_', $tagName);
        $tagName = trim($tagName, ' _');

        return $tagName;
    }

    public static function getTagParams($tag)
    {
        $params = array();
        $tag = trim(CHtml::decode($tag));
        if (preg_match_all('/([a-z]+)=(\'|")([a-z0-9\-_\/\\\]+)(\'|")/i', $tag, $matches)) {
            if (isset($matches[1], $matches[3])) {
                $params[$matches[1][0]] = $matches[3][0];
            }
        }
        return $params;
    }

    /**
     * StringHelper::uniqid()
     *
     * I had a few collisions with uniqid() and decided to create my own, hopefully a bit better.
     *
     * @param mixed $prefix
     * @param bool $moreEntropy
     * @return string
     */
    public static function uniqid($prefix = null, $moreEntropy = false)
    {
        $uniqid = self::random(2, true, true) . substr(uniqid('', true), -3). self::random(5, true) . substr(uniqid(), -3);

        if (!empty($prefix)) {
            $uniqid = $prefix . $uniqid;
        }

        if (!empty($moreEntropy)) {
            $uniqid .= '.' . self::random(12, false, false, true);
        }

        return $uniqid;
    }

    /**
     * StringHelper::fixFileEncoding()
     *
     * @param mixed $filePath
     * @return mixed
     */
    public static function fixFileEncoding($filePath)
    {
        if (!is_file($filePath)) {
            return false;
        }

        if (!($handle = @fopen($filePath, 'r'))) {
            return false;
        }

        $sample = '';
        $line = 1;
        while (($buffer = @fgets($handle, 4096)) !== false && $line < 500) {
            $sample .= $buffer;
            $line++;
        }
        fclose($handle);

        // is utf-8 check 1
        if (CommonHelper::functionExists('mb_check_encoding') && mb_check_encoding($sample, 'UTF-8')) {
            return true;
        }

        // is utf-8 check 2
        if (self::isUtf8($sample)) {
            return true;
        }
        
        if (!CommonHelper::functionExists('mb_detect_encoding')) {
            return false;
        }
        
        $encodingList = array(
            "UTF-8", "UTF-32", "UTF-32BE", "UTF-32LE",
            "UTF-16", "UTF-16BE", "UTF-16LE", "ISO-8859-1", "WINDOWS-1252", "ASCII"
        );

        $encoding = mb_detect_encoding($sample, $encodingList, true);
        if ($encoding === 'UTF-8') {
            return true;
        }

        if (empty($encoding)) {
            return false; // what to do here?
        }

        if (!CommonHelper::functionExists('mb_convert_encoding')) {
            return false;
        }
        
        if (!is_writable($filePath)) {
            return false;
        }

        if (!($input = @file_get_contents($filePath))) {
            return false;
        }

        $input = mb_convert_encoding($input, "UTF-8", $encoding);
        return @file_put_contents($filePath, $input);
    }

    /**
     * StringHelper::isUtf8()
     *
     * @param mixed $string
     * @return bool
     */
    public static function isUtf8($string)
    {
        // http://www.php.net/manual/en/function.mb-detect-encoding.php#68607
        return preg_match('%(?:
            [\xC2-\xDF][\x80-\xBF]                      # non-overlong 2-byte
            |\xE0[\xA0-\xBF][\x80-\xBF]                 # excluding overlongs
            |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}          # straight 3-byte
            |\xED[\x80-\x9F][\x80-\xBF]                 # excluding surrogates
            |\xF0[\x90-\xBF][\x80-\xBF]{2}              # planes 1-3
            |[\xF1-\xF3][\x80-\xBF]{3}                  # planes 4-15
            |\xF4[\x80-\x8F][\x80-\xBF]{2}              # plane 16
            )+%xs', $string);
    }

    /**
     * StringHelper::decodeSurroundingTags()
     *
     * @param mixed $content
     * @return string
     */
    public static function decodeSurroundingTags($content)
    {
        return str_replace(
            array(urlencode('['), urlencode(']'), urlencode('|'), urlencode('{'), urlencode('}')), 
            array('[', ']', '|', '{', '}'), 
            $content
        );
    }

    /**
     * StringHelper::normalizeTranslationString()
     *
     * @since 1.1
     * @param string $str
     * @return string
     */
    public static function normalizeTranslationString($str)
    {
        $str = trim(str_replace(array("\r\n", "\n", "\t", "\r"), ' ', $str));
        return preg_replace('/\s{1,}/', ' ', $str);
    }

    /**
     * StringHelper::normalizeUrl()
     *
     * @param string $url
     * @return string
     */
    public static function normalizeUrl($url)
    {
        // if it has a query string 
        if (
        	strpos($url, '?')   !== false || strpos($url, '&') !== false || 
	        strpos($url, '%3F') !== false || strpos($url, '%26') !== false
        ) {
            $replacements = array(
                '&amp;'  => '&', 
                '%5B'    => '[',
                '%5D'    => ']',
                '%26'    => '&',
                '%3D'    => '=',
                '%3F'    => '?',
                '%7C'    => '|',
                ' '      => '%20',
                '&utm;_' => '&utm_',
            );
            $url = str_replace(array_keys($replacements), array_values($replacements), $url);
        }
        return $url;
    }

    /**
     * StringHelper::normalizeUrlsInContent()
     * 
     * @param $content
     * @return mixed
     */
    public static function normalizeUrlsInContent($content)
    {
        $pattern = '/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i';
        if (!preg_match_all($pattern, $content, $matches)) {
            return $content;
        }
        
        $matches = array_unique($matches[0]);
        $searchReplace = array();
        
        foreach ($matches as $match) {
            $normalized = self::normalizeUrl($match);
            if ($normalized != $match) {
                $searchReplace[$match] = $normalized;
            }
        }
        
        return str_ireplace(array_keys($searchReplace), array_values($searchReplace), $content);
    }

    /**
     * Translates a camel case string into a string with
     * underscores (e.g. firstName -> first_name)
     * http://paulferrett.com/2009/php-camel-case-functions/
     *
     * @param string $str String in camel case format
     * @return string $str Translated into underscore format
     */
    public static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        return preg_replace_callback('/([A-Z])/', array('StringHelper', '_fromCamelCaseCb'), $str);
    }

    /**
     * @param $c
     * @return string
     */
    public function _fromCamelCaseCb($c)
    {
        return "_" . strtolower($c[1]);
    }

    /**
     * Translates a string with underscores
     * into camel case (e.g. first_name -> firstName)
     * http://paulferrett.com/2009/php-camel-case-functions/
     *
     * @param string $str String in underscore format
     * @param bool $capitalise_first_char If true, capitalise the first char in $str
     * @return string $str translated into camel caps
     */
    public static function toCamelCase($str, $capitalise_first_char = false)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        }
        return preg_replace_callback('/_([a-z])/', array('StringHelper', '_toCamelCaseCb'), $str);
    }

    /**
     * @param $c
     * @return string
     */
    public function _toCamelCaseCb($c) 
    {
        return strtoupper($c[1]);
    }

    /**
     * @param $csvFile
     * @return mixed|void
     * 
     * https://coderwall.com/p/qsldcq/php-csv-autodetect-delimiter
     */
    public static function detectCsvDelimiter($csvFile)
    {
        $default = ',';
        
        if (empty($csvFile) || !is_file($csvFile)) {
            return $default;
        }
        
        if (!CommonHelper::functionExists('str_getcsv')) {
            return $default;
        }
        
        $delimiters = array(
            ';'  => 0,
            ','  => 0,
            "\t" => 0,
            "|"  => 0,
        );

        if (!($fp = @fopen($csvFile, "r"))) {
            return $default;
        }
        $firstLine = fgets($fp);
        fclose($fp);
        
        if (empty($firstLine)) {
            return $default;
        }
        
        foreach ($delimiters as $delimiter => $count) {
            $delimiters[$delimiter] = count(str_getcsv($firstLine, $delimiter));
        }
        
        if (max(array_values($delimiters)) == 0) {
            return $default;
        }

        return array_search(max($delimiters), $delimiters);
    }

    /**
     * @param $email
     * @return string
     */
    public static function maskEmailAddress($email)
    {
        if (stripos($email, '@') === false) {
            return $email;
        }
        list($name, $domain) = explode('@', $email);
        
        // name
        $name   = str_split($name);
        $length = count($name);
        foreach ($name as $index => $letter) {
            if (!in_array($index, array(0, $length-1))) {
                $name[$index] = '*';
            }
        }
        $name = implode('', $name);
        if (strlen($name) == 1) {
            $name = '*';
        }

        // domain tld
        $domain = explode('.', $domain);
        $tld    = array_pop($domain);
        $domain = implode('.', $domain);
        
        // domain name
        $domain = str_split($domain);
        $length = count($domain);
        foreach ($domain as $index => $letter) {
            if (!in_array($index, array(0, $length-1))) {
                $domain[$index] = '*';
            }
        }
        $domain = implode('', $domain);
        if (strlen($domain) == 1) {
            $domain = '*';
        }
        
        return $name . '@' . ($domain . '.' . $tld);
    }
    
    /**
     * @param $text
     * @return mixed
     */
    public static function remove4BytesChars($text) 
    {
        $text = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);
        
        return $text;
    }

    /**
     * @param $number
     * @return string
     */
    public static function asPercentFloat($number)
    {
        return (round((float)str_replace(',', '.', $number), 2)) . '%';
    }

    /**
     * @param $string
     * @return bool
     */
    public static function isMd5($string)
    {
        return (bool)preg_match('/^[a-f0-9]{32}$/', $string);
    }

    /**
     * @param $string
     * @return string
     */
    public static function md5Once($string)
    {
        return self::isMd5($string) ? (string)$string : md5($string);
    }

    /**
     * @param $string
     * @return bool
     */
    public static function isSha1($string)
    {
        return (bool)preg_match('/^[a-f0-9]{40}$/', $string);
    }

    /**
     * @param $string
     * @return string
     */
    public static function sha1Once($string)
    {
        return self::isSha1($string) ? (string)$string : sha1($string);
    }
}
