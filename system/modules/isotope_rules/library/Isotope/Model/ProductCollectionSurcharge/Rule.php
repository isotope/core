<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollectionSurcharge;

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\Rule as RuleModel;

/**
 * Class Payment
 *
 * Implements payment surcharge in product collection.
 *
 * {@inheritdoc}
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class Rule extends ProductCollectionSurcharge implements IsotopeProductCollectionSurcharge
{

    public static function createForRuleInCollection(RuleModel $objRule, IsotopeProductCollection $objCollection)
    {
        // Cart subtotal
        if (
            (
                $objRule->minSubtotal > 0
                && $objCollection->getSubtotal() < $objRule->minSubtotal
            ) || (
                $objRule->maxSubtotal > 0
                && $objCollection->getSubtotal() > $objRule->maxSubtotal
            )
        ) {
            return null;
        }

        $arrCollectionItems = $objCollection->getItems();

        $blnMatch = false;
        $intTotal = 0;
        $arrLimit = array();

        $objSurcharge              = new static();
        $objSurcharge->label       = $objRule->getLabel();
        $objSurcharge->price       = $objRule->getPercentageLabel();
        $objSurcharge->total_price = 0;
        $objSurcharge->tax_class   = 0;
        $objSurcharge->before_tax  = true;
        $objSurcharge->addToTotal  = true;

        // Product or producttype restrictions
        if ($objRule->productRestrictions != '' && $objRule->productRestrictions != 'none') {
            $database = \Database::getInstance();

            $arrLimit = $database
                ->prepare('SELECT object_id FROM tl_iso_rule_restriction WHERE pid=? AND type=?')
                ->execute($objRule->id, $objRule->productRestrictions)
                ->fetchEach('object_id');

            if ($objRule->productRestrictions == 'pages' && !empty($arrLimit)) {
                $arrLimit = $database
                    ->prepare(
                        sprintf(
                            'SELECT pid FROM %s WHERE page_id IN (%s)',
                            \Isotope\Model\ProductCategory::getTable(),
                            implode(',', array_fill(0, count($arrLimit), '?'))
                        )
                    )
                    ->execute($arrLimit)
                    ->fetchEach('pid');
            }

            if ($objRule->quantityMode == 'cart_products' || $objRule->quantityMode == 'cart_items') {
                $intTotal = 0;
                foreach ($arrCollectionItems as $objItem) {
                    if (!$objItem->hasProduct()) {
                        continue;
                    }

                    $objProduct = $objItem->getProduct();

                    if (
                        (
                            (
                                $objRule->productRestrictions == 'products'
                                || $objRule->productRestrictions == 'variants'
                                || $objRule->productRestrictions == 'pages'
                            ) && (
                                in_array($objProduct->id, $arrLimit)
                                || (
                                    $objProduct->pid > 0
                                    && in_array($objProduct->pid, $arrLimit)
                                )
                            )
                        ) || (
                            $objRule->productRestrictions == 'producttypes'
                            && in_array($objProduct->type, $arrLimit)
                        )
                    ) {
                        $intTotal += $objRule->quantityMode == 'cart_items' ? $objItem->quantity : 1;
                    }
                }
            }
        } else {
            switch ($objRule->quantityMode) {
                case 'cart_products':
                    $intTotal = $objCollection->countItems();
                    break;

                case 'cart_items':
                    $intTotal = $objCollection->sumItemsQuantity();
                    break;
            }
        }

        foreach ($arrCollectionItems as $objItem) {
            if (!$objItem->hasProduct()) {
                continue;
            }

            $objProduct = $objItem->getProduct();

            // Product restrictions
            if (
                (
                    (
                        $objRule->productRestrictions == 'products'
                        || $objRule->productRestrictions == 'variants'
                        || $objRule->productRestrictions == 'pages'
                    )
                    && (
                        !in_array($objProduct->id, $arrLimit)
                        && (
                            $objProduct->pid == 0
                            || !in_array($objProduct->pid, $arrLimit)
                        )
                    )
                ) || (
                    $objRule->productRestrictions == 'producttypes'
                    && !in_array($objProduct->type, $arrLimit)
                )
            ) {
                continue;
            } elseif ($objRule->productRestrictions == 'attribute') {
                switch ($objRule->attributeCondition) {
                    case 'eq':
                        if (!($objProduct->{$objRule->attributeName} == $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'neq':
                        if (!($objProduct->{$objRule->attributeName} != $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'lt':
                        if (!($objProduct->{$objRule->attributeName} < $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'gt':
                        if (!($objProduct->{$objRule->attributeName} > $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'elt':
                        if (!($objProduct->{$objRule->attributeName} <= $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'egt':
                        if (!($objProduct->{$objRule->attributeName} >= $objRule->attributeValue)) {
                            continue(2);
                        }
                        break;

                    case 'starts':
                        if (stripos($objProduct->{$objRule->attributeName}, $objRule->attributeValue) !== 0) {
                            continue(2);
                        }
                        break;

                    case 'ends':
                        if (
                            strripos($objProduct->{$objRule->attributeName}, $objRule->attributeValue)
                            !== (strlen($objProduct->{$objRule->attributeName}) - strlen($objRule->attributeValue))
                        ) {
                            continue(2);
                        }
                        break;

                    case 'contains':
                        if (stripos($objProduct->{$objRule->attributeName}, $objRule->attributeValue) === false) {
                            continue(2);
                        }
                        break;

                    default:
                        throw new \Exception('Unknown rule condition "' . $objRule->attributeCondition . '"');
                }
            }

            // Because we apply to the quantity of only this product, we override $intTotal in every foreach loop
            if ($objRule->quantityMode != 'cart_products' && $objRule->quantityMode != 'cart_items') {
                $intTotal = $objItem->quantity;
            }

            // Quantity does not match, do not apply to this product
            if (
                (
                    $objRule->minItemQuantity > 0
                    && $objRule->minItemQuantity > $intTotal
                ) || (
                    $objRule->maxItemQuantity > 0
                    && $objRule->maxItemQuantity < $intTotal
                )
            ) {
                continue;
            }

            // Apply To
            switch ($objRule->applyTo) {
                case 'products':
                    $fltDiscount = $objRule->calculateDiscount($objItem->getTotalPrice(), $objItem->quantity);

                    $objSurcharge->total_price += $fltDiscount;
                    $objSurcharge->setAmountForCollectionItem($fltDiscount, $objItem);
                    break;

                case 'items':
                    $fltDiscount = $objItem->quantity * $objRule->calculateDiscount($objItem->getPrice());

                    $objSurcharge->total_price += $fltDiscount;
                    $objSurcharge->setAmountForCollectionItem($fltDiscount, $objItem);
                    break;

                case 'subtotal':
                    $blnMatch = true;

                    $objSurcharge->total_price += $objItem->getTotalPrice();

                    if ($objRule->tax_class == -1) {
                        $fltDiscount = $objRule->calculateDiscount($objItem->getTotalPrice(), $objItem->quantity);
                        $objSurcharge->setAmountForCollectionItem($fltDiscount, $objItem);
                    }
                    break;
            }
        }

        if ($objRule->applyTo == 'subtotal' && $blnMatch) {
            // discount total! not related to tax subtraction
            $fltDiscount = $objRule->calculateDiscount($objSurcharge->total_price);

            $objSurcharge->total_price = $fltDiscount;
            $objSurcharge->before_tax  = ($objRule->tax_class != 0 ? true : false);
            $objSurcharge->tax_class   = ($objRule->tax_class > 0 ? $objRule->tax_class : 0);

            // If fixed price discount with splitted taxes, calculate total amount of discount per taxed product
            /*
             * TODO: This seems to cannot work, $arrSubtract is not set anywhere?!
            if ($objRule->tax_class == -1 && !$blnPercentage) {
                foreach ($arrSubtract as $objItem) {
                    $fltPrice = $objRule->getDiscountValue() / 100 * (100 / $fltTotal * $objItem->getTaxFreeTotalPrice());
                    $objSurcharge->setAmountForCollectionItem($fltPrice, $objItem);
                }
            }
            */
        }

        return $objSurcharge->total_price == 0 ? null : $objSurcharge;
    }
}
