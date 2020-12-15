<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * IpLocation
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.2
 */
 
/**
 * This is the model class for table "ip_location".
 *
 * The followings are the available columns in table 'ip_location':
 * @property string $location_id
 * @property string $ip_address
 * @property string $country_code
 * @property string $country_name
 * @property string $zone_name
 * @property string $city_name
 * @property string $latitude
 * @property string $longitude
 * @property string $timezone
 * @property integer $timezone_offset
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property CampaignTrackOpen[] $trackOpens
 * @property CampaignTrackUnsubscribe[] $trackUnsubscribes
 * @property CampaignTrackUrl[] $trackUrls
 */
class IpLocation extends ActiveRecord
{
    /**
     * @var
     */
    public $counter;
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{ip_location}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('ip_address, country_code, country_name, latitude, longitude', 'required'),
			array('ip_address', 'length', 'max'=>15),
			array('country_code', 'length', 'max'=>3),
			array('country_name, zone_name, city_name', 'length', 'max'=>150),
			array('latitude', 'length', 'max'=>10),
			array('longitude', 'length', 'max'=>11),
            array('timezone', 'length', 'max' => 100),
            array('timezone_offset', 'numerical'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'trackOpens'        => array(self::HAS_MANY, 'CampaignTrackOpen', 'location_id'),
            'trackUnsubscribes' => array(self::HAS_MANY, 'CampaignTrackUnsubscribe', 'location_id'),
            'trackUrls'         => array(self::HAS_MANY, 'CampaignTrackUrl', 'location_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'location_id'    => Yii::t('ip_location', 'Location'),
			'ip_address'     => Yii::t('ip_location', 'Ip address'),
			'country_code'   => Yii::t('ip_location', 'Country code'),
			'country_name'   => Yii::t('ip_location', 'Country name'),
			'zone_name'      => Yii::t('ip_location', 'Zone name'),
			'city_name'      => Yii::t('ip_location', 'City name'),
			'latitude'       => Yii::t('ip_location', 'Latitude'),
			'longitude'      => Yii::t('ip_location', 'Longitude'),
            'timezone'       => Yii::t('ip_location', 'Timezone'),
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

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return IpLocation the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param string $separator
     * @param array $attributes
     * @return string
     */
    public function getLocation($separator = ', ', array $attributes = array())
    {
        if (empty($attributes)) {
            $attributes = array('country_name', 'zone_name', 'city_name');
        }
        
        $location = array();
        foreach ($attributes as $attribute) {
            if (!empty($this->$attribute)) {
                $location[] = $this->$attribute;
            }
        }
        
        return implode($separator, $location);
    }

    /**
     * @param $ipAddress
     * @return IpLocation|null
     */
    public static function findByIp($ipAddress)
    {
        if (empty($ipAddress)) {
            return null;
        }
        
        static $cache = array();
        if (isset($cache[$ipAddress]) || array_key_exists($ipAddress, $cache)) {
            return $cache[$ipAddress];
        }
        
        $location = self::model()->findByAttributes(array(
            'ip_address' => $ipAddress,
        ));

        if (!empty($location)) {
            return $cache[$ipAddress] = $location;
        }
        
        $location = new self();
        $location->attributes = self::createAttributesFromResponse($ipAddress);
        $location->save();
        
        return $cache[$ipAddress] = $location;
    }

    /**
     * @param string $timezone
     * @param string $time
     * @return int
     */
    public static function getTimezoneOffset($timezone = 'UTC', $time = 'now')
    {
        try {
        
            $originDtz = new DateTimeZone($timezone);
            $originDt  = new DateTime($time, $originDtz);

            $offset = $originDtz->getOffset($originDt);
        
        } catch (Exception $e) {
            
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

            $offset = 0;
        }
        
        return $offset;
    }
    
    /**
     * @param $remote
     * @param string $origin
     * @return int
     */
    public static function calculateTimezonesOffset($remote, $origin = 'UTC')
    {
        return (int)self::getTimezoneOffset($remote) - (int)self::getTimezoneOffset($origin);
    }

    /**
     * @param $ipAddress
     * @return array
     */
    public static function createAttributesFromResponse($ipAddress)
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            return array();
        }

        $dbFile = Yii::app()->params['ip.location.maxmind.db.path'];

        static $hasDbFile = null;
        if ($hasDbFile === null) {
            $hasDbFile = is_file($dbFile);
        }

        if (!$hasDbFile) {
            return array();
        }

        $className = '\MaxMind\Db\Reader';
        $reader    = new $className($dbFile);

        try {
            $response = $reader->get($ipAddress);
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            $response = array();
        }

        $reader->close();

        if (empty($response)) {
            return array();
        }
        
        $attributes = array(
            'ip_address'    => $ipAddress,
            'country_code'  => !isset($response['country']['iso_code']) ? null : strtoupper($response['country']['iso_code']),
            'country_name'  => !isset($response['country']['names']['en']) ? null : ucwords(strtolower($response['country']['names']['en'])),
            'zone_name'     => !isset($response['subdivisions'][0]['names']['en']) ? null : ucwords(strtolower($response['subdivisions'][0]['names']['en'])),
            'city_name'     => !isset($response['city']['names']['en']) ? null : ucwords(strtolower($response['city']['names']['en'])),
            'latitude'      => !isset($response['location']['latitude']) ? 0 : (float)$response['location']['latitude'],
            'longitude'     => !isset($response['location']['longitude']) ? 0 : (float)$response['location']['longitude'],
            'timezone'      => !isset($response['location']['time_zone']) ? null : $response['location']['time_zone'],
        );
        
        $attributes['timezone_offset'] = empty($attributes['timezone']) ? null : self::calculateTimezonesOffset($attributes['timezone']);
        
        return $attributes;
    }

    /**
     * @return bool
     */
    public function updateTimezoneInfo()
    {
        if ($this->timezone !== null && $this->timezone_offset !== null) {
            return true;
        }
        
        if (!($attributes = self::createAttributesFromResponse($this->ip_address))) {
            return false;
        }
        
        if ($attributes['timezone'] === null || $attributes['timezone_offset'] === null) {
            return false;
        }
        
        $this->attributes = $attributes;
        
        return $this->save();
    }
}
