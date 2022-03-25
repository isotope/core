<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductCategory;

use Contao\Backend;
use Isotope\Backend\Product\Label;
use Isotope\Model\Product;


class Callback extends Backend
{

    /**
     * List the products
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        $objProduct = Product::findByPk($row['pid']);

        return sprintf(
            '<span style="display:block;float:left;width:50px;">%s</span><span style="display:block;float:left;margin: 0 0 0 10px;padding: 18px 0;">%s</span>',
            Label::generateImage($objProduct),
            $objProduct->name
        );
    }
}
