<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * LicenseController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class LicenseController extends Controller
{
    public function actionIndex()
    {
        $this->data['breadcrumbs'] = array(
            'License' => 'index.php?route=license',
        );
        
        $license = null;
        if (is_file($file = MW_ROOT_PATH . '/license.txt')) {
            $license = file_get_contents($file);
        }
        
        $this->render('license', compact('license'));
    }
    
}