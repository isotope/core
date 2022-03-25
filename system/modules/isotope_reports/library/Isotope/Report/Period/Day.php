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

use Contao\Date;

class Day implements PeriodInterface
{
    public function format($tstamp)
    {
        return Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $tstamp);
    }

    public function getKey($tstamp)
    {
        return date('Ymd', $tstamp);
    }

    public function getPeriodStart($tstamp)
    {
        $date = new Date($tstamp);

        return $date->dayBegin;
    }

    public function getPeriodEnd($tstamp)
    {
        $date = new Date($tstamp);

        return $date->dayEnd;
    }

    public function getNext($tstamp)
    {
        return strtotime('+1 day', $tstamp);
    }

    public function getPrevious($tstamp)
    {
        return strtotime('-1 day', $tstamp);
    }

    public function getSqlField($fieldName)
    {
        return "DATE_FORMAT(FROM_UNIXTIME($fieldName), '%Y%m%d')";
    }

    public function getJavascriptClosure()
    {
        $format = Date::formatToJs($GLOBALS['TL_CONFIG']['dateFormat']);

        return "
function(x) {
    return new Date(x*1000).format('$format');
}";
    }
}
