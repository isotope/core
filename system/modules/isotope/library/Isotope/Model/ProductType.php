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


/**
 * ProductType defines a product configuration
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class ProductType extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_producttypes';

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
        $arrAttributes = deserialize($varValue, true);

        array_filter($arrAttributes, function($a) {
            if ($a['enabled']) {
                return true;
            }

            return false;
        });

        uasort($arrAttributes, function ($a, $b) {
            return $a["position"] > $b["position"];
        });

        return array_keys($arrAttributes);
    }
}
