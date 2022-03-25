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
class Limit
{

    /**
     * Limit config
     */
    protected $intLimit;


    /**
     * Prevent direct instantiation
     */
    protected function __construct($limit)
    {
        $this->intLimit = (int) $limit;
    }

    public function __sleep()
    {
        return array('intLimit');
    }

    /**
     * Check if current limit is not in a given list
     *
     * @param array $arrLimits
     *
     * @return bool
     */
    public function notIn($arrLimits)
    {
        return !\in_array($this->intLimit, $arrLimits);
    }

    /**
     * Check if limit equals value
     *
     * @param int $value
     *
     * @return bool
     */
    public function equals($value)
    {
        return ($this->intLimit === (int) $value);
    }

    /**
     * Get the limit value
     *
     * @return int
     */
    public function asInt()
    {
        return $this->intLimit;
    }

    /**
     * Create filter
     * @param   int
     * @return  Limit
     */
    public static function to($limit)
    {
        return new static($limit);
    }
}
