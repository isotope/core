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

namespace Isotope\IntegrityCheck;

class VariantOrphans extends AbstractIntegrityCheck
{

    /**
     * IDs of invalid variants
     * @var int[]
     */
    protected $arrErrors;

    /**
     * Add IDs of invalid variants to the error description
     *
     * @return string
     */
    public function getDescription()
    {
        $strDescription = parent::getDescription();

        if ($this->hasError()) {
            return sprintf($strDescription, count($this->arrErrors), implode(', ', $this->arrErrors));
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
            $this->arrErrors = \Database::getInstance()->query("
                SELECT id FROM tl_iso_product
                WHERE
                  language=''
                  AND pid IN (
                    SELECT id FROM tl_iso_product WHERE type IN (
                      SELECT id FROM tl_iso_producttype WHERE variants=''
                    )
                  )
            ")->fetchEach('id');
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

            // Delete the variants
            \Database::getInstance()->query("
                DELETE FROM tl_iso_product
                WHERE
                  id IN (" . implode(',', $this->arrErrors) . ")
                  OR pid IN (" . implode(',', $this->arrErrors) . ")
            ");
        }
    }
}