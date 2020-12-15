<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class Ext_tour_slideshow_skipController extends Controller
{
    // the extension instance
    public $extension;

    /**
     * Index 
     */
    public function actionIndex()
    {
        $appName = Yii::app()->apps->getCurrentAppName();
        $id      = null;

        if ($appName == TourSlideshow::APPLICATION_BACKEND) {
            $id = Yii::app()->user->getId();
        } elseif ($appName == TourSlideshow::APPLICATION_CUSTOMER) {
            $id = Yii::app()->customer->getId();
        }
        
        if (empty($id)) {
            return $this->renderJson(array());
        }

        $criteria = new CDbCriteria();
        $criteria->compare('slideshow_id', (int)Yii::app()->request->getPost('slideshow'));
        $criteria->compare('application', $appName);
        $criteria->compare('status', TourSlideshow::STATUS_ACTIVE);
        $slideshow = TourSlideshow::model()->find($criteria);

        if (empty($slideshow)) {
            return $this->renderJson(array());
        }
        
        $this->extension->setOption('views.' . $appName . '.' . $id . '.viewed', $slideshow->slideshow_id);

        return $this->renderJson(array());
    }
}
