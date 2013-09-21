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

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\Product;

/**
 * Build filter configuration for request cache
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Filter implements \ArrayAccess
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

    /**
     * Verify if filter value is a valid option
     * @param   array
     * @return  bool
     */
    public function valueNotIn(array $arrValues)
    {
        return !in_array($this->arrConfig['value'], $arrValues);
    }

    /**
     * Check if filter value equals given value
     * @param   mixed
     * @return  bool
     */
    public function valueEquals($value)
    {
        return ($this->arrConfig['value'] == $value);
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

        if ($group == '') {
            throw new \UnexpectedValueException('Group name can\'t be empty.');
        }

        $this->arrConfig['group'] = $group;

        return $this;
    }

    /**
     * Check if filter has a grouping
     * @return  bool
     */
    public function hasGroup()
    {
        return isset($this->arrConfig['group']);
    }

    /**
     * Get group name for this filter
     * @return  string
     */
    public function getGroup()
    {
        return (string) $this->arrConfig['group'];
    }

    /**
     * Check if product matches the filter
     * @param   IsotopeProduct
     * @return  bool
     */
    public static function matches(IsotopeProduct $objProduct)
    {
        if ($this->arrConfig['operator'] == '') {
            throw new \BadMethodCallException('Filter operator is not yet configured');
        }

        $varValues = $objProduct->{$this->arrConfig['attribute']};

        // If the attribute is not set for this product, we will ignore this attribute
        if ($varValues === null) {
            return true;
        } elseif (!is_array($varValues)) {
            $varValues = array($varValues);
        }

        foreach ($varValues as $varValue)
        {
            switch ($this->arrConfig['operator']) {
                case 'like':
                    if (stripos($varValue, $filter['value']) !== false) {
                        return true;
                    }
                    break;

                case 'gt':
                    if ($varValue > $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case 'lt':
                    if ($varValue < $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case 'gte':
                    if ($varValue >= $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case 'lte':
                    if ($varValue <= $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case 'neq':
                    if ($varValue != $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case 'eq':
                    if ($varValue == $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                default:
                    throw new \UnexpectedValueException('Unknown filter operator "' . $this->arrConfig['operator'] . '"');
            }
        }

        return false;
    }

    /**
     * Check if filter attribute is dynamic (can't use SQL filter then)
     * @return  bool
     */
    public function isDynamicAttribute()
    {
        return in_array($this->arrConfig['attribute'], Attribute::getDynamicAttributeFields());
    }

    /**
     * Check if filter attribute is dynamic (can't use SQL filter then)
     * @return  bool
     */
    public function isMultilingualAttribute()
    {
        return in_array($this->arrConfig['attribute'], Attribute::getMultilingualFields());
    }

    /**
     * Get WHERE statement for SQL filter
     * @return  string
     */
    public function sqlWhere()
    {
        if ($this->arrConfig['operator'] == '') {
            throw new \BadMethodCallException('Filter operator is not yet configured');
        }

        if ($this->isMultilingualAttribute()) {
            $strWhere = 'IFNULL(translation.' . $this->arrConfig['attribute'] . ', ' . Product::getTable() . '.' . $this->arrConfig['attribute'] . ')';
        } else {
            $strWhere = Product::getTable() . '.' . $this->arrConfig['attribute'];
        }

        switch ($this->arrConfig['operator']) {
            case 'like':
                $strWhere .= ' LIKE ?';
                break;

            case 'gt':
                $strWhere .= ' > ?';
                break;

            case 'lt':
                $strWhere .= ' < ?';
                break;

            case 'gte':
                $strWhere .= ' >= ?';
                break;

            case 'lte':
                $strWhere .= ' <= ?';
                break;

            case 'neq':
                $strWhere .= ' != ?';
                break;

            case 'eq':
                $strWhere .= ' = ?';
                break;

            default:
                throw new \UnexpectedValueException('Unknown filter operator "' . $this->arrConfig['operator'] . '"');
        }

        return $strWhere;
    }

    /**
     * Get value for SQL filter
     * @return  string
     */
    public function sqlValue()
    {
        return ($this->arrConfig['operator'] == 'like' ? ('%'.$this->arrConfig['value'].'%') : $this->arrConfig['value']);
    }

    /**
     * Get filter operator suitable for SQL query
     * @return string
     */
    public static function getOperatorForSQL()
    {
        if ($this->arrConfig['operator'] == '') {
            throw new \BadMethodCallException('Filter operator is not yet configured');
        }

        switch ($this->arrConfig['operator'])
        {
            case 'like':
                return 'LIKE';

            case 'gt':
                return '>';

            case 'lt':
                return '<';

            case 'gte':
                return '>=';

            case 'lte':
                return '<=';

            case 'neq':
                return '!=';

            case 'eq':
                return '=';

            default:
                throw new \UnexpectedValueException('Unknown filter operator "' . $this->arrConfig['operator'] . '"');
        }
    }

    /**
     * Make sure filter operator or value is not modified
     * @throws  \BadMethodCallException
     */
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
