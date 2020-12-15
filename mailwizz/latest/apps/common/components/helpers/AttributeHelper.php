<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AttributeHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */

class AttributeHelper
{
    /**
     * @param array $attributes
     * @param array $skipCheck
     * @return array
     */
    public static function removeSpecialAttributes(array $attributes = array(), array $skipCheck = array())
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $skipCheck)) {
                continue;
            }
            
            if (substr($key, -3) == '_id' || substr($key, -4) == '_uid') {
                unset($attributes[$key]);
                continue;
            }
            
            if (
                stripos($key, 'password')   !== false || 
                stripos($key, 'hash')       !== false || 
                stripos($key, 'meta_data')  !== false
            ) {
                unset($attributes[$key]);
                continue;
            }
        }
        return $attributes;
    }
}