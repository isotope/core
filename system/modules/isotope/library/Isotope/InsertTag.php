<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2020 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\ProductBuilder;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Haste\Input\Input;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection\Order;

class InsertTag
{

    /**
     * Replace known Isotope insert tags.
     *
     * @param string $insertTag
     *
     * @return string|bool
     */
    public function replace($insertTag)
    {
        $tokens = StringUtil::trimsplit('::', $insertTag) + [null, null, null];

        switch ($tokens[0]) {
            case 'cart':
                return $this->getValueForCollectionTag(Isotope::getCart(), $tokens);

            case 'order':
                if (!Input::get('uid') || ($order = Order::findOneBy('uniqid', Input::get('uid'))) === null) {
                    return '';
                }

                return $this->getValueForCollectionTag($order, $tokens);

            case 'isolabel':
                return System::getContainer()->get('translator')->trans($tokens[1], [], 'isotope', $tokens[2] ?? null);

            case 'isotope':
            case 'cache_isotope':
                return $this->getValueForIsotopeTag($tokens[1]);
        }

        return false;
    }

    /**
     * Replace insert tag for a product collection.
     *
     *
     * @return string
     */
    private function getValueForCollectionTag(IsotopeProductCollection $collection, array $tokens)
    {
        switch ($tokens[1]) {
            case 'items':
                return $collection->countItems();

            case 'quantity':
                return $collection->sumItemsQuantity();

            case 'items_label':
                $intCount = $collection->countItems();

                if (!$intCount) {
                    return '';
                }

                if ($intCount == 1) {
                    return '(' . $GLOBALS['TL_LANG']['MSC']['productSingle'] . ')';
                }

                return sprintf('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')', $intCount);

            case 'quantity_label':
                $intCount = $collection->sumItemsQuantity();

                if (!$intCount) {
                    return '';
                }

                if ($intCount == 1) {
                    return '(' . $GLOBALS['TL_LANG']['MSC']['productSingle'] . ')';
                }

                return sprintf('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')', $intCount);

            case 'subtotal':
                return Isotope::formatPriceWithCurrency($collection->getSubtotal());

            case 'taxfree_subtotal':
                return Isotope::formatPriceWithCurrency($collection->getTaxFreeSubtotal());

            case 'total':
                return Isotope::formatPriceWithCurrency($collection->getTotal());

            case 'taxfree_total':
                return Isotope::formatPriceWithCurrency($collection->getTaxFreeTotal());

            case 'billing_address':
                if (!$collection instanceof IsotopeOrderableCollection
                    || ($address = $collection->getBillingAddress()) === null
                ) {
                    return '';
                }

                return $this->getValueForAddressTag($address, $tokens[2]);

            case 'shipping_address':
                if (!$collection instanceof IsotopeOrderableCollection
                    || !$collection->hasShipping()
                    || ($address = $collection->getShippingAddress()) === null
                ) {
                    return '';
                }

                return $this->getValueForAddressTag($address, $tokens[2]);

            case 'weight':
                return Isotope::formatPrice($collection->addToScale()->amountIn($tokens[2]), false);

            default:
                return $collection->{$tokens[1]};
        }
    }

    /**
     * Replace insert tag for an address
     *
     * @param string|null $attribute
     * @return string
     */
    private function getValueForAddressTag(Address $address, $attribute = null)
    {
        if (null === $attribute) {
            return $address->generate();
        }

        $tokens = $address->getTokens();

        return $tokens[$attribute];
    }

    /**
     * Replace {{isotope::}} insert tags.
     *
     * @param string $token
     *
     * @return string
     *
     * @deprecated Deprecated since version 2.3, to be removed in 3.0.
     */
    private function getValueForIsotopeTag($token)
    {
        if (strpos($token, 'cart_') === 0) {
            return $this->getValueForCollectionTag(Isotope::getCart(), array('cart', substr($token, 5)));
        }

        return '';
    }
}
