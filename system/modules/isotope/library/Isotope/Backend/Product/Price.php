<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;

use Contao\Backend;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\Widget;
use Isotope\Backend\SubtableVersion;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\TaxClass;


class Price extends Backend
{

    /**
     * Save prices history when creating a new version of a product
     * @param   string
     * @param   int
     */
    public function createVersion($strTable, $intId)
    {
        if ($strTable !== Product::getTable()) {
            return;
        }

        $arrData = array('prices' => array(), 'tiers' => array());

        $objPrices = ProductPrice::findBy('pid', $intId);

        if (null !== $objPrices) {
            $objTiers = Database::getInstance()->query(
                'SELECT * FROM tl_iso_product_pricetier WHERE pid IN (' . implode(',', $objPrices->fetchEach('id')) . ')'
            );

            $arrData['prices'] = $objPrices->fetchAll();
            $arrData['tiers']  = $objTiers->fetchAllAssoc();
        }

        SubtableVersion::create($strTable, $intId, ProductPrice::getTable(), $arrData);

        $current = Database::getInstance()
            ->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND active='1'")
            ->limit(1)
            ->execute($strTable, $intId)
        ;

        if (1 === $current->numRows) {
            $data = StringUtil::deserialize($current->data);

            if (empty($arrData['prices'])) {
                $data['price'] = '';
            } elseif (null === $objPrices->getRelated('tax_class')) {
                $data['price'] = $arrData['prices'][0]['tier_values'];
            } else {
                $data['price'] = sprintf(
                    '%s (%s)',
                    $arrData['prices'][0]['tier_values'], $objPrices->getRelated('tax_class')->name
                );
            }

            Database::getInstance()
                     ->prepare("UPDATE tl_version SET data=? WHERE id=?")
                     ->execute(serialize($data), $current->id)
            ;
        }
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

        $arrData = SubtableVersion::find('tl_iso_product_price', $intId, $intVersion);

        if (null !== $arrData) {
            Database::getInstance()->query('
                DELETE FROM tl_iso_product_pricetier
                WHERE pid IN (
                    SELECT id FROM tl_iso_product_price WHERE pid=' . $intId . '
                )
            ');

            Database::getInstance()->query('DELETE FROM tl_iso_product_price WHERE pid=' . $intId);

            Controller::loadDataContainer('tl_iso_product_price');
            Controller::loadDataContainer('tl_iso_product_pricetier');

            $tableFields = array_flip(Database::getInstance()->getFieldNames('tl_iso_product_price'));

            foreach ($arrData['prices'] as $data) {
                $data = array_intersect_key($data, $tableFields);

                // Reset fields added after storing the version to their default value (see contao/core#7755)
                foreach (array_diff_key($tableFields, $data) as $k=>$v) {
                    $data[$k] = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA']['tl_iso_product_price']['fields'][$k]['sql']);
                }

                Database::getInstance()->prepare('INSERT INTO tl_iso_product_price %s')->set($data)->execute();
            }

            $tableFields = array_flip(Database::getInstance()->getFieldNames('tl_iso_product_pricetier'));

            foreach ($arrData['tiers'] as $data) {
                $data = array_intersect_key($data, $tableFields);

                // Reset fields added after storing the version to their default value (see contao/core#7755)
                foreach (array_diff_key($tableFields, $data) as $k=>$v) {
                    $data[$k] = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA']['tl_iso_product_pricetier']['fields'][$k]['sql']);
                }

                Database::getInstance()->prepare('INSERT INTO tl_iso_product_pricetier %s')->set($data)->execute();
            }

            Database::getInstance()
                     ->prepare("UPDATE tl_version SET active='' WHERE pid=? AND fromTable=?")
                     ->execute($intId, 'tl_iso_product_price')
            ;

            Database::getInstance()
                     ->prepare('UPDATE tl_version SET active=1 WHERE pid=? AND fromTable=? AND version=?')
                     ->execute($intId, 'tl_iso_product_price', $intVersion)
            ;
        }
    }

    /**
     * Load price from prices subtable
     */
    public function load($varValue, DataContainer $dc)
    {
        $objPrice = Database::getInstance()->query("
            SELECT t.id, p.id AS pid, p.tax_class, t.price
            FROM tl_iso_product_price p
            LEFT JOIN tl_iso_product_pricetier t ON p.id=t.pid AND t.min=1
            WHERE p.pid={$dc->id} AND p.config_id=0 AND p.member_group=0 AND p.start='' AND p.stop=''
        ");

        if (!$objPrice->numRows) {
            $objTax = TaxClass::findFallback();

            return array(
                'value' => '0.00',
                'unit'  => null === $objTax ? 0 : $objTax->id,
            );
        }

        return array('value' => $objPrice->price, 'unit' => $objPrice->tax_class);
    }

    /**
     * Save price to the prices subtable
     */
    public function save($varValue, DataContainer $dc)
    {
        $time = time();

        // Parse the timePeriod widget
        $arrValue = StringUtil::deserialize($varValue, true);
        $strPrice = (string) $arrValue['value'];
        $intTax   = (int) $arrValue['unit'];

        $objPrice = Database::getInstance()->query("
            SELECT t.id, p.id AS pid, p.tax_class, t.price
            FROM tl_iso_product_price p
            LEFT JOIN tl_iso_product_pricetier t ON p.id=t.pid AND t.min=1
            WHERE p.pid={$dc->id} AND p.config_id=0 AND p.member_group=0 AND p.start='' AND p.stop=''
        ");

        // Price tier record already exists, update it
        if ($objPrice->numRows && $objPrice->id > 0) {

            if ($objPrice->price != $strPrice) {
                Database::getInstance()
                    ->prepare("UPDATE tl_iso_product_pricetier SET tstamp=$time, price=? WHERE id=?")
                    ->execute($strPrice, $objPrice->id)
                ;

                $dc->createNewVersion = true;
            }

            if ($objPrice->tax_class != $intTax) {
                Database::getInstance()
                    ->prepare("UPDATE tl_iso_product_price SET tstamp=$time, tax_class=? WHERE id=?")
                    ->execute($intTax, $objPrice->pid)
                ;

                $dc->createNewVersion = true;
            }

        } else {

            $intPrice = $objPrice->pid;

            // Neither price tier nor price record exist, must add both
            if (!$objPrice->numRows) {
                $intPrice = Database::getInstance()
                    ->prepare("INSERT INTO tl_iso_product_price (pid,tstamp,tax_class) VALUES (?,?,?)")
                    ->execute($dc->id, $time, $intTax)
                    ->insertId
                ;

            } elseif ($objPrice->tax_class != $intTax) {
                Database::getInstance()
                    ->prepare('UPDATE tl_iso_product_price SET tstamp=?, tax_class=? WHERE id=?')
                    ->execute($time, $intTax, $intPrice)
                ;
            }

            Database::getInstance()
                ->prepare('INSERT INTO tl_iso_product_pricetier (pid,tstamp,min,price) VALUES (?,?,1,?)')
                ->execute($intPrice, $time, $strPrice)
            ;

            $dc->createNewVersion = true;
        }

        return '';
    }
}
