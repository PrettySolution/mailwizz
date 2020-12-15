<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ConsoleCommandList
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.1
 */

/**
 * This is the model class for table "{{console_command}}".
 *
 * The followings are the available columns in table '{{console_command}}':
 * @property integer $command_id
 * @property string $command
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ConsoleCommandListHistory[] $history
 */
class ConsoleCommandList extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{console_command}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array();
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'history' => array(self::HAS_MANY, 'ConsoleCommandListHistory', 'command_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'command_id' => Yii::t('console', 'ID'),
            'command'    => Yii::t('console', 'Command'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ConsoleCommand the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}