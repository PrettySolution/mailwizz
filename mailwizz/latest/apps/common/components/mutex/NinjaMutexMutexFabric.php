<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * NinjaMutexMutexFabric
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class NinjaMutexMutexFabric extends \NinjaMutex\MutexFabric
{
    /**
     * @param string $name
     * @param string $registeredLockImplementorName
     */
    protected function createMutex($name, $registeredLockImplementorName)
    {
        $this->mutexes[$registeredLockImplementorName][$name] = new NinjaMutexMutex($name, $this->implementors[$registeredLockImplementorName]);
    }
    
}