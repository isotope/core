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

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\Shipping;

/**
 * @property string $productCalculation
 */
class ProductPrice extends Shipping
{
    public const PRICE_HIGHEST_PRODUCT = 'highestProduct';
    public const PRICE_LOWEST_PRODUCT = 'lowestProduct';
    public const PRICE_SUM_PRODUCTS = 'sumProducts';
    public const PRICE_HIGHEST_ITEM = 'highestItem';
    public const PRICE_LOWEST_ITEM = 'lowestItem';
    public const PRICE_SUM_ITEMS = 'sumItems';

    /**
     * @var string
     */
    private $attributeName = 'shipping_price';

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException on unknown quantity mode
     * @throws \UnexpectedValueException on unknown product type condition
     */
    public function isAvailable()
    {
        if (!parent::isAvailable()) {
            return false;
        }

        /** @var ProductCollectionItem $item */
        foreach (Isotope::getCart()->getItems() as $item) {
            if ($item->hasProduct() && $this->hasShippingPrice($item->getProduct())) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        $isFirst = true;
        $price = 0;
        $product = null;

        foreach ($objCollection->getItems() as $item) {
            $cartProduct = $item->getProduct();

            if (null === $cartProduct || !$this->hasShippingPrice($cartProduct)) {
                continue;
            }

            $shippingPrice = $cartProduct->{$this->attributeName};

            switch ($this->productCalculation) {
                case static::PRICE_SUM_PRODUCTS:
                    $price += $shippingPrice;
                    break;

                case static::PRICE_LOWEST_PRODUCT:
                    if ($isFirst || $shippingPrice < $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case static::PRICE_HIGHEST_PRODUCT:
                    if ($isFirst || $shippingPrice > $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case static::PRICE_SUM_ITEMS:
                    $price += ($item->quantity * $shippingPrice);
                    break;

                case static::PRICE_LOWEST_ITEM:
                    $shippingPrice = $item->quantity * $shippingPrice;

                    if ($isFirst || $shippingPrice < $price) {
                        $price = $shippingPrice;
                    }
                    break;

                case static::PRICE_HIGHEST_ITEM:
                default:
                    $shippingPrice = $item->quantity * $shippingPrice;

                    if ($isFirst || $shippingPrice >= $price) {
                        $price = $shippingPrice;

                        if (!$this->productCalculation) {
                            $product = $cartProduct;
                        }
                    }
                    break;
            }

            $isFirst = false;
        }

        // Legacy mode
        if (null !== $product) {
            return Isotope::calculatePrice($price, $product, $this->attributeName, $this->arrData['tax_class']);
        }

        return Isotope::calculatePrice($price, $this, 'price', $this->arrData['tax_class']);
    }

    /**
     * @return bool
     */
    private function hasShippingPrice(IsotopeProduct $product)
    {
        $attributes = array_merge($product->getType()->getAttributes(), $product->getType()->getVariantAttributes());

        if (!$product->isExemptFromShipping() && \in_array($this->attributeName, $attributes, true)) {
            return true;
        }

        return false;
    }
}
