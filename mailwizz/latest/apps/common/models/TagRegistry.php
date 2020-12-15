<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UserAutoLoginToken
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "tag_registry".
 *
 * The followings are the available columns in table 'tag_registry':
 * @property integer $tag_id
 * @property string $tag
 * @property string $description
 * @property string $date_added
 * @property string $last_updated
 */
class TagRegistry extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tag_registry}}';
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return TagRegistry the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
