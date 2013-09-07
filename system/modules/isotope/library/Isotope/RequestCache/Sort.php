<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\RequestCache;

/**
 * Build sorting configuration for request cache
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Sort implements ArrayAccess
{

    /**
     * Filter config
     */
    protected $arrConfig = array();


    /**
     * Prevent direct instantiation
     */
    protected function __construct($direction)
    {
        $this->arrConfig = array($direction, SORT_REGULAR);
    }

    public function __sleep()
    {
        return array('arrConfig');
    }

    /**
     * @see     http://php.net/arrayaccess
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * @see     http://php.net/arrayaccess
     */
    public function offsetExists($offset)
    {
        return isset($this->arrConfig[$offset]);
    }

    /**
     * @see     http://php.net/arrayaccess
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * @see     http://php.net/arrayaccess
     */
    public function offsetGet($offset)
    {
        return $this->arrConfig[$offset];
    }

    /**
     * Treat values as numbers
     * @return  Sort
     */
    public function asNumbers()
    {
        $this->arrConfig[1] = SORT_NUMERIC;

        return $this;
    }

    /**
     * Treat values as strings
     * @return  Sort
     */
    public function asStrings()
    {
        $this->arrConfig[1] = SORT_STRING;

        return $this;
    }

    /**
     * Create filter
     */
    public static function ascending()
    {
        return new static(SORT_ASC);
    }

    public static function descending()
    {
        return new static(SORT_DESC);
    }
}
