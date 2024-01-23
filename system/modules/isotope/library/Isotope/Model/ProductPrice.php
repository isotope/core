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

use Contao\Database;
use Contao\Database\Result;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;
use Contao\Model\QueryBuilder;
use Contao\StringUtil;
use Isotope\Collection\ProductPrice as ProductPriceCollection;
use Isotope\Interfaces\IsotopePrice;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;


/**
 * ProductPrice defines an advanced price of a product
 */
class ProductPrice extends Model implements IsotopePrice
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_product_price';

    /**
     * Tiers for this price
     * @var array
     */
    protected $arrTiers = array();

    /**
     * Construct the object
     *
     * @param Result|array $objResult An optional database result or array
     */
    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        $this->arrTiers = array_combine(
            explode(',', $this->arrData['tier_keys']),
            explode(',', $this->arrData['tier_values'])
        );

        ksort($this->arrTiers);
    }

    /**
     * Return true if more than one price is available
     *
     * @return bool
     */
    public function hasTiers()
    {
        return (\count($this->arrTiers) > 1);
    }

    /**
     * Return price
     *
     * @param int   $intQuantity
     * @param array $arrOptions
     *
     * @return float
     */
    public function getAmount($intQuantity = 1, array $arrOptions = array())
    {
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this, 'price', $this->tax_class, null, $arrOptions);
    }

    /**
     * Return original price
     *
     * @param int   $intQuantity
     * @param array $arrOptions
     *
     * @return float
     */
    public function getOriginalAmount($intQuantity = 1, array $arrOptions = array())
    {
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this, 'original_price', $this->tax_class, null, $arrOptions);
    }

    /**
     * Return net price (without taxes)
     *
     * @param int   $intQuantity
     * @param array $arrOptions
     *
     * @return float
     */
    public function getNetAmount($intQuantity = 1, array $arrOptions = array())
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        /** @var TaxClass $objTaxClass */
        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateNetPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this, 'net_price', 0, null, $arrOptions);
    }

    /**
     * Return gross price (with all taxes)
     *
     * @param int   $intQuantity
     * @param array $arrOptions
     *
     * @return float
     */
    public function getGrossAmount($intQuantity = 1, array $arrOptions = array())
    {
        $fltAmount = $this->getValueForTier($intQuantity);

        /** @var TaxClass $objTaxClass */
        if (($objTaxClass = $this->getRelated('tax_class')) !== null) {
            $fltAmount = $objTaxClass->calculateGrossPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this, 'gross_price', 0, null, $arrOptions);
    }

    /**
     * Get lowest amount of all tiers
     *
     * @param array $arrOptions
     *
     * @return float
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
     * @return array
     */
    public function getTiers()
    {
        return $this->arrTiers;
    }

    /**
     * Return lowest tier (= minimum quantity)
     *
     * @return int
     */
    public function getLowestTier()
    {
        $intMin = (int) min(array_keys($this->arrTiers));

        return $intMin ?: 1;
    }

    /**
     * Return value for a price tier, finding closest match
     *
     * @param int $intTier
     *
     * @return float
     */
    public function getValueForTier($intTier)
    {
        do {
            if (isset($this->arrTiers[$intTier])) {
                return $this->arrTiers[$intTier];
            }

            --$intTier;

        } while ($intTier > 0);

        return $this->arrTiers[min(array_keys($this->arrTiers))];
    }

    /**
     * Generate price for HTML rendering
     *
     * @param bool  $blnShowTiers
     * @param int   $intQuantity
     * @param array $arrOptions
     *
     * @return string
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

    public function setProduct(IsotopeProduct $product)
    {
        $this->arrRelated['pid'] = $product;
    }

    /**
     * Find prices for a given product and collection
     *
     * @param IsotopeProduct                             $objProduct
     * @param IsotopeProductCollection|ProductCollection $objCollection
     * @param array                                      $arrOptions
     *
     * @return IsotopePrice
     */
    public static function findByProductAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrOptions['column'] = array();
        $arrOptions['value'] = array();

        if ($objProduct->hasAdvancedPrices()) {

            $time = Date::floorToMinute($objCollection->getLastModification());
            $arrGroups = static::getMemberGroups($objCollection->getRelated('member'));

            $arrOptions['column'][] = "$t.config_id IN (" . (int) $objCollection->config_id . ",0)";
            $arrOptions['column'][] = "$t.member_group IN(" . implode(',', $arrGroups) . ")";
            $arrOptions['column'][] = "($t.start='' OR $t.start<'$time')";
            $arrOptions['column'][] = "($t.stop='' OR $t.stop>'" . ($time + 60) . "')";

            $arrOptions['order'] = "$t.config_id DESC, " . Database::getInstance()->findInSet('member_group', $arrGroups) . ", $t.start DESC, $t.stop DESC";

        } else {

            $arrOptions['column'][] = "$t.config_id=0";
            $arrOptions['column'][] = "$t.member_group=0";
            $arrOptions['column'][] = "$t.start=''";
            $arrOptions['column'][] = "$t.stop=''";
        }

        if ($objProduct->hasVariantPrices() && !$objProduct->isVariant()) {
            $arrIds = $objProduct->getVariantIds() ?: array(0);
            $arrOptions['column'][] = "$t.pid IN (" . implode(',', $arrIds) . ")";
        } else {
            $arrOptions['column'][] = "$t.pid=" . ($objProduct->hasVariantPrices() ? $objProduct->getId() : $objProduct->getProductId());
        }

        /** @var ProductPriceCollection $objResult */
        $objResult = static::find($arrOptions);

        return (null === $objResult) ? null : $objResult->filterDuplicatesBy('pid');
    }

    /**
     * @param int   $intProduct
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
     * @param int   $intProduct
     * @param array $arrOptions
     *
     * @return ProductPrice|null
     */
    public static function findPrimaryByProductId($intProduct, array $arrOptions = array())
    {
        $t = static::$strTable;

        $arrOptions = array_merge(
            array(
                'column' => array(
                    "$t.config_id=0",
                    "$t.member_group=0",
                    "$t.start=''",
                    "$t.stop=''",
                    "$t.pid=" . $intProduct
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
     * @param array $arrIds
     * @param array $arrOptions
     *
     * @return Collection|null
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
     * @param array                                      $arrIds
     * @param IsotopeProductCollection|ProductCollection $objCollection
     *
     * @return Collection|null
     */
    public static function findAdvancedByProductIdsAndCollection(array $arrIds, IsotopeProductCollection $objCollection = null)
    {
        if (null === $objCollection) {
            $configIds = [0];
            $objMember = null;
        } else {
            $configIds = [(int) $objCollection->config_id, 0];
            $objMember = $objCollection->getRelated('member');
        }

        $arrGroups = static::getMemberGroups($objMember);

        return static::findAdvancedByProductIds($arrIds, $arrGroups, $configIds);
    }

    /**
     * Find advanced price for multiple product/variant IDs
     *
     * @return Collection|null
     */
    public static function findAdvancedByProductIds(array $arrIds, array $arrGroups = [0], array $configIds = [0])
    {
        $time = Date::floorToMinute();

        $queries = [];

        foreach ($arrIds as $id) {
            $queries[] = "
                SELECT
                    tl_iso_product_price.*,
                    GROUP_CONCAT(tl_iso_product_pricetier.min) AS tier_keys,
                    GROUP_CONCAT(tl_iso_product_pricetier.price) AS tier_values
                FROM tl_iso_product_price
                LEFT JOIN tl_iso_product_pricetier ON tl_iso_product_pricetier.pid = tl_iso_product_price.id
                WHERE
                    config_id IN (" . implode(',', $configIds) . ") AND
                    member_group IN(" . implode(',', $arrGroups) . ") AND
                    (start='' OR start<'$time') AND
                    (stop='' OR stop>'" . ($time + 60) . "') AND
                    tl_iso_product_price.pid=" . $id . "
                GROUP BY tl_iso_product_price.id
                ORDER BY config_id DESC, " . Database::getInstance()->findInSet('member_group', $arrGroups) . ", start DESC, stop DESC
                LIMIT 1
            ";
        }

        $objResult = Database::getInstance()->query('('.implode(") UNION (", $queries).')');

        if ($objResult->numRows) {
            return ProductPriceCollection::createFromDbResult($objResult, static::$strTable);
        }

        return null;
    }

    /**
     * Compile a list of member groups suitable for retrieving prices. This includes a 0 at the last position in array
     *
     * @param object $objMember
     *
     * @return array
     */
    protected static function getMemberGroups($objMember)
    {
        if (null !== $objMember) {
            $arrGroups = StringUtil::deserialize($objMember->groups);
        }

        if (!isset($arrGroups) || !\is_array($arrGroups)) {
            $arrGroups = array();
        }

        $arrGroups[] = 0;

        return $arrGroups;
    }

    /**
     * {@inheritdoc}
     */
    protected static function buildFindQuery(array $arrOptions)
    {
        $arrOptions['group'] = (($arrOptions['group'] ?? null) ? $arrOptions['group'].', ' : '') . 'tl_iso_product_price.id';

        $query = QueryBuilder::find($arrOptions);
        $from  = substr($query, strpos($query, '*')+1);
        $query = "SELECT tl_iso_product_price.*, GROUP_CONCAT(tl_iso_product_pricetier.min) AS tier_keys, GROUP_CONCAT(tl_iso_product_pricetier.price) AS tier_values" . $from;

        $query = str_replace(
            'FROM tl_iso_product_price',
            'FROM tl_iso_product_price LEFT JOIN tl_iso_product_pricetier ON tl_iso_product_pricetier.pid = tl_iso_product_price.id',
            $query
        );

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    protected static function createCollection(array $arrModels, $strTable)
    {
        return new ProductPriceCollection($arrModels, $strTable);
    }

    /**
     * {@inheritdoc}
     */
    protected static function createCollectionFromDbResult(Result $objResult, $strTable)
    {
        return ProductPriceCollection::createFromDbResult($objResult, $strTable);
    }
}
