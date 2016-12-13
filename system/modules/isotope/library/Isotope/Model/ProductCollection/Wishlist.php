<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollection;

use Isotope\Isotope;
use Isotope\Model\ProductCollection;

class Wishlist extends ProductCollection
{
    /**
     * @inheritDoc
     */
    public function getSurcharges()
    {
        return [];
    }
}
