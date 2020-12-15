<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BoxHeaderContent
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

class BoxHeaderContent
{
    /**
     * left side
     */
    const LEFT = 'left';

    /**
     * right side
     */
    const RIGHT = 'right';
    
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var string
     */
    protected $side = self::RIGHT;

    /**
     * @param string $side
     * @return BoxHeaderContent
     */
    public static function make($side = self::RIGHT)
    {
        return new self($side);
    }

    /**
     * BoxHeaderContent constructor.
     * @param string $side
     */
    public function __construct($side = self::RIGHT)
    {
        $this->side = $side;
    }

    /**
     * @param $item
     * @return $this
     */
    public function add($item)
    {
        $this->items[] = $item;  
        return $this;
    }

    /**
     * @param $item
     * @param $condition
     * @return $this
     */
    public function addIf($item, $condition)
    {
        if ($condition) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * @param bool $return
     * @return array
     */
    public function render($return = false)
    {
        $controller = Yii::app()->getController();
        $filterName = sprintf('box_header_%s_content', $this->side);
        $items      = array_filter(array_map('trim', (array)Yii::app()->hooks->applyFilters($filterName, $this->items, $controller)));
        
        if ($return) {
            return $items;
        }
        
        echo implode(PHP_EOL, $items);
    }
}
