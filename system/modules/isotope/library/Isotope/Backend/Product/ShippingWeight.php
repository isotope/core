<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

class ShippingWeight
{
    /**
     * load_callback for tl_iso_product.shipping_weight
     *
     * @param mixed $value
     *
     * @return array
     */
    public function onLoad($value)
    {
        list($value, $unit) = explode(' ', $value, 2);

        return [
            'value' => $value,
            'unit'  => $unit
        ];
    }

    /**
     * save_callback for tl_iso_product.shipping_weight
     *
     * @param mixed $value
     *
     * @return string
     */
    public function onSave($value)
    {
        $value = deserialize($value);

        if (!is_array($value)) {
            return null;
        }

        return (string) $value['value'] . ' ' . (string) $value['unit'];
    }
}
