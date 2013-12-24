<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Interfaces;

use Isotope\Model\ProductCollectionItem;

/**
 * IsotopeProductCollectionSurcharge interface defines an Isotope collection surcharge
 */
interface IsotopeProductCollectionSurcharge
{

    public function hasTax();

    public function getAmountForCollectionItem(ProductCollectionItem $objItem);

    public function setAmountForCollectionItem($fltAmount, ProductCollectionItem $objItem);

    public function addTaxNumber($intTax);

    public function getTaxNumbers();
}
