<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\ProductCollectionSurcharge;

use Contao\Database;
use Contao\System;
use Haste\Units\Mass\Weight;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Isotope;
use Isotope\Model\ProductCategory;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\Rule as RuleModel;

/**
 * Implements payment surcharge in product collection
 */
class Rule extends ProductCollectionSurcharge implements IsotopeProductCollectionSurcharge
{

    public static function createForRuleInCollection(RuleModel $objRule, IsotopeProductCollection $objCollection)
    {
        // Cart subtotal
        if (($objRule->minSubtotal > 0 && $objCollection->getSubtotal() < $objRule->minSubtotal) || ($objRule->maxSubtotal > 0 && $objCollection->getSubtotal() > $objRule->maxSubtotal)) {
            return null;
        }

        // Cart weight
        $objScale = Isotope::getCart()->addToScale();

        if (($minWeight = Weight::createFromTimePeriod($objRule->minWeight)) !== null
            && $objScale->isLessThan($minWeight)
        ) {
            return null;
        }

        if (($maxWeight = Weight::createFromTimePeriod($objRule->maxWeight)) !== null
            && $objScale->isMoreThan($maxWeight)
        ) {
            return null;
        }

        // !HOOK: modify if rule is available
        if (isset($GLOBALS['ISO_HOOKS']['ruleAvailable']) && \is_array($GLOBALS['ISO_HOOKS']['ruleAvailable'])) {
            foreach ($GLOBALS['ISO_HOOKS']['ruleAvailable'] as $callback) {
                if (!System::importStatic($callback[0])->{$callback[1]}($objRule, $objCollection)) {
                    return null;
                }
            }
        }

        $arrCollectionItems = $objCollection->getItems();

        $blnMatch      = false;
        $blnPercentage = $objRule->isPercentage();
        $fltDiscount   = $blnPercentage ? $objRule->getPercentage() : 0;
        $fltTotal      = 0;
        $arrSubtract   = array();
        $productCondition = (bool) $objRule->productCondition;

        $objSurcharge = new static();
        $objSurcharge->label = $objRule->getLabel();
        $objSurcharge->price = $objRule->getPercentageLabel();
        $objSurcharge->total_price = 0;
        $objSurcharge->tax_class = 0;
        $objSurcharge->before_tax = true;
        $objSurcharge->addToTotal = true;

        // Product or producttype restrictions
        if ($objRule->productRestrictions != '' && $objRule->productRestrictions != 'none') {
            $arrLimit = Database::getInstance()->execute("SELECT object_id FROM tl_iso_rule_restriction WHERE pid={$objRule->id} AND type='{$objRule->productRestrictions}'")->fetchEach('object_id');

            if ($objRule->productRestrictions == 'pages' && !empty($arrLimit)) {
                $arrLimit = Database::getInstance()->execute("SELECT pid FROM " . ProductCategory::getTable() . " WHERE page_id IN (" . implode(',', $arrLimit) . ")")->fetchEach('pid');
            }

            if ($objRule->quantityMode == 'cart_products' || $objRule->quantityMode == 'cart_items') {
                $intTotal = 0;
                foreach ($arrCollectionItems as $objItem) {
                    if (!$objItem->hasProduct()) {
                        continue;
                    }

                    $objProduct = $objItem->getProduct();

                    if ((($objRule->productRestrictions == 'products' || $objRule->productRestrictions == 'variants' || $objRule->productRestrictions == 'pages')
                            && (\in_array($objProduct->id, $arrLimit) === $productCondition || ($objProduct->pid > 0 && \in_array($objProduct->pid, $arrLimit) === $productCondition)))
                        || ($objRule->productRestrictions == 'producttypes' && \in_array($objProduct->type, $arrLimit) === $productCondition)
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
            // FIXME
            if ((($objRule->productRestrictions == 'products' || $objRule->productRestrictions == 'variants' || $objRule->productRestrictions == 'pages')
                    && (\in_array($objProduct->id, $arrLimit) !== $productCondition && ($objProduct->pid == 0 || \in_array($objProduct->pid, $arrLimit) !== $productCondition)))
                || ($objRule->productRestrictions == 'producttypes' && \in_array($objProduct->type, $arrLimit) !== $productCondition)
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
                        if ($objProduct->{$objRule->attributeName} >= $objRule->attributeValue) {
                            continue(2);
                        }
                        break;

                    case 'gt':
                        if ($objProduct->{$objRule->attributeName} <= $objRule->attributeValue) {
                            continue(2);
                        }
                        break;

                    case 'elt':
                        if ($objProduct->{$objRule->attributeName} > $objRule->attributeValue) {
                            continue(2);
                        }
                        break;

                    case 'egt':
                        if ($objProduct->{$objRule->attributeName} < $objRule->attributeValue) {
                            continue(2);
                        }
                        break;

                    case 'starts':
                        if (stripos($objProduct->{$objRule->attributeName}, $objRule->attributeValue) !== 0) {
                            continue(2);
                        }
                        break;

                    case 'ends':
                        if (strripos($objProduct->{$objRule->attributeName}, $objRule->attributeValue) !== (\strlen($objProduct->{$objRule->attributeName}) - \strlen($objRule->attributeValue))) {
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
            // This matches tl_iso_rules.quantityMode="product_quantity"
            if ($objRule->quantityMode != 'cart_products' && $objRule->quantityMode != 'cart_items') {
                $intTotal = $objItem->quantity;
            }

            // Quantity does not match, do not apply to this product
            if (($objRule->minItemQuantity > 0 && $objRule->minItemQuantity > $intTotal) || ($objRule->maxItemQuantity > 0 && $objRule->maxItemQuantity < $intTotal)) {
                continue;
            }

            // Apply To
            switch ($objRule->applyTo) {
                case 'products':
                    $fltPrice = (float) ($blnPercentage ? ($objItem->getTotalPrice() / 100 * $fltDiscount) : $objRule->discount);
                    $fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
                    $objSurcharge->total_price += $fltPrice;
                    $objSurcharge->setAmountForCollectionItem($fltPrice, $objItem);
                    break;

                case 'items':
                    $fltPrice = ((float) ($blnPercentage ? ($objItem->getPrice() / 100 * $fltDiscount) : $objRule->discount)) * $objItem->quantity;
                    $fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
                    $objSurcharge->total_price += $fltPrice;
                    $objSurcharge->setAmountForCollectionItem($fltPrice, $objItem);
                    break;

                case 'subtotal':
                    $blnMatch = true;
                    $objSurcharge->total_price += $objItem->getTotalPrice();

                    if ($objRule->tax_class == -1) {
                        if ($blnPercentage) {
                            $fltPrice = $objItem->getTotalPrice() / 100 * $fltDiscount;
                            $objSurcharge->setAmountForCollectionItem($fltPrice, $objItem);
                        } else {
                            $arrSubtract[] = $objItem;
                            $fltTotal += (float) $objItem->getTaxFreeTotalPrice();
                        }
                    }
                    break;
            }
        }

        if ($objRule->applyTo == 'subtotal' && $blnMatch) {
            // discount total! not related to tax subtraction
            $fltPrice = (float) ($blnPercentage ? ($objSurcharge->total_price / 100 * $fltDiscount) : $objRule->discount);
            $objSurcharge->total_price = $fltPrice > 0 ? (floor(round($fltPrice * 100, 4)) / 100) : (ceil(round($fltPrice * 100, 4)) / 100);
            $objSurcharge->before_tax = ($objRule->tax_class != 0 ? true : false);
            $objSurcharge->tax_class = ($objRule->tax_class > 0 ? $objRule->tax_class : 0);

            // If fixed price discount with splitted taxes, calculate total amount of discount per taxed product
            if ($objRule->tax_class == -1 && !$blnPercentage) {
                foreach ($arrSubtract as $objItem) {
                    $fltPrice = $objRule->discount / 100 * (100 / $fltTotal * $objItem->getTaxFreeTotalPrice());
                    $objSurcharge->setAmountForCollectionItem($fltPrice, $objItem);
                }
            }
        }

        return $objSurcharge->total_price == 0 ? null : $objSurcharge;
    }
}
