<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailTemplateUploadBehavior
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class EmailTemplateUploadBehavior extends CActiveRecordBehavior
{
    private $_cdnSubdomain = null;

    /**
     * EmailTemplateUploadBehavior::handleUpload()
     *
     * @return bool
     */
    public function handleUpload()
    {
        // no reason to go further if there are errors.
        if ($this->owner->hasErrors() || empty($this->owner->archive)) {
            return false;
        }

        // we need the zip archive class, cannot work without.
        if (!class_exists('ZipArchive', false)) {
            $this->owner->addError('archive', Yii::t('app', 'ZipArchive class required in order to unzip the file.'));
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($this->owner->archive->tempName, ZipArchive::CREATE) !== true) {
            $this->owner->addError('archive', Yii::t('app', 'Cannot open the archive file.'));
            return false;
        }

        if (empty($this->owner->template_uid)) {
            $this->owner->template_uid = $this->owner->generateUid();
        }

        $storageDirName = $this->owner->template_uid;
        $tmpUploadPath = FileSystemHelper::getTmpDirectory() . '/' . $storageDirName;
        if (!file_exists($tmpUploadPath) && !@mkdir($tmpUploadPath, 0777, true)) {
            $this->owner->addError('archive', Yii::t('app', 'Cannot create temporary directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $tmpUploadPath)));
            return false;
        }

        $zip->extractTo($tmpUploadPath);
        $zip->close();

        // try to find the entry file, index.html
        $archiveName = str_replace(array('../', './', '..\\', '.\\', '..'), '', basename($this->owner->archive->name, '.zip'));
        $entryFilePath = null;
        $possibleEntryFiles = array('index.html', 'index.htm', $archiveName.'.html', $archiveName.'.htm');
        foreach ($possibleEntryFiles as $entry) {
            if (is_file($file = $tmpUploadPath . '/'. $entry)) {
                $entryFilePath = $file;
                break;
            }
        }

        if ($entryFilePath === null && $files = FileSystemHelper::readDirectoryContents($tmpUploadPath, true)) {
            foreach ($files as $file) {
                $file = str_replace(array('../', './', '..\\', '.\\', '..'), '', $file);
                foreach ($possibleEntryFiles as $entry) {
                    if (substr($file, -strlen($entry)) === $entry) {
                        $entryFilePath = $file;
                        break;
                    }
                }
                if ($entryFilePath) {
                    break;
                }
            }
            // maybe named something else?
            if ($entryFilePath === null) {
                foreach ($files as $file) {
                    $file = str_replace(array('../', './', '..\\', '.\\', '..'), '', $file);
                    if (substr($file, -strlen('.html')) === '.html') {
                        $entryFilePath = $file;
                        break;
                    }
                    if (substr($file, -strlen('.htm')) === '.htm') {
                        $entryFilePath = $file;
                        break;
                    }
                }
            }
        }

        // the entry file was not found, too bad...
        if ($entryFilePath === null) {
            $this->owner->addError('archive', Yii::t('app', 'Cannot find template entry file, usually called index.html'));
            return false;
        }

        $entryFilePathDir = dirname($entryFilePath);
        $htmlContent = trim(@file_get_contents($entryFilePath));

        if (empty($htmlContent)) {
            $this->owner->addError('archive', Yii::t('app', 'The template entry file seems to be empty.'));
            return false;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
        if (!file_exists($storagePath) && !@mkdir($storagePath, 0777, true)) {
            $this->owner->addError('archive', Yii::t('app', 'Cannot create temporary directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $storagePath)));
            return false;
        }

        $storagePath .= '/' . $storageDirName;
        if (!file_exists($storagePath) && !@mkdir($storagePath, 0777, true)) {
            $this->owner->addError('archive', Yii::t('app', 'Cannot create temporary directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $storagePath)));
            return false;
        }

        libxml_use_internal_errors(true);
        $cleanContent = Yii::app()->ioFilter->purify($htmlContent);
        
        require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        $query = qp($cleanContent, 'body', array(
            'ignore_parser_warnings'    => true,
            'convert_to_encoding'       => Yii::app()->charset,
            'convert_from_encoding'     => Yii::app()->charset,
            'use_parser'                => 'html',
        ));

        // to do: what action should we take here?
        if (count(libxml_get_errors()) > 0) {

        }

        $images = $query->top()->find('img');
        if ($images->length == 0) {
            $images = array();
        }

	    $extensions     = array('png', 'jpg', 'jpeg', 'gif');
        $foundImages    = array();
        $screenshot     = null;
        foreach ($extensions as $ext) {
            if (is_file($entryFilePathDir . '/screenshot.'.$ext) && @copy($entryFilePathDir . '/screenshot.'.$ext, $storagePath . '/screenshot.'.$ext)) {
                $screenshot = '/frontend/assets/gallery/'.$storageDirName.'/screenshot.'.$ext;
                break;
            }
        }

        $imageSearchReplace = array();
        foreach ($images as $image) {

            if (!($src = urldecode($image->attr('src')))) {
                continue;
            }

            $src = str_replace(array('../', './', '..\\', '.\\', '..'), '', $src);
            $src = trim($src);
            if (preg_match('/^https?/i', $src) || strpos($src, '//') === 0 || FilterVarHelper::url($src)) {
                continue;
            }

            if (!is_file($entryFilePathDir . '/' . $src)) {
                continue;
            }

	        $ext = pathinfo($src, PATHINFO_EXTENSION);
	        if (empty($ext) || !in_array(strtolower($ext), $extensions)) {
		        continue;
	        }
	        unset($ext);

            $imageInfo = @getimagesize($entryFilePathDir . '/' . $src);
            if (empty($imageInfo[0]) || empty($imageInfo[1])) {
                continue;
            }

            if (!@copy($entryFilePathDir . '/' . $src, $storagePath . '/' . basename($src))) {
                continue;
            }

            if (empty($screenshot)) {
                $foundImages[] = array(
                    'name'   => basename($src),
                    'width'  => $imageInfo[0],
                    'height' => $imageInfo[1],
                );
            }

            $relSrc = 'frontend/assets/gallery/'.$storageDirName.'/'.basename($src);
            $newSrc = Yii::app()->apps->getAppUrl('frontend', $relSrc, true, true);
            if ($this->getCdnSubdomain()) {
                $newSrc = sprintf('%s/%s', $this->getCdnSubdomain(), $relSrc);
            }
            $imageSearchReplace[ $image->attr('src') ] = $newSrc;
        }

        if (empty($screenshot) && !empty($foundImages)) {
            $largestImage = array('name' => null, 'width' => 0, 'height' => 0);
            foreach ($foundImages as $index => $imageData) {
                if ($imageData['width'] > $largestImage['width'] && $imageData['height'] > $largestImage['height']) {
                    $largestImage = $imageData;
                }
            }

            if (!empty($largestImage['name']) && $largestImage['width'] >= 160 && $largestImage['height'] >= 160) {
                $screenshot = '/frontend/assets/gallery/'.$storageDirName.'/'.$largestImage['name'];
            }
        }

        if (!empty($screenshot)) {
            $this->owner->screenshot        = $screenshot;
            $this->owner->create_screenshot = 'no';
        }

        if (empty($this->owner->name)) {
            $this->owner->name = basename(Yii::app()->ioFilter->getCISecurity()->sanitize_filename($this->owner->archive->name), '.zip');
        }

        $sort = array();
        foreach ($imageSearchReplace as $k => $v) {
            $sort[] = strlen($k);
        }
        array_multisort($imageSearchReplace, $sort, SORT_NUMERIC, SORT_DESC);
        
        $this->owner->content = str_replace(array_keys($imageSearchReplace), array_values($imageSearchReplace), $htmlContent);

        // because bg images escape the above code block and looping each element is out of the question
        // (042 and 047 are octal quotes)
        preg_match_all('/url\((\042|\047)?([a-z0-9_\-\s\.\/]+)(\042|\047)?\)/six', $this->owner->content, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $src) {
                $originalSrc = $src;

                $src = urldecode($src);
                $src = str_replace(array('../', './', '..\\', '.\\', '..'), '', $src);
                $src = trim($src);

                if (preg_match('/^https?/i', $src) || strpos($src, '//') === 0 || FilterVarHelper::url($src)) {
                    continue;
                }

                if (!is_file($entryFilePathDir . '/' . $src)) {
                    $this->owner->content = str_replace($originalSrc, '', $this->owner->content);
                    continue;
                }

	            $extensionName = strtolower(pathinfo($src, PATHINFO_EXTENSION));
	            if (!in_array($extensionName, array('jpg', 'jpeg', 'png', 'gif'))) {
		            $this->owner->content = str_replace($originalSrc, '', $this->owner->content);
		            continue;
	            }
	            
                $imageInfo = @getimagesize($entryFilePathDir . '/' . $src);
                if (empty($imageInfo[0]) || empty($imageInfo[1])) {
                    $this->owner->content = str_replace($originalSrc, '', $this->owner->content);
                    continue;
                }

                if(!@copy($entryFilePathDir . '/' . $src, $storagePath . '/' . basename($src))) {
                    $this->owner->content = str_replace($originalSrc, '', $this->owner->content);
                    continue;
                }

                $relSrc = 'frontend/assets/gallery/'.$storageDirName.'/'.basename($src);
                $newSrc = Yii::app()->apps->getAppUrl('frontend', $relSrc, true, true);
                if ($this->getCdnSubdomain()) {
                    $newSrc = sprintf('%s/%s', $this->getCdnSubdomain(), $relSrc);
                }
                $this->owner->content = str_replace($originalSrc, $newSrc, $this->owner->content);
            }
        }

        libxml_use_internal_errors(false);

        FileSystemHelper::deleteDirectoryContents($tmpUploadPath, true, 1);

        $this->owner->content = StringHelper::decodeSurroundingTags($this->owner->content);

        // give a chance for last moment changes
        Yii::app()->hooks->doAction('email_template_upload_behavior_handle_upload_before_save_content', array(
            'template'        => $this->owner,
            'originalContent' => $this->owner->content,
            'storagePath'     => $storagePath,
            'storageDirName'  => $storageDirName,
            'cdnSubdomain'    => $this->getCdnSubdomain(),
        ));

        return $this->owner->save(false);
    }

    /**
     * EmailTemplateUploadBehavior::afterDelete()
     *
     * @param mixed $event
     * @return
     */
    public function afterDelete($event)
    {
        $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
        $templatePath = $storagePath.'/'.$this->owner->template_uid;

        if (file_exists($templatePath) && is_dir($templatePath)) {
            FileSystemHelper::deleteDirectoryContents($templatePath, true, 1);
        }
    }

    /**
     * EmailTemplateUploadBehavior::getCdnSubdomain()
     *
     * @return mixed
     */
    protected function getCdnSubdomain()
    {
        if ($this->_cdnSubdomain !== null) {
            return $this->_cdnSubdomain;
        }
        $this->_cdnSubdomain = false;

        if (Yii::app()->hasComponent('customer') && Yii::app()->customer->getId() && ($customer = Yii::app()->customer->getModel())) {
            if ($customer->getGroupOption('cdn.enabled', 'no') == 'yes' && $customer->getGroupOption('cdn.use_for_email_assets', 'no') == 'yes') {
                $this->_cdnSubdomain = $customer->getGroupOption('cdn.subdomain');
            }
        }

        $options = Yii::app()->options;

        if (!$this->_cdnSubdomain && $options->isTrue('system.customer_cdn.enabled') && $options->isTrue('system.customer_cdn.use_for_email_assets') && strlen($options->get('system.customer_cdn.subdomain'))) {
            $this->_cdnSubdomain = $options->get('system.customer_cdn.subdomain');
        }

        if (!$this->_cdnSubdomain && $options->isTrue('system.cdn.enabled') && $options->isTrue('system.cdn.use_for_email_assets') && strlen($options->get('system.cdn.subdomain'))) {
            $this->_cdnSubdomain = $options->get('system.cdn.subdomain');
        }

        if (!empty($this->_cdnSubdomain) && stripos($this->_cdnSubdomain, 'http') !== 0) {
            $this->_cdnSubdomain = 'http://' . $this->_cdnSubdomain;
        }
        
        return $this->_cdnSubdomain;
    }
}
