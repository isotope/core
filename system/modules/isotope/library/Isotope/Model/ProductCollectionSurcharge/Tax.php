<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollectionSurcharge;

use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge;

/**
 * Implements tax surcharge in product collection
 */
class Tax extends ProductCollectionSurcharge implements IsotopeProductCollectionSurcharge
{

    /**
     * A tax class can never have tax!
     * @return bool
     */
    public function hasTax()
    {
        return false;
    }
}
