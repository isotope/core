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

class AttributeOptionOrphans extends AbstractIntegrityCheck
{

    /**
     * IDs of invalid attribute options
     * @var int[]
     */
    protected $arrErrors;

    /**
     * Add IDs of invalid attribute options to the error description
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

            // Options of attributes with wrong optionsSource
            $arrErrors1 = Database::getInstance()->query("
                SELECT id
                FROM tl_iso_attribute_option
                WHERE
                    ptable = 'tl_iso_attribute'
                    AND pid IN (
                        SELECT id FROM tl_iso_attribute WHERE optionsSource != 'table'
                    )
            ")->fetchEach('id');

            // Options of attributes that do not exist anymore
            $arrErrors2 = Database::getInstance()->query("
                SELECT id
                FROM tl_iso_attribute_option
                WHERE
                    ptable = 'tl_iso_attribute'
                    AND pid NOT IN (
                        SELECT id FROM tl_iso_attribute
                    )
            ")->fetchEach('id');

            // Options of products where the attribute has the wrong optionsSource
            $arrErrors3 = Database::getInstance()->query("
                SELECT o.id
                FROM tl_iso_attribute_option o
                LEFT JOIN tl_iso_attribute a ON o.field_name=a.field_name
                WHERE
                    o.ptable = 'tl_iso_product'
                    AND (a.optionsSource != 'product'
                        OR a.variant_option != ''
                        OR a.customer_defined != '1'
                    )
            ")->fetchEach('id');

            // Options of products that do not exist anymore
            $arrErrors4 = Database::getInstance()->query("
                SELECT id
                FROM tl_iso_attribute_option
                WHERE
                    ptable = 'tl_iso_product'
                    AND pid NOT IN (
                        SELECT id FROM tl_iso_product
                    )
            ")->fetchEach('id');

            $this->arrErrors = array_merge(
                $arrErrors1,
                $arrErrors2,
                $arrErrors3,
                $arrErrors4
            );
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
     */
    public function repair()
    {
        if ($this->hasError()) {

            // Delete the attribute options
            Database::getInstance()->query("
                DELETE FROM tl_iso_attribute_option
                WHERE
                  id IN (" . implode(',', $this->arrErrors) . ")
            ");
        }
    }
}
