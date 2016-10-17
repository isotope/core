<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttributeWithRange;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to implement base price calculation
 */
class Price extends Attribute implements IsotopeAttributeWithRange
{
    /**
     * @inheritdoc
     */
    public function __construct(\Database\Result $objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'price';

        parent::__construct($objResult);
    }

    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['timePeriod'];
    }

    /**
     * @inheritdoc
     */
    public function getFrontendWidget()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getValue(IsotopeProduct $product)
    {
        return $product->getPrice()->getAmount();
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $objPrice = $objProduct->getPrice();

        if (null === $objPrice) {
            return '';
        }

        return $objPrice->generate($objProduct->getType()->showPriceTiers());
    }

    /**
     * Returns whether range filter can be used on this attribute.
     *
     * @return bool
     */
    public function allowRangeFilter()
    {
        return true;
    }
}
