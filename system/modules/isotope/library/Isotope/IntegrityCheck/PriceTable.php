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

class PriceTable extends AbstractIntegrityCheck
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
            $this->arrErrors = 0;

            $arrProducts = \Database::getInstance()->query("
                SELECT id FROM tl_iso_product
                WHERE (
                    pid=0
                    AND type IN (SELECT id FROM tl_iso_producttype WHERE prices='')
                ) OR (
                  pid>0 AND language=''
                  AND pid IN (
                    SELECT id FROM tl_iso_product WHERE type IN (
                      SELECT id FROM tl_iso_producttype WHERE prices=''
                    )
                  )
                )
            ")->fetchEach('id');

            if (!empty($arrProducts)) {
                $objPrices = \Database::getInstance()->query("
                    SELECT id, pid, COUNT(*) AS total
                    FROM tl_iso_product_price
                    WHERE " . \Database::getInstance()->findInSet('pid', implode(',', $arrProducts)) . "
                    GROUP BY pid
                    HAVING total>1
                ");

                $this->arrErrors = $objPrices->fetchEach('pid');
            }
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

    }
}