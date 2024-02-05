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
use Contao\Database\Result;
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
        if ($taxClass = \Isotope\Model\TaxClass::findFallback())
        {
            $GLOBALS['TL_DCA']['tl_iso_product_price']['fields']['tax_class']['default'] = (int) $taxClass->id;
        }
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
     * @param array|Result $rows
     * @param string       $strId
     * @param \DcaWizard   $objWidget
     *
     * @return string
     */
    public function generateWizardList($rows, $strId, $objWidget)
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

        $operations = \is_array($objWidget->operations ?? null) ? $objWidget->operations : ['edit'];

        if ($rows instanceof Result) {
            $rows = $rows->fetchAllAssoc();
        }

        foreach ($rows as $row) {
            $arrTiers = array();
            $objTiers = Database::getInstance()->execute(
                "SELECT * FROM tl_iso_product_pricetier WHERE pid={$row['id']} ORDER BY min"
            );

            while ($objTiers->next()) {
                $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
            }

            $strReturn .= '
<tr>
    <td class="tl_file_list">' . implode(', ', $arrTiers) . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'tax_class', $row['tax_class']) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'config_id', $row['config_id']) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $row['member_group']) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $row['start']) ? : '-') . '</td>
    <td class="tl_file_list">' . (Format::dcaValue('tl_iso_product_price', 'member_group', $row['stop']) ? : '-') . '</td>
    <td class="tl_file_list tl_right_nowrap">';

            foreach ($operations as $operation) {
                $strReturn .= $objWidget->generateRowOperation($operation, $row);
            }

            $strReturn .= '</td>
</tr>
';
        }

        return $strReturn . '
</tbody>
</table>';
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

            foreach ($arrUpdate as $min => $price) {
                Database::getInstance()
                    ->prepare("UPDATE tl_iso_product_pricetier SET tstamp=$time, price=? WHERE pid=? AND min=?")
                    ->execute($price, $dc->id, $min)
                ;
            }

            foreach ($arrInsert as $min => $price) {
                Database::getInstance()
                    ->prepare("INSERT INTO tl_iso_product_pricetier (pid,tstamp,min,price) VALUES (?, $time, ?, ?)")
                    ->execute($dc->id, $min, $price);
            }
        }

        return '';
    }
}
