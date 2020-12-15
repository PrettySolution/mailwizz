<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListDefault
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "list_default".
 *
 * The followings are the available columns in table 'list_default':
 * @property integer $list_id
 * @property string $from_name
 * @property string $from_email
 * @property string $reply_to
 * @property string $subject
 *
 * The followings are the available model relations:
 * @property Lists $list
 */
class ListDefault extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_default}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('from_name, reply_to, from_email', 'required'),

            array('from_name', 'length', 'min' => 2, 'max' => 255),
            array('reply_to, from_email', 'length', 'min' => 5, 'max' => 100),
            array('reply_to, from_email', 'email', 'validateIDN' => true),
            array('subject', 'length', 'max'=>255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'list_id'   => Yii::t('lists', 'List'),
            'from_name' => Yii::t('lists', 'From name'),
            'from_email'=> Yii::t('lists', 'From email'),
            'reply_to'  => Yii::t('lists', 'Reply to'),
            'subject'   => Yii::t('lists', 'Subject'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListDefault the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'from_name' => Yii::t('lists', 'This is the name of the "From" header used in campaigns, use a name that your subscribers will easily recognize, like your website name or company name.'),
            'from_email'=> Yii::t('lists', 'This is the email of the "From" header used in campaigns, use a name that your subscribers will easily recognize, containing your website name or company name.'),
            'reply_to'  => Yii::t('lists', 'If a user replies to one of your campaigns, the reply will go to this email address. Make sure you check it often.'),
            'subject'   => Yii::t('lists', 'Default subject for campaigns, this can be changed for any particular campaign.'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'from_name' => Yii::t('lists', 'My Super Company INC'),
            'from_email'=> Yii::t('lists', 'newsletter@my-super-company.com'),
            'reply_to'  => Yii::t('lists', 'reply@my-super-company.com'),
            'subject'   => Yii::t('lists', 'Weekly newsletter'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function mergeWithCustomerInfo(Customer $customer)
    {
        $this->from_name     = $customer->getFullName();
        $this->from_email    = $customer->email;
        $this->reply_to      = $customer->email;

        return $this;
    }
}
