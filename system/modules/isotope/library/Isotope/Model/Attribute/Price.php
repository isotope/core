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

use Isotope\Collection\ProductPrice as ProductPriceCollection;
use Isotope\Interfaces\IsotopeAttributeWithRange;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;
use Isotope\Model\ProductPrice;

/**
 * Attribute to implement base price calculation
 */
class Price extends Attribute implements IsotopeAttributeWithRange
{
    /**
     * @inheritdoc
     */
    public function __construct(\Contao\Database\Result $objResult = null)
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
        $price = $product->getPrice();

        if (!$price instanceof IsotopePrice) {
            return null;
        }

        return $price->getAmount();
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
     * {@inheritdoc}
     */
    public function allowRangeFilter()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueRange(IsotopeProduct $product)
    {
        $amounts = [];
        $price = $product->getPrice();

        if (!$price instanceof IsotopePrice) {
            return null;
        }

        if ($price instanceof ProductPriceCollection) {
            /** @var IsotopePrice $item */
            foreach ($price as $item) {
                if ($item instanceof ProductPrice) {
                    $amounts[] = $item->getLowestAmount();
                }

                $amounts[] = $item->getAmount();
            }
        } else {
            if ($price instanceof ProductPrice) {
                $amounts[] = $price->getLowestAmount();
            }

            $amounts[] = $price->getAmount();
        }

        return array_filter([min($amounts), max($amounts)]);
    }
}
