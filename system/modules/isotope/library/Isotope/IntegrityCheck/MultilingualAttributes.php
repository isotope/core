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

class MultilingualAttributes extends AbstractIntegrityCheck
{

    /**
     * ID/Name map of invalid attributes
     * @var string[]
     */
    protected $arrErrors;

    /**
     * Add names of invalid attributes to the error description
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
            $this->arrErrors = Database::getInstance()->execute("
                SELECT id, field_name
                FROM tl_iso_attribute
                WHERE type IN ('select', 'radio', 'checkbox')
                    AND multilingual='1'
            ")->fetchEach('field_name');
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
                UPDATE tl_iso_attribute
                SET multilingual=''
                WHERE
                  id IN (" . implode(',', array_keys($this->arrErrors)) . ")
            ");
        }
    }
}
