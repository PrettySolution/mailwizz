<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 *
 * EmailTemplateParser
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class EmailTemplateParser extends CApplicationComponent
{
    private $_isParsed = false;

    private $_content;

    private $_documentMap;

    private $_newHtml;

    private $_providedHtml;
    
    // 1.3.6.5
    public $tidyOptions = array();

    // experimental, don't rely on it, removable in future!
    public $keepBodyConditionalTags = true;

    /**
     * EmailTemplateParser::setContent()
     *
     * @param mixed $content
     * @return EmailTemplateParser
     */
    public function setContent($content)
    {
        $this->_isParsed = false;
        $this->_content = $content;
        return $this;
    }

    /**
     * EmailTemplateParser::getContent()
     *
     * @return mixed
     */
    public function getContent()
    {
        if ($this->_isParsed) {
            return $this->_content;
        }

        if (empty($this->_content)) {
            $this->_isParsed = true;
            return $this->_content = null;
        }

        $ioFilter = Yii::app()->ioFilter;

        // decode, disabled in 1.3.4.7
        // $this->_content = CHtml::decode($this->_content);

        // 1.3.6.9 - remove empty comments before
        $this->_content = preg_replace('/<![\s\-]+>/', '', $this->_content);
        
        // 1.3.7 - no script/cdata
        $this->_content = preg_replace('/<script(.*?)<\/script>/six', '', $this->_content);
        $this->_content = preg_replace('/<!\[CDATA\[(.*?)\]\]>/six', '', $this->_content);
        
        // since 1.3.6.5
        $tidyEnabled = Yii::app()->params['email.templates.tidy.enabled'];
        $tidyEnabled = $tidyEnabled && Yii::app()->options->get('system.common.use_tidy', 'yes') == 'yes';
        if ($tidyEnabled && class_exists('tidy', false)) {
            $tidy    = new tidy();
            $options = CMap::mergeArray(Yii::app()->params['email.templates.tidy.options'], $this->tidyOptions);
            $tidy->parseString($this->_content, $options, 'utf8');
            if ($tidy->cleanRepair()) {
                $this->_content = $tidy->html()->value;
            }
        }
        //

        // remove invisible chars
        $this->_content = $this->removeInvisibleCharacters($this->_content);

        // compact words if there's the case for this
        $this->_content = $this->compactWords($this->_content);

        // remove attributes like onclick|onload|etc. (042 and 047 are octal quotes)
        $this->_content = preg_replace('#\bon\w*\s*=\s*(\042|\047)([^\\1]*?)(\\1)([^\w]+)?#six', '', $this->_content);

        // some expressions never allowed
        $notAllowedRegex = array(
            'javascript\s*:', 'expression\s*(\(|&\#40;)', 'vbscript\s*:', 'Redirect\s+302',
            "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
        );

        foreach ($notAllowedRegex as $regex) {
            $this->_content = preg_replace('#'.$regex.'#is', '', $this->_content);
        }

        // some strings never allowed
        $notAllowedStrings = array(
            'document.cookie', 'document.write', '.parentNode', '.innerHTML',
            'window.location', '-moz-binding', '<![CDATA[', '<comment>'
        );

        $this->_content = str_replace($notAllowedStrings, '', $this->_content);

        if (empty($this->_content)) {
            $this->_isParsed = true;
            return $this->_content = null;
        }

        $content = $this->_content;

        // if no body element, goodbye.
        preg_match('/<body[^>]*>(.*?)<\/body>/si', $content, $matches);
        if (empty($matches[1])) {
            $this->_isParsed = true;
            return $this->_content = null;
        }

        $conditionalPatternMatch   = '/<!--\[if\s(?:[^<]+|<(?!!\[endif\]-->))*<!\[endif\]-->/six';
        $conditionalPatternReplace = '/(<!--\[[^\]]+\]>)([^\]]+)?(<!\[[^\]]+\]-->)/six';

        if ($this->keepBodyConditionalTags) {
            $_bodyComments = array();
            preg_match_all($conditionalPatternMatch, $matches[1], $_matches);
            if (!empty($_matches[0])) {
                foreach ($_matches[0] as $_comment) {
                    if (!preg_match('/<!--\[if(.*)?\]>(.*)<!\[endif\]-->/six', $_comment, $__matches)) {
                        continue;
                    }
                    $__comment = !empty($__matches[2]) ? $__matches[2] : null;
                    if (!$__comment) {
                        continue;
                    }
                    // 1.3.6.7
                    $styleReplacementMap = array();
                    if (preg_match_all('/style="(.*?)"/', $__comment, $styleMatches)) {
                        foreach ($styleMatches[1] as $styleStr) {
                            $styleReplacementMap['#' . sha1(uniqid(rand(0, time()), true)) . '#'] = $ioFilter->xssClean($styleStr);
                        }
                    }
                    if (!empty($styleReplacementMap)) {
                        $styleReplacementMapTemp = array();
                        foreach ($styleReplacementMap as $key => $value) {
                            $styleReplacementMapTemp['style="'. $value .'"'] = 'style="'.$key.'"';
                        }
                        $_comment = str_replace(array_keys($styleReplacementMapTemp), array_values($styleReplacementMapTemp), $_comment);
                        unset($styleReplacementMapTemp);
                    }
                    //
                    
                    $_comment = str_replace($__comment, $ioFilter->xssClean($__comment), $_comment);
                    
                    // 1.3.6.7
                    if (!empty($styleReplacementMap)) {
                        $_comment = str_replace(array_keys($styleReplacementMap), array_values($styleReplacementMap), $_comment);
                    }
                    
                    $_key = '|' . sha1(uniqid(rand(0, time()), true)) . '|';
                    $_bodyComments[$_key] = $_comment;
                }
                if (!empty($_bodyComments)) {
                    $matches[1] = str_replace($_matches[0], array_keys($_bodyComments), $matches[1]);
                }
            }
        }
        
        // 1.3.6.1
        preg_match_all('/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i', $matches[1], $urls);
        $urls  = !empty($urls[5]) ? $urls[5] : array();
        $hrefs = array();
        foreach ($urls as $url) {
            if (!preg_match('/\[([A-Z0-9\_]+)\]/i', $url)) {
                continue;
            }    
            $hrefs[$url] = '#' . sha1($url);
        }
        if (!empty($hrefs)) {
            $matches[1] = str_replace(array_keys($hrefs), array_values($hrefs), $matches[1]);
        }
        //
        
        $matches[1] = $ioFilter->purify($matches[1]);
        
        // 1.3.6.1
        if (!empty($hrefs)) {
            $matches[1] = str_replace(array_values($hrefs), array_keys($hrefs), $matches[1]);
            unset($hrefs);
        }
        //
        
        if ($this->keepBodyConditionalTags && !empty($_bodyComments)) {
            $matches[1] = str_replace(array_keys($_bodyComments), array_values($_bodyComments), $matches[1]);
        }

        $this->_documentMap = new CMap(array(
            'head'              => null,
            'metaTags'          => null,
            'css'               => null,
            'conditionalCss'    => null,
            'body'              => $this->decodeSurroundingTags(trim($matches[1])),
        ));

        $_cssBlock  = '';
        $cssBlock   = '';
        $conditionalCssBlock = '';

        // conditional tags?
        $head = null;
        preg_match('/<head[^>]*>(.*?)<\/head>/si', $content, $matches);
        if (!empty($matches[1])) {
            $head = $matches[1];
        }
        
        // 1.3.6.7
        if (preg_match_all($conditionalPatternMatch, $head, $matches)) {
            foreach ($matches[0] as $index => $condition) {
                $condition = preg_replace_callback('/<style[^>]*>(.*?)<\/style>/six', array($this, 'replaceInConditionalBlock'), $condition);
                $conditionalCssBlock .= $condition . "\n";
            }
        }
        //
        
        $this->_documentMap->add('conditionalCss', $conditionalCssBlock);

        // remove the conditional tags now
        $content = preg_replace($conditionalPatternReplace, '', $content);

        // extract all the styles from the now lighter content.
        preg_match_all('/<style[^>]*>(.*?)<\/style>/six', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $plainCss) {
                $cssBlock .= trim($plainCss) . "\n";
            }
            $cssBlock = trim($this->encode($ioFilter->purify($cssBlock)));
            $_cssBlock = $cssBlock;
            $cssBlock = "\n" . '<style type="text/css">' . "\n" . $cssBlock . "\n" . '</style>' . "\n";
        }

        $this->_documentMap->add('css', $cssBlock);

        $stub = '<!DOCTYPE html>
        <html>
            <head>

            </head>
            <body>

            </body>
        </html>';

        require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        $this->_newHtml = qp($stub, null, array(
            'ignore_parser_warnings'    => true,
            'convert_to_encoding'       => Yii::app()->charset,
            'convert_from_encoding'     => Yii::app()->charset,
            'use_parser'                => 'html',
        ));

        $this->_newHtml->top()->find('head')->append($this->_documentMap->itemAt('css'));
        $this->_newHtml->top()->find('head')->append($this->_documentMap->itemAt('conditionalCss'));
        $this->_newHtml->top()->find('body')->append($this->_documentMap->itemAt('body'));

        libxml_use_internal_errors(true);
        $this->_providedHtml = qp($this->_content, null, array(
            'ignore_parser_warnings'    => true,
            'convert_to_encoding'       => Yii::app()->charset,
            'convert_from_encoding'     => Yii::app()->charset,
            'use_parser'                => 'html',
        ));

        // to do: what action should we take here?
        if (count(libxml_get_errors()) > 0) {

        }

        $body = $this->_providedHtml->top()->find('body');
        if ($body->length == 1) {
            $bodyAttributes = $body->attr();
            if (!empty($bodyAttributes)) {
                foreach ($bodyAttributes as $name => $value) {
                    unset($bodyAttributes[$name]);
                    if (stripos($name, 'on') === 0 || stripos($value, 'javascript') !== false) {
                        continue;
                    }
                    $bodyAttributes[CHtml::encode($name)] = CHtml::encode($value);
                }
                $this->_newHtml->top()->find('body')->attr($bodyAttributes);
            }
        }

        $head = $this->_providedHtml->top()->find('head');
        if ($head->length == 1) {
            $metaTags = $this->_providedHtml->top()->find('head')->find('meta');
            if ($metaTags->length > 0) {
                foreach ($metaTags as $metaTag) {
                    $attributes = $metaTag->attr();
                    if (!empty($attributes)) {
                        foreach ($attributes as $name => $value) {
                            $metaTag->attr(CHtml::encode($name), CHtml::encode($value));
                        }
                        $this->_newHtml->top()->find('head')->prepend($metaTag);
                    }
                }
            }
            
            // 1.3.6.7
            $linkTags = $this->_providedHtml->top()->find('head')->find('link');
            if ($linkTags->length > 0) {
                foreach ($linkTags as $linkTag) {
                    $attributes = $linkTag->attr();
                    if (!empty($attributes)) {
                        foreach ($attributes as $name => $value) {
                            $linkTag->attr(CHtml::encode($name), CHtml::encode($value));
                        }
                        $this->_newHtml->top()->find('head')->prepend($linkTag);
                    }
                }
            }
            
            $headAttributes = $this->_providedHtml->top()->find('head')->attr();
            if (!empty($headAttributes)) {
                foreach ($headAttributes as $name => $value) {
                    unset($headAttributes[$name]);
                    if (stripos($name, 'on') === 0 || stripos($value, 'javascript') !== false) {
                        continue;
                    }
                    $headAttributes[CHtml::encode($name)] = CHtml::encode($value);
                }
                $this->_newHtml->top()->find('head')->attr($headAttributes);
            }
            
            $htmlAttributes = $this->_providedHtml->top()->find('html');
            $htmlAttributes = $htmlAttributes->length ? $htmlAttributes->attr(): array();
            if (!empty($htmlAttributes)) {
                foreach ($htmlAttributes as $name => $value) {
                    unset($htmlAttributes[$name]);
                    if (stripos($name, 'on') === 0 || stripos($value, 'javascript') !== false) {
                        continue;
                    }
                    $htmlAttributes[CHtml::encode($name)] = CHtml::encode($value);
                }
                $this->_newHtml->top()->find('html')->attr($htmlAttributes);
            }
            //
        }

        $charsetMeta = $this->_newHtml->top()->find('head meta[http-equiv="Content-Type"]');
        if ($charsetMeta->length > 0) {
            $charsetMeta->remove(); // this can cause problems!
        }

        $charsetMeta = $this->_newHtml->top()->find('head')->find('meta[name=charset]');
        if ($charsetMeta->length == 1) {
            $charsetMeta->attr('content', Yii::app()->charset);
        } else {
            $this->_newHtml->top()->find('head')->prepend(CHtml::metaTag(Yii::app()->charset, 'charset'));
        }

        $title = $this->_providedHtml->top()->find('head title');
        if ($title->length > 0) {
            $titleText    = $title->text();
            $decodedTitle = utf8_decode($titleText);
            $titleText    = !empty($decodedTitle) ? $decodedTitle : $titleText;
            $this->_newHtml->top()->find('head')->append('<title>'.CHtml::encode($titleText).'</title>');
        } else {
            $this->_newHtml->top()->find('head')->append('<title>Untitled</title>');
        }

        $finalSearchReplace = array(
            '&gt;'       => '>',
            'url(&quot;' => 'url(',
            '&quot;)'    => ')',
            'url("'      => 'url(',
            "url('"      => 'url(',
            '")'         => ')',
            "')"         => ')'
        );
        
        // 1.3.6.7, the doctype
        if (preg_match('/<!DOCTYPE(.*)?>/i', $this->_providedHtml->top()->html(), $_matches)) {
            $finalSearchReplace['<!DOCTYPE html>'] = $_matches[0];
        }
        //
        
        // 1.3.6.7 - search for background image and add it as an attribute
        $backgroundElements = array('table', 'td', 'div', 'p', 'span', 'body');
        foreach ($backgroundElements as $elem) {
            $instances = $this->_newHtml->top()->find($elem);
            if ($instances->length == 0) {
                continue;
            }
            foreach ($instances as $instance) {
                if (!($instance->attr('style') && stripos($instance->attr('style'), 'background') !== false)) {
                    continue;
                }
                if (!preg_match('/background(-image)?\s*:\s*url\((.*?)\)/six', $instance->attr('style'), $matches)) {
                    continue;
                }
                $url = str_replace(array('"', "'"), '', $matches[2]);
                if (!FilterVarHelper::url($url)) {
                    continue;
                }
                $instance->attr('background', $url);
            }
        }
        //
        
        $this->_isParsed = true;
        $this->_content = $this->_newHtml->top()->html();
        $this->_content = $this->decodeSurroundingTags($this->_content);
        $this->_content = str_replace(array_keys($finalSearchReplace), array_values($finalSearchReplace), $this->_content);
        libxml_use_internal_errors(false);
        return $this->_content;
    }

    /**
     * EmailTemplateParser::getDocumentMap()
     *
     * @return CMap $documentMap
     */
    public function getDocumentMap()
    {
        return $this->_documentMap;
    }

    /**
     * EmailTemplateParser::getNewHtml()
     *
     * @return QueryPath $newHtml
     */
    public function getNewHtml()
    {
        return $this->_newHtml;
    }

    /**
     * EmailTemplateParser::getProvidedHtml()
     *
     * @return QueryPath $providedHtml
     */
    public function getProvidedHtml()
    {
        return $this->_providedHtml;
    }
    
    /**
     * EmailTemplateParser::replaceInConditionalBlock()
     *
     * @param mixed $matches
     * @return string
     */
    public function replaceInConditionalBlock($matches)
    {
        if (empty($matches[1])) {
            return '';
        }
        // $cssBlock = $this->encode(Yii::app()->ioFilter->purify($matches[1]));
        $cssBlock = $this->encode($matches[1]);
        return '<style type="text/css">' . "\n". trim($cssBlock) ."\n". '</style>';
    }

    /**
     * EmailTemplateParser::decodeSurroundingTags()
     *
     * @param mixed $content
     * @return string
     */
    protected function decodeSurroundingTags($content)
    {
        return StringHelper::decodeSurroundingTags($content);
    }

    /**
     * EmailTemplateParser::compactWords()
     *
     * Credits to CI's Security class.
     *
     * @param mixed $str
     * @return string
     */
    protected function compactWords($str)
    {
        $words = array(
            'javascript', 'expression', 'vbscript', 'script', 'base64',
            'applet', 'alert', 'document', 'write', 'cookie', 'window',
            'style', 'link', 'meta'
        );

        foreach ($words as $word) {
            $temp = '';

            for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++) {
                $temp .= substr($word, $i, 1)."\s*";
            }

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', array($this, 'compactExplodedWords'), $str);
        }

        return $str;
    }

    /**
     * EmailTemplateParser::compactExplodedWords()
     *
     * Credits to CI's Security class.
     *
     * @param mixed $matches
     * @return string
     */
    protected function compactExplodedWords($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
    }

    /**
     * EmailTemplateParser::removeInvisibleCharacters()
     *
     * Credits to CI's Security class.
     *
     * @param mixed $str
     * @param bool $url_encoded
     * @return string
     */
    protected function removeInvisibleCharacters($str, $url_encoded = TRUE)
    {
        $non_displayables = array();

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/';    // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/';    // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        }
        while ($count);

        return $str;
    }

    /**
     * EmailTemplateParser::encode()
     *
     * @param mixed $text
     * @return string
     */
    protected function encode($text)
    {
        return htmlspecialchars($text, ENT_NOQUOTES, Yii::app()->charset);
    }

    /**
     * EmailTemplateParser::parseMediaQueriesIntoArray()
     *
     * @param string $css
     * @return array
     */
    protected function parseMediaQueriesIntoArray($css)
    {
        $blocks = array();
        $start = 0;
        while (($start = strpos($css, "@media", $start)) !== false) {
            $s = array();
            $i = strpos($css, "{", $start);
            if ($i !== false) {
                array_push($s, $css[$i]);
                $i++;
                while (!empty($s)) {
                    if ($css[$i] == "{") {
                        array_push($s, "{");
                    } elseif ($css[$i] == "}") {
                        array_pop($s);
                    }
                    $i++;
                }
                $blocks[] = substr($css, $start, ($i + 1) - $start);
                $start = $i;
            }
        }
        return $blocks;
    }
}
