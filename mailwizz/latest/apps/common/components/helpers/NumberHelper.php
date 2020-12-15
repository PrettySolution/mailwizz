<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * NumberHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.8
 */

class NumberHelper
{
	/**
	 * @param int $min
	 * @param int $max
	 * @param int $step
	 * @param string $prefix
	 *
	 * @return int
	 */
	public static function pullUniqueFromRange($min = 1, $max = 10, $step = 1, $prefix = '')
	{
		$range  = range($min, $max, $step);
		$key    = sha1($prefix . json_encode($range) . date('Ymd'));
		
		if (!Yii::app()->mutex->acquire($key, 5)) {
			shuffle($range);
			$number = array_shift($range);
			return $number;
		}

		$cachedRange = Yii::app()->cache->get($key);
		$cachedRange = !empty($cachedRange) && is_array($cachedRange) ? $cachedRange : $range;

		shuffle($cachedRange);

		$number = array_shift($cachedRange);

		Yii::app()->cache->set($key, $cachedRange);

		Yii::app()->mutex->release($key);

		return $number;
	}
}
