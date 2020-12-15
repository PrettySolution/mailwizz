<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DnsTxtHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.6
 */

class DnsTxtHelper
{
    /**
     * @return array
     */
    public static function getDkimRequirementsErrors()
    {
        $errors = array();
        if (!defined('PKCS7_TEXT')) {
            $errors[] = Yii::t('sending_domains', 'OpenSSL extension missing.');
        }
        $functions = array('exec', 'escapeshellarg', 'dns_get_record', 'openssl_pkey_get_private', 'openssl_sign', 'openssl_error_string');
        foreach ($functions as $func) {
            if (!CommonHelper::functionExists($func)) {
                $errors[] = Yii::t('sending_domains', '{func} function must be enabled in order to handle the DKIM keys.', array('{func}' => $func));
            }
        }
        return $errors;
    }

    /**
     * @return array
     */
    public static function generateDkimKeys()
    {
        if ($errors = self::getDkimRequirementsErrors()) {
            return array('errors' => $errors);
        }
        
        $key = StringHelper::random(10);
        $publicKey   = $key . '.public';
        $privateKey  = $key . '.private';
        $tempStorage = Yii::getPathOfAlias('common.runtime.dkim');

        if ((!file_exists($tempStorage) || !is_dir($tempStorage)) && !@mkdir($tempStorage, 0777)) {
            return array('errors' => array(Yii::t('sending_domains', 'Unable to create {dir} directory.', array('{dir}' => $tempStorage))));
        }
        
        // try to make it writable
        @chmod($tempStorage, 0777);
        
        // private key
	    $keySize = Yii::app()->params['email.custom.dkim.key.size'];
        $line = exec(sprintf('cd %s && /usr/bin/openssl genrsa -out %s %d', escapeshellarg($tempStorage), escapeshellarg($privateKey), $keySize), $output, $return);
        if ((int)$return != 0) {
            $fail = !empty($output) ? implode("<br />", $output) : $line;
            return array('errors' => array(Yii::t('sending_domains', 'While generating the private key, exec failed with: {fail}', array(
                '{fail}' => !empty($fail) ? $fail : Yii::t('sending_domains', 'Unknown error, most probably cannot exec the openssl command!'),
            ))));
        }
        if (!is_file($tempStorage . '/' . $privateKey)) {
            return array('errors' => array(Yii::t('sending_domains', 'Unable to check the private key file.')));
        }

        // public key
        $line = exec(sprintf('cd %s && /usr/bin/openssl rsa -in %s -out %s -pubout -outform PEM', escapeshellarg($tempStorage), escapeshellarg($privateKey), escapeshellarg($publicKey)), $output, $return);
        if ((int)$return != 0) {
            $fail = !empty($output) ? implode("<br />", $output) : $line;
            return array('errors' => array(Yii::t('sending_domains', 'While generating the public key, exec failed with: {fail}', array(
                '{fail}' => !empty($fail) ? $fail : Yii::t('sending_domains', 'Unknown error, most probably cannot exec the openssl command!')
            ))));
        }
        if (!is_file($tempStorage . '/' . $publicKey)) {
            return array('errors' => array(Yii::t('sending_domains', 'Unable to check the public key file.')));
        }

        $dkim_private_key = file_get_contents($tempStorage . '/' . $privateKey);
        $dkim_public_key  = file_get_contents($tempStorage . '/' . $publicKey);

        unlink($tempStorage . '/' . $privateKey);
        unlink($tempStorage . '/' . $publicKey);

        return array('errors' => array(), 'private_key' => $dkim_private_key, 'public_key' => $dkim_public_key);
    }

    /**
     * @param $key
     * @return mixed|string
     */
    public static function cleanDkimKey($key)
    {
        $key = str_replace(array(
            '-----BEGIN PUBLIC KEY-----', 
            '-----END PUBLIC KEY-----',
            '-----BEGIN RSA PRIVATE KEY-----',
            '-----END RSA PRIVATE KEY-----'
        ), '', $key);
        $key = trim(preg_replace('/\s+/', '', $key));
        return $key;
    }

    /**
     * @return mixed
     */
    public static function getDefaultDkimPrivateKey()
    {
        return Yii::app()->options->get('system.dns.spf_dkim.dkim_private_key', '');
    }

    /**
     * @return mixed
     */
    public static function getDefaultDkimPublicKey()
    {
        return Yii::app()->options->get('system.dns.spf_dkim.dkim_public_key', '');
    }

    /**
     * @return mixed
     */
    public static function getDefaultSpfValue()
    {
        return Yii::app()->options->get('system.dns.spf_dkim.spf', '');
    }

    /**
     * @return mixed
     */
    public static function getDkimSelector()
    {
        return Yii::app()->params['email.custom.dkim.selector'];
    }

    /**
     * @return mixed
     */
    public static function getDkimFullSelector()
    {
        return Yii::app()->params['email.custom.dkim.full_selector'];
    }
}