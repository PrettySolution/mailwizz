<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSmtpPostal
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class DeliveryServerSmtpPostal extends DeliveryServerSmtp
{
    /**
     * @var string 
     */
    protected $serverType = 'smtp-postal';

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DeliveryServer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParamsArray(array $params = array())
    {
        $params['transport'] = self::TRANSPORT_SMTP;
        return parent::getParamsArray($params);
    }

    /**
     * @inheritdoc
     */
    public function getDswhUrl()
    {
        $url = Yii::app()->options->get('system.urls.frontend_absolute_url') . 'dswh/postal';
        if (MW_IS_CLI) {
            return $url;
        }
        if (Yii::app()->request->isSecureConnection && parse_url($url, PHP_URL_SCHEME) == 'http') {
            $url = substr_replace($url, 'https', 0, 4);
        }
        return $url;
    }
    
}
