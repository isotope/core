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

interface PeriodInterface
{
    /**
     * Formats the given timestamp into a human readable format
     *
     * @param int $tstamp
     *
     * @return string
     */
    public function format($tstamp);

    /**
     * Formats the timestamp into a machine-comparable format (e.g. Ymd for Year-Day-Month)
     *
     * @param int $tstamp
     *
     * @return int
     */
    public function getKey($tstamp);

    /**
     * Gets period start timestamp relative to the given timestamp.
     *
     * @param int $tstamp
     *
     * @return int
     */
    public function getPeriodStart($tstamp);

    /**
     * Gets the period end timestamp relative to the given timestamp.
     *
     * @param int $tstamp
     *
     * @return int
     */
    public function getPeriodEnd($tstamp);

    /**
     * Gets the timestamp increased by one period (e.g. "next month").
     *
     * @param int $tstamp
     *
     * @return mixed
     */
    public function getNext($tstamp);

    /**
     * Gets the timestamp decreased by one period (e.g. "previous month").
     *
     * @param int $tstamp
     *
     * @return int
     */
    public function getPrevious($tstamp);

    /**
     * Get SQL field query for a machine-comparable value of the given field value.
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getSqlField($fieldName);

    /**
     * Get a javascript function to convert a timestamp into human-readable format.
     * The function should accept one parameter which is the timestamp to be converted.
     *
     * @return string
     */
    public function getJavascriptClosure();
}
