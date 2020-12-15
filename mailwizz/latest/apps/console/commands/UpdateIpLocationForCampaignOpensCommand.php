<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateIpLocationForCampaignOpensCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */

class UpdateIpLocationForCampaignOpensCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->stdout('Your PHP Version must be at least 5.4!');
            return 1;
        }
        
        if (!is_file(Yii::app()->params['ip.location.maxmind.db.path'])) {
            $this->stdout('The IP location database file is missing. See See Backend > Locations > MaxMind Database!');
            return 1;
        }
        
        $this->stdout('Starting processing...');

        $lastLogID  = 0;
        $iterations = 0;
        $criteria = new CDbCriteria();
        $criteria->select = 'id, ip_address';
        $criteria->order  = 'id ASC';
        $criteria->limit  = 1000;
        
        while (true) {
            $iterations++;
            $this->stdout('This is the iteration number ' . $iterations);
            
            $criteria->addCondition('location_id IS NULL AND id > ' . (int)$lastLogID);
            $models = CampaignTrackOpen::model()->findAll($criteria);
            if (empty($models)) {
                $this->stdout('No more rows to process!');
                break;
            }
            
            foreach ($models as $model) {
                $this->stdout('Processing IP: ' . $model->ip_address);
                try {
                    $location = IpLocation::findByIp($model->ip_address);
                    if (!empty($location)) {
                        $model->saveAttributes(array(
                            'location_id' => $location->location_id,
                        ));
                    }
                } catch (Exception $e) {
                    $this->stdout('Exception ocurred: ' . $e->getMessage());
                }
                $lastLogID = (int)$model->id;
            }
        }
        
        $this->stdout('DONE!');

        return 0;
    }
}