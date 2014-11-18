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

namespace Isotope\Model;

use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Model\ProductCollectionSurcharge\Tax;

/**
 * Class Surcharge
 *
 * Provide methods to handle Isotope product collection surcharges.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class ProductCollectionSurcharge extends TypeAgent
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_product_collection_surcharge';

    /**
     * Interface to validate product collection surcharge
     * @var string
     */
    protected static $strInterface = '\Isotope\Interfaces\IsotopeProductCollectionSurcharge';

    /**
     * List of types (classes) for this model
     * @var array
     */
    protected static $arrModelTypes = array();

    /**
     * Tax amount for individual products
     * @var array
     */
    protected $arrProducts = array();

    /**
     * IDs of applicable taxes
     * @var array
     */
    protected $arrTaxIds = array();


    /**
     * Return if the surcharge has tax
     *
     * @return bool
     */
    public function hasTax()
    {
        return ($this->tax_class > 0 || !empty($this->arrProducts)) ? true : false;
    }

    /**
     * Get tax amount for an individual collection item
     *
     * @param ProductCollectionItem $objItem
     *
     * @return float
     */
    public function getAmountForCollectionItem(ProductCollectionItem $objItem)
    {
        if (isset($this->arrProducts[$objItem->id])) {

            return (float) $this->arrProducts[$objItem->id];
        }

        return 0;
    }

    /**
     * Set tax amount for a collection item
     *
     * @param float                 $fltAmount
     * @param ProductCollectionItem $objItem
     */
    public function setAmountForCollectionItem($fltAmount, ProductCollectionItem $objItem)
    {
        if ($fltAmount != 0) {
            $this->arrProducts[$objItem->id] = $fltAmount;
        } else {
            unset($this->arrProducts[$objItem->id]);
        }
    }

    /**
     * Update IDs of tax per product config
     *
     * @param array $arrIdMap
     *
     * @deprecated Deprecated since version 2.2, to be removed in 3.0.
     *             Surcharges are generated on the fly, so it does not make sense to convert item IDs
     */
    public function convertCollectionItemIds($arrIdMap)
    {
        $arrProducts = array();

        foreach ($this->arrProducts as $k => $v) {
            if (isset($arrIdMap[$k])) {
                $arrProducts[$arrIdMap[$k]] = $v;
            }
        }

        $this->arrProducts = $arrProducts;
    }


    /**
     * Split tax amount amongst collection products
     *
     * @param IsotopeProductCollection $objCollection
     * @param \Model                   $objSource
     */
    public function applySplittedTax(IsotopeProductCollection $objCollection, $objSource)
    {
        $this->tax_class  = 0;
        $this->before_tax = true;
        $fltTotal = 0;

        if (!$objSource->isPercentage()) {
            $fltTotal = $objCollection->getTaxFreeSubtotal();

            if ($fltTotal == 0) {
                return;
            }
        }

        foreach ($objCollection->getItems() as $objItem) {
            if ($objSource->isPercentage()) {
                $fltProductPrice = $objItem->getTotalPrice() / 100 * $objSource->getPercentage();
            } else {
                $fltProductPrice = $this->total_price / 100 * (100 / $fltTotal * $objItem->getTaxFreeTotalPrice());
            }

            $fltProductPrice = $fltProductPrice > 0 ? (floor($fltProductPrice * 100) / 100) : (ceil($fltProductPrice * 100) / 100);

            $this->setAmountForCollectionItem($fltProductPrice, $objItem);
        }
    }

    /**
     * Add a tax number
     *
     * @param int $intId
     */
    public function addTaxNumber($intId)
    {
        if (!in_array($intId, $this->arrTaxIds)) {
            $this->arrTaxIds[] = $intId;
        }
    }

    /**
     * Get comma separated list of tax ids
     *
     * @return string
     */
    public function getTaxNumbers()
    {
        return implode(',', $this->arrTaxIds);
    }

    /**
     * Set the current record from an array
     *
     * @param array $arrData The data record
     *
     * @return \Model The model object
     */
    public function setRow(array $arrData)
    {
        $this->arrProducts = deserialize($arrData['products']);
        $this->arrTaxIds   = deserialize($arrData['tax_ids']);

        if (!is_array($this->arrProducts)) {
            $this->arrProducts = array();
        }

        if (!is_array($this->arrTaxIds)) {
            $this->arrTaxIds = array();
        }

        unset($arrData['products'], $arrData['tax_ids']);

        return parent::setRow($arrData);
    }

    /**
     * Modify the current row before it is stored in the database
     *
     * @param array $arrSet The data array
     *
     * @return array The modified data array
     */
    protected function preSave(array $arrSet)
    {
        $arrSet['products'] = serialize($this->arrProducts);

        return $arrSet;
    }


    /**
     * Generate surhcharges for a collection
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return array
     */
    public static function findForCollection(IsotopeProductCollection $objCollection)
    {
        $arrPreTax  = array();
        $arrPostTax = array();

        // !HOOK: get collection surcharges
        if (isset($GLOBALS['ISO_HOOKS']['findSurchargesForCollection']) && is_array($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'] as $callback) {

                $objCallback = \System::importStatic($callback[0]);
                $arrResult   = $objCallback->{$callback[1]}($objCollection);

                foreach ($arrResult as $objSurcharge) {
                    if (!($objSurcharge instanceof IsotopeProductCollectionSurcharge) || $objSurcharge instanceof Tax) {
                        throw new \InvalidArgumentException('Instance of ' . get_class($objSurcharge) . ' is not a valid product collection surcharge.');
                    }

                    if ($objSurcharge->hasTax()) {
                        $arrPreTax[] = $objSurcharge;
                    } else {
                        $arrPostTax[] = $objSurcharge;
                    }
                }
            }
        }

        /** @var \Isotope\Model\ProductCollection\Order $objCollection */
        /** @var Tax[] $arrTaxes */
        $arrTaxes     = array();
        $arrAddresses = array('billing' => $objCollection->getBillingAddress(), 'shipping' => $objCollection->getShippingAddress());

        foreach ($objCollection->getItems() as $objItem) {
            // This should never happen, but we can't calculate it
            if (!$objItem->hasProduct()) {
                continue;
            }

            $objProduct  = $objItem->getProduct();

            /** @var \Isotope\Model\TaxClass $objTaxClass */
            $objTaxClass = $objProduct->getPrice() ? $objProduct->getPrice()->getRelated('tax_class') : null;

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $arrTaxIds = array();
            $fltPrice  = $objItem->getTotalPrice();

            /** @var \Isotope\Model\ProductCollectionSurcharge $objSurcharge */
            foreach ($arrPreTax as $objSurcharge) {
                $fltPrice += $objSurcharge->getAmountForCollectionItem($objItem);
            }

            /** @var \Isotope\Model\TaxRate $objIncludes */
            if (($objIncludes = $objTaxClass->getRelated('includes')) !== null) {
                if ($objIncludes->isApplicable($fltPrice, $arrAddresses)) {
                    $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                    if (!isset($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id])) {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]              = new Tax();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->label       = $objTaxClass->getLabel() ? : $objIncludes->getLabel();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price       = $objIncludes->getAmount() . ($objIncludes->isPercentage() ? '%' : '');
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addToTotal  = false;
                    } else {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::roundPrice(($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                        if (is_numeric($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price) && is_numeric($objIncludes->getAmount())) {
                            $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price += $objIncludes->getAmount();
                        }
                    }

                    $taxId = array_search($objTaxClass->id . '_' . $objIncludes->id, array_keys($arrTaxes)) + 1;
                    $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addTaxNumber($taxId);
                    $arrTaxIds[] = $taxId;
                }
            }

            if (($objRates = $objTaxClass->getRelated('rates')) !== null) {

                /** @var \Isotope\Model\TaxRate $objTaxRate */
                foreach ($objRates as $objTaxRate) {

                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses)) {
                        $fltTax = $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                        if (!isset($arrTaxes[$objTaxRate->id])) {
                            $arrTaxes[$objTaxRate->id]              = new Tax();
                            $arrTaxes[$objTaxRate->id]->label       = $objTaxRate->getLabel();
                            $arrTaxes[$objTaxRate->id]->price       = $objTaxRate->getAmount() . ($objTaxRate->isPercentage() ? '%' : '');
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                            $arrTaxes[$objTaxRate->id]->addToTotal  = true;
                        } else {
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::roundPrice(($arrTaxes[$objTaxRate->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                            if (is_numeric($arrTaxes[$objTaxRate->id]->price) && is_numeric($objTaxRate->getAmount())) {
                                $arrTaxes[$objTaxRate->id]->price += $objTaxRate->getAmount();
                            }
                        }

                        $taxId = array_search($objTaxRate->id, array_keys($arrTaxes)) + 1;
                        $arrTaxes[$objTaxRate->id]->addTaxNumber($taxId);
                        $arrTaxIds[] = $taxId;

                        if ($objTaxRate->stop) {
                            break;
                        }
                    }
                }
            }

            $strTaxId = implode(',', $arrTaxIds);

            if ($objItem->tax_id != $strTaxId) {
                $objCollection->updateItem($objItem, array('tax_id' => $strTaxId));
            }

            foreach ($arrPreTax as $objSurcharge) {
                if ($objSurcharge->getAmountForCollectionItem($objItem) > 0) {
                    foreach ($arrTaxIds as $taxId) {
                        $objSurcharge->addTaxNumber($taxId);
                    }
                }
            }
        }

        /** @var \Isotope\Model\ProductCollectionSurcharge $objSurcharge */
        foreach ($arrPreTax as $objSurcharge) {

            /** @var \Isotope\Model\TaxClass $objTaxClass */
            $objTaxClass = TaxClass::findByPk($objSurcharge->tax_class);

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $fltPrice = $objSurcharge->total_price;

            /** @var \Isotope\Model\TaxRate $objIncludes */
            if (($objIncludes = $objTaxClass->getRelated('includes')) !== null) {
                if ($objIncludes->isApplicable($fltPrice, $arrAddresses)) {
                    $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                    if (!isset($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id])) {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]              = new Tax();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->label       = $objTaxClass->getLabel() ? : $objIncludes->getLabel();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price       = $objIncludes->getAmount() . ($objIncludes->isPercentage() ? '%' : '');
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addToTotal  = false;
                    } else {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::roundPrice(($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                        if (is_numeric($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price) && is_numeric($objIncludes->getAmount())) {
                            $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price += $objIncludes->getAmount();
                        }
                    }

                    if ($objTaxClass->notNegative && $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price < 0) {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = 0;
                    }

                    $taxId = array_search($objTaxClass->id . '_' . $objIncludes->id, array_keys($arrTaxes)) + 1;
                    $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addTaxNumber($taxId);
                    $objSurcharge->addTaxNumber($taxId);
                }
            }

            if (($objRates = $objTaxClass->getRelated('rates')) !== null) {

                /** @var \Isotope\Model\TaxRate $objTaxRate */
                foreach ($objRates as $objTaxRate) {

                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses)) {
                        $fltTax = $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                        if (!isset($arrTaxes[$objTaxRate->id])) {
                            $arrTaxes[$objTaxRate->id]              = new Tax();
                            $arrTaxes[$objTaxRate->id]->label       = $objTaxRate->getLabel();
                            $arrTaxes[$objTaxRate->id]->price       = $objTaxRate->getAmount() . ($objTaxRate->isPercentage() ? '%' : '');
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                            $arrTaxes[$objTaxRate->id]->addToTotal  = true;
                        } else {
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::roundPrice(($arrTaxes[$objTaxRate->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                            if (is_numeric($arrTaxes[$objTaxRate->id]->price) && is_numeric($objTaxRate->getAmount())) {
                                $arrTaxes[$objTaxRate->id]->price += $objTaxRate->getAmount();
                            }
                        }

                        if ($objTaxClass->notNegative && $arrTaxes[$objTaxRate->id]->total_price < 0) {
                            $arrTaxes[$objTaxRate->id]->total_price = 0;
                        }

                        $taxId = array_search($objTaxRate->id, array_keys($arrTaxes)) + 1;
                        $arrTaxes[$objTaxRate->id]->addTaxNumber($taxId);
                        $objSurcharge->addTaxNumber($taxId);

                        if ($objTaxRate->stop) {
                            break;
                        }
                    }
                }
            }
        }

        return array_merge($arrPreTax, $arrTaxes, $arrPostTax);
    }


    /**
     * Create a payment surcharge
     *
     * @param IsotopePayment           $objPayment
     * @param IsotopeProductCollection $objCollection
     *
     * @return Payment
     */
    public static function createForPaymentInCollection(IsotopePayment $objPayment, IsotopeProductCollection $objCollection)
    {
        return static::buildSurcharge('Isotope\Model\ProductCollectionSurcharge\Payment', $GLOBALS['TL_LANG']['MSC']['paymentLabel'], $objPayment, $objCollection);
    }

    /**
     * Create a shipping surcharge
     *
     * @param IsotopeShipping          $objShipping
     * @param IsotopeProductCollection $objCollection
     *
     * @return Shipping
     */
    public static function createForShippingInCollection(IsotopeShipping $objShipping, IsotopeProductCollection $objCollection)
    {
        return static::buildSurcharge('Isotope\Model\ProductCollectionSurcharge\Shipping', $GLOBALS['TL_LANG']['MSC']['shippingLabel'], $objShipping, $objCollection);
    }


    /**
     * Build a product collection surcharge for given class type
     *
     * @param string                         $strClass
     * @param string                         $strLabel
     * @param IsotopePayment|IsotopeShipping $objSource
     * @param IsotopeProductCollection       $objCollection
     *
     * @return ProductCollectionSurcharge
     */
    protected static function buildSurcharge($strClass, $strLabel, $objSource, IsotopeProductCollection $objCollection)
    {
        $intTaxClass = $objSource->tax_class;

        /** @var \Isotope\Model\ProductCollectionSurcharge $objSurcharge */
        $objSurcharge = new $strClass();
        $objSurcharge->label = ($strLabel . ' (' . $objSource->getLabel() . ')');
        $objSurcharge->price = ($objSource->isPercentage() ? $objSource->getPercentage() . '%' : '&nbsp;');
        $objSurcharge->total_price = $objSource->getPrice();
        $objSurcharge->tax_free_total_price = $objSource->total_price;
        $objSurcharge->tax_class = $intTaxClass;
        $objSurcharge->before_tax = ($intTaxClass ? true : false);
        $objSurcharge->addToTotal = true;

        if ($intTaxClass == -1) {
            $objSurcharge->applySplittedTax($objCollection, $objSource);
        } elseif ($objSurcharge->tax_class > 0) {

            /** @var \Isotope\Model\TaxClass $objTaxClass */
            if (($objTaxClass = TaxClass::findByPk($objSurcharge->tax_class)) !== null) {

                /** @var \Isotope\Model\TaxRate $objIncludes */
                if (($objIncludes = $objTaxClass->getRelated('includes')) !== null) {

                    $fltPrice = $objSurcharge->total_price;
                    $arrAddresses = array('billing' => $objCollection->getBillingAddress(), 'shipping' => $objCollection->getShippingAddress());

                    if ($objIncludes->isApplicable($fltPrice, $arrAddresses)) {
                        $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);
                        $objSurcharge->tax_free_total_price = $fltPrice - $fltTax;
                    }
                }
            }
        }

        return $objSurcharge;
    }
}
