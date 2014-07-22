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

namespace Isotope;

use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Cart;
use Isotope\Model\ProductCollection\Order;


/**
 * Class Isotope\Automator
 *
 * Provide methods to run Isotope automated jobs.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Automator extends \Controller
{

    /**
     * Remove carts that have not been accessed for a given number of days
     */
    public function deleteOldCarts()
    {
        $t = Cart::getTable();
        $objCarts = Cart::findBy(array("$t.member=0", "$t.tstamp<?"), array(time() - $GLOBALS['TL_CONFIG']['iso_cartTimeout']));

        if (($intPurged = $this->deleteOldCollections($objCarts)) > 0) {
            \System::log('Deleted ' . $intPurged . ' old guest carts', __METHOD__, TL_CRON);
        }
    }

    /**
     * Remove orders that have not been completed for a given number of days
     */
    public function deleteOldOrders()
    {
        $t = Order::getTable();
        $objOrders = Order::findBy(array("$t.order_status=0", "$t.tstamp<?"), array(time() - $GLOBALS['TL_CONFIG']['iso_orderTimeout']));

        if (($intPurged = $this->deleteOldCollections($objOrders)) > 0) {
            \System::log('Deleted ' . $intPurged . ' incomplete orders', __METHOD__, TL_CRON);
        }
    }

    /**
     * Update the store configs with latest currency conversion data
     * @param   int Config id (optional, if none given, all will be taken)
     */
    public function convertCurrencies($intId = 0)
    {
        $arrColumns     = array(Config::getTable() . '.currencyAutomator=?');
        $arrValues      = array('1');

        if ($intId > 0) {
            $arrColumns[]   = Config::getTable() . '.id=?';
            $arrValues[]    = $intId;
        }

        $objConfigs = Config::findBy($arrColumns, $arrValues);

        if (null === $objConfigs) {
            return;
        }

        while ($objConfigs->next()) {
            switch ($objConfigs->currencyProvider) {
                case 'ecb.int':
                    $fltCourse       = ($objConfigs->currency == 'EUR') ? 1 : 0;
                    $fltCourseOrigin = ($objConfigs->currencyOrigin == 'EUR') ? 1 : 0;

                    $objRequest = new \Request();
                    $objRequest->send('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

                    if ($objRequest->hasError()) {
                        \System::log('Error retrieving data from European Central Bank (ecb.int): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new \SimpleXMLElement($objRequest->response);

                    foreach ($objXml->Cube->Cube->Cube as $currency) {
                        if (!$fltCourse && strtolower($currency['currency']) == strtolower($objConfigs->currency)) {
                            $fltCourse = (float) $currency['rate'];
                        }

                        if (!$fltCourseOrigin && strtolower($currency['currency']) == strtolower($objConfigs->currencyOrigin)) {
                            $fltCourseOrigin = (float) $currency['rate'];
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin) {
                        \System::log('Could not find currency to convert in European Central Bank (ecb.int).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objConfigs->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $objConfigs->save();
                    break;

                case 'admin.ch':
                    $fltCourse       = ($objConfigs->currency == 'CHF') ? 1 : 0;
                    $fltCourseOrigin = ($objConfigs->currencyOrigin == 'CHF') ? 1 : 0;

                    $objRequest = new \Request();
                    $objRequest->send('http://www.afd.admin.ch/publicdb/newdb/mwst_kurse/wechselkurse.php');

                    if ($objRequest->hasError()) {
                        \System::log('Error retrieving data from Swiss Federal Department of Finance (admin.ch): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new \SimpleXMLElement($objRequest->response);

                    foreach ($objXml->devise as $currency) {
                        if (!$fltCourse && $currency['code'] == strtolower($objConfigs->currency)) {
                            $fltCourse = (float) $currency->kurs;
                        }

                        if (!$fltCourseOrigin && $currency['code'] == strtolower($objConfigs->currencyOrigin)) {
                            $fltCourseOrigin = (float) $currency->kurs;
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin) {
                        \System::log('Could not find currency to convert in Swiss Federal Department of Finance (admin.ch).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objConfigs->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $objConfigs->save();
                    break;

                default:
                    // !HOOK: other currency providers
                    if (isset($GLOBALS['ISO_HOOKS']['convertCurrency']) && is_array($GLOBALS['ISO_HOOKS']['convertCurrency'])) {
                        foreach ($GLOBALS['ISO_HOOKS']['convertCurrency'] as $callback) {
                            $objCallback = \System::importStatic($callback[0]);
                            $objCallback->$callback[1]($objConfigs->current());
                        }
                    }
            }
        }
    }


    /**
     * Delete product collections if they are older than given seconds and not locked
     * @param   string
     * @return  int
     */
    protected function deleteOldCollections($objCollections)
    {
        $intPurged = 0;

        if (null !== $objCollections) {

            /** @var \Isotope\Model\ProductCollection $objCollection */
            foreach ($objCollections as $objCollection) {
                if (!$objCollection->isLocked()) {
                    $objCollection->delete();
                    $intPurged += 1;
                }
            }
        }

        return $intPurged;
    }
}
