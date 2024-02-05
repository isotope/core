<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Attribute;

/**
 * Attribute to implement shipping price calculation
 *
 * @internal do not use, this class might be removed in a future minor release!
 */
class ShippingPrice extends Attribute
{
    /**
     * @inheritdoc
     */
    public function __construct($objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'shipping_price';

        parent::__construct($objResult);
    }

    /**
     * @inheritdoc
     */
    public function getFrontendWidget()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        return Isotope::formatPriceWithCurrency($objProduct->{$this->field_name});
    }
}
