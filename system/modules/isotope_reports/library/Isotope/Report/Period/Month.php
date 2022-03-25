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

class Month implements PeriodInterface
{
    public function format($tstamp)
    {
        return date('m/Y', $tstamp);
    }

    public function getKey($tstamp)
    {
        return date('Ym', $tstamp);
    }

    public function getPeriodStart($tstamp)
    {
        $date = new Date($tstamp);

        return $date->monthBegin;
    }

    public function getPeriodEnd($tstamp)
    {
        $date = new Date($tstamp);

        return $date->monthEnd;
    }

    public function getNext($tstamp)
    {
        return strtotime('+1 month', $tstamp);
    }

    public function getPrevious($tstamp)
    {
        return strtotime('-1 month', $tstamp);
    }

    public function getSqlField($fieldName)
    {
        return "DATE_FORMAT(FROM_UNIXTIME($fieldName), '%Y%m')";
    }

    public function getJavascriptClosure()
    {
        return "
function(x) {
    return new Date(x*1000).format('%m/%Y');
}";
    }
}
