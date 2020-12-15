<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * NinjaMutexMutex
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class NinjaMutexMutex extends \NinjaMutex\Mutex
{
    /**
     * @inheritdoc
     */
    public function __destruct()
    {

    }
}