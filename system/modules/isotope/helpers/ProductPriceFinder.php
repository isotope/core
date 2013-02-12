<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;

use Isotope\Interfaces\IsotopeProduct;


/**
 * Class ProductPriceFinder
 *
 * Provides helper methods to find the price of a product.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductPriceFinder extends \System
{

    /**
     * Generate price data for a given product
     * @param IsotopeProduct
     * @return array
     */
    public static function findPrice(IsotopeProduct $objProduct)
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

        return array_merge(array
        (
            'price'                => null,
            'tax_class'            => null,
            'from_price'        => null,
            'high_price'        => null,
            'price_tiers'        => null,
        ), $arrData);
    }


    /**
     * Find price data for product without variant and without advanced prices
     * @param IsotopeProduct
     * @return array
     */
    protected static function findProductPrice(IsotopeProduct $objProduct)
    {
        $arrData = $objProduct->getData();

        return array
        (
            'price'        => $arrData['price'],
            'tax_class'    => $arrData['tax_class'],
        );
    }


    /**
     * Find price data for a variant product without advanced prices
     * @param IsotopeProduct
     * @return array
     */
    protected static function findVariantPrice(IsotopeProduct $objProduct)
    {
        $time = time();
        $arrProduct = $objProduct->getData();

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

        return $arrData;
    }


    /**
     * Find price data for a product without variant prices but with advanced prices
     * @param IsotopeProduct
     * @return array
     */
    protected static function findAdvancedProductPrice(IsotopeProduct $objProduct)
    {
        return self::getAdvancedPrices(array($objProduct->id), $objProduct->quantity_requested, $objProduct->show_price_tiers);
    }


    /**
     * Find price data for a variant product with advanced prices and with variant prices
     * @param IsotopeProduct
     * @return array
     */
    protected static function findAdvancedVariantPrice(IsotopeProduct $objProduct)
    {
        $arrIds = $objProduct->pid == 0 ? $objProduct->getVariantIds() : array($objProduct->id);
        $arrData = self::getAdvancedPrices($arrIds, $objProduct->quantity_requested, $objProduct->show_price_tiers);

        if ($objProduct->pid == 0)
        {
            $arrData['from_price'] = self::findLowestAdvancedPriceOfVariants($arrIds, $objProduct->show_price_tiers);
        }

        return $arrData;
    }


    /**
     * Get advanced prices for a list of products (usially one product ID in an array, or an array of variant IDs)
     * @param array
     * @param int
     * @return array
     */
    protected static function getAdvancedPrices(array $arrIds, $intQuantity=1, $blnShowPriceTiers=false)
    {
        $time = time();
        $arrData = array();
        $blnPriceFound = false;
        $arrGroups = self::getMemberGroups();

        $objPrices = \Database::getInstance()->execute("SELECT min, price, tax_class
                                                        FROM tl_iso_price_tiers t
                                                        LEFT JOIN tl_iso_prices p ON t.pid=p.id
                                                        WHERE
                                                            t.pid=
                                                            (
                                                                SELECT id
                                                                FROM tl_iso_prices
                                                                WHERE
                                                                    config_id IN (". (int) Isotope::getInstance()->Config->id . ",0)
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

        return $arrData;
    }


    /**
     * Find lowest price of all variants when using advanced prices
     * @param array
     * @return decimal|null
     */
    protected static function findLowestAdvancedPriceOfVariants($arrVariantIds, $blnShowPriceTiers=false)
    {
        $time = time();
        $arrGroups = self::getMemberGroups();

        if ($blnShowPriceTiers)
        {
            $objResult = \Database::getInstance()->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price
                                                            FROM tl_iso_price_tiers
                                                            WHERE pid IN
                                                            (
                                                                SELECT id
                                                                FROM
                                                                (
                                                                    SELECT p1.id, p1.pid FROM tl_iso_prices p1 LEFT JOIN tl_iso_products p2 ON p1.pid=p2.id
                                                                    WHERE
                                                                        p1.pid IN (" . implode(',', $arrVariantIds) . ")
                                                                        AND p1.config_id IN (" . (int) Isotope::getInstance()->Config->id . ",0)
                                                                        AND p1.member_group IN(" . implode(',', $arrGroups) . ")
                                                                        AND (p1.start='' OR p1.start<$time)
                                                                        AND (p1.stop='' OR p1.stop>$time)
                                                                    ORDER BY p1.config_id DESC, p1.member_group=" . implode(" DESC, p1.member_group=", $arrGroups) . " DESC, p1.start DESC, p1.stop DESC
                                                                ) AS p
                                                                GROUP BY pid
                                                            )");
        }
        else
        {
            $objResult = \Database::getInstance()->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM tl_iso_price_tiers WHERE id IN (
                                                                SELECT id FROM tl_iso_price_tiers WHERE id IN (
                                                                    SELECT id
                                                                    FROM tl_iso_price_tiers
                                                                    WHERE pid IN
                                                                    (
                                                                        SELECT id
                                                                        FROM
                                                                        (
                                                                            SELECT p1.id, p1.pid FROM tl_iso_prices p1 LEFT JOIN tl_iso_products p2 ON p1.pid=p2.id
                                                                            WHERE
                                                                                p1.pid IN (" . implode(',', $arrVariantIds) . ")
                                                                                AND p1.config_id IN (" . (int) Isotope::getInstance()->Config->id . ",0)
                                                                                AND p1.member_group IN(" . implode(',', $arrGroups) . ")
                                                                                AND (p1.start='' OR p1.start<$time)
                                                                                AND (p1.stop='' OR p1.stop>$time)
                                                                            ORDER BY p1.config_id DESC, p1.member_group=" . implode(" DESC, p1.member_group=", $arrGroups) . " DESC, p1.start DESC, p1.stop DESC
                                                                        ) AS p
                                                                        GROUP BY pid
                                                                    )
                                                                    ORDER BY min)
                                                                GROUP BY pid
                                                            )");
        }

        return ($objResult->low_price > 0 && $objResult->low_price < $objResult->high_price) ? $objResult->low_price : null;
    }


    /**
     * Compile a list of member groups suitable for retrieving prices. This includes a 0 at the last position in array
     * @return array
     */
    protected static function getMemberGroups()
    {
        if (FE_USER_LOGGED_IN === true)
        {
            $arrGroups = FrontendUser::getInstance()->groups;
        }

        if (!is_array($arrGroups))
        {
            $arrGroups = array();
        }

        $arrGroups[] = 0;

        return $arrGroups;
    }
}
