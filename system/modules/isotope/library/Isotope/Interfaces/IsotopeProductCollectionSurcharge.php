<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;

use Isotope\Interfaces\IsotopeProduct;

/**
 * IsotopeProductCollectionSurcharge interface defines an Isotope collection surcharge
 */
interface IsotopeProductCollectionSurcharge
{

    public function hasTax();

    public function getAmountForProduct(IsotopeProduct $objProduct);

    public function setAmountForProduct($fltAmount, IsotopeProduct $objProduct);
}
