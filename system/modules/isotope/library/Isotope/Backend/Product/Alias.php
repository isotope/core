<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\Product;


class Alias extends \Backend
{

    /**
     * Autogenerate a product alias if it has not been set yet
     * @param mixed
     * @param DataContainer
     * @return string
     * @throws Exception
     */
    public function save($varValue, \DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue = standardize(\Input::post('name'));

            if ($varValue == '') {
                $varValue = standardize(\Input::post('sku'));
            }

            if ($varValue == '') {
                $varValue = strlen($dc->activeRecord->name) ? standardize($dc->activeRecord->name) : standardize($dc->activeRecord->sku);
            }

            if ($varValue == '') {
                $varValue = $dc->id;
            }
        }

        $objAlias = \Database::getInstance()->prepare("SELECT id FROM tl_iso_product WHERE id=? OR alias=?")
                                   ->execute($dc->id, $varValue);

        // Check whether the product alias exists
        if ($objAlias->numRows > 1) {
            if (!$autoAlias) {
                throw new OverflowException(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '.' . $dc->id;
        }

        return $varValue;
    }
}
