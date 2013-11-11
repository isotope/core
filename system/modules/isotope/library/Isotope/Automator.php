<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
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
     * Remove carts that have not been accessed for a given number of days (depending on store config)
     */
    public function deleteOldCarts()
    {
        $intPurged = 0;
        $objCarts = Cart::findBy(array('member=0', 'tstamp<?'), array(time() - $GLOBALS['TL_CONFIG']['iso_cartTimeout']));

        while ($objCarts->next()) {
            if (($objOrder = Order::findOneBy('source_collection_id', $objCarts->current()->id)) !== null && $objOrder->status == 0) {
                $objOrder->delete();
            }

            $objCarts->current()->delete();
            $intPurged += 1;
        }

        if ($intPurged > 0) {
            \System::log('Purged ' . $intPurged . ' old guest carts', __METHOD__, TL_CRON);
        }
    }


    /**
     * Update the store configs with latest currency conversion data
     * @return void
     */
    public function convertCurrencies()
    {
        $objConfigs = Config::findBy('currencyAutomator', '1');

        if (null === $objConfigs) {
            return;
        }

        while ($objConfigs->next())
        {
            switch ($objConfigs->currencyProvider)
            {
                case 'ecb.int':
                    $fltCourse = ($objConfigs->currency == 'EUR') ? 1 : 0;
                    $fltCourseOrigin = ($objConfigs->currencyOrigin == 'EUR') ? 1 : 0;

                    $objRequest = new \Request();
                    $objRequest->send('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

                    if ($objRequest->hasError())
                    {
                        \System::log('Error retrieving data from European Central Bank (ecb.int): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new SimpleXMLElement($objRequest->response);

                    foreach ($objXml->Cube->Cube->Cube as $currency)
                    {
                        if (!$fltCourse && strtolower($currency['currency']) == strtolower($objConfigs->currency))
                        {
                            $fltCourse = (float) $currency['rate'];
                        }

                        if (!$fltCourseOrigin && strtolower($currency['currency']) == strtolower($objConfigs->currencyOrigin))
                        {
                            $fltCourseOrigin = (float) $currency['rate'];
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin)
                    {
                        \System::log('Could not find currency to convert in European Central Bank (ecb.int).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objConfigs->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $objConfigs->save();
                    break;

                case 'admin.ch':
                    $fltCourse = ($objConfigs->currency == 'CHF') ? 1 : 0;
                    $fltCourseOrigin = ($objConfigs->currencyOrigin == 'CHF') ? 1 : 0;

                    $objRequest = new \Request();
                    $objRequest->send('http://www.afd.admin.ch/publicdb/newdb/mwst_kurse/wechselkurse.php');

                    if ($objRequest->hasError())
                    {
                        \System::log('Error retrieving data from Swiss Federal Department of Finance (admin.ch): ' . $objRequest->error . ' (Code ' . $objRequest->code . ')', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objXml = new SimpleXMLElement($objRequest->response);

                    foreach ($objXml->devise as $currency)
                    {
                        if (!$fltCourse && $currency['code'] == strtolower($objConfigs->currency))
                        {
                            $fltCourse = (float) $currency->kurs;
                        }

                        if (!$fltCourseOrigin && $currency['code'] == strtolower($objConfigs->currencyOrigin))
                        {
                            $fltCourseOrigin = (float) $currency->kurs;
                        }
                    }

                    // Log if one of the currencies is not available
                    if (!$fltCourse || !$fltCourseOrigin)
                    {
                        \System::log('Could not find currency to convert in Swiss Federal Department of Finance (admin.ch).', __METHOD__, TL_ERROR);

                        return;
                    }

                    $objConfigs->priceCalculateFactor = ($fltCourse / $fltCourseOrigin);
                    $objConfigs->save();
                    break;

                default:
                    // !HOOK: other currency providers
                    if (isset($GLOBALS['ISO_HOOKS']['convertCurrency']) && is_array($GLOBALS['ISO_HOOKS']['convertCurrency']))
                    {
                        foreach ($GLOBALS['ISO_HOOKS']['convertCurrency'] as $callback)
                        {
                            $objCallback = \System::importStatic($callback[0]);
                            $objCallback->$callback[1]($objConfigs->current());
                        }
                    }
            }
        }
    }
}
