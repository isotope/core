<?php

namespace Isotope\Report\Period;

interface PeriodInterface
{

    public function format($tstamp);

    public function getKey($tstamp);

    public function getPeriodStart($tstamp);

    public function getPeriodEnd($tstamp);

    public function getNext($tstamp);

    public function getPrevious($tstamp);

    public function getSqlField($fieldName);

    public function getJavascriptClosure();
}
