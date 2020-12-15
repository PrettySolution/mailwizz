<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * FilesystemController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class FilesystemController extends Controller
{
    public function actionIndex()
    {
        if (!getSession('requirements') || !getSession('license_data')) {
            redirect('index.php?route=requirements');
        }

        $this->data['requirements'] = require dirname(__FILE__) . '/../inc/filesystem.php';
        $result = 1;  // 1: all pass, 0: fail, -1: pass with warnings
        
        foreach($this->data['requirements'] as $i => $requirement) {
            
            if($requirement[1] && !$requirement[3]) {
                $result = 0;
            } elseif($result > 0 && !$requirement[1] && !$requirement[3]) {
                $result = -1;
            }
        }

        if (setSession('filesystem', (int)(getPost('result', 0) != 0 && $result != 0))) {
            redirect('index.php?route=database');
        }
        
        $this->data['result'] = $result;
        
        $this->data['pageHeading'] = 'File System';
        $this->data['breadcrumbs'] = array(
            'File system checks' => 'index.php?route=filesystem',
        );
        
        $this->render('filesystem');
    }
    
}