<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSmtpMySmtpCom
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.8
 */

class DeliveryServerSmtpMySmtpCom extends DeliveryServerSmtp
{
    /**
     * @var string 
     */
    protected $serverType = 'smtp-mysmtpcom';

	/**
	 * @var string
	 */
	protected $_providerUrl = 'https://mysmtp.com/';
	
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
}
