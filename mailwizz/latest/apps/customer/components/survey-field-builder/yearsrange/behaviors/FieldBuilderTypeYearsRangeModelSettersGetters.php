<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderTypeYearsRangeModelSettersGetters
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

class FieldBuilderTypeYearsRangeModelSettersGetters extends CBehavior
{
	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setYearStart($value)
	{
		return $this->owner->getModelMetaData()->add('year_start', $value);
	}

	/**
	 * @return mixed
	 */
	public function getYearStart()
	{
		return $this->owner->getModelMetaData()->itemAt('year_start');
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setYearEnd($value)
	{
		return $this->owner->getModelMetaData()->add('year_end', $value);
	}

	/**
	 * @return mixed
	 */
	public function getYearEnd()
	{
		return $this->owner->getModelMetaData()->itemAt('year_end');
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function setYearStep($value)
	{
		return $this->owner->getModelMetaData()->add('year_step', $value);
	}

	/**
	 * @return int
	 */
	public function getYearStep()
	{
		$step = (int)$this->owner->getModelMetaData()->itemAt('year_step');
		return $step <= 1 ? 1 : $step;
	}

	/**
	 * @return int
	 */
	public function getYearMin()
	{
		return date('Y') - 300;
	}

	/**
	 * @return int
	 */
	public function getYearMax()
	{
		return date('Y') + 300;
	}
}