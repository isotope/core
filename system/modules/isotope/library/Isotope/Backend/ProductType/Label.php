<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductType;

use Contao\Backend;
use Haste\Util\Format;

class Label extends Backend
{

    /**
     * Generate a product label and return it as HTML string
     * @param array
     * @param string
     * @param object
     * @param array
     * @return string
     */
    public function generate($row, $label, $dc, $args)
    {
        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
            if ('name' === $field && $row['fallback']) {
                $args[$i] = sprintf(
                    '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
                    $row['name'],
                    Format::dcaLabel($dc->table, 'fallback')
                );
            } else {
                $args[$i] = Format::dcaValue($dc->table, $field, $row[$field]);
            }
        }

        return $args;
    }
}
