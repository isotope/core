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
class Limit
{

    /**
     * Limit config
     */
    protected $intLimit = null;


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
     * @return  bool
     */
    public function notIn($arrLimits)
    {
        return !in_array($this->intLimit, $arrLimits);
    }

    /**
     * Check if limit equals value
     * @param   int
     * @return  bool
     */
    public function equals($value)
    {
        return ($this->intLimit === (int) $value);
    }

    /**
     * Get the limit value
     * @return  int
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
