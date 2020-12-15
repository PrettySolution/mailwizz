<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * LanguageHelper
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */
 
class LanguageHelper 
{

    /**
     * LanguageHelper::getAppLanguageCode()
     * 
     * @return string
     */
    public static function getAppLanguageCode()
    {
        $languageCode = $language = Yii::app()->language;
        if (strpos($language, '_') !== false) {
            $languageAndRegionCode = explode('_', $language);
            list($languageCode, $regionCode) = $languageAndRegionCode;
        }
        return $languageCode;  
    }
}