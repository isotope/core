<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Isotope\Interfaces\IsotopeOrderableCollection;
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
 *
 * @property int    $id
 * @property int    $pid
 * @property int    $sorting
 * @property int    $tstamp
 * @property string $type
 * @property int    $source_id
 * @property string $label
 * @property string $price
 * @property float  $total_price
 * @property float  $tax_free_total_price
 * @property string $tax_id
 * @property bool   $before_tax
 * @property bool   $addToTotal
 * @property bool   $applyRoundingIncrement
 * @property array  $products
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
     * @param Model                    $objSource
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
        if (!\in_array($intId, $this->arrTaxIds)) {
            $this->arrTaxIds[] = (int) $intId;
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
     * @return Model The model object
     */
    public function setRow(array $arrData)
    {
        $this->arrProducts = StringUtil::deserialize($arrData['products']);
        $this->arrTaxIds   = explode(',', $arrData['tax_id']);

        if (!\is_array($this->arrProducts)) {
            $this->arrProducts = array();
        }

        if (!\is_array($this->arrTaxIds)) {
            $this->arrTaxIds = array();
        }

        unset($arrData['products'], $arrData['tax_id']);

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
        $arrSet['tax_id']   = implode(',', $this->arrTaxIds);

        return $arrSet;
    }


    /**
     * Generate surcharges for a collection
     *
     * Process:
     * 1. Collect surcharges (e.g. shipping and billing) from Isotope core and submodules using hook
     * 2. Split surcharges by "with or without tax"
     *    => surcharges without tax are placed after tax surcharges and ignored in the complex compilation step
     * 3. Run through all product collection items and calculate their tax amount
     * 4. Run through all surcharges with tax and calculate their tax amount
     *
     * @param IsotopeProductCollection|\Isotope\Model\ProductCollection\Order $objCollection
     *
     * @return array
     */
    public static function findForCollection(IsotopeProductCollection $objCollection)
    {
        $arrPreTax  = [];
        $arrPostTax = [];
        $arrTaxes   = [];

        // !HOOK: get collection surcharges
        if (isset($GLOBALS['ISO_HOOKS']['findSurchargesForCollection']) && \is_array($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'] as $callback) {
                $arrResult = System::importStatic($callback[0])->{$callback[1]}($objCollection);

                foreach ($arrResult as $objSurcharge) {
                    if (!($objSurcharge instanceof IsotopeProductCollectionSurcharge) || $objSurcharge instanceof Tax) {
                        throw new \InvalidArgumentException('Instance of ' . \get_class($objSurcharge) . ' is not a valid product collection surcharge.');
                    }

                    if ($objSurcharge->hasTax()) {
                        $arrPreTax[] = $objSurcharge;
                    } else {
                        $arrPostTax[] = $objSurcharge;
                    }
                }
            }
        }

        static::addTaxesForItems($arrTaxes, $objCollection, $arrPreTax);

        static::addTaxesForSurcharges(
            $arrTaxes,
            $arrPreTax,
            [
                'billing'  => $objCollection->getBillingAddress(),
                'shipping' => $objCollection->getShippingAddress(),
            ]
        );

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

        /** @var ProductCollectionSurcharge $objSurcharge */
        $objSurcharge = new $strClass();
        $objSurcharge->source_id = $objSource->id;
        $objSurcharge->label = sprintf($strLabel, $objSource->getLabel());
        $objSurcharge->price = ($objSource->isPercentage() ? $objSource->getPercentage() . '%' : '&nbsp;');
        $objSurcharge->total_price = $objSource->getPrice();
        $objSurcharge->tax_free_total_price = $objSurcharge->total_price;
        $objSurcharge->tax_class = $intTaxClass;
        $objSurcharge->before_tax = ($intTaxClass ? true : false);
        $objSurcharge->addToTotal = true;

        if ($intTaxClass == -1) {
            $objSurcharge->applySplittedTax($objCollection, $objSource);
        } elseif ($intTaxClass > 0) {

            /** @var TaxClass $objTaxClass */
            if (($objTaxClass = TaxClass::findByPk($intTaxClass)) !== null) {

                /** @var TaxRate $objIncludes */
                if (($objIncludes = $objTaxClass->getRelated('includes')) !== null) {

                    $fltPrice = $objSurcharge->total_price;
                    $arrAddresses = array(
                        'billing'  => $objCollection->getBillingAddress(),
                        'shipping' => $objCollection->getShippingAddress(),
                    );

                    if ($objIncludes->isApplicable($fltPrice, $arrAddresses)) {
                        $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);
                        $objSurcharge->tax_free_total_price = $fltPrice - $fltTax;
                    }
                }
            }
        }

        return $objSurcharge;
    }

    /**
     * Create or add taxes for each collection item
     *
     * @param Tax[]                        $arrTaxes
     * @param IsotopeOrderableCollection   $objCollection
     * @param ProductCollectionSurcharge[] $arrSurcharges
     * @param Address[]                    $arrAddresses
     */
    private static function addTaxesForItems(array &$arrTaxes, IsotopeProductCollection $objCollection, array $arrSurcharges, array $arrAddresses = null)
    {
        foreach ($objCollection->getItems() as $objItem) {

            // This should never happen, but we can't calculate it
            if (!$objItem->hasProduct()) {
                continue;
            }

            $objProduct  = $objItem->getProduct();

            /** @var TaxClass $objTaxClass */
            $objTaxClass = $objProduct->getPrice() ? $objProduct->getPrice()->getRelated('tax_class') : null;

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $arrTaxIds = [];
            $fltPrice  = $objItem->getTotalPrice();

            /** @var ProductCollectionSurcharge $objSurcharge */
            foreach ($arrSurcharges as $objSurcharge) {
                $fltPrice += $objSurcharge->getAmountForCollectionItem($objItem);
            }

            $productAddresses = $arrAddresses;

            if (null === $productAddresses) {
                $productAddresses = array(
                    'billing'  => $objCollection->getBillingAddress(),
                    'shipping' => $objProduct->isExemptFromShipping() ? $objCollection->getBillingAddress() : $objCollection->getShippingAddress(),
                );
            }

            /** @var TaxRate $objIncludes */
            if (($objIncludes = $objTaxClass->getRelated('includes')) !== null
                && $objIncludes->isApplicable($fltPrice, $productAddresses)
            ) {
                $addToTotal = static::getTaxAddState(false);
                $total = $addToTotal ? $objIncludes->calculateAmountAddedToPrice($fltPrice) : $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                $arrTaxIds[] = static::addTax(
                    $arrTaxes,
                    $objTaxClass->id . '_' . $objIncludes->id,
                    ($objTaxClass->getLabel() ?: $objIncludes->getLabel()),
                    $objIncludes->getAmount(),
                    $objIncludes->isPercentage(),
                    $total,
                    $objTaxClass->applyRoundingIncrement,
                    $addToTotal,
                    $objTaxClass->notNegative
                );
            }

            /** @var TaxRate[] $objRates */
            if (($objRates = $objTaxClass->getRelated('rates')) !== null) {
                foreach ($objRates as $objTaxRate) {

                    if ($objTaxRate->isApplicable($fltPrice, $productAddresses)) {
                        $addToTotal = static::getTaxAddState(true);
                        $total = $addToTotal ? $objTaxRate->calculateAmountAddedToPrice($fltPrice) : $objTaxRate->calculateAmountIncludedInPrice($fltPrice);

                        $arrTaxIds[] = static::addTax(
                            $arrTaxes,
                            $objTaxRate->id,
                            $objTaxRate->getLabel(),
                            $objTaxRate->getAmount(),
                            $objTaxRate->isPercentage(),
                            $total,
                            $objTaxClass->applyRoundingIncrement,
                            $addToTotal,
                            $objTaxClass->notNegative
                        );

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

            foreach ($arrSurcharges as $objSurcharge) {
                if ($objSurcharge->getAmountForCollectionItem($objItem) > 0) {
                    foreach ($arrTaxIds as $taxId) {
                        $objSurcharge->addTaxNumber($taxId);
                    }
                }
            }
        }
    }

    /**
     * Create or add taxes for pre-tax collection surcharges
     *
     * @param Tax[]                        $arrTaxes
     * @param ProductCollectionSurcharge[] $arrSurcharges
     * @param Address[]                    $arrAddresses
     */
    private static function addTaxesForSurcharges(array &$arrTaxes, array $arrSurcharges, array $arrAddresses)
    {
        foreach ($arrSurcharges as $objSurcharge) {

            /** @var TaxClass $objTaxClass */
            $objTaxClass = TaxClass::findByPk($objSurcharge->tax_class);

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $fltPrice = $objSurcharge->total_price;

            /** @var TaxRate $objIncludes */
            if (($objIncludes = $objTaxClass->getRelated('includes')) !== null
                && $objIncludes->isApplicable($fltPrice, $arrAddresses)
            ) {
                $addToTotal = static::getTaxAddState(false);
                $fltPrice = $addToTotal ? $objIncludes->calculateAmountAddedToPrice($fltPrice) : $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                $taxId = static::addTax(
                    $arrTaxes,
                    $objTaxClass->id . '_' . $objIncludes->id,
                    ($objTaxClass->getLabel() ?: $objIncludes->getLabel()),
                    $objIncludes->getAmount(),
                    $objIncludes->isPercentage(),
                    $fltPrice,
                    $objTaxClass->applyRoundingIncrement,
                    $addToTotal,
                    $objTaxClass->notNegative
                );

                $objSurcharge->addTaxNumber($taxId);
            }

            /** @var TaxRate[] $objRates */
            if (($objRates = $objTaxClass->getRelated('rates')) !== null) {
                foreach ($objRates as $objTaxRate) {

                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses)) {
                        $addToTotal = static::getTaxAddState(true);
                        $fltPrice = $addToTotal ? $objTaxRate->calculateAmountAddedToPrice($fltPrice) : $objTaxRate->calculateAmountIncludedInPrice($fltPrice);

                        $taxId = static::addTax(
                            $arrTaxes,
                            $objTaxRate->id,
                            $objTaxRate->getLabel(),
                            $objTaxRate->getAmount(),
                            $objTaxRate->isPercentage(),
                            $fltPrice,
                            $objTaxClass->applyRoundingIncrement,
                            $addToTotal,
                            $objTaxClass->notNegative
                        );

                        $objSurcharge->addTaxNumber($taxId);

                        if ($objTaxRate->stop) {
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Add tax amount to the array of taxes, creating a new instance of Tax model if necessary
     *
     * @param array  $arrTaxes
     * @param string $id
     * @param string $label
     * @param mixed  $price
     * @param bool   $isPercentage
     * @param float  $total
     * @param bool   $applyRoundingIncrement
     * @param bool   $addToTotal
     * @param bool   $notNegative
     *
     * @return int
     */
    private static function addTax(array &$arrTaxes, $id, $label, $price, $isPercentage, $total, $applyRoundingIncrement, $addToTotal, $notNegative)
    {
        $objTax = $arrTaxes[$id] ?? null;

        if (!$objTax instanceof Tax) {
            $objTax                         = new Tax();
            $objTax->label                  = $label;
            $objTax->price                  = $price . ($isPercentage ? '%' : '');
            $objTax->total_price            = $total;
            $objTax->addToTotal             = $addToTotal;
            $objTax->applyRoundingIncrement = $applyRoundingIncrement;

            $arrTaxes[$id]       = $objTax;
        } else {
            $objTax->total_price = ($objTax->total_price + $total);

            if (is_numeric($objTax->price) && is_numeric($price)) {
                $objTax->price += $price;
            }
        }

        if ($notNegative && $objTax->total_price < 0) {
            $objTax->total_price = 0;
        }

        $taxId = array_search($id, array_keys($arrTaxes)) + 1;
        $objTax->addTaxNumber($taxId);

        return $taxId;
    }

    /**
     * Get "add to total" state for tax rate
     *
     * @param bool $default The legacy state (if tax was included in backend price)
     *
     * @return bool
     */
    private static function getTaxAddState($default)
    {
        switch (Isotope::getConfig()->getPriceDisplay()) {
            case Config::PRICE_DISPLAY_NET:
                return true;

            case Config::PRICE_DISPLAY_GROSS:
            case Config::PRICE_DISPLAY_FIXED:
                return false;

            case Config::PRICE_DISPLAY_LEGACY:
            default:
                return $default;
        }
    }
}
