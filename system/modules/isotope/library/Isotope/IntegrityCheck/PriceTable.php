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
            $this->arrErrors = array();

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
                    SELECT tl_iso_product_price.*, COUNT(*) AS total
                    FROM tl_iso_product_price
                    WHERE " . \Database::getInstance()->findInSet('pid', implode(',', $arrProducts)) . "
                    GROUP BY pid
                    HAVING total>1 OR config_id>0 OR member_group>0 OR start!='' OR stop!=''
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
        if ($this->hasError()) {

            foreach ($this->arrErrors as $productId) {
                $objPrices = \Database::getInstance()->prepare("
                    SELECT * FROM tl_iso_product_price
                    WHERE pid=?
                ")->execute($productId);

                if ($objPrices->numRows > 0) {
                    $keep = array();
                    $delete = array();

                    // Find prices that are valid as "non-advanced" and try to keep that price
                    while ($objPrices->next()) {
                        if ($objPrices->config_id == 0
                            && $objPrices->member_group == 0
                            && $objPrices->start == ''
                            && $objPrices->stop == ''
                        ) {
                            $keep[] = $objPrices->id;
                        } else {
                            $delete[] = $objPrices->id;
                        }
                    }

                    // If more than one price qualifies, we will keep the first (lowest ID) one
                    if (count($keep) > 1) {
                        $delete = array_merge($delete, array_diff($keep, array(min($keep))));
                        $keep = min($keep);
                    }

                    // If there are no valid prices, we must take one of the unqualified and remote restrictions
                    elseif (empty($keep)) {
                        $keep = min($delete);
                        unset($delete[array_search($keep, $delete)]);
                    }

                    // Make sure $keep only holds one item
                    if (is_array($keep)) {
                        $keep = $keep[0];
                    }

                    // Make sure the price we keep does not have config etc. assigned
                    \Database::getInstance()->prepare("
                        UPDATE tl_iso_product_price
                        SET
                          config_id=0,
                          member_group=0,
                          start='',
                          stop=''
                        WHERE id=?
                    ")->execute($keep);

                    // Now delete the additional prices and price tiers
                    if (!empty($delete)) {
                        \Database::getInstance()->prepare("
                            DELETE FROM tl_iso_product_price
                            WHERE id IN (" . implode(',', $delete) . ")
                        ");

                        \Database::getInstance()->prepare("
                            DELETE FROM tl_iso_product_price_tier
                            WHERE pid IN (" . implode(',', $delete) . ")
                        ");
                    }
                }
            }
        }
    }
}