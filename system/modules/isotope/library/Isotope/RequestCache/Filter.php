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

use Contao\StringUtil;
use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Interfaces\IsotopeAttributeWithRange;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\Product;

/**
 * Build filter configuration for request cache
 */
class Filter implements \ArrayAccess
{
    const CONTAINS      = 'like';
    const IN_ARRAY      = 'in';
    const EQUAL         = 'eq';
    const NOT_EQUAL     = 'neq';
    const GREATER_THAN  = 'gt';
    const GREATER_EQUAL = 'gte';
    const SMALLER_THAN  = 'lt';
    const SMALLER_EQUAL = 'lte';

    /**
     * Filter config
     */
    protected $arrConfig = array();

    /**
     * Prevent direct instantiation
     *
     * @param string $attribute
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
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return isset($this->arrConfig[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException('Use methods to set filter config');
    }

    /**
     * {@inheritdoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->arrConfig[$offset];
    }

    /**
     * Verify if filter value is a valid option
     *
     * @param array $arrValues
     *
     * @return bool
     */
    public function valueNotIn(array $arrValues)
    {
        return !\in_array($this->arrConfig['value'], $arrValues, false);
    }

    /**
     * Check if filter value equals given value
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function valueEquals($value)
    {
        return ($this->arrConfig['value'] == $value);
    }


    public function contains($value)
    {
        $this->filter(static::CONTAINS, $value);

        return $this;
    }

    public function inArray(array $value)
    {
        $this->filter(static::IN_ARRAY, $value);

        return $this;
    }

    public function isEqualTo($value)
    {
        $this->filter(static::EQUAL, $value);

        return $this;
    }

    public function isNotEqualTo($value)
    {
        $this->filter(static::NOT_EQUAL, $value);

        return $this;
    }

    public function isSmallerThan($value)
    {
        $this->filter(static::SMALLER_THAN, $value);

        return $this;
    }

    public function isSmallerOrEqualTo($value)
    {
        $this->filter(static::SMALLER_EQUAL, $value);

        return $this;
    }

    public function isGreaterThan($value)
    {
        $this->filter(static::GREATER_THAN, $value);

        return $this;
    }

    public function isGreaterOrEqualTo($value)
    {
        $this->filter(static::GREATER_EQUAL, $value);

        return $this;
    }

    public function groupBy($group)
    {
        if (\array_key_exists('group', $this->arrConfig)) {
            throw new \BadMethodCallException('Filter already has a group');
        }

        if ('' === (string) $group) {
            throw new \UnexpectedValueException('Group name can\'t be empty.');
        }

        $this->arrConfig['group'] = (string) $group;

        return $this;
    }

    /**
     * Check if filter has a grouping
     *
     * @return bool
     */
    public function hasGroup()
    {
        return \array_key_exists('group', $this->arrConfig);
    }

    /**
     * Get group name for this filter
     *
     * @return string
     */
    public function getGroup()
    {
        return (string) ($this->arrConfig['group'] ?? null);
    }

    /**
     * Check if product matches the filter
     *
     * @param IsotopeProduct $objProduct
     *
     * @return bool
     *
     * @throws \UnexpectedValueException
     */
    public function matches(IsotopeProduct $objProduct)
    {
        if ($this->arrConfig['operator'] == '') {
            throw new \BadMethodCallException('Filter operator is not yet configured');
        }

        $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$this->arrConfig['attribute']];

        if ($attribute instanceof IsotopeAttributeWithRange && $attribute->allowRangeFilter()) {
            $varValues = $attribute->getValueRange($objProduct);
        } elseif ($attribute instanceof IsotopeAttribute) {
            $varValues = $attribute->getValue($objProduct);
        } else {
            $varValues = $objProduct->{$this->arrConfig['attribute']};
        }

        // If the attribute is not set for this product, we will ignore this attribute
        if (null === $varValues) {
            return false;
        }

        if (!\is_array($varValues)) {
            $varValues = StringUtil::deserialize($varValues, true);
        }

        foreach ($varValues as $varValue) {
            switch ($this->arrConfig['operator']) {
                case static::CONTAINS:
                    if (stripos($varValue, $this->arrConfig['value']) !== false) {
                        return true;
                    }
                    break;

                case static::IN_ARRAY:
                    if (stripos($varValue, $this->arrConfig['value']) !== false) {
                        return true;
                    }
                    break;

                case static::GREATER_THAN:
                    if ($varValue > $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case static::SMALLER_THAN:
                    if ($varValue < $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case static::GREATER_EQUAL:
                    if ($varValue >= $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case static::SMALLER_EQUAL:
                    if ($varValue <= $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case static::NOT_EQUAL:
                    if ($varValue != $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                case static::EQUAL:
                    if ($varValue == $this->arrConfig['value']) {
                        return true;
                    }
                    break;

                default:
                    throw new \UnexpectedValueException(
                        'Unknown filter operator "' . $this->arrConfig['operator'] . '"'
                    );
            }
        }

        return false;
    }

    /**
     * Check if filter attribute is dynamic (can't use SQL filter then)
     *
     * @return bool
     */
    public function isDynamicAttribute()
    {
        return \in_array($this->arrConfig['attribute'], Attribute::getDynamicAttributeFields(), true);
    }

    /**
     * Check if filter attribute is dynamic (can't use SQL filter then)
     *
     * @return bool
     */
    public function isMultilingualAttribute()
    {
        return \in_array($this->arrConfig['attribute'], Attribute::getMultilingualFields(), true);
    }

    /**
     * Get WHERE statement for SQL filter
     *
     * @return string
     */
    public function sqlWhere()
    {
        $where = $this->getFieldForSQL() . ' ' . $this->getOperatorForSQL();

        if (static::IN_ARRAY === $this->arrConfig['operator']) {
            return $where;
        }

        return $where.' ?';
    }

    /**
     * Get value for SQL filter
     *
     * @return string|array
     */
    public function sqlValue()
    {
        if (static::CONTAINS === $this->arrConfig['operator']) {
            return ('%' . $this->arrConfig['value'] . '%');
        }

        return $this->arrConfig['value'];
    }

    /**
     * Get filter operator suitable for SQL query
     *
     * @return string
     */
    public function getOperatorForSQL()
    {
        if ('' === (string) $this->arrConfig['operator']) {
            throw new \BadMethodCallException('Filter operator is not yet configured');
        }

        switch ($this->arrConfig['operator']) {
            case static::CONTAINS:
                return 'LIKE';

            case static::IN_ARRAY:
                $total = \count((array) $this->arrConfig['value']);

                return 'IN ('.implode(',', array_fill(0, $total, '?')).')';

            case static::GREATER_THAN:
                return '>';

            case static::SMALLER_THAN:
                return '<';

            case static::GREATER_EQUAL:
                return '>=';

            case static::SMALLER_EQUAL:
                return '<=';

            case static::NOT_EQUAL:
                return '!=';

            case static::EQUAL:
                return '=';

            default:
                throw new \UnexpectedValueException('Unknown filter operator "' . $this->arrConfig['operator'] . '"');
        }
    }

    /**
     * Sets operator and value of the filter.
     *
     * @param string       $operator
     * @param string|array $value
     *
     * @throws \BadMethodCallException
     */
    protected function filter($operator, $value)
    {
        $this->preventModification();

        $this->arrConfig['operator'] = $operator;
        $this->arrConfig['value'] = $value;
    }

    /**
     * Make sure filter operator or value is not modified
     *
     * @throws \BadMethodCallException
     */
    protected function preventModification()
    {
        if (isset($this->arrConfig['operator']) || isset($this->arrConfig['value'])) {
            throw new \BadMethodCallException('Filter is already configured.');
        }
    }

    /**
     * Returns the compiled name of the SQL field (depending on multilingual attributes).
     *
     * @return string
     */
    protected function getFieldForSQL()
    {
        if ($this->isMultilingualAttribute() && Product::countTranslatedProducts()) {
            $field = sprintf(
                'IFNULL(translation.%s, %s.%s)',
                $this->arrConfig['attribute'],
                Product::getTable(),
                $this->arrConfig['attribute']
            );
        } else {
            $field = $this->arrConfig['attribute'];

            if (false === strpos($field, '.')) {
                $field = Product::getTable().'.'.$field;
            }
        }

        return $field;
    }

    /**
     * Create filter
     *
     * @param string $name
     *
     * @return static
     */
    public static function attribute($name)
    {
        return new static($name);
    }
}
