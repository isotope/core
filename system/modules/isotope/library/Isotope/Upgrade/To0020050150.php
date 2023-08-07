<?php
/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2017 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Upgrade;

use Contao\Database;
use Contao\StringUtil;

class To0020050150 extends Base
{
    public function run($blnInstalled)
    {
        if (!$blnInstalled) {
            return;
        }

        $attributes = Database::getInstance()->execute(
            "SELECT field_name FROM tl_iso_attribute WHERE type='checkbox' AND (optionsSource='table' OR optionsSource='foreignKey')"
        )->fetchEach('field_name');

        if (empty($attributes)) {
            return;
        }

        $fields = implode(', ', $attributes);

        $products = Database::getInstance()->execute("SELECT id, $fields FROM tl_iso_product")->fetchAllAssoc();

        foreach ($products as $product) {
            $update = [];

            foreach ($attributes as $attribute) {
                if (0 !== strpos($product[$attribute], 'a:')) {
                    continue;
                }

                $value = StringUtil::deserialize($product[$attribute]);

                if (\is_array($value)) {
                    $update[$attribute] = implode(',', $value);
                }
            }

            if (empty($update)) {
                continue;
            }

            Database::getInstance()->prepare("UPDATE tl_iso_product %s WHERE id=?")->set($update)->execute($product['id']);
        }
    }
}
