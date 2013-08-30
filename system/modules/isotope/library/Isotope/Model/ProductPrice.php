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
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this->getRelated('pid'), 'price', $this->tax_class);
    }

    /**
     * Return original price
     * @param   int
     * @return  float
     */
    public function getOriginalAmount($intQuantity=1)
    {
        return Isotope::calculatePrice($this->getValueForTier($intQuantity), $this->getRelated('pid'), 'original_price', $this->tax_class);
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
            $objTaxClass->calculateNetPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this->getRelated('pid'), 'net_price');
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
            $objTaxClass->calculateGrossPrice($fltAmount);
        }

        return Isotope::calculatePrice($fltAmount, $this->getRelated('pid'), 'gross_price');
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
     * Find price data for a given product
     * @param   IsotopeProduct
     * @param   IsotopeProductCollection
     * @return  IsotopePrice
     */
    public static function findActiveByProductAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection)
    {
        $arrOptions = array
		(
			'limit'  => 1,
			'return' => 'Model'
		);

        if ($objProduct->hasAdvancedPrices()) {

            $time = time();
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
    public static function findLowestActiveByVariantsAndCollection(IsotopeProduct $objProduct, IsotopeProductCollection $objCollection)
    {
        if (!$objProduct->hasVariantPrices()) {
            throw new \LogicException('Cannot find low price, product ID ' . ($objProduct->pid ?: $objProduct->id) . ' has no variant prices');
        }

        $arrIds = $objProduct->getVariantIds();

        if (empty($arrIds)) {
            return null;
        }

        $arrOptions = array();

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
     * Set value for a specific price tier
     */
    public function setValueForTier($fltValue, $intTier)
    {
        $this->arrTiers[$intTier] = (float) $fltValue;
    }

    /**
     * Store product in the relation mapping so we dont need to fetch it
     * @param   IsotopeProduct
     */
    public function setProduct(IsotopeProduct $objProduct)
    {
        if ($this->pid != $objProduct->id) {
            throw new \InvalidArgumentException('Product ID does not match with price PID');
        }

        $this->arrRelation['pid'] = $objProduct;
    }


    /**
     * Find price data for a given product
     * @param   IsotopeProduct
     * @return  IsotopePrice
     */
    public static function findForProduct(IsotopeProduct $objProduct)
    {
        $blnAdvancedPrices = $objProduct->hasAdvancedPrices();
        $blnVariantPrices = $objProduct->hasVariantPrices();

        if ($blnAdvancedPrices && $blnVariantPrices)
        {
            $arrData = self::findAdvancedVariantPrice($objProduct);
        }
        elseif ($blnAdvancedPrices)
        {
            $arrData = self::findAdvancedProductPrice($objProduct);
        }
        elseif ($blnVariantPrices)
        {
            $arrData = self::findVariantPrice($objProduct);
        }
        else
        {
            $arrData = self::findProductPrice($objProduct);
        }

        $arrData = array_merge(array
        (
            'min'           => 1,
            'price'         => null,
            'tax_class'     => null,
            'from_price'    => null,
        ), $arrData);

        $objPrice = new static();
        $objPrice->pid = $objProduct->id;
        $objPrice->min = $arrData['min'];
        $objPrice->price = $arrData['price'];
        $objPrice->tax_class = (int) $arrData['tax_class'];
        $objPrice->from_price = $arrData['from_price'];

        $objPrice->setProduct($objProduct);

        foreach ($arrData['price_tiers'] as $arrTier) {
            $objPrice->setValueForTier($arrTier['price'], $arrTier['min']);
        }

        return $objPrice;
    }


    /**
     * Find price data for product without variant and without advanced prices
     * @param   IsotopeProduct
     * @return  array
     */
    protected static function findProductPrice(IsotopeProduct $objProduct)
    {
        $arrData = $objProduct->row();

        return array
        (
            'price'        => $arrData['price'],
            'tax_class'    => $arrData['tax_class'],
            'price_tiers'  => array(array('min'=>1, 'price'=>$arrData['price'])),
        );
    }


    /**
     * Find price data for a variant product without advanced prices
     * @param   IsotopeProduct
     * @return  array
     */
    protected static function findVariantPrice(IsotopeProduct $objProduct)
    {
        $time = time();
        $arrProduct = $objProduct->row();

        $arrData['price'] = $arrProduct['price'];
        $arrData['tax_class'] = $arrProduct['tax_class'];

        // Only look for low price if no variant is selected
        if ($objProduct->pid == 0)
        {
            $objResult = \Database::getInstance()->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM tl_iso_products
                                                            WHERE pid=" . ($objProduct->pid ? $objProduct->pid : $objProduct->id) . " AND language=''"
                                                            . (BE_USER_LOGGED_IN === true ? '' : " AND published='1' AND (start='' OR start<$time) AND (stop='' OR stop>$time)")
                                                            . " GROUP BY pid");

            // Must inherit price from any variant found
            $arrData['price'] = $objResult->low_price;

            if ($objResult->low_price > 0 && $objResult->low_price < $objResult->high_price)
            {
                $arrData['from_price'] = $objResult->low_price;
            }
        }

        $arrData['price_tiers'] = array(array('min'=>1, 'price'=>$arrProduct['price']));

        return $arrData;
    }


    /**
     * Find price data for a product without variant prices but with advanced prices
     * @param   IsotopeProduct
     * @return  array
     */
    protected static function findAdvancedProductPrice(IsotopeProduct $objProduct)
    {
        return self::getAdvancedPrices(array($objProduct->id), $objProduct->quantity_requested, $objProduct->canSeePriceTiers());
    }


    /**
     * Find price data for a variant product with advanced prices and with variant prices
     * @param   IsotopeProduct
     * @return  array
     */
    protected static function findAdvancedVariantPrice(IsotopeProduct $objProduct)
    {
        $arrIds = $objProduct->pid == 0 ? $objProduct->getVariantIds() : array($objProduct->id);

        if (empty($arrIds)) {
            return array();
        }

        $arrData = self::getAdvancedPrices($arrIds, $objProduct->quantity_requested, $objProduct->canSeePriceTiers());

        if ($objProduct->pid == 0)
        {
            $arrData['from_price'] = self::findLowestAdvancedPriceOfVariants($arrIds, $objProduct->canSeePriceTiers());
        }

        return $arrData;
    }


    /**
     * Get advanced prices for a list of products (usially one product ID in an array, or an array of variant IDs)
     * @param   array
     * @param   int
     * @return  array
     */
    protected static function getAdvancedPrices(array $arrIds, $intQuantity=1, $blnShowPriceTiers=false)
    {
        $time = time();
        $arrData = array();
        $blnPriceFound = false;
        $arrGroups = self::getMemberGroups(\FrontendUser::getInstance());

        $objPrices = \Database::getInstance()->execute("SELECT min, price, tax_class
                                                        FROM tl_iso_price_tiers t
                                                        LEFT JOIN tl_iso_prices p ON t.pid=p.id
                                                        WHERE
                                                            t.pid=
                                                            (
                                                                SELECT id
                                                                FROM tl_iso_prices
                                                                WHERE
                                                                    config_id IN (". (int) Isotope::getInstance()->getConfig()->id . ",0)
                                                                    AND member_group IN(" . implode(',', $arrGroups) . ")
                                                                    AND (start='' OR start<$time)
                                                                    AND (stop='' OR stop>$time)
                                                                    AND pid IN (" . implode(',', $arrIds) . ")
                                                                ORDER BY config_id DESC, member_group=" . implode(' DESC, member_group=', $arrGroups) . " DESC, start DESC, stop DESC
                                                                LIMIT 1
                                                            )
                                                        ORDER BY min DESC");

        $arrPrices = $objPrices->fetchAllAssoc();
        $blnShowPriceTiers = $objPrices->numRows > 1 ? $blnShowPriceTiers : false;

        foreach ($arrPrices as $arrPrice)
        {
            if (!$blnPriceFound && $arrPrice['min'] <= $intQuantity)
            {
                $arrData['price'] = $arrPrice['price'];
                $arrData['tax_class'] = $arrPrice['tax_class'];
                $blnPriceFound = true;
            }

            if ($blnShowPriceTiers && $arrData['from_price'] == null || $arrPrice['price'] < $arrData['from_price'])
            {
                $arrData['from_price'] = $arrPrice['price'];
            }
        }

        $arrData['price_tiers'] = array_reverse($arrPrices);

        if (!$blnPriceFound && !empty($arrPrices))
        {
            $arrData['min'] = (int) $arrData['price_tiers'][0]['min'];
            $arrData['price'] = $arrData['price_tiers'][0]['price'];
            $arrData['tax_class'] = $arrData['price_tiers'][0]['tax_class'];
        }

        return $arrData;
    }


    /**
     * Find lowest price of all variants when using advanced prices
     * @param   array
     * @return  decimal|null
     */
    protected static function findLowestAdvancedPriceOfVariants($arrVariantIds, $blnShowPriceTiers=false)
    {
        $time = time();
        $arrGroups = self::getMemberGroups(\FrontendUser::getInstance());

        if ($blnShowPriceTiers)
        {
            $objResult = \Database::getInstance()->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price
                                                            FROM tl_iso_price_tiers
                                                            WHERE pid IN
                                                            (
                                                                SELECT id
                                                                FROM
                                                                (
                                                                    SELECT id, pid FROM tl_iso_prices
                                                                    WHERE
                                                                        pid IN (" . implode(',', $arrVariantIds) . ")
                                                                        AND config_id IN (" . (int) Isotope::getInstance()->getConfig()->id . ",0)
                                                                        AND member_group IN(" . implode(',', $arrGroups) . ")
                                                                        AND (start='' OR start<$time)
                                                                        AND (stop='' OR stop>$time)
                                                                    ORDER BY config_id DESC, member_group=" . implode(" DESC, member_group=", $arrGroups) . " DESC, start DESC, stop DESC
                                                                ) AS p
                                                                GROUP BY pid
                                                            )");
        }
        else
        {
            $objResult = \Database::getInstance()->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM (
                                                                SELECT * FROM tl_iso_price_tiers WHERE id IN (
                                                                    SELECT id
                                                                    FROM tl_iso_price_tiers
                                                                    WHERE pid IN
                                                                    (
                                                                        SELECT id
                                                                        FROM
                                                                        (
                                                                            SELECT id, pid FROM tl_iso_prices
                                                                            WHERE
                                                                                pid IN (" . implode(',', $arrVariantIds) . ")
                                                                                AND config_id IN (" . (int) Isotope::getInstance()->getConfig()->id . ",0)
                                                                                AND member_group IN(" . implode(',', $arrGroups) . ")
                                                                                AND (start='' OR start<$time)
                                                                                AND (stop='' OR stop>$time)
                                                                            ORDER BY config_id DESC, member_group=" . implode(" DESC, member_group=", $arrGroups) . " DESC, start DESC, stop DESC
                                                                        ) AS p
                                                                        GROUP BY pid
                                                                    )
                                                                    ORDER BY min)
                                                                GROUP BY pid
                                                            ) AS price_tiers");
        }

        return ($objResult->low_price > 0 && $objResult->low_price < $objResult->high_price) ? $objResult->low_price : null;
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
