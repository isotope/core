<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollectionSurcharge;

use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge;

/**
 * Class Tax
 *
 * Implements tax surcharge in product collection
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
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
