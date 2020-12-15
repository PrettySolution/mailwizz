<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscribers7DaysActivityWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.2
 */
 
class ListSubscribers7DaysActivityWidget extends CWidget 
{
    public $list;
    
    public function run() 
    {
        $list = $this->list;
        
        if ($list->customer->getGroupOption('lists.show_7days_subscribers_activity_graph', 'yes') != 'yes') {
            return;
        }
        
        $cacheKey = sha1(__METHOD__ . $list->list_id . date('H') . 'v1');
        if (($chartData = Yii::app()->cache->get($cacheKey)) === false) {
           
            $chartData = array(
                'confirmed' => array(
                    'label' => '&nbsp;' . Yii::t('list_subscribers', 'Confirmed'),
                    'data'  => array(),
                ),
                'unconfirmed' => array(
                    'label' => '&nbsp;' . Yii::t('list_subscribers', 'Unconfirmed'),
                    'data'  => array(),
                ),
                'unsubscribed' => array(
                    'label' => '&nbsp;' . Yii::t('list_subscribers', 'Unsubscribed'),
                    'data'  => array(),
                ),
                'blacklisted' => array(
                    'label' => '&nbsp;' . Yii::t('list_subscribers', 'Blacklisted'),
                    'data'  => array(),
                ),
                'bounces' => array(
                    'label' => '&nbsp;' . Yii::t('list_subscribers', 'Bounces'),
                    'data'  => array(),
                ),
            );
            
            for ($i = 0; $i < 7; $i++) {
                $timestamp = strtotime(sprintf('-%d days', $i));
                
                // confirmed
                $count = ListSubscriber::model()->count(array(
                    'condition' => 'list_id = :lid AND status = :st AND DATE(date_added) = :date',
                    'params'    => array(
                        ':lid'  => $list->list_id,
                        ':st'   => ListSubscriber::STATUS_CONFIRMED,
                        ':date' => date('Y-m-d', $timestamp)
                    ),
                ));
                $chartData['confirmed']['data'][] = array($timestamp * 1000, (int)$count);
                
                // unconfirmed
                $count = ListSubscriber::model()->count(array(
                    'condition' => 'list_id = :lid AND status = :st AND DATE(date_added) = :date',
                    'params'    => array(
                        ':lid'  => $list->list_id,
                        ':st'   => ListSubscriber::STATUS_UNCONFIRMED,
                        ':date' => date('Y-m-d', $timestamp)
                    ),
                ));
                $chartData['unconfirmed']['data'][] = array($timestamp * 1000, (int)$count);

                // unsubscribes
                $count = ListSubscriber::model()->count(array(
                    'condition' => 'list_id = :lid AND status = :st AND DATE(date_added) = :date',
                    'params'    => array(
                        ':lid'  => $list->list_id,
                        ':st'   => ListSubscriber::STATUS_UNSUBSCRIBED,
                        ':date' => date('Y-m-d', $timestamp)
                    ),
                ));
                $chartData['unsubscribed']['data'][] = array($timestamp * 1000, (int)$count);

                // blacklisted
                $count = ListSubscriber::model()->count(array(
                    'condition' => 'list_id = :lid AND status = :st AND DATE(date_added) = :date',
                    'params'    => array(
                        ':lid'  => $list->list_id,
                        ':st'   => ListSubscriber::STATUS_BLACKLISTED,
                        ':date' => date('Y-m-d', $timestamp)
                    ),
                ));
                $chartData['blacklisted']['data'][] = array($timestamp * 1000, (int)$count);

                // bounces
                $criteria = new CDbCriteria();
                $criteria->compare('DATE(t.date_added)', date('Y-m-d', $timestamp));
                $criteria->with['campaign'] = array(
                    'select'    => false,
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'with'      => array(
                        'list'  => array(
                            'select'    => false,
                            'together'  => true,
                            'joinType'  => 'INNER JOIN',
                            'condition' => 'list.list_id = :lid',
                            'params'    => array(':lid' => $list->list_id),
                        ),
                    ),
                );
                $count = CampaignBounceLog::model()->count($criteria);
                $chartData['bounces']['data'][] = array($timestamp * 1000, (int)$count);
            }

            $chartData = array_values($chartData);
            Yii::app()->cache->set($cacheKey, $chartData, 3600);
        }
        
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.crosshair.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.time.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/strftime/strftime-min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/list-subscribers-7days-activity.js'));
        
        $this->render('7days-activity', compact('chartData'));
    }
}