<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
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

class ProductPrice extends Shipping
{
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

        $price   = 0;
        $product = null;

        /** @var ProductCollectionItem $item */
        foreach ($objCollection->getItems() as $item) {
            if (!$item->hasProduct() || !$this->hasShippingPrice($item->getProduct())) {
                continue;
            }

            $cartProduct = $item->getProduct();
            $cartPrice   = $item->quantity * $cartProduct->{$this->attributeName};

            if ($cartPrice >= $price) {
                $price   = $cartPrice;
                $product = $cartProduct;
            }
        }

        return Isotope::calculatePrice($price, $product, $this->attributeName, $this->arrData['tax_class']);
    }

    /**
     * @param IsotopeProduct $product
     *
     * @return bool
     */
    private function hasShippingPrice(IsotopeProduct $product)
    {
        $attributes = array_merge($product->getType()->getAttributes(), $product->getType()->getVariantAttributes());

        if (!$product->isExemptFromShipping() && in_array($this->attributeName, $attributes, true)) {
            return true;
        }

        return false;
    }
}
