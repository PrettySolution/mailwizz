<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AmazonHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class AmazonHelper
{
    /**
     * @return array
     */
    public static function getRegionsList()
    {
        return Yii::app()->hooks->applyFilters('amazon_helper_get_regions_list', array(
            'us-east-1'      => 'US East (N. Virginia)',
            'us-east-2'      => 'US East (Ohio)',
            'us-west-1'      => 'US West (N. California)',
            'us-west-2'      => 'US West (Oregon)',
            'ca-central-1'   => 'Canada (Central)',
            'eu-west-1'      => 'EU (Ireland)',
            'eu-central-1'   => 'EU (Frankfurt)',
            'eu-west-2'      => 'EU (London)',
            'ap-northeast-1' => 'Asia Pacific (Tokyo)',
            'ap-northeast-2' => 'Asia Pacific (Seoul)',
            'ap-southeast-1' => 'Asia Pacific (Singapore)',
            'ap-southeast-2' => 'Asia Pacific (Sydney)',
            'ap-south-1'     => 'Asia Pacific (Mumbai)',
            'sa-east-1'      => 'South America (São Paulo)',
        ));
    }
}
