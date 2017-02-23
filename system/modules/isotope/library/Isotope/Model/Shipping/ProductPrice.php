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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\Shipping;

/**
 * Class ProductPrice
 */
class ProductPrice extends Shipping
{
    /**
     * Attribute name
     * @var string
     */
    private $attributeName = 'shipping_price';

    /**
     * @inheritdoc
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
            if (!in_array($this->attributeName, $item->getProduct()->getType()->getAttributes(), true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
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
            if (!$item->hasProduct()) {
                continue;
            }

            $cartProduct = $item->getProduct();

            if ($cartProduct->isExemptFromShipping()) {
                continue;
            }

            $cartPrice = $item->quantity * $cartProduct->{$this->attributeName};

            if ($cartPrice > $price) {
                $price   = $cartPrice;
                $product = $cartProduct;
            }
        }

        if ($product === null) {
            return 0;
        }

        return Isotope::calculatePrice($price, $product, $this->attributeName, $this->arrData['tax_class']);
    }
}
