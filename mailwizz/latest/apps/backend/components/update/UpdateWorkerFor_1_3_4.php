<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_3_4
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
class UpdateWorkerFor_1_3_4 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        // $this->runQueriesFromSqlFile('1.3.4');
        
        // update old layouts
        $models = ListPageType::model()->findAll();
        $searchReplace = array(
            'panel panel-default'   => 'box box-primary borderless',
            'panel-heading'         => 'box-header',
            'panel-title'           => 'box-title',
            'panel-body'            => 'box-body',
            'callout'               => 'callout callout-info',
            'panel-footer'          => 'box-footer',
            'panel-'                => 'box-',
            '@import url(\'http://fonts.googleapis.com/css?family=Open+Sans\');'            => '',
            '@import url(\'http://fonts.googleapis.com/css?family=Noto+Sans:700italic\');'  => '',
            '#b94a48'               => '#367fa9',
            '\'Open Sans\','        => '',
            '\'Noto Sans\','        => '',
        );
        foreach ($models as $model) {
            $model->content = str_replace(array_keys($searchReplace), array_values($searchReplace), $model->content);
            $model->save(false);
        }
        
        $common = Yii::app()->options->get('system.email_templates.common');
        $common = str_replace('#b94a48', '#367fa9', $common);
        Yii::app()->options->set('system.email_templates.common', $common);
        // end update
    }
} 