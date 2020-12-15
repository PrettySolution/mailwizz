<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionSocialLinks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */

class OptionSocialLinks extends OptionBase
{
    /**
     * @var string 
     */
    protected $_categoryName = 'system.social_links';

    /**
     * @var string
     */
    public $facebook = '';

    /**
     * @var string
     */
    public $twitter = '';

    /**
     * @var string
     */
    public $linkedin = '';

    /**
     * @var string
     */
    public $instagram = '';

    /**
     * @var string
     */
    public $youtube = '';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('facebook, twitter, linkedin, instagram, youtube', 'length', 'max' => 255),
            array('facebook, twitter, linkedin, instagram, youtube', 'url'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'facebook'  => Yii::t('settings', 'Facebook'),
            'twitter'   => Yii::t('settings', 'Twitter'),
            'linkedin'  => Yii::t('settings', 'Linkedin'),
            'instagram' => Yii::t('settings', 'Instagram'),
            'youtube'   => Yii::t('settings', 'Youtube'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'facebook'  => Yii::t('settings', 'Your business facebook url'),
            'twitter'   => Yii::t('settings', 'Your business twitter url'),
            'linkedin'  => Yii::t('settings', 'Your business linkedin url'),
            'instagram' => Yii::t('settings', 'Your business instagram url'),
            'youtube'   => Yii::t('settings', 'Your business youtube url'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
