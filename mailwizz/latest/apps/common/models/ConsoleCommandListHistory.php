<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ConsoleCommandListHistory
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.1
 */

/**
 * This is the model class for table "{{console_command_history}}".
 *
 * The followings are the available columns in table '{{console_command_history}}':
 * @property integer $id
 * @property integer $command_id
 * @property string $action
 * @property string $params
 * @property string $start_time
 * @property string $end_time
 * @property integer $start_memory
 * @property integer $end_memory
 * @property string $status
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property ConsoleCommandList $command
 */
class ConsoleCommandListHistory extends ActiveRecord
{
    /**
     * Flag for success status
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Flag for error status
     */
    const STATUS_ERROR = 'error';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{console_command_history}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('command_id, action, params, status', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'command' => array(self::BELONGS_TO, 'ConsoleCommandList', 'command_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'id'            => Yii::t('console', 'ID'),
            'command_id'    => Yii::t('console', 'Command'),
            'action'        => Yii::t('console', 'Action'),
            'params'        => Yii::t('console', 'Params'),
            'start_time'    => Yii::t('console', 'Start time'),
            'end_time'      => Yii::t('console', 'End time'),
            'start_memory'  => Yii::t('console', 'Start memory'),
            'end_memory'    => Yii::t('console', 'End memory'),
            
            //
            'duration'      => Yii::t('console', 'Duration'),
            'memoryUsage'   => Yii::t('console', 'Memory usage'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->with['command'] = array(
            'together' => true,
            'joinType' => 'INNER JOIN',
        );
        
        if (!empty($this->command_id) && !is_numeric($this->command_id)) {
            $criteria->compare('command.command', $this->command_id, true);
        } else {
            $criteria->compare('t.command_id', $this->command_id);
        }
        
        $criteria->compare('t.action', $this->action, true);
        $criteria->compare('t.params', $this->params, true);
        $criteria->compare('t.status', $this->status);
        
        $criteria->order = 't.id DESC';
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ConsoleCommandHistory the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        parent::afterSave();
        $this->deleteOlderRecords();
    }
    
    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_SUCCESS => Yii::t('app', 'Success'),
            self::STATUS_ERROR   => Yii::t('app', 'Error'),
        );
    }

    /**
     * @param int $keep
     * @return int
     */
    public function deleteOlderRecords($keep = 10)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'id';
        $criteria->compare('command_id', (int)$this->command_id);
        $criteria->order = 'id DESC';
        $criteria->limit = (int)$keep;
        
        $models = self::model()->findAll($criteria);
        $ids    = array();
        
        foreach ($models as $model) {
            $ids[] = $model->id;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('command_id', (int)$this->command_id);
        
        if (!empty($ids)) {
            $criteria->addNotInCondition('id', $ids);
        }
        
        return $this->deleteAll($criteria);
    }

    /**
     * @return array
     */
    public static function getCommandsListAsOptions()
    {
        $options = array();
        $models  = ConsoleCommandList::model()->findAll();
        foreach ($models as $model) {
            $options[$model->command_id] = $model->command;
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getActionAsOptions()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'DISTINCT(action) as action';
        $criteria->group = 'action';
        
        $options = array();
        $models  = self::model()->findAll($criteria);
        foreach ($models as $model) {
            $options[$model->action] = $model->action;
        }
        return $options;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        return round($this->end_time - $this->start_time, 2) . ' ' . Yii::t('console', 'seconds');
    }

    /**
     * @return string
     */
    public function getMemoryUsage()
    {
        return CommonHelper::formatBytes($this->end_memory - $this->start_memory);
    }
}