<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionImporter
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class OptionImporter extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.importer';

    public $enabled = 'yes';

    public $file_size_limit = 1048576; // 1 mb by default

    public $import_at_once = 50; // per batch

    public $pause = 1; // pause between the batches

    public $memory_limit;

    public $check_mime_type = 'yes';

    public $web_enabled = 'yes';

    public $cli_enabled = 'no';
    
    public $url_enabled = 'no';
    
    public $suppression_list_cli_enabled = 'no';

    public $email_blacklist_cli_enabled = 'no';

    public function rules()
    {
        $rules = array(
            array('enabled, file_size_limit, import_at_once, pause, check_mime_type, web_enabled, cli_enabled, url_enabled, suppression_list_cli_enabled, email_blacklist_cli_enabled', 'required'),
            array('enabled, web_enabled, cli_enabled, url_enabled, suppression_list_cli_enabled, email_blacklist_cli_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('file_size_limit, import_at_once, pause', 'numerical', 'integerOnly' => true),
            array('import_at_once', 'numerical', 'min' => 50, 'max' => 100000),
            array('pause', 'numerical', 'min' => 0, 'max' => 60),
            array('memory_limit', 'in', 'range' => array_keys($this->getMemoryLimitOptions())),
            array('file_size_limit', 'in', 'range' => array_keys($this->getFileSizeOptions())),
            array('check_mime_type', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'enabled'                      => Yii::t('settings', 'Enabled'),
            'file_size_limit'              => Yii::t('settings', 'File size limit'),
            'import_at_once'               => Yii::t('settings', 'Import at once'),
            'pause'                        => Yii::t('settings', 'Pause'),
            'memory_limit'                 => Yii::t('settings', 'Memory limit'),
            'check_mime_type'              => Yii::t('settings', 'Check mime type'),
            'cli_enabled'                  => Yii::t('settings', 'CLI import enabled'),
            'web_enabled'                  => Yii::t('settings', 'Web import enabled'),
            'url_enabled'                  => Yii::t('settings', 'Url import enabled'),
            'suppression_list_cli_enabled' => Yii::t('settings', 'Suppression lists CLI import enabled'),
            'email_blacklist_cli_enabled'  => Yii::t('settings', 'Email blacklist CLI import enabled'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled'                      => null,
            'file_size_limit'              => null,
            'import_at_once'               => null,
            'pause'                        => null,
            'memory_limit'                 => null,
            'check_mime_type'              => null,
            'web_enabled'                  => null,
            'cli_enabled'                  => null,
            'url_enabled'                  => null,
            'suppression_list_cli_enabled' => null,
            'email_blacklist_cli_enabled'  => null,
        );

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'                      => Yii::t('settings', 'Whether customers are allowed to import subscribers.'),
            'file_size_limit'              => Yii::t('settings', 'The maximum allowed file size for upload.'),
            'import_at_once'               => Yii::t('settings', 'How many subscribers to import per batch.'),
            'pause'                        => Yii::t('settings', 'How many seconds the script should "sleep" after each batch of subscribers.'),
            'memory_limit'                 => Yii::t('settings', 'The maximum memory amount the import process is allowed to use while processing one batch of subscribers.'),
            'check_mime_type'              => Yii::t('settings', 'Whether to check the uploaded file mime type.'),
            'cli_enabled'                  => Yii::t('settings', 'Whether the CLI import is enabled. Please keep in mind that you have to add a cron job in order for this to work.'),
            'web_enabled'                  => Yii::t('settings', 'Whether the import via customer browser is enabled.'),
            'url_enabled'                  => Yii::t('settings', 'Whether the recurring import from remote urls is allowed. CLI import has to be enabled as well in order for this to work. Please note that importing from unknown sources can be dangerous. Use with caution.'),
            'suppression_list_cli_enabled' => Yii::t('settings', 'Whether the CLI import for suppression lists is enabled. Please keep in mind that you have to add a cron job in order for this to work.'),
            'email_blacklist_cli_enabled'  => Yii::t('settings', 'Whether the CLI import for email blacklist is enabled. Please keep in mind that you have to add a cron job in order for this to work.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
