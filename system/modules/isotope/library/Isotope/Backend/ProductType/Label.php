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

namespace Isotope\Backend\ProductType;

use Haste\Util\Format;
use Isotope\Model\Product;
use Isotope\Model\ProductPrice;
use Isotope\Model\ProductType;


class Label extends \Backend
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
            if ($field == 'name' && $row['fallback']) {
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
