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


/**
 * ProductType defines a product configuration
 *
 * @property int    id
 * @property int    tstamp
 * @property string name
 * @property string class
 * @property bool   fallback
 * @property string description
 * @property bool   prices
 * @property bool   show_price_tiers
 * @property string list_template
 * @property string reader_template
 * @property int    list_gallery
 * @property int    reader_gallery
 * @property mixed  attributes
 * @property bool   variants
 * @property mixed  variant_attributes
 * @property bool   force_variant_options
 * @property bool   shipping_exempt
 * @property bool   downloads
 */
class ProductType extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_producttype';

    /**
     * Product attributes
     * @var array
     */
    protected $arrAttributes;

    /**
     * Product variant attributes
     * @var array
     */
    protected $arrVariantAttributes;


    /**
     * Returns true if variants are enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function hasVariants()
    {
        return (bool) $this->variants;
    }

    /**
     * Returns true if advanced prices are enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function hasAdvancedPrices()
    {
        return (bool) $this->prices;
    }

    /**
     * Returns true if show price tiers is enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function showPriceTiers()
    {
        return (bool) $this->show_price_tiers;
    }

    /**
     * Returns true if downloads are enabled in the product type, otherwise returns false
     * @return  bool
     */
    public function hasDownloads()
    {
        return (bool) $this->downloads;
    }

    /**
     * Get enabled attributes by sorting
     * @return  array
     */
    public function getAttributes()
    {
        if (null === $this->arrAttributes) {
            $this->arrAttributes = $this->getEnabledAttributesByPosition($this->attributes);
        }

        return $this->arrAttributes;
    }

    /**
     * Get enabled variant attributes by sorting
     * @return  array
     */
    public function getVariantAttributes()
    {
        if (null === $this->arrVariantAttributes) {

            if (!$this->hasVariants()) {
                $this->arrVariantAttributes = array();
            } else {
                $this->arrVariantAttributes = $this->getEnabledAttributesByPosition($this->variant_attributes);
            }
        }

        return $this->arrVariantAttributes;
    }

    /**
     * Sort the attributes based on their position (from wizard) and return their names only
     * @param   mixed
     * @return  array
     */
    protected function getEnabledAttributesByPosition($varValue)
    {
        $arrFields     = &$GLOBALS['TL_DCA']['tl_iso_product']['fields'];
        $arrAttributes = deserialize($varValue, true);

        $arrAttributes = array_filter($arrAttributes, function ($a) use ($arrFields) {
            if ($a['enabled'] && is_array($arrFields[$a['name']]) && $arrFields[$a['name']]['attributes']['legend'] != '') {
                return true;
            }

            return false;
        });

        uasort($arrAttributes, function ($a, $b) {
            return $a["position"] > $b["position"];
        });

        return array_keys($arrAttributes);
    }

    /**
     * Get all product types that are in use
     * @param   array
     * @return  Collection|null
     */
    public static function findAllUsed(array $arrOptions = array())
    {
        $t = static::$strTable;

        return static::findBy(array("$t.id IN (SELECT type FROM tl_iso_product WHERE pid=0)"), null, $arrOptions);
    }

    /**
     * Find fallback product type
     * @param   array
     * @return  ProductType|null
     */
    public static function findFallback(array $arrOptions = array())
    {
        return static::findOneBy('fallback', '1', $arrOptions);
    }

    /**
     * Find product type for product data (as array)
     * @param   array
     * @param   array
     * @return  ProductType|null
     */
    public static function findByProductData(array $row, array $arrOptions = array())
    {
        if ($row['pid'] > 0 && ($objProduct = Product::findByPk($row['pid'])) !== null) {
            return $objProduct->getRelated('type');
        }

        return static::findByPk($row['type']);
    }
}
