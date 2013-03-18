<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Tax;

/**
 * Class Surcharge
 *
 * Provide methods to handle Isotope product collection surcharges.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class ProductCollectionSurcharge extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_iso_collection_surcharge';

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
     * @return bool
     */
    public function hasTax()
    {
        return ($this->tax_class > 0 || !empty($this->arrProducts)) ? true : false;
    }

    /**
     * Get tax amount for an individual product
     * @param IsotopeProduct
     */
    public function getAmountForProduct(IsotopeProduct $objProduct)
    {
        if (isset($this->arrProducts[$objProduct->collection_id])) {

            return (float) $this->arrProducts[$objProduct->collection_id];
        }

        return 0;
    }

    /**
     * Set tax amount for a product
     * @param  float
     * @param  IsotopeProduct
     */
    public function setAmountForProduct($fltAmount, IsotopeProduct $objProduct)
    {
        if ($objProduct->collection_id == 0) {
            throw new \UnderflowException('Product must be in the cart (must have collection_id value)');
        }

        if ($fltAmount != 0) {
            $this->arrProducts[$objProduct->collection_id] = $fltAmount;
        } else {
            unset($this->arrProducts[$objProduct->collection_id]);
        }
    }

    /**
     * Split tax amount amongst collection products
     * @param IsotopeProductCollection
     */
    public function applySplittedTax(IsotopeProductCollection $objCollection)
    {
        $this->tax_class = 0;
        $this->before_tax = true;

        $arrProducts = $objCollection->getProducts();

        if (!$blnPercentage) {
            $fltTotal = $objCollection->taxFreeSubTotal;

            if ($fltTotal == 0) {
                return;
            }
        }

        foreach ($arrProducts as $objProduct)
        {
            if ($blnPercentage)
            {
                $fltProductPrice = $objProduct->total_price / 100 * $fltSurcharge;
            }
            else
            {
                $fltProductPrice = $this->total_price / 100 * (100 / $fltTotal * $objProduct->tax_free_total_price);
            }

            $fltProductPrice = $fltProductPrice > 0 ? (floor($fltProductPrice * 100) / 100) : (ceil($fltProductPrice * 100) / 100);

            $this->setAmountForProduct($fltProductPrice, $objProduct);
        }
    }

    /**
     * Add a tax number
     * @param int
     */
    public function addTaxNumber($intId)
    {
        if (!in_array($intId, $this->arrTaxIds)) {
            $this->arrTaxIds[] = $intId;
        }
    }

    /**
     * Get comma separated list of tax ids
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
        $this->arrTaxIds = deserialize($arrData['tax_ids']);

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
     * @param  IsotopeProductCollection
     * @return array
     */
    public static function findForCollection(IsotopeProductCollection $objCollection)
    {
        $arrPreTax = array();
        $arrPostTax = array();

        // !HOOK: get collection surcharges
        if (isset($GLOBALS['ISO_HOOKS']['findSurchargesForCollection']) && is_array($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['findSurchargesForCollection'] as $callback) {

                $objCallback = \System::importStatic($callback[0]);
                $arrResult = $objCallback->{$callback[1]}($objCollection);

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

        $arrTaxes = array();
        $arrProducts = $objCollection->getProducts();
        $arrAddresses = array('billing'=>$objCollection->billing_address, 'shipping'=>$objCollection->shipping_address);

        foreach ($arrProducts as $objProduct)
        {
            $objTaxClass = TaxClass::findByPk($objProduct->tax_class);

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $arrTaxIds = array();
            $fltPrice = $objProduct->total_price;

            foreach ($arrPreTax as $objSurcharge)
            {
                $fltPrice += $objSurcharge->getAmountForProduct($objProduct);
            }

            $objIncludes = $objTaxClass->getRelated('includes');

            if ($objIncludes->id > 0)
            {
                if ($objIncludes->isApplicable($fltPrice, $arrAddresses))
                {
                    $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                    if (!isset($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]))
                    {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id] = new Tax();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->label         = $objTaxClass->label ?: $objIncludes->label;
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price         = $objIncludes->getAmount() . ($objIncludes->isPercentage() ? '%' : '');
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price   = Isotope::getInstance()->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->add           = false;
                    }
                    else
                    {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::getInstance()->roundPrice(($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                        if (is_numeric($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price) && is_numeric($objIncludes->getAmount()))
                        {
                            $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price += $objIncludes->getAmount();
                        }
                    }

                    $taxId = array_search($objTaxClass->id . '_' . $objIncludes->id, array_keys($arrTaxes)) + 1;
                    $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addTaxNumber($taxId);
                    $arrTaxIds[] = $taxId;
                }
            }

            $objRates = $objTaxClass->getRelated('rates');

            if (null !== $objRates)
            {
                while ($objRates->next())
                {
                    $objTaxRate = $objRates->current();

                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses))
                    {
                        $fltTax = $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                        if (!isset($arrTaxes[$objTaxRate->id]))
                        {
                            $arrTaxes[$objTaxRate->id] = new Tax();
                            $arrTaxes[$objTaxRate->id]->label          = $objTaxRate->label;
                            $arrTaxes[$objTaxRate->id]->price          = $objTaxRate->getAmount() . ($objTaxRate->isPercentage() ? '%' : '');
                            $arrTaxes[$objTaxRate->id]->total_price    = Isotope::getInstance()->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                            $arrTaxes[$objTaxRate->id]->add            = true;
                        }
                        else
                        {
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::getInstance()->roundPrice(($arrTaxes[$objTaxRate->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                            if (is_numeric($arrTaxes[$objTaxRate->id]->price) && is_numeric($objTaxRate->getAmount()))
                            {
                                $arrTaxes[$objTaxRate->id]->price += $objTaxRate->getAmount();
                            }
                        }

                        $taxId = array_search($objTaxRate->id, array_keys($arrTaxes)) + 1;
                        $arrTaxes[$objTaxRate->id]->addTaxNumber($taxId);
                        $arrTaxIds[] = $taxId;

                        if ($objTaxRate->stop)
                        {
                            break;
                        }
                    }
                }
            }

            $strTaxId = implode(',', $arrTaxIds);

            if ($objProduct->tax_id != $strTaxId)
            {
                $objCollection->updateProduct($objProduct, array('tax_id'=>$strTaxId));
            }

            foreach ($arrPreTax as $objSurcharge)
            {
                if ($objSurcharge->getAmountForProduct($objProduct) > 0) {
                    foreach ($arrTaxIds as $taxId) {
                        $objSurcharge->addTaxNumber($taxId);
                    }
                }
            }
        }

        foreach ($arrPreTax as $objSurcharge)
        {
            $objTaxClass = TaxClass::findByPk($objSurcharge->tax_class);

            // Skip products without tax class
            if (null === $objTaxClass) {
                continue;
            }

            $fltPrice = $objSurcharge->total_price;

            $objIncludes = $objTaxClass->getRelated('includes');

            if ($objIncludes->id > 0)
            {
                if ($objIncludes->isApplicable($fltPrice, $arrAddresses))
                {
                    $fltTax = $objIncludes->calculateAmountIncludedInPrice($fltPrice);

                    if (!isset($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]))
                    {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id] = new Tax();
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->label         = $objTaxClass->label ?: $objIncludes->label;
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price         = $objIncludes->getAmount() . ($objIncludes->isPercentage() ? '%' : '');
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price   = Isotope::getInstance()->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->add           = false;
                    }
                    else
                    {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = Isotope::getInstance()->roundPrice(($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                        if (is_numeric($arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price) && is_numeric($objIncludes->getAmount()))
                        {
                            $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->price += $objIncludes->getAmount();
                        }
                    }

                    if ($objTaxClass->notNegative && $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price < 0)
                    {
                        $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->total_price = 0;
                    }

                    $taxId = array_search($objTaxClass->id . '_' . $objIncludes->id, array_keys($arrTaxes)) + 1;
                    $arrTaxes[$objTaxClass->id . '_' . $objIncludes->id]->addTaxNumber($taxId);
                    $objSurcharge->addTaxNumber($taxId);
                }
            }

            $objRates = $objTaxClass->getRelated('rates');

            if (null !== $objRates)
            {
                while ($objRates->next())
                {
                    $objTaxRate = $objRates->current();

                    if ($objTaxRate->isApplicable($fltPrice, $arrAddresses))
                    {
                        $fltTax = $objTaxRate->calculateAmountAddedToPrice($fltPrice);

                        if (!isset($arrTaxes[$objTaxRate->id]))
                        {
                            $arrTaxes[$objTaxRate->id] = new Tax();
                            $arrTaxes[$objTaxRate->id]->label          = $objTaxRate->label;
                            $arrTaxes[$objTaxRate->id]->price          = $objTaxRate->getAmount() . ($objTaxRate->isPercentage() ? '%' : '');
                            $arrTaxes[$objTaxRate->id]->total_price    = Isotope::getInstance()->roundPrice($fltTax, $objTaxClass->applyRoundingIncrement);
                            $arrTaxes[$objTaxRate->id]->add            = true;
                        }
                        else
                        {
                            $arrTaxes[$objTaxRate->id]->total_price = Isotope::getInstance()->roundPrice(($arrTaxes[$objTaxRate->id]->total_price + $fltTax), $objTaxClass->applyRoundingIncrement);

                            if (is_numeric($arrTaxes[$objTaxRate->id]->price) && is_numeric($objTaxRate->getAmount()))
                            {
                                $arrTaxes[$objTaxRate->id]->price += $objTaxRate->getAmount();
                            }
                        }

                        if ($objTaxClass->notNegative && $arrTaxes[$objTaxRate->id]->total_price < 0)
                        {
                            $arrTaxes[$objTaxRate->id]->total_price = 0;
                        }

                        $taxId = array_search($objTaxRate->id, array_keys($arrTaxes)) + 1;
                        $arrTaxes[$objTaxRate->id]->addTaxNumber($taxId);
                        $objSurcharge->addTaxNumber($taxId);

                        if ($objTaxRate->stop)
                        {
                            break;
                        }
                    }
                }
            }
        }

        return array_merge($arrPreTax, $arrTaxes, $arrPostTax);
    }

    /**
     * Return a model or collection based on the database result type
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '')
        {
            return null;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = \Model\QueryBuilder::find($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit']))
        {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset']))
        {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
        {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {
            $strClass = '\Isotope\Model\ProductCollectionSurcharge\\' . $objResult->type;

            return new $strClass($objResult);
        } else {

            return new \Isotope\Model\Collection\ProductCollectionSurcharge($objResult, static::$strTable);
        }
    }
}
