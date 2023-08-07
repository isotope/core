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

class Week implements PeriodInterface
{
    public function format($tstamp)
    {
        return date('\K\W W/', $tstamp) . substr(date('o', $tstamp), 2);
    }

    public function getKey($tstamp)
    {
        return date('oW', $tstamp);
    }

    public function getPeriodStart($tstamp)
    {
        $date = new Date($tstamp);

        return $date->getWeekBegin(1);
    }

    public function getPeriodEnd($tstamp)
    {
        $date = new Date($tstamp);

        return $date->getWeekEnd(1);
    }

    public function getNext($tstamp)
    {
        return strtotime('+7 days', $tstamp);
    }

    public function getPrevious($tstamp)
    {
        return strtotime('-7 days', $tstamp);
    }

    public function getSqlField($fieldName)
    {
        return "YEARWEEK(FROM_UNIXTIME($fieldName))";
    }

    public function getJavascriptClosure()
    {
        return "
function(x) {
    function week(source) {
        var target  = new Date(source.valueOf());
        var dayNr   = (source.getDay() + 6) % 7;

        target.setDate(target.getDate() - dayNr + 3);

        var firstThursday = target.valueOf();

        target.setMonth(0, 1);
        if (target.getDay() != 4) {
            target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
        }

        return 1 + Math.ceil((firstThursday - target) / 604800000); // 604800000 = 7 * 24 * 3600 * 1000
    }

    function weekYear(source) {
        var target  = new Date(source.valueOf());
        target.setDate(target.getDate() - ((source.getDay() + 6) % 7) + 3);

        return target.getFullYear();
    }

    var source = new Date(x*1000);
    var week   = week(source);
    var year   = weekYear(source);

    return 'KW ' + String('0'+week).slice(-2) + '/' + String(year).substring(2);
}
";
    }
}
