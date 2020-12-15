<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailTemplateTagFilter
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class EmailTemplateTagFilter extends CApplicationComponent
{
    /**
     * EmailTemplateTagFilter::getFiltersMap()
     *
     * @return array
     */
    public function getFiltersMap()
    {
        return array(
            // name            // callback
            'urlencode'     => 'urlencode',
            'rawurlencode'  => 'rawurlencode',
            'htmlencode'    => array('CHtml', 'encode'),
            'trim'          => 'trim',
            'uppercase'     => 'strtoupper',
            'lowercase'     => 'strtolower',
            'ucwords'       => 'ucwords',
            'ucfirst'       => 'ucfirst',
            'reverse'       => 'strrev',
            'defaultvalue'  => array($this, 'setDefaultValueIfEmpty'),
            'defaultValue'  => array($this, 'setDefaultValueIfEmpty'),
            'md5'           => 'md5',
            'sha1'          => 'sha1',
            'base64encode'  => 'base64_encode',
        );
    }

    /**
     * EmailTemplateTagFilter::apply()
     *
     * @param mixed $content
     * @param mixed $registeredTags
     * @return string
     */
    public function apply($content, array $registeredTags)
    {
        $filtersMap = $this->getFiltersMap();

        $searchReplace = array();
        foreach ($registeredTags as $tagName => $tagValue) {

            //if (empty($tagValue)) {
            //    continue;
            //}

            $tagName = str_replace(array('[', ']'), '', $tagName);
            if (strpos($content, '['.$tagName.':filter:') === false) {
                continue;
            }

            // do we really need preg_quote ?
            if (preg_match_all('/\['.preg_quote($tagName, '/').':filter:([a-z0-9|,\(\)\s\p{L}&;#]+)\]/iu', $content, $matches)) {
                if (empty($matches[1])) {
                    continue;
                }

                $filterTags     = array_unique($matches[0]);
                $filterStrings  = array_unique($matches[1]);

                if (count($filterStrings) != count($filterTags)) {
                    continue;
                }

                $tagToFilters = array_combine($filterTags, $filterStrings);
                unset($filterTags, $filterStrings);

                foreach ($tagToFilters as $tag => $filtersString) {
                    $filters = explode('|', $filtersString);
                    if (empty($filters)) {
                        continue;
                    }

                    $filters    = array_map('trim', $filters);
                    $filtered   = false;
                    foreach ($filters as $filterName) {
                        $filterArgs = array();
                        if (($startPos = strpos($filterName, '(')) !== false && ($endPos = strpos($filterName, ')')) !== false) {
                            $name = substr($filterName, 0, $startPos);
                            $args = trim(substr($filterName, $startPos + 1), ')');
                            $filterArgs = array_map('trim', explode(',', $args));
                            $filterName = $name;
                            unset($name, $args);
                        }

                        if (!isset($filtersMap[$filterName]) || !is_callable($filtersMap[$filterName])) {
                            continue;
                        }
                        array_unshift($filterArgs, $tagValue);
                        $filtered   = true;
                        $tagValue   = call_user_func_array($filtersMap[$filterName], $filterArgs);
                    }

                    if ($filtered && !empty($tagValue)) {
                        $searchReplace[$tag] = $tagValue;
                    }
                }
            }
        }

        if (empty($searchReplace)) {
            return $content;
        }

        $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        return $content;
    }

    public function setDefaultValueIfEmpty($tagValue, $defaultValue = null)
    {
        return !empty($tagValue) ? $tagValue : $defaultValue;
    }

}
