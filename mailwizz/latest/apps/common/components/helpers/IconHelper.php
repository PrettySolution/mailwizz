<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * IconHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

class IconHelper
{

    /**
     * @param $name
     * @return string
     */
    public static function make($name)
    {
        $className = null;

        if (strpos($name, 'glyphicon') === 0) {
            $className = 'glyphicon ' . $name;
        }

        if (!$className && strpos($name, 'fa') === 0) {
            $className = 'fa ' . $name;
        }

        if (!$className && strpos($name, 'ion') === 0) {
            $className = 'ion ' . $name;
        }

        if (!$className) {
            $icons     = self::getRegisteredActionIcons();
            $className = isset($icons[$name]) ? $icons[$name] : null;
        }

        if (!$className) {
            $className = 'fa fa-circle-o';
        }

        return '<i class="'.$className.'"></i>';
    }

    /**
     * @return array
     */
    protected static function getRegisteredActionIcons()
    {
        return array(
            'create'    => 'fa fa-plus-square',
            'update'    => 'fa fa-pencil-square-o',
            'view'      => 'fa fa-eye',
            'delete'    => 'glyphicon glyphicon-trash',
            'refresh'   => 'fa fa-refresh',
            'back'      => 'fa fa-arrow-circle-left',
            'forward'   => 'fa fa-arrow-circle-right',
            'prev'      => 'fa fa-chevron-circle-left',
            'next'      => 'fa fa-chevron-circle-right',
            'save'      => 'fa fa-save',
            'cancel'    => 'fa fa-times-circle-o',
            'info'      => 'fa fa-info-circle',
            'copy'      => 'glyphicon glyphicon-subtitles',
            'bulk'      => 'fa fa-indent',
            'filter'    => 'glyphicon glyphicon-filter',
            'campaign'  => 'fa fa-envelope',
            'export'    => 'glyphicon glyphicon-export',
            'import'    => 'glyphicon glyphicon-import',
            'download'  => 'fa fa-cloud-download',
            'upload'    => 'fa fa-cloud-upload',
            'list'      => 'glyphicon glyphicon-list-alt',
            'envelope'  => 'fa fa-envelope',
            'tools'     => 'ion ion-hammer',
        );
    }
}