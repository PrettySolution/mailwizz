<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ImageHelper
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ImageHelper
{
    /**
     * ImageHelper::resize()
     * 
     * @param string $imageFilePath
     * @param mixed $width
     * @param mixed $height
     * @param bool $forceSize
     * @return mixed
     */
    public static function resize($imageFilePath, $width = null, $height = null, $forceSize = false)
    {
        $_imageFilePath = rawurldecode($imageFilePath);
        if (false === ($_imageFilePath = realpath(Yii::getPathOfAlias('root') . '/' . ltrim($imageFilePath,'/')))) {
            $_imageFilePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imageFilePath,'/');
        }
        
        $imageFilePath = str_replace('\\', '/', $_imageFilePath);

	    $extension = pathinfo($imageFilePath, PATHINFO_EXTENSION);
	    if (empty($extension) || !in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
		    return false;
	    }

	    $extensionName = strtolower(pathinfo($imageFilePath, PATHINFO_EXTENSION));
	    if (!in_array($extensionName, array('jpg', 'jpeg', 'png', 'gif'))) {
		    return false;
	    }
	    
        if (!is_file($imageFilePath) || !($imageInfo = @getimagesize($imageFilePath))) {
            return false;
        }

	    list($originalWidth, $originalHeight) = $imageInfo;
        
        $width  = (int)$width  > 0 ? (int)$width  : (int)$originalWidth;
        $height = (int)$height > 0 ? (int)$height : (int)$originalHeight;
        
        if (empty($width) && empty($height)) {
            return false;
        }
        
        if (empty($width)) {
            $width = floor($originalWidth * $height / $originalHeight);
        } elseif (empty($height)) {
            $height = floor($originalHeight * $width / $originalWidth);
        }
        
        $md5File    = md5_file($imageFilePath);
        $filePrefix = substr($md5File, 0, 2) . substr($md5File, 10, 2) . substr($md5File, 20, 2) . substr($md5File, 30, 2);
        
        $baseResizeUrl  = Yii::app()->apps->getAppUrl('frontend', 'frontend/assets/files/resized/' . $width . 'x' . $height, false, true) . '/';
        $baseResizePath = Yii::getPathOfAlias('root.frontend.assets.files.resized.' . $width . 'x' . $height);
        
        $imageName      = $filePrefix . '-' . basename($imageFilePath);
        $alreadyResized = $baseResizePath . '/' . $imageName;
        
        $oldImageLastModified = @filemtime($imageFilePath);
        $newImageLastModified = 0;
        
        if ($isAlreadyResized = is_file($alreadyResized)) {
            $newImageLastModified = @filemtime($alreadyResized);
        }

        if ($isAlreadyResized && @getimagesize($alreadyResized) && $oldImageLastModified < $newImageLastModified) {
            return $baseResizeUrl . rawurlencode($imageName);
        }
            
        if (!file_exists($baseResizePath) && !@mkdir($baseResizePath, 0777, true)) {
            return false;       
        }
        
        // since 1.5.2 - if the sizes are larger than the original image, just copy the image over
        if ($width >= $originalWidth && $height >= $originalHeight) {
            if (copy($imageFilePath, $baseResizePath . '/' . $imageName)) {
                return $baseResizeUrl . rawurlencode($imageName);
            }
        }
        
        require_once Yii::getPathOfAlias('common.vendors.PhpThumb') . '/ThumbLib.inc.php';
        
        try {
            
            $thumb = PhpThumbFactory::create($imageFilePath);
        
            if (!$forceSize) {
                $thumb->adaptiveResize($width, $height);
            } else {
                $thumb->resize($width, $height);
            }
    
            if (!$thumb->save($baseResizePath . '/' . $imageName)) {
                return false;
            }
        
        } catch (Exception $e) {
            
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            return false;
            
        }
            
        return $baseResizeUrl . rawurlencode($imageName);
    }
}