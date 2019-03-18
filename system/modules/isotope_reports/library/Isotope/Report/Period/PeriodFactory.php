<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Report\Period;

class PeriodFactory
{

    public static function create($type)
    {
        switch ($type) {
            case 'day':
                return new Day();

            case 'week':
                return new Week();

            case 'month':
                return new Month();

            case 'year':
                return new Year();

            default:
                throw new \InvalidArgumentException('Invalid period "' . $type . '". Reset your session to continue.');
        }
    }
}
