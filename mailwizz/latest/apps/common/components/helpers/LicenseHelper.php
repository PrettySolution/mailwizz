<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * LicenseHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class LicenseHelper
{
    /**
     * @param OptionLicense|null $model
     * @return array
     */
    public static function verifyLicense(OptionLicense $model = null)
    {
        if ($model === null) {
            $model = new OptionLicense();
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('status', PricePlanOrder::STATUS_COMPLETE);
        $criteria->addCondition('total > 0');
        $ordersCount = PricePlanOrder::model()->count($criteria);

        $request = AppInitHelper::simpleCurlPost("https://www.mailwizz.com/api/license/verify", array(
            "key"           => $model->purchase_code,
            "orders_count"  => $ordersCount,
        ));
        
        return $request;
    }
}
