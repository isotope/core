<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\IntegrityCheck;

use Contao\Database;
use Contao\StringUtil;

class VariantPrices extends AbstractIntegrityCheck
{

    /**
     * IDs of invalid records found
     * @var int[]
     */
    protected $arrErrors;

    /**
     * Add IDs of invalid records to the error description
     *
     * @return string
     */
    public function getDescription()
    {
        $strDescription = parent::getDescription();

        if ($this->hasError()) {
            return sprintf($strDescription, \count($this->arrErrors), implode(', ', $this->arrErrors));
        }

        return $strDescription;
    }

    /**
     * Check database table if errors are found
     *
     * @return bool
     */
    public function hasError()
    {
        if (null === $this->arrErrors) {
            // Prices without product
            $this->arrErrors = Database::getInstance()->execute(
                "SELECT id FROM tl_iso_product_price WHERE pid NOT IN (SELECT id FROM tl_iso_product WHERE language='')"
            )->fetchEach('id');

            $productTypeIds = [];
            $variantTypeIds = [];
            $productTypes = Database::getInstance()->execute("SELECT id, variants, attributes, variant_attributes FROM tl_iso_producttype")->fetchAllAssoc();

            foreach ($productTypes as $type) {
                $attributes = StringUtil::deserialize($type['attributes'], true);
                $variantAttributes = StringUtil::deserialize($type['variant_attributes'], true);

                if ($type['variants'] && !($attributes['price']['enabled'] ?? false) && ($variantAttributes['price']['enabled'] ?? false)) {
                    $variantTypeIds[] = $type['id'];
                } else if (!$type['variants'] && ($attributes['price']['enabled'] ?? false)) {
                    $productTypeIds[] = $type['id'];
                }
            }

            $pIds = $vIds = [];

            // Prices belonging to variants of a product that should not have variants
            if (!empty($productTypeIds)) {
                $pIds = Database::getInstance()->execute("
                    SELECT id
                    FROM tl_iso_product_price
                    WHERE pid IN (
                        SELECT id FROM tl_iso_product WHERE language='' AND pid IN (
                            SELECT id FROM tl_iso_product WHERE pid=0 AND language='' AND type IN (".implode(',', $productTypeIds).')
                        )
                    )
                ')->fetchEach('id');
            }

            // Prices belonging to base product of a product that havs variants
            if (!empty($variantTypeIds)) {
                $vIds = Database::getInstance()->execute("
                    SELECT id
                    FROM tl_iso_product_price
                    WHERE pid IN (
                        SELECT id FROM tl_iso_product WHERE pid=0 AND language='' AND type IN (".implode(',', $variantTypeIds).')
                    )
                ')->fetchEach('id');
            }

            $this->arrErrors = array_unique(array_merge($this->arrErrors, $pIds, $vIds));
        }

        return !empty($this->arrErrors);
    }

    /**
     * Return true if this issue can be automatically repaired
     *
     * @return bool
     */
    public function canRepair()
    {
        return true;
    }

    /**
     * Try to fix the integrity issue
     *
     * @return bool
     */
    public function repair()
    {
        if ($this->hasError()) {
            Database::getInstance()->execute('DELETE FROM tl_iso_product_price WHERE id IN ('.implode(',', $this->arrErrors).')');
            Database::getInstance()->execute('DELETE FROM tl_iso_product_pricetier WHERE pid IN ('.implode(',', $this->arrErrors).')');
        }

        return true;
    }
}
