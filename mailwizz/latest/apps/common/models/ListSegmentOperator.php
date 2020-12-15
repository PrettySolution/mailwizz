<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSegmentOperator
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "list_segment_operator".
 *
 * The followings are the available columns in table 'list_segment_operator':
 * @property integer $operator_id
 * @property string $name
 * @property string $slug
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListSegmentCondition[] $segmentConditions
 */
class ListSegmentOperator extends ActiveRecord
{
    const IS = 'is';
    
    const IS_NOT = 'is-not';
    
    const CONTAINS = 'contains';
    
    const NOT_CONTAINS = 'not-contains';
    
    const STARTS_WITH = 'starts';
    
    const ENDS_WITH = 'ends';
    
    const GREATER = 'greater';
    
    const LESS = 'less';
    
    const NOT_STARTS_WITH = 'not-starts';
    
    const NOT_ENDS_WITH = 'not-ends';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_segment_operator}}';
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'segmentConditions' => array(self::HAS_MANY, 'ListSegmentCondition', 'operator_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSegmentOperator the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeDelete()
    {
        return false;
    }
}
