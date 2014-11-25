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

use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;


/**
 * ProductPrice defines an advanced price of a product
 */
class ProductPrice extends \Model implements IsotopePrice
{

    /**
     * Name of the current table
     *
     * @var string
     */
    protected static $strTable = 'tl_iso_product_price';

    /**
     * Tiers for this price
     *
     * @var array
     */
    protected $arrTiers = array();

    /**
     * Construct the object
     *
     * @param   array
     * @param   array
     * @param   boolean
     */
    public function __construct(\Database\Result $objResult = null)
    {
        parent::__construct($objResult);

        $objTiers = \Database::getInstance()->prepare("SELECT * FROM tl_iso_product_pricetier WHERE pid=? ORDER BY min")->execute($objResult->id);

        while ($objTiers->next()) {
            $this->arrTiers[$objTiers->min] = $objTiers->price;
        }
    }

    /**
     * Return true if more than one price is available
     *
     * @return  bool
     */
    public function hasTiers()
    {
        return (count($this->arrTiers) > 1);
    }

    /**
     * Return price
     *
     * @param   int   $intQuantity
     * @param   array $arrOptions
     *
     * @return  float
     */
    public function getAmount($intQuantity = 1, array $arrOptions = array())
    {
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this, 'price', $this->tax_class, null, $arrOptions);
    }

    /**
     * Return original price
     *
     * @param   int   $intQuantity
     * @param   array $arrOptions
     *
     * @return  float
     */
    public function getOriginalAmount($intQuantity = 1, array $arrOptions = array())
    {
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this, 'original_price', $this->tax_class, null, $arrOptions);
    }

    /**
     * Return net price (without taxes)
     *
     * @param   int   $intQuantity
     * @param   array $arrOptions
     *
     * @return  float
     */
    public function getNetAmount($intQuantity = 1, array $arrOptions = array())
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        /** @var \Isotope\Model\TaxClass $objTaxClass */
        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateNetPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this, 'net_price', 0, null, $arrOptions);
    }

    /**
     * Return gross price (with all taxes)
     *
     * @param   int   $intQuantity
     * @param   array $arrOptions
     *
     * @return  float
     */
    public function getGrossAmount($intQuantity = 1, array $arrOptions = array())
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        /** @var \Isotope\Model\TaxClass $objTaxClass */
        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateGrossPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this, 'gross_price', 0, null, $arrOptions);
    }

    /**
     * Get lowest amount of all tiers
     *
     * @param   array $arrOptions
     *
     * @return  float
     */
    public function getLowestAmount(array $arrOptions = array())
    {
        if (!$this->hasTiers()) {
            return $this->getAmount();
        }

        return Isotope::calculatePrice(min($this->arrTiers), $this, 'price', $this->tax_class, null, $arrOptions);
    }

    /**
     * Return price tiers array
     *
     * @return  array
     */
    public function getTiers()
    {
        return $this->arrTiers;
    }

    /**
     * Return lowest tier (= minimum quantity)
     *
     * @return  int
     */
    public function getLowestTier()
    {
        $intMin = (int) min(array_keys($this->arrTiers));

        return $intMin ?: 1;
    }

    /**
     * Return value for a price tier, finding clostest match
     *
     * @param   int
     *
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

        return $this->arrTiers[min(array_keys($this->arrTiers))];
    }

    /**
     * Generate price for HTML rendering
     *
     * @param   bool  $blnShowTiers
     * @param   int   $intQuantity
     * @param   array $arrOptions
     *
     * @return  string
     */
    public function generate($blnShowTiers = false, $intQuantity = 1, array $arrOptions = array())
    {
        $blnShowFrom = false;

        $fltPrice = $this->getAmount($intQuantity, $arrOptions);

        if ($blnShowTiers) {
            $fltLowest = $this->getLowestAmount($arrOptions);

            if ($fltPrice != $fltLowest) {
                $blnShowFrom = true;
                $fltPrice = $fltLowest;
            }
        }

        $strPrice = Isotope::formatPriceWithCurrency($fltPrice);

        if ($blnShowFrom) {
            return sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $strPrice);
        }

        $fltOriginalPrice = $this->getOriginalAmount($intQuantity, $arrOptions);

        if ($fltPrice < $fltOriginalPrice) {
            $strOriginalPrice = Isotope::formatPriceWithCurrency($fltOriginalPrice);

            // @deprecated remove <strike>, should be a CSS setting
            return '<div class="original_price"><strike>' . $strOriginalPrice . '</strike></div><div class="price">' . $strPrice . '</div>';
        }

        return $strPrice;
    }

    /**
     * Find prices for a given product and collection
     *
     * @param IsotopeProduct           $objProduct
     * @param IsotopeProductCollection $objCollection
     * @param array                    $arrOptions
     *
     * @return IsotopePrice
     */
    public static function findByProductAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection, array $arrOptions = array())
    {
        $arrOptions['column'] = array();
        $arrOptions['value'] = array();

        if ($objProduct->hasAdvancedPrices()) {

            $time = $objCollection->getLastModification();
            $arrGroups = static::getMemberGroups($objCollection->getRelated('member'));

            $arrOptions['column'][] = "config_id IN (" . (int) $objCollection->config_id . ",0)";
            $arrOptions['column'][] = "member_group IN(" . implode(',', $arrGroups) . ")";
            $arrOptions['column'][] = "(start='' OR start<$time)";
            $arrOptions['column'][] = "(stop='' OR stop>$time)";

            $arrOptions['order'] = "config_id DESC, " . \Database::getInstance()->findInSet('member_group', $arrGroups) . ", start DESC, stop DESC";

        } else {

            $arrOptions['column'][] = "config_id=0";
            $arrOptions['column'][] = "member_group=0";
            $arrOptions['column'][] = "start=''";
            $arrOptions['column'][] = "stop=''";
        }

        if ($objProduct->hasVariantPrices() && !$objProduct->isVariant()) {
            $arrIds = $objProduct->getVariantIds() ?: array(0);
            $arrOptions['column'][] = "pid IN (" . implode(',', $arrIds) . ")";
        } else {
            $arrOptions['column'][] = "pid=" . ($objProduct->hasVariantPrices() ? $objProduct->id : $objProduct->getProductId());
        }

        $objResult = static::find($arrOptions);

        return (null === $objResult) ? null : $objResult->filterDuplicatesBy('pid');
    }

    /**
     * @param       $intProduct
     * @param array $arrOptions
     *
     * @return ProductPrice|null
     * @deprecated use findPrimaryByProductId
     */
    public static function findPrimaryByProduct($intProduct, array $arrOptions = array())
    {
        return static::findPrimaryByProductId($intProduct, $arrOptions);
    }

    /**
     * Find primary price for a product
     *
     * @param   int
     *
     * @return  ProductPrice|null
     */
    public static function findPrimaryByProductId($intProduct, array $arrOptions = array())
    {
        $arrOptions = array_merge(
            array(
                'column' => array(
                    "config_id=0",
                    "member_group=0",
                    "start=''",
                    "stop=''",
                    "pid=" . $intProduct
                ),
                'limit'  => 1,
                'return' => 'Model'
            ),
            $arrOptions
        );

        return static::find($arrOptions);
    }

    /**
     * Find primary price for multiple product/variant IDs
     *
     * @param   array
     * @param   array
     *
     * @return  \Model\Collection|null
     */
    public static function findPrimaryByProductIds(array $arrIds, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrOptions = array_merge(
            array(
                'column' => array(
                    "$t.config_id=0",
                    "$t.member_group=0",
                    "$t.start=''",
                    "$t.stop=''",
                    "$t.pid IN (" . implode(',', $arrIds) . ")",
                ),
                'return' => 'Collection'
            ),
            $arrOptions
        );

        $objResult = static::find($arrOptions);

        return (null === $objResult) ? null : $objResult->filterDuplicatesBy('pid');
    }

    /**
     * Find advanced price for multiple product/variant IDs
     *
     * @param   array
     * @param   IsotopeProductCollection
     *
     * @return  \Model\Collection|null
     */
    public static function findAdvancedByProductIdsAndCollection(array $arrIds, IsotopeProductCollection $objCollection)
    {
        $time = time();
        $arrGroups = static::getMemberGroups($objCollection->getRelated('member'));

        $objResult = \Database::getInstance()->query("
            SELECT * FROM (
                SELECT *
                FROM " . static::$strTable . "
                WHERE
                    config_id IN (" . (int) $objCollection->config_id . ",0) AND
                    member_group IN(" . implode(',', $arrGroups) . ") AND
                    (start='' OR start<$time) AND
                    (stop='' OR stop>$time) AND
                    pid IN (" . implode(',', $arrIds) . ")
                ORDER BY config_id DESC, " . \Database::getInstance()->findInSet('member_group', $arrGroups) . ", start DESC, stop DESC
            ) AS prices
            GROUP BY pid
        ");

        if ($objResult->numRows) {
            return \Isotope\Collection\ProductPrice::createFromDbResult($objResult, static::$strTable);
        }

        return null;
    }

    /**
     * Find records and return the model or model collection
     *
     * Supported options:
     *
     * * column: the field name
     * * value:  the field value
     * * limit:  the maximum number of rows
     * * offset: the number of rows to skip
     * * order:  the sorting order
     * * eager:  load all related records eagerly
     *
     * @param array $arrOptions The options array
     *
     * @return \Model|\Model\Collection|null A model, model collection or null if the result is empty
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '') {
            return null;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = \Model\QueryBuilder::find($arrOptions);

        $objStatement = \Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit'])) {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset'])) {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0) {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1) {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model') {
            $strPk = static::$strPk;
            $intPk = $objResult->$strPk;

            // Try to load from the registry
            $objModel = \Model\Registry::getInstance()->fetch(static::$strTable, $intPk);

            if ($objModel !== null) {
                return $objModel->mergeRow($objResult->row());
            }

            return new static($objResult);
        } else {
            return \Isotope\Collection\ProductPrice::createFromDbResult($objResult, static::$strTable);
        }
    }

    /**
     * Compile a list of member groups suitable for retrieving prices. This includes a 0 at the last position in array
     *
     * @return  array
     */
    protected static function getMemberGroups($objMember)
    {
        if (null !== $objMember) {
            $arrGroups = deserialize($objMember->groups);
        }

        if (!is_array($arrGroups)) {
            $arrGroups = array();
        }

        $arrGroups[] = 0;

        return $arrGroups;
    }
}
