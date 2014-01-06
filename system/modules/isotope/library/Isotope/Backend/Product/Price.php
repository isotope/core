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

namespace Isotope\Backend\Product;

use Isotope\Backend\SubtableVersion;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;


class Price extends \Backend
{

    /**
     * Save prices history when creating a new version of a product
     * @param   string
     * @param   int
     * @param   \DataContainer
     */
    public function createVersion($strTable, $intId, $dc)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $arrData = array('prices' => array(), 'tiers' => array());

        $objPrices = ProductPrice::findBy('pid', $intId);

        if (null !== $objPrices) {
            $objTiers = \Database::getInstance()->query("SELECT * FROM tl_iso_product_pricetier WHERE pid IN (" . implode(',', $objPrices->fetchEach('id')) . ")");

            $arrData['prices'] = $objPrices->fetchAll();
            $arrData['tiers']  = $objTiers->fetchAllAssoc();
        }

        SubtableVersion::create($strTable, $intId, ProductPrice::getTable(), $arrData);
    }

    /**
     * Restore pricing information when restoring a product
     * @param   int
     * @param   string
     * @param   array
     * @param   int
     */
    public function restoreVersion($intId, $strTable, $arrData, $intVersion)
    {
        if ($strTable != Product::getTable()) {
            return;
        }

        $arrData = SubtableVersion::find(ProductPrice::getTable(), $intId, $intVersion);

        if (null !== $arrData) {
            \Database::getInstance()->query("DELETE FROM tl_iso_product_pricetier WHERE pid IN (SELECT id FROM " . ProductPrice::getTable() . " WHERE pid=$intId)");
            \Database::getInstance()->query("DELETE FROM " . ProductPrice::getTable() . " WHERE pid=$intId");

            foreach ($arrData['prices'] as $arrRow) {
                \Database::getInstance()->prepare("INSERT INTO " . ProductPrice::getTable() . " %s")->set($arrRow)->executeUncached();
            }

            foreach ($arrData['tiers'] as $arrRow) {
                \Database::getInstance()->prepare("INSERT INTO tl_iso_product_pricetier %s")->set($arrRow)->executeUncached();
            }
        }
    }

    /**
     * Load price from prices subtable
     * @param   mixed
     * @param   DataContainer
     * @return  mixed
     */
    public function load($varValue, \DataContainer $dc)
    {
        $objPrice = \Database::getInstance()->query("SELECT t.id, p.id AS pid, p.tax_class, t.price FROM " . ProductPrice::getTable() . " p LEFT JOIN tl_iso_product_pricetier t ON p.id=t.pid AND t.min=1 WHERE p.pid={$dc->id} AND p.config_id=0 AND p.member_group=0 AND p.start='' AND p.stop=''");

        if (!$objPrice->numRows) {
            $objTax = TaxClass::findFallback();

            return array(
                'value' => '0.00',
                'unit'  => (null === $objTax ? 0 : $objTax->id),
            );
        }

        return array('value' => $objPrice->price, 'unit' => $objPrice->tax_class);
    }

    /**
     * Save price to the prices subtable
     * @param   mixed
     * @param   DataContainer
     * @return  mixed
     */
    public function save($varValue, \DataContainer $dc)
    {
        $time = time();

        // Parse the timePeriod widget
        $arrValue = deserialize($varValue, true);
        $strPrice = (string) $arrValue['value'];
        $intTax   = (int) $arrValue['unit'];

        $objPrice = \Database::getInstance()->query("SELECT t.id, p.id AS pid, p.tax_class, t.price FROM " . ProductPrice::getTable() . " p LEFT JOIN tl_iso_product_pricetier t ON p.id=t.pid AND t.min=1 WHERE p.pid={$dc->id} AND p.config_id=0 AND p.member_group=0 AND p.start='' AND p.stop=''");

        // Price tier record already exists, update it
        if ($objPrice->numRows && $objPrice->id > 0) {

            if ($objPrice->price != $strPrice) {
                \Database::getInstance()->prepare("UPDATE tl_iso_product_pricetier SET tstamp=$time, price=? WHERE id=?")->executeUncached($strPrice, $objPrice->id);

                $dc->createNewVersion = true;
            }

            if ($objPrice->tax_class != $intTax) {
                \Database::getInstance()->prepare(
                    "UPDATE " . ProductPrice::getTable() . " SET tstamp=$time, tax_class=? WHERE id=?
                ")->executeUncached($intTax, $objPrice->pid);

                $dc->createNewVersion = true;
            }

        } else {

            $intPrice = $objPrice->pid;

            // Neither price tier nor price record exist, must add both
            if (!$objPrice->numRows) {
                $intPrice = \Database::getInstance()->prepare("
                    INSERT INTO " . ProductPrice::getTable() . " (pid,tstamp,tax_class) VALUES (?,?,?)
                ")->execute($dc->id, $time, $intTax)->insertId;

            } elseif ($objPrice->tax_class != $intTax) {
                \Database::getInstance()->prepare("
                    UPDATE " . ProductPrice::getTable() . " SET tstamp=?, tax_class=? WHERE id=?
                ")->execute($time, $intTax, $intPrice);
            }

            \Database::getInstance()->prepare("
                INSERT INTO tl_iso_product_pricetier (pid,tstamp,min,price) VALUES (?,?,1,?)
            ")->executeUncached($intPrice, $time, $strPrice);

            $dc->createNewVersion = true;
        }

        return '';
    }
}
