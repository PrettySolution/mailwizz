<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TwigHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.9
 */

class TwigHelper
{
    /**
     * @var Twig_Environment|null
     */
    protected static $instance;

    /**
     * @return Twig_Environment
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = self::createInstance();
        }
        return self::$instance;
    }

    /**
     * @return Twig_Environment
     */
    public static function createInstance()
    {
        $instance = new Twig_Environment(new Twig_Loader_String());
        $instance = Yii::app()->hooks->applyFilters('twig_create_instance', $instance);
        return $instance;
    }
}
