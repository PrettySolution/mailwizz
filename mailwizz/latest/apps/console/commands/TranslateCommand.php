<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TranslateCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.6
 *
 */

class TranslateCommand extends ConsoleCommand
{
    /**
     * @var int 
     */
    public $verbose = 1;
    
    /**
     * @return int
     */
    public function actionIndex()
    {
        Yii::app()->hooks->doAction('console_command_translate_before_process', $this);

        $result = $this->process();

        Yii::app()->hooks->doAction('console_command_translate__after_process', $this);

        return $result;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $attributes   = array();
        $languageName = $this->prompt('Please provide the language name(i.e: English) : ');
        if (empty($languageName)) {
            $this->stdout('Please provide a valid language name!', false);
            return 0;
        }
        $attributes['name'] = $languageName;
        
        $languageCode = $this->prompt('Please enter the 2 letter language code(i.e: en) : ');
        if (!preg_match('/[a-z]{2}/', $languageCode)) {
            $this->stdout('Please provide a valid 2 letter language code!', false);
            return 0;
        }
        $attributes['language_code'] = $languageCode;

        $regionCode = $this->prompt('Please enter the 2 letter region code(i.e: us). This is optional, leave empty if you are not sure. : ');
        if (!empty($regionCode) && !preg_match('/[a-z]{2}/', $languageCode)) {
            $this->stdout('Please provide a valid 2 letter region code!', false);
            return 0;
        }
        $attributes['region_code'] = $regionCode;
        
        $language = Language::model()->findByAttributes($attributes);
        if (empty($language)) {
            $language = new Language();
            $language->setAttributes($attributes);
            if (!$language->save()) {
                $this->stdout($language->shortErrors->getAllAsString("\n"), false);
                return 0;
            }
        }
        
        $directories = array(
            Yii::getPathOfAlias('api'),
            Yii::getPathOfAlias('backend'),
            Yii::getPathOfAlias('common'),
            Yii::getPathOfAlias('customer'),
            Yii::getPathOfAlias('extensions'),
            Yii::getPathOfAlias('frontend'),
        );
        
        $stub = file_get_contents(Yii::getPathOfAlias('common.extensions.translate.stub') . '.php');
        $messagesPath = Yii::getPathOfAlias('common.messages') . '/' . $languageCode;
        if (!empty($regionCode)) {
            $messagesPath .= '_' . $regionCode;
        }
        
        if ((!file_exists($messagesPath) || !is_dir($messagesPath)) && !@mkdir($messagesPath, 0777, true)) {
            $this->stdout(sprintf('Please make sure the folder "%s" is writable!', dirname($messagesPath)), false);
            return 0;
        }
        
        $input = $this->confirm(sprintf('Do you agree to create the translation messages in: %s ?', $messagesPath), true);
        if (!$input) {
            return 0;
        }
        
        foreach ($directories as $directory) {
            $this->stdout(sprintf('Processing: "%s"', $directory));
            
            $files = FileSystemHelper::readDirectoryContents($directory, true);
            foreach ($files as $file) {
                $this->stdout(sprintf('Processing: "%s"', $file));
                $messages = $this->extractMessages($file);
                foreach ($messages as $category => $_messages) {
                    $data = array();
                    if (is_file($categoryFile = $messagesPath . '/' . $category . '.php')) {
                        $data = require $categoryFile;
                    }
                    foreach ($_messages as $key => $value) {
                        if (!empty($data[$value])) {
                            continue;
                        }
                        $data[$value] = $value;
                    }
                    $newStub = str_replace('[[category]]', $category, $stub);
                    $newStub .= 'return ' . var_export($data, true) . ';' . "\n";
                    $newStub = str_replace("\\\\\\'", "\\'", $newStub);
                    file_put_contents($categoryFile, $newStub);
                }
            }
        }
        
        return 0;
    }

    /**
     * @param $fileName
     * @return array
     */
    public function extractMessages($fileName)
    {
        $messages = array();
        $subject  = file_get_contents($fileName);
        $n = preg_match_all('/\bYii::t\s*\(\s*(\'[\w.\/]*?(?<!\.)\'|"[\w.]*?(?<!\.)")\s*,\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*[,\)]/s',$subject, $matches, PREG_SET_ORDER);
        for ($i = 0; $i < $n; ++$i) {
            if (($pos = strpos($matches[$i][1], '.')) !== false) {
                $category = substr($matches[$i][1], $pos+1, -1);
            } else {
                $category = substr($matches[$i][1], 1, -1);
            }
            $message = trim($matches[$i][2]);
            
            // make sure we remove the single/double quotes from start/end of string
            $message = trim($message);
            $message = substr($message, 1);
            $message = substr($message, 0, -1);
            
            $messages[$category][] = $message;
        }
        return $messages;
    }
}
