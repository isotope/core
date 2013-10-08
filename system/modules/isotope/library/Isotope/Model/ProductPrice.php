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
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;


/**
 * ProductPrice defines an advanced price of a product
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductPrice extends \Model implements IsotopePrice
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_prices';

    /**
     * Tiers for this price
     * @var array
     */
    protected $arrTiers = array();

    /**
     * Construct the object
     * @param   array
     * @param   array
     * @param   boolean
     */
    public function __construct(\Database\Result $objResult=null)
    {
        parent::__construct($objResult);

        $objTiers = \Database::getInstance()->prepare("SELECT * FROM tl_iso_price_tiers WHERE pid=? ORDER BY min")->execute($objResult->id);

        while ($objTiers->next()) {
            $this->arrTiers[$objTiers->min] = $objTiers->price;
        }
    }

    /**
     * Return true if more than one price is available
     * @return  bool
     */
    public function hasTiers()
    {
        return (count($this->arrTiers) > 1);
    }

    /**
     * Return price
     * @param   int
     * @return  float
     */
    public function getAmount($intQuantity=1)
    {
        // @todo should pass product object as second parameter
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), null, 'price', $this->tax_class);
    }

    /**
     * Return original price
     * @param   int
     * @return  float
     */
    public function getOriginalAmount($intQuantity=1)
    {
        // @todo should pass product object as second parameter
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), null, 'original_price', $this->tax_class);
    }

    /**
     * Return net price (without taxes)
     * @param   int
     * @return  float
     */
    public function getNetAmount($intQuantity=1)
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateNetPrice($fltAmount);
        }

        // @todo should pass product object as second parameter
        return Isotope::calculatePrice($fltAmount, null, 'net_price');
    }

    /**
     * Return gross price (with all taxes)
     * @param   int
     * @return  float
     */
    public function getGrossAmount($intQuantity=1)
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateGrossPrice($fltAmount);
        }

        // @todo should pass product object as second parameter
        return Isotope::calculatePrice($fltAmount, null, 'gross_price');
    }

    /**
     * Get lowest amount of all tiers
     * @return  float
     */
    public function getLowestAmount()
    {
        if (!$this->hasTiers()) {
            return $this->getAmount();
        }

        // @todo should pass product object as second parameter
        return Isotope::calculatePrice(min($this->arrTiers), null, 'price', $this->tax_class);
    }

    /**
     * Return price tiers array
     * @return  array
     */
    public function getTiers()
    {
        return $this->arrTiers;
    }

    /**
     * Return lowest tier (= minimum quantity)
     * @return  int
     */
    public function getLowestTier()
    {
        $intMin = $this->hasTiers() ? (int) min(array_keys($this->arrTiers)) : 0;

        return $intMin ?: 1;
    }

    /**
     * Return value for a price tier, finding clostest match
     * @param   int
     * @return  float
     */
    public function getValueForTier($intTier)
    {
        do {
            if (isset($this->arrTiers[$intTier])) {
                return $this->arrTiers[$intTier];
            }

            $intTier -= 1;

        } while ($intTier > 0);

        return 0;
    }

    /**
     * Generate price for HTML rendering
     * @param   show from-price
     * @return  string
     */
    public function generate($blnShowFrom=true)
    {
        if ($blnShowFrom) {

            if ($this->hasTiers()) {
                $fltPrice = $this->getLowestAmount();
                $fltOriginalPrice = $this->getLowestAmount();
            } else {
                $fltPrice = $this->getAmount();
                $fltOriginalPrice = $this->getAmount();
            }

            $strPrice = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], Isotope::formatPriceWithCurrency($fltPrice));
            $strOriginalPrice = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], Isotope::formatPriceWithCurrency($fltOriginalPrice));

        } else {

            // Add price to template
            $fltPrice = $this->getAmount();
            $fltOriginalPrice = $this->getOriginalAmount();

            $strPrice = Isotope::formatPriceWithCurrency($fltPrice);
            $strOriginalPrice = Isotope::formatPriceWithCurrency($fltOriginalPrice);
        }

        if ($fltPrice != $fltOriginalPrice) {
            $strPrice = '<div class="original_price"><strike>' . $strOriginalPrice . '</strike></div><div class="price">' . $strPrice . '</div>';
        }

        return $strPrice;
    }

    /**
     * Find price data for a given product
     * @param   IsotopeProduct
     * @param   IsotopeProductCollection
     * @return  IsotopePrice
     */
    public static function findActiveByProductAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection, array $arrOptions=array())
    {
        $arrOptions = array_merge(
            array(
    			'limit'  => 1,
    			'return' => 'Model'
    		),
            $arrOptions
        );

        if ($objProduct->hasAdvancedPrices()) {

            $time = $objCollection->getLastModification();
            $arrGroups = static::getMemberGroups($objCollection->getRelated('member'));

            $arrOptions['column'] = array(
                "pid=" . $objProduct->id,
                "config_id IN (". (int) $objCollection->config_id . ",0)",
                "member_group IN(" . implode(',', $arrGroups) . ")",
                "(start='' OR start<$time)",
                "(stop='' OR stop>$time)"
            );

            $arrOptions['order'] = "config_id DESC, " . \Database::getInstance()->findInSet('member_group', $arrGroups) . ", start DESC, stop DESC";

        } else {

            $arrOptions['column'] = array(
                "pid=" . $objProduct->id,
                "config_id=0",
                "member_group=0",
                "start=''",
                "stop=''"
            );
        }

        return static::find($arrOptions);
    }

    /**
     * Find lowest price for a list of variants
     * @param   IsotopeProduct
     * @param   IsotopeProductCollection
     * @return  IsotopePrice
     */
    public static function findLowestActiveByVariantsAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection, array $arrOptions=array())
    {
        if (!$objProduct->hasVariantPrices()) {
            throw new \LogicException('Cannot find low price, product ID ' . ($objProduct->pid ?: $objProduct->id) . ' has no variant prices');
        }

        $arrIds = $objProduct->getVariantIds();

        if (empty($arrIds)) {
            return null;
        }

        if ($objProduct->hasAdvancedPrices()) {

            $time = time();
            $arrGroups = static::getMemberGroups($objCollection->getRelated('member'));
            $objPrices = null;

            $objResult = \Database::getInstance()->query("
                SELECT * FROM (
                    SELECT *
                    FROM " . static::$strTable . "
                    WHERE
                        pid IN (" . implode(',', $objProduct->getVariantIds()) . ") AND
                        config_id IN (". (int) $objCollection->config_id . ",0) AND
                        member_group IN(" . implode(',', $arrGroups) . ") AND
                        (start='' OR start<$time) AND
                        (stop='' OR stop>$time)
                    ORDER BY config_id DESC, " . \Database::getInstance()->findInSet('member_group', $arrGroups) . ", start DESC, stop DESC
                ) AS prices
                GROUP BY pid
            ");

            if ($objResult->numRows) {
                $objPrices = new \Model\Collection($objResult, static::$strTable);
            }

        } else {

            $arrOptions['column'] = array(
                "pid IN (" . implode(',', $objProduct->getVariantIds()) . ")",
                "config_id=0",
                "member_group=0",
                "start=''",
                "stop=''"
            );

            $objPrices = static::find($arrOptions);
        }

        if (null === $objPrices){
            return null;
        }

        $blnTiers = $objProduct->canSeePriceTiers();
        $objLowest = $objPrices->current();
        $fltLowest = $blnTiers ? min($objLowest->getTiers()) : $objLowest->getAmount($objLowest->getLowestTier());

        // Iterate through models and find the lowest price
        while ($objPrices->next()) {

            $objCurrent = $objPrices->current();
            $fltCurrent = $blnTiers ? min($objCurrent->getTiers()) : $objCurrent->getAmount($objLowest->getLowestTier());

            if ($fltCurrent < $fltLowest) {
                $fltLowest = $fltCurrent;
                $objLowest = $objCurrent;
            }
        }

        return $objLowest;
    }

    /**
     * Find primary price for a product
     * @param   int
     * @return  ProductPrice|null
     */
    public static function findPrimaryByProduct($intProduct, array $arrOptions=array())
    {
        $arrOptions = array_merge(
            array(
                'column' => array(
                    "pid=" . $intProduct,
                    "config_id=0",
                    "member_group=0",
                    "start=''",
                    "stop=''"
                ),
    			'limit'  => 1,
    			'return' => 'Model'
    		),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Compile a list of member groups suitable for retrieving prices. This includes a 0 at the last position in array
     * @return  array
     */
    protected static function getMemberGroups($objMember)
    {
        if (null !== $objMember)
        {
            $arrGroups = deserialize($objMember->groups);
        }

        if (!is_array($arrGroups))
        {
            $arrGroups = array();
        }

        $arrGroups[] = 0;

        return $arrGroups;
    }
}
