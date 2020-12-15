<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionSpfDkim
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.6
 */
 
class OptionSpfDkim extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.dns.spf_dkim';
    
    public $spf = '';
    
    public $dkim_public_key = '';
    
    public $dkim_private_key = '';
    
    public $update_sending_domains = 'no';
    
    public function rules()
    {
        $rules = array(
            array('spf', 'safe'),
            array('dkim_private_key', 'match', 'pattern' => '/-----BEGIN\sRSA\sPRIVATE\sKEY-----(.*)-----END\sRSA\sPRIVATE\sKEY-----/sx'),
            array('dkim_public_key', 'match', 'pattern' => '/-----BEGIN\sPUBLIC\sKEY-----(.*)-----END\sPUBLIC\sKEY-----/sx'),
            array('dkim_private_key, dkim_public_key', 'length', 'max' => 10000),
            array('update_sending_domains', 'in', 'range' => array_keys($this->getYesNoOptions())),

        );
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'spf'                    => Yii::t('settings', 'The SPF value'),
            'dkim_private_key'       => Yii::t('settings', 'Dkim private key'),
            'dkim_public_key'        => Yii::t('settings', 'Dkim public key'),
            'update_sending_domains' => Yii::t('settings', 'Update sending domains'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'spf'              => 'v=spf1 mx a ptr mail.otherdomain.com ~all',
            'dkim_private_key' => "-----BEGIN RSA PRIVATE KEY-----\n ... \n-----END RSA PRIVATE KEY-----",
            'dkim_public_key'  => "-----BEGIN PUBLIC KEY-----\n ... \n-----END PUBLIC KEY-----",
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'spf'                       => Yii::t('settings', 'The SPF value, i.e: v=spf1 mx a ptr mail.otherdomain.com ~all'),
            'update_sending_domains'    => Yii::t('settings', 'Whether to update the sending domains with the new keys and force them to be revalidated'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
