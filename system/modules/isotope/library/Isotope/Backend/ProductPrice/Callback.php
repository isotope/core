<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductPrice;

use Contao\Backend;
use Contao\Database;
use Contao\StringUtil;
use Haste\Util\Format;


class Callback extends Backend
{

    /**
     * Load default values for the DCA
     */
    public function initializeDCA()
    {
        // Set default tax class
        $GLOBALS['TL_DCA']['tl_iso_product_price']['fields']['tax_class']['default'] = (int) \Isotope\Model\TaxClass::findFallback()->id;
    }


    /**
     * List all price rows
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        if (!$row['id']) {
            return '';
        }

        $arrTiers = array();
        $objTiers = Database::getInstance()->execute("SELECT * FROM tl_iso_product_pricetier WHERE pid={$row['id']} ORDER BY min");

        while ($objTiers->next()) {
            $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
        }

        $arrInfo = array('<tr><td><span class="tl_label">' . $GLOBALS['TL_LANG']['tl_iso_product_price']['price_tiers'][0] . ':</span></td><td>' . implode(', ', $arrTiers) . '</td></tr>');

        foreach ($row as $name => $value) {
            switch ($name) {
                case 'id':
                case 'pid':
                case 'tstamp':
                    break;

                default:
                    if ($value != '' && $value > 0) {
                        $arrInfo[] = '<tr><td><span class="tl_label">' . Format::dcaLabel('tl_iso_product_price', $name) . ':</span></td><td>' . Format::dcaValue('tl_iso_product_price', $name, $value) . '</td></tr>';
                    }
                    break;
            }
        }

        return '<table class="tl_header_table">' . implode('', $arrInfo) . '</table>';
    }


    /**
     * Generate a list of tiers for a wizard in products
     *
     * @param object     $objRecords
     * @param string     $strId
     * @param \DcaWizard $objWidget
     *
     * @return string
     */
    public function generateWizardList($objRecords, $strId, \DcaWizard $objWidget)
    {
        $strReturn = '
<table class="tl_listing showColumns">
<thead>
    <tr>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'price_tiers') . '</td>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'tax_class') . '</td>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'config_id') . '</td>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'member_group') . '</td>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'start') . '</td>
        <td class="tl_folder_tlist">' . Format::dcaLabel('tl_iso_product_price', 'stop') . '</td>
        <td class="tl_folder_tlist">&nbsp;</td>
    </tr>
</thead>
<tbody>';

        while ($objRecords->next()) {

            $arrTiers = array();
            $objTiers = Database::getInstance()->execute(
                "SELECT * FROM tl_iso_product_pricetier WHERE pid={$objRecords->id} ORDER BY min"
            );

            while ($objTiers->next()) {
                $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
            }

            $strReturn .= '
<tr>
    <td class="tl_file_list">' . implode(', ', $arrTiers) . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'tax_class', $objRecords->tax_class) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'config_id', $objRecords->config_id) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $objRecords->member_group) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $objRecords->start) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $objRecords->stop) ? : '-') . '</td>
    <td class="tl_file_list">' . $objWidget->generateRowOperation('edit', $objRecords->row()) . '</td>
</tr>
';
        }

        $strReturn .= '
</tbody>
</table>';

        return $strReturn;
    }


    /**
     * Get tiers and return them as array
     * @param mixed
     * @param object
     * @return array
     */
    public function loadTiers($varValue, $dc)
    {
        if (!$dc->id) {
            return array();
        }

        $arrTiers = Database::getInstance()->execute("SELECT min, price FROM tl_iso_product_pricetier WHERE pid={$dc->id} ORDER BY min")
            ->fetchAllAssoc();

        if (empty($arrTiers)) {
            return array(array('min' => 1));
        }

        return $arrTiers;
    }


    /**
     * Save the price tiers
     * @param mixed
     * @param object
     * @return string
     */
    public function saveTiers($varValue, $dc)
    {
        $arrNew = StringUtil::deserialize($varValue);

        if (!\is_array($arrNew) || empty($arrNew)) {
            Database::getInstance()->query("DELETE FROM tl_iso_product_pricetier WHERE pid={$dc->id}");
        } else {
            $time      = time();
            $arrInsert = array();
            $arrUpdate = array();
            $arrDelete = Database::getInstance()
                ->execute("SELECT min FROM tl_iso_product_pricetier WHERE pid={$dc->id}")
                ->fetchEach('min')
            ;

            foreach ($arrNew as $new) {
                $pos = array_search($new['min'], $arrDelete);

                if ($pos === false) {
                    $arrInsert[$new['min']] = $new['price'];
                } else {
                    $arrUpdate[$new['min']] = $new['price'];
                    unset($arrDelete[$pos]);
                }
            }

            if (!empty($arrDelete)) {
                Database::getInstance()->query(
                    "DELETE FROM tl_iso_product_pricetier WHERE pid={$dc->id} AND min IN (" . implode(',', $arrDelete) . ")"
                );
            }

            if (!empty($arrUpdate)) {
                foreach ($arrUpdate as $min => $price) {
                    Database::getInstance()
                        ->prepare("UPDATE tl_iso_product_pricetier SET tstamp=$time, price=? WHERE pid=? AND min=?")
                        ->execute($price, $dc->id, $min)
                    ;
                }
            }

            if (!empty($arrInsert)) {
                foreach ($arrInsert as $min => $price) {
                    Database::getInstance()
                        ->prepare("INSERT INTO tl_iso_product_pricetier (pid,tstamp,min,price) VALUES (?, $time, ?, ?)")
                        ->execute($dc->id, $min, $price);
                }
            }
        }

        return '';
    }
}
