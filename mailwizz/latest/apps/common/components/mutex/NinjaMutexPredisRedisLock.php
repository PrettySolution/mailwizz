<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * NinjaMutexPredisRedisLock
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class NinjaMutexPredisRedisLock extends \NinjaMutex\Lock\PredisRedisLock
{
    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        
    }
}