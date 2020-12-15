<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionLicense
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 */

class OptionLicense extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.license';

    public $market_place;

    public $purchase_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('market_place, purchase_code', 'required'),
            array('market_place, purchase_code', 'length', 'max' => 255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'market_place'  => Yii::t('settings', 'Market place'),
            'purchase_code' => Yii::t('settings', 'Purchase code'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array();
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getMarketplacesList()
    {
        return array(
            'envato'    => Yii::t('settings', 'Envato Market Places'),
            'mailwizz'  => Yii::t('settings', 'Mailwizz Website'),
        );
    }

	/**
	 * @return mixed
	 */
    public function getPurchaseCode()
    {
    	return (string)$this->purchase_code;
    }

	/**
	 * @return string
	 */
    public function getMissingPurchaseCodeMessage()
    {
    	return file_get_contents(Yii::getPathOfAlias('common.data.license.missing-purchase-code') . '.php');
    }
}
