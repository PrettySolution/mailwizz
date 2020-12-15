<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HeaderHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */

class HeaderHelper
{
    /**
     * @param string $fileName
     * @param int $fileSize
     * @param string $mimeType
     */
    public static function setDownloadHeaders($fileName, $fileSize = 0, $mimeType = 'application/octet-stream')
    {
        if (headers_sent()) {
            return;
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header('Content-type: ' . $mimeType);
        header("Content-Transfer-Encoding: Binary");
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        
        if ($fileSize) {
            header("Content-Length: " . $fileSize);
        }
    }
}