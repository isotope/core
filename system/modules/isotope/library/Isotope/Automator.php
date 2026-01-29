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
use Contao\System;
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
            [time() - strtotime(System::getContainer()->getParameter('isotope.cart_timeout'), 0)]
        );

        if (($intPurged = $this->deleteOldCollections($objCarts)) > 0) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'Deleted ' . $intPurged . ' old guest carts', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::CRON)]);
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
                time() - strtotime(System::getContainer()->getParameter('isotope.order_timeout'), 0),
            ]
        );

        if (($intPurged = $this->deleteOldCollections($objOrders)) > 0) {
            \Contao\System::getContainer()->get('monolog.logger.contao')->log(\Psr\Log\LogLevel::INFO, 'Deleted ' . $intPurged . ' incomplete orders', ['contao' => new \Contao\CoreBundle\Monolog\ContaoContext(__METHOD__, \Contao\CoreBundle\Monolog\ContaoContext::CRON)]);
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
