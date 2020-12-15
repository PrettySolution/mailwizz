<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * CronController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class CronController extends Controller
{
    public function actionIndex()
    {
        if (!getSession('admin') || !getSession('license_data')) {
            redirect('index.php?route=admin');
        }
        
        if (getPost('next')) {
            setSession('cron', 1);
            redirect('index.php?route=finish');
        }
        
        $this->data['pageHeading'] = 'Cron jobs';
        $this->data['breadcrumbs'] = array(
            'Cron jobs' => 'index.php?route=cron',
        );

        $this->render('cron');
    }
    
    public function getCliPath()
    {
        return CommonHelper::findPhpCliPath();
    }
    
}