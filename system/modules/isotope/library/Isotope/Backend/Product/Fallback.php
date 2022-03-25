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
use Contao\System;

class Fallback extends Backend
{

    /**
     * Reset fallback checkbox for other variants of a product.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function reset($varValue, DataContainer $dc)
    {
        if (!$varValue || !$dc->activeRecord->pid) {
            return $varValue;
        }

        Database::getInstance()
            ->prepare("UPDATE tl_iso_product SET fallback='' WHERE pid=? AND id!=?")
            ->execute($dc->activeRecord->pid, $dc->activeRecord->id)
        ;

        return $varValue;
    }

    /**
     * @param DataContainer $dc
     */
    public function setFromUrl($dc)
    {
        $product = Database::getInstance()->prepare('SELECT * FROM tl_iso_product WHERE id=?')->execute($dc->id);
        $dc->activeRecord = $product;

        if ($product->numRows) {
            $value = $product->fallback ? '' : '1';
            $dca   = &$GLOBALS['TL_DCA']['tl_iso_product']['fields']['fallback'];

            if (!empty($dca['save_callback']) && \is_array($dca['save_callback'])) {
                foreach ($dca['save_callback'] as $callback) {
                    if (\is_array($callback)) {
                        $value = System::importStatic($callback[0])->{$callback[1]}($value, $dc);
                    } else {
                        $value = $callback($value, $dc);
                    }
                }
            }

            Database::getInstance()
                ->prepare('UPDATE tl_iso_product SET fallback=? WHERE id=?')
                ->execute($value, $product->id)
            ;
        }

        Controller::redirect(System::getReferer());
    }
}
