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
use Haste\Input\Input;
use Isotope\Interfaces\IsotopeOrderableCollection;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Address;
use Isotope\Model\Product;
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

            case 'favorites':
                return $this->getValueForCollectionTag(Isotope::getFavorites(), $tokens);

            case 'order':
                if (!Input::get('uid') || ($order = Order::findOneBy('uniqid', Input::get('uid'))) === null) {
                    return '';
                }

                return $this->getValueForCollectionTag($order, $tokens);

            case 'product':
                if (($product = $this->findCurrentProduct($tokens[2])) === null) {
                    return '';
                }

                return $this->getValueForProductTag($product, $tokens[1]);

            case 'isolabel':
                return $this->getValueForLabel($tokens[1], $tokens[2]);

            case 'isotope':
            case 'cache_isotope':
                return $this->getValueForIsotopeTag($tokens[1]);

            case 'product_price':
                if (($product = $this->findCurrentProduct($tokens[2])) === null) {
                    return '';
                }

                return $this->getPriceForProductTag($product, $tokens[1]);
        }

        return false;
    }

    /**
     * Replace insert tag for a product collection.
     *
     * @param IsotopeProductCollection $collection
     * @param array                    $tokens
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
                } else {
                    return sprintf('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')', $intCount);
                }
                break;

            case 'quantity_label':
                $intCount = $collection->sumItemsQuantity();

                if (!$intCount) {
                    return '';
                }

                if ($intCount == 1) {
                    return '(' . $GLOBALS['TL_LANG']['MSC']['productSingle'] . ')';
                } else {
                    return sprintf('(' . $GLOBALS['TL_LANG']['MSC']['productMultiple'] . ')', $intCount);
                }
                break;

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
     * @param Address     $address
     * @param string|null $attribute
     *
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
     * Replace InsertTag for a product
     *
     * 2 possible use cases:
     * {{product::attribute}}                - gets the data of the current product
     *                                         (Product::getActive() or GET parameter "product")
     * {{product::attribute::product_id}}    - gets the data of the specified product ID
     *
     * @param IsotopeProduct $product
     * @param string         $attribute
     *
     * @return string
     */
    private function getValueForProductTag(IsotopeProduct $product, $attribute)
    {
        return $product->$attribute;
    }

    /**
     * Replaces given label from values in the translation table
     *
     * @param string $label    The label to be translated
     * @param string $language Optionally accepts the desired language, uses current language if empty
     *
     * @return string
     */
    private function getValueForLabel($label, $language = null)
    {
        return Translation::get($label, $language);
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

    /**
     * Find product based on InsertTag parameter
     *
     * @param int $id
     *
     * @return IsotopeProduct|null
     */
    private function findCurrentProduct($id = null)
    {
        if (null !== $id) {
            return Product::findAvailableByPk($id);
        } elseif (Product::getActive() !== null) {
            return Product::getActive();
        } else {
            return Product::findAvailableByIdOrAlias(Input::getAutoItem('product', false, true));
        }
    }

    /**
     * Replace InsertTag for a product price
     *
     * 2 possible use cases:
     * {{product_price::type}}               - gets the price of the current product
     *                                         (Product::getActive() or GET parameter "product")
     * {{productPrice::type::product_id}}    - gets the price of the specified product ID
     *
     * @param IsotopeProduct $product
     * @param string         $type
     *
     * @return string
     */
    private function getPriceForProductTag(IsotopeProduct $product, $type)
    {
        switch ($type) {
            case 'amount':
                return Isotope::formatPriceWithCurrency($product->getPrice()->getAmount());

            case 'original_amount':
                return Isotope::formatPriceWithCurrency($product->getPrice()->getOriginalAmount());

            case 'net_amount':
                return Isotope::formatPriceWithCurrency($product->getPrice()->getNetAmount());

            case 'gross_amount':
                return Isotope::formatPriceWithCurrency($product->getPrice()->getGrossAmount());

            case 'html':
            default:
                return Isotope::formatPriceWithCurrency($product->getPrice()->generate());
        }
    }
}
