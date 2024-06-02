<?php

/**
 * Haste utilities for Contao Open Source CMS
 *
 * Copyright (C) 2012-2013 Codefog & terminal42 gmbh
 *
 * @package    Haste
 * @link       http://github.com/codefog/contao-haste/
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Haste\Data;


class Collection implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    protected $data;


    public function __construct(array $value, $label='', array $additional=array())
    {
        $this->data = array_merge(
            array(
                'value' => $value,
                'label' => $label
            ),
            $additional
        );
    }

    /**
     * Get a string representation of the data collection
     *
     * @return string
     */
    public function __toString()
    {
        $varValue = (($this->data['formatted'] ?? null) ?: $this->data['value']);

        if (is_array($varValue)) {
            return implode('', $varValue);
        }

        return (string) $varValue;
    }

    /**
     * Get array iterator on the value object
     *
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data['value']);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Retrieve array value
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * Set array value
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * Unset array value
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}
