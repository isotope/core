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

        $this->import('Isotope\Isotope', 'Isotope');

        $arrTiers = array();
        $objTiers = $this->Database->execute("SELECT * FROM tl_iso_price_tiers WHERE pid={$row['id']} ORDER BY min");

        while ($objTiers->next())
        {
            $arrTiers[] = "{$objTiers->min}={$objTiers->price}";
        }

        $arrInfo = array('<strong>'.$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers'][0].':</strong> ' . implode(', ', $arrTiers));

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
                        $arrInfo[] = '<strong>' . Isotope::formatLabel('tl_iso_prices', $name) . '</strong>: ' . Isotope::formatValue('tl_iso_prices', $name, $value);
                    }
                    break;
            }
        }

        return '<ul style="margin:0"><li>' . implode('</li><li>', $arrInfo) . '</li></ul>';
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

        $arrTiers = $this->Database->execute("SELECT min, price FROM tl_iso_price_tiers WHERE pid={$dc->id} ORDER BY min")
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
            $this->Database->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id}");
        }
        else
        {
            $time = time();
            $arrInsert = array();
            $arrUpdate = array();
            $arrDelete = $this->Database->execute("SELECT min FROM tl_iso_price_tiers WHERE pid={$dc->id}")->fetchEach('min');

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
                $this->Database->query("DELETE FROM tl_iso_price_tiers WHERE pid={$dc->id} AND min IN (" . implode(',', $arrDelete) . ")");
            }

            if (!empty($arrUpdate))
            {
                foreach ($arrUpdate as $min => $price)
                {
                    $this->Database->prepare("UPDATE tl_iso_price_tiers SET tstamp=$time, price=? WHERE pid={$dc->id} AND min=?")->executeUncached($price, $min);
                }
            }

            if (!empty($arrInsert))
            {
                foreach ($arrInsert as $min => $price)
                {
                    $this->Database->prepare("INSERT INTO tl_iso_price_tiers (pid,tstamp,min,price) VALUES ({$dc->id}, $time, ?, ?)")->executeUncached($min, $price);
                }
            }
        }

        return '';
    }
}
