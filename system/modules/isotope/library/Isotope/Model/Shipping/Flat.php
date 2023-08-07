<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Shipping;

use Contao\StringUtil;
use Haste\Units\Mass\Weight;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Shipping;

/**
 * Class Flat
 *
 * @property string $flatCalculation
 * @property string $flatWeight
 */
class Flat extends Shipping
{
    /**
     * @inheritdoc
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if ('' === (string) $this->arrData['price']) {
            return null;
        }

        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if ($this->isPercentage()) {
            $fltPrice = $objCollection->getSubtotal() / 100 * $this->getPercentage();
        } else {
            $fltPrice = (float) $this->arrData['price'];
        }

        if ('perProduct' === $this->flatCalculation || 'perItem' === $this->flatCalculation) {
            $arrItems      = $objCollection->getItems();
            $intMultiplier = 0;

            foreach ($arrItems as $objItem) {
                if (!$objItem->hasProduct() || $objItem->getProduct()->isExemptFromShipping()) {
                    continue;
                }

                if ('calculation' === $this->product_types_condition) {
                    $allowedTypes = StringUtil::deserialize($this->product_types);
                    $productType  = $objItem->getProduct()->getType();

                    if (\is_array($allowedTypes) && !\in_array($productType->id, $allowedTypes, false)) {
                        continue;
                    }
                }

                $intMultiplier += ('perProduct' === $this->flatCalculation) ? 1 : $objItem->quantity;
            }

            $fltPrice *= $intMultiplier;
        }

        if ('perWeight' === $this->flatCalculation) {
            $weight = Weight::createFromTimePeriod($this->flatWeight);

            if ($weight) {
                $cartWeight = floor($objCollection->addToScale()->amountIn($weight->getWeightUnit()));
                $multiply = floor($weight->getWeightValue());

                if ($multiply >= 1) {
                    $fltPrice = $fltPrice * ($cartWeight / $multiply);
                }
            }
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }
}
