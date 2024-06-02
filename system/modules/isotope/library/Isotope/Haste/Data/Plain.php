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


class Plain extends \ArrayObject
{

    /**
     * @param array|null|object $value
     * @param string            $label
     * @param array             $additional
     */
    public function __construct($value, $label='', array $additional=array())
    {
        $values = array_merge(
            array(
                'value' => $value,
                'label' => $label
            ),
            $additional
        );

        parent::__construct($values, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $varValue = ($this->formatted ?? $this->value);

        if (is_array($varValue)) {
            return implode(', ', $varValue);
        }

        return (string) $varValue;
    }
}
