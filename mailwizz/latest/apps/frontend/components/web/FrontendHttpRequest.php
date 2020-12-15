<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FrontendHttpRequest
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class FrontendHttpRequest extends BaseHttpRequest
{
    /**
     * FrontendHttpRequest::checkCurrentRoute()
     *
     * @return bool
     */
    protected function checkCurrentRoute()
    {
        if (stripos($this->pathInfo, 'webhook') !== false) {
            return false;
        }
        return parent::checkCurrentRoute();
    }

}