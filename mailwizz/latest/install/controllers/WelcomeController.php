<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * WelcomeController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class WelcomeController extends Controller
{
    public function actionIndex()
    {
        // start clean
        $_SESSION = array();
        
        $this->validateRequest();
        
        if (getSession('welcome')) {
            redirect('index.php?route=requirements');
        }
        
        $this->data['marketPlaces'] = $this->getMarketPlaces();
        
        $this->data['pageHeading'] = 'Welcome';
        $this->data['breadcrumbs'] = array(
            'Welcome' => 'index.php?route=welcome',
        );
        
        $this->render('welcome');
    }
    
    protected function validateRequest()
    {
        if (!getPost('next')) {
            return;
        }
        
			$licenseData = array(
			'first_name' => 'PROWEBBER',
			'last_name' => 'SCRIPS',
			'email' => 'mailwizz@prowebber.com',
			'market_place' => 'envato',
			'purchase_code' => 'NULLED by prowebber.ru',
			);
        
        setSession('license_data', $licenseData);
        setSession('welcome', 1);
    }
    
    public function getMarketPlaces()
    {
        return array(
            'envato'    => 'Envato Market Places',
            'mailwizz'  => 'Mailwizz Website',
        );
    }

}