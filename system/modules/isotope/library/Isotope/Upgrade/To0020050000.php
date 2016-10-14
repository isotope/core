<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

class To0020050000 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        $this->convertShippingWeight();
    }

    private function convertShippingWeight()
    {
        $products = \Database::getInstance()->execute('SELECT id, shipping_weight FROM tl_iso_product');

        while ($products->next()) {
            if ('' === (string) $products->shipping_weight) {
                continue;
            }

            $weight = deserialize($products->shipping_weight);

            if (!is_array($weight)) {
                continue;
            }

            $value = sprintf(
                '%s %s',
                (string) ($weight['value'] ?: $weight[0]),
                (string) ($weight['unit'] ?: $weight[1])
            );

            \Database::getInstance()
                ->prepare('UPDATE tl_iso_product SET shipping_weight=? WHERE id=?')
                ->execute($value, $products->id)
            ;
        }
    }
}
