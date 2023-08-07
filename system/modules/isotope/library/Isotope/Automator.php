<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope;

use Contao\Controller;
use Contao\Request;
use Contao\System;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;

class Automator extends Controller
{
    /**
     * Make the constructor public.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Remove carts that have not been accessed for a given number of days
     */
    public function deleteOldCarts()
    {
        $t = Cart::getTable();
        $objCarts = Cart::findBy(
            ["($t.member=0 AND $t.tstamp<?) OR ($t.member > 0 AND $t.member NOT IN (SELECT id FROM tl_member))"],
            [time() - $GLOBALS['TL_CONFIG']['iso_cartTimeout']]
        );

        if (($intPurged = $this->deleteOldCollections($objCarts)) > 0) {
            System::log('Deleted ' . $intPurged . ' old guest carts', __METHOD__, TL_CRON);
        }
    }

    /**
     * Remove orders that have not been completed for a given number of days
     */
    public function deleteOldOrders()
    {
        $t = Order::getTable();
        $objOrders = Order::findBy(
            [
                "$t.order_status=0",
                "$t.tstamp<?"
            ],
            [
                time() - $GLOBALS['TL_CONFIG']['iso_orderTimeout']
            ]
        );

        if (($intPurged = $this->deleteOldCollections($objOrders)) > 0) {
            System::log('Deleted ' . $intPurged . ' incomplete orders', __METHOD__, TL_CRON);
        }
    }

    /**
     * Update the store configs with latest currency conversion data
     *
     * @param int $intId Config id (optional, if none given, all will be taken)
     */
    public function convertCurrencies($intId = 0)
    {
        $arrColumns = [Config::getTable() . '.currencyAutomator=?'];
        $arrValues  = ['1'];

        if ($intId > 0) {
            $arrColumns[]   = Config::getTable() . '.id=?';
            $arrValues[]    = $intId;
        }

        /** @var Config[] $configs */
        $configs = Config::findBy($arrColumns, $arrValues);

        if (null === $configs) {
            return;
        }

        foreach ($configs as $config) {
            switch ($config->currencyProvider) {
                case 'ecb_int':
                case 'ecb.int': // Backwards compatibility
                    $fltCourse       = ('EUR' === $config->currency) ? 1 : 0;
                    $fltCourseOrigin = ('EUR' === $config->currencyOrigin) ? 1 : 0;

                    $objRequest = new Request();
                    $objRequest->send('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

                    if ($objRequest->hasError()) {
                        System::log('Error retrieving data from European Central Bank (ecb.int): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new \SimpleXMLElement($objRequest->response);

                    foreach ($objXml->Cube->Cube->Cube as $currency) {
                        if (!$fltCourse && strtolower($currency['currency']) == strtolower($config->currency)) {
                            $fltCourse = (float) $currency['rate'];
                        }

                        if (!$fltCourseOrigin
                            && strtolower($currency['currency']) == strtolower($config->currencyOrigin)
                        ) {
                            $fltCourseOrigin = (float) $currency['rate'];
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin) {
                        System::log('Could not find currency to convert in European Central Bank (ecb.int).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $config->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $config->save();
                    break;

                case 'admin_ch':
                case 'admin.ch': // Backwards compatibility
                    $fltCourse       = ('CHF' === $config->currency) ? 1 : 0;
                    $fltCourseOrigin = ('CHF' === $config->currencyOrigin) ? 1 : 0;

                    $objRequest = new Request();
                    $objRequest->send('http://www.afd.admin.ch/publicdb/newdb/mwst_kurse/wechselkurse.php');

                    if ($objRequest->hasError()) {
                        System::log('Error retrieving data from Swiss Federal Department of Finance (admin.ch): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new \SimpleXMLElement($objRequest->response);

                    foreach ($objXml->devise as $currency) {
                        if (!$fltCourse && $currency['code'] == strtolower($config->currency)) {
                            $fltCourse = (float) $currency->kurs;
                        }

                        if (!$fltCourseOrigin && $currency['code'] == strtolower($config->currencyOrigin)) {
                            $fltCourseOrigin = (float) $currency->kurs;
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin) {
                        System::log('Could not find currency to convert in Swiss Federal Department of Finance (admin.ch).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $config->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $config->save();
                    break;

                default:
                    // !HOOK: other currency providers
                    if (isset($GLOBALS['ISO_HOOKS']['convertCurrency'])
                        && \is_array($GLOBALS['ISO_HOOKS']['convertCurrency'])
                    ) {
                        foreach ($GLOBALS['ISO_HOOKS']['convertCurrency'] as $callback) {
                            System::importStatic($callback[0])->{$callback[1]}($config);
                        }
                    }
            }
        }
    }


    /**
     * Delete product collections if they are older than given seconds and not locked
     *
     * @param ProductCollection[] $objCollections
     *
     * @return int
     */
    protected function deleteOldCollections($objCollections)
    {
        $intPurged = 0;

        if (null !== $objCollections) {
            foreach ($objCollections as $objCollection) {
                if (!$objCollection->isLocked()) {
                    $objCollection->delete();
                    ++$intPurged;
                }
            }
        }

        return $intPurged;
    }
}
