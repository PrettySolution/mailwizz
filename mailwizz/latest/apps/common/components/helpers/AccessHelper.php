<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AccessHelper
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
class AccessHelper
{   
    // shortcut method
    public static function hasRouteAccess($route)
    {
        $app = Yii::app();
        if ($app->apps->isAppName('backend') && $app->hasComponent('user') && $app->user->getId() && $app->user->getModel()) {
            return (bool)$app->user->getModel()->hasRouteAccess($route);
        }
        return true;
    }
}