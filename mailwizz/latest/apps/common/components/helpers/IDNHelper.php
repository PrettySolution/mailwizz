<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * IDNHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.8
 */

class IDNHelper
{
    /**
     * @param $value
     * @return string
     */
    public static function encode($value)
    {
        $parsed = false;
        if (CommonHelper::functionExists('idn_to_ascii')) {
            $variant = null;
            if (defined('INTL_IDNA_VARIANT_UTS46')) {
                $variant = INTL_IDNA_VARIANT_UTS46;
            } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                $variant = INTL_IDNA_VARIANT_2003;
            }

            if ($variant) {
                $value  = idn_to_ascii($value, 0, $variant);
                $parsed = true;
            }
        }

        if (!$parsed) {
            require_once(Yii::getPathOfAlias('system.vendors.Net_IDNA2.Net').DIRECTORY_SEPARATOR.'IDNA2.php');
            try {
                $idna   = new Net_IDNA2();
                $_value = @$idna->encode($value);
                $value  = $_value;
            } catch (Exception $e) {

            }
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public static function decode($value)
    {
        $parsed = false;
        if (CommonHelper::functionExists('idn_to_utf8')) {
            $variant = null;
            if (defined('INTL_IDNA_VARIANT_UTS46')) {
                $variant = INTL_IDNA_VARIANT_UTS46;
            } elseif (defined('INTL_IDNA_VARIANT_2003')) {
                $variant = INTL_IDNA_VARIANT_2003;
            }

            if ($variant) {
                $value  = idn_to_utf8($value, 0, $variant);
                $parsed = true;
            }
        }

        if (!$parsed) {
            require_once(Yii::getPathOfAlias('system.vendors.Net_IDNA2.Net').DIRECTORY_SEPARATOR.'IDNA2.php');
            try {
                $idna   = new Net_IDNA2();
                $_value = @$idna->decode($value);
                $value  = $_value;
            } catch (Exception $e) {

            }
        }

        return $value;
    }
}