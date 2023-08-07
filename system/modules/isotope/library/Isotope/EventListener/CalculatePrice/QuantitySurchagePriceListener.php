<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

declare(strict_types=1);

namespace Isotope\EventListener\CalculatePrice;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute\QuantitySurcharge;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;
use Isotope\Model\TaxClass;

class QuantitySurchagePriceListener
{
    public function __invoke($fltPrice, $source, $field, $taxClassId, array $options)
    {
        /**
         * @var IsotopeProduct $product
         * @var ProductType $type
         */
        if (!$source instanceof ProductPrice
            || !($product = $source->getRelated('pid')) instanceof IsotopeProduct
            || null === ($type = $product->getType())
        ) {
            return $fltPrice;
        }

        $fltAmount = $fltPrice;
        $attributes = array_merge(
            $type->getAttributes(),
            $type->getVariantAttributes()
        );

        foreach ($attributes as $name) {
            $attribute = $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$name] ?? null;

            if ($attribute instanceof QuantitySurcharge) {
                $value = $product->{$name};
                $quantity = $options[$name];

                if ($quantity > 0 && $value <> 0) {
                    $amount = $quantity * $value;

                    $taxClass = $source->getRelated('tax_class');

                    if ('net_price' === $field && $taxClass instanceof TaxClass) {
                        $fltAmount += $taxClass->calculateNetPrice($amount);
                    } elseif ('gross_price' === $field && $taxClass instanceof TaxClass) {
                        $fltAmount += $taxClass->calculateGrossPrice($amount);
                    } else {
                        $fltAmount += $amount;
                    }
                }
            }
        }

        return $fltAmount;
    }
}
