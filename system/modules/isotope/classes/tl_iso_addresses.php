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
 */

namespace Isotope;


/**
 * Class tl_iso_addresses
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_addresses extends \Backend
{

    /**
     * Generate and return the address label
     * @param array
     * @return string
     */
    public function renderLabel($arrAddress)
    {
        $this->import('Isotope\Isotope', 'Isotope');

        $objAddress = new \Isotope\Model\Address();
        $objAddress->setData($arrAddress);
        $strBuffer = $objAddress->generateHtml();

        $strBuffer .= '<div style="color:#b3b3b3;margin-top:8px">' . $GLOBALS['TL_LANG']['tl_iso_addresses']['store_id'][0] . ' ' . $arrAddress['store_id'];

        if ($arrAddress['isDefaultBilling'])
        {
            $strBuffer .= ', ' . $GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultBilling'][0];
        }

        if ($arrAddress['isDefaultShipping'])
        {
            $strBuffer .= ', ' . $GLOBALS['TL_LANG']['tl_iso_addresses']['isDefaultShipping'][0];
        }

        $strBuffer .= '</div>';
        return $strBuffer;
    }


    /**
     * Reset all default checkboxes when setting a new address as default
     * @param mixed
     * @param object
     * @return mixed
     * @link http://www.contao.org/callback.html#save_callback
     */
    public function updateDefault($varValue, $dc)
    {
        $objAddress = ($dc instanceof \DataContainer) ? $dc->activeRecord : $dc;

        if ($varValue == '1' && $objAddress->{$dc->field} != $varValue)
        {
            $this->Database->execute("UPDATE tl_iso_addresses SET {$dc->field}='' WHERE pid={$objAddress->pid} AND store_id={$objAddress->store_id}");
        }

        return $varValue;
    }
}
