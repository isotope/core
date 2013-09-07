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
 * Build filter configuration for request cache
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Filter implements ArrayAccess
{

    /**
     * Filter config
     */
    protected $arrConfig = array();


    /**
     * Prevent direct instantiation
     */
    protected function __construct($attribute)
    {
        $this->arrConfig['attribute'] = $attribute;
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


    public function contains($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'like';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isEqualTo($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'eq';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isNotEqualTo($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'neq';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isSmallerThan($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'lt';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isSmallerOrEqualTo($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'lte';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isGreaterThan($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'gt';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function isGreaterOrEqualTo($value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = 'gte';
        $this->arrConfig['value'] = $value;

        return $this;
    }

    public function groupBy($group)
    {
        if (isset($this->arrConfig['group'])) {
            throw new \BadMethodCallException('Filter already has a group');
        }

        $this->arrConfig['group'] = $group;

        return $this;
    }

    protected function preventModification()
    {
        if (isset($this->arrConfig['operator']) || isset($this->arrConfig['value'])) {
            throw new \BadMethodCallException('Filter is already configured.');
        }
    }

    /**
     * Create filter
     */
    public static function attribute($name)
    {
        return new static($name);
    }
}
