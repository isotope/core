<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;


/**
 * Class tl_iso_prices
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_prices extends \Backend
{

    /**
     * Load default values for the DCA
     */
    public function initializeDCA()
    {
        // Set default tax class
        $GLOBALS['TL_DCA']['tl_iso_prices']['fields']['tax_class']['default'] = (int) \Database::getInstance()->execute("SELECT id FROM tl_iso_tax_class WHERE fallback='1'")->id;
    }


    /**
     * List all price rows
     * @param array
     * @return string
     */
    public function listRows($row)
    {
        if (!$row['id'])
        {
            return '';
        }

        $arrTiers = array();
        $objTiers = \Database::getInstance()->execute("SELECT * FROM tl_iso_price_tiers WHERE pid={$row['id']} ORDER BY min");

        while ($objTiers->next())
        {
            $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
        }

        $arrInfo = array('<tr><td><span class="tl_label">'.$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers'][0].':</span></td><td>' . implode(', ', $arrTiers) . '</td></tr>');

        foreach ($row as $name => $value)
        {
            switch ($name)
            {
                case 'id':
                case 'pid':
                case 'tstamp':
                    break;

                default:
                    if ($value != '' && $value > 0)
                    {
                        $arrInfo[] = '<tr><td><span class="tl_label">' . Isotope::formatLabel('tl_iso_prices', $name) . ':</span></td><td>' . Isotope::formatValue('tl_iso_prices', $name, $value) . '</td></tr>';
                    }
                    break;
            }
        }

        return '<table class="tl_header_table">' . implode('', $arrInfo) . '</table>';
    }


    /**
     * Generate a list of tiers for a wizard in products
     * @param object
     * @param string
     * @return string
     */
    public function generateWizardList($objRecords, $strId)
    {
    	$strReturn = '
<table class="tl_listing showColumns">
<thead>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'price_tiers') . '</td>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'tax_class') . '</td>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'config_id') . '</td>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'member_group') . '</td>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'start') . '</td>
    <td class="tl_folder_tlist">' . Isotope::formatLabel('tl_iso_prices', 'stop') . '</td>
</thead>
<tbody>';

    	while ($objRecords->next()) {

    	    $arrTiers = array();
            $objTiers = \Database::getInstance()->execute("SELECT * FROM tl_iso_price_tiers WHERE pid={$objRecords->id} ORDER BY min");

            while ($objTiers->next()) {
                $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
            }

	    	$strReturn .= '
<tr>
    <td class="tl_file_list">' . implode(', ', $arrTiers) . '</td>
    <td class="tl_file_list">' . (Isotope::formatValue('tl_iso_prices', 'tax_class', $objRecords->tax_class) ?: '-') . '</td>
    <td class="tl_file_list">' . (Isotope::formatValue('tl_iso_prices', 'config_id', $objRecords->config_id) ?: '-') . '</td>
    <td class="tl_file_list">' . (Isotope::formatValue('tl_iso_prices', 'member_group', $objRecords->member_group) ?: '-') . '</td>
    <td class="tl_file_list">' . (Isotope::formatValue('tl_iso_prices', 'member_group', $objRecords->start) ?: '-') . '</td>
    <td class="tl_file_list">' . (Isotope::formatValue('tl_iso_prices', 'member_group', $objRecords->stop) ?: '-') . '</td>
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
        if (!$dc->id)
        {
            return array();
        }

        $arrTiers = \Database::getInstance()->execute("SELECT min, price FROM tl_iso_price_tiers WHERE pid={$dc->id} ORDER BY min")
                                            ->fetchAllAssoc();

        if (empty($arrTiers))
        {
            return array(array('min'=>1));
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
        $arrNew = deserialize($varValue);

        if (!is_array($arrNew) || empty($arrNew))
        {
            \Database::getInstance()->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id}");
        }
        else
        {
            $time = time();
            $arrInsert = array();
            $arrUpdate = array();
            $arrDelete = \Database::getInstance()->execute("SELECT min FROM tl_iso_price_tiers WHERE pid={$dc->id}")->fetchEach('min');

            foreach ($arrNew as $new)
            {
                $pos = array_search($new['min'], $arrDelete);

                if ($pos === false)
                {
                    $arrInsert[$new['min']] = $new['price'];
                }
                else
                {
                    $arrUpdate[$new['min']] = $new['price'];
                    unset($arrDelete[$pos]);
                }
            }

            if (!empty($arrDelete))
            {
                \Database::getInstance()->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id} AND min IN (" . implode(',', $arrDelete) . ")");
            }

            if (!empty($arrUpdate))
            {
                foreach ($arrUpdate as $min => $price)
                {
                    \Database::getInstance()->prepare("UPDATE tl_iso_price_tiers SET tstamp=$time, price=? WHERE pid={$dc->id} AND min=?")->executeUncached($price, $min);
                }
            }

            if (!empty($arrInsert))
            {
                foreach ($arrInsert as $min => $price)
                {
                    \Database::getInstance()->prepare("INSERT INTO tl_iso_price_tiers (pid,tstamp,min,price) VALUES ({$dc->id}, $time, ?, ?)")->executeUncached($min, $price);
                }
            }
        }

        return '';
    }
}
