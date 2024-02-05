<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\RequestCache;

/**
 * Build sorting configuration for request cache
 */
class Sort implements \ArrayAccess
{

    /**
     * Sorting config
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
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return isset($this->arrConfig[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->arrConfig[$offset];
    }

    /**
     * Return true if sorting is ascending
     *
     * @return bool
     */
    public function isAscending()
    {
        return ($this->arrConfig[0] == SORT_ASC);
    }

    /**
     * Return true if sorting is descending
     *
     * @return bool
     */
    public function isDescending()
    {
        return ($this->arrConfig[0] == SORT_DESC);
    }

    /**
     * Treat values as numbers
     *
     * @return $this
     */
    public function asNumbers()
    {
        $this->arrConfig[1] = SORT_NUMERIC;

        return $this;
    }

    /**
     * Treat values as strings
     *
     * @return $this
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
