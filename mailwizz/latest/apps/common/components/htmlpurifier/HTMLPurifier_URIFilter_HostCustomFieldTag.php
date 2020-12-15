<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HTMLPurifier_URIFilter_HostCustomFieldTag
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.1
 */

class HTMLPurifier_URIFilter_HostCustomFieldTag extends HTMLPurifier_URIFilter
{
    public $name = 'HostCustomFieldTag';
    
    public function filter(&$uri, $config, $context) 
    {
        return true;
    }
}