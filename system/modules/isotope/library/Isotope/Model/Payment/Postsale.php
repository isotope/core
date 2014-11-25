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

namespace Isotope\Model\Payment;

use Isotope\Interfaces\IsotopePostsale;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Payment;


/**
 * Basic class for postsale handling
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Postsale extends Payment implements IsotopePostsale
{

    /**
     * Show message while we are waiting for server-to-server order confirmation
     *
     * @param   IsotopeProductCollection $objOrder    The order being places
     * @param   \Module                  $objModule   The checkout module instance
     *
     * @return  bool
     */
    public function processPayment(IsotopeProductCollection $objOrder, \Module $objModule)
    {
        if ($objOrder->order_status > 0) {
            unset($_SESSION['POSTSALE_TIMEOUT']);

            return true;
        }

        if (!isset($_SESSION['POSTSALE_TIMEOUT'])) {
            $_SESSION['POSTSALE_TIMEOUT'] = 12;
        } else {
            $_SESSION['POSTSALE_TIMEOUT'] = $_SESSION['POSTSALE_TIMEOUT'] - 1;
        }

        if ($_SESSION['POSTSALE_TIMEOUT'] > 0) {

            // Reload page every 5 seconds
            $GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="5,' . \Environment::get('base') . \Environment::get('request') . '">';

            // Do not index or cache the page
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache    = 0;

            $objTemplate          = new \Isotope\Template('mod_message');
            $objTemplate->type    = 'processing';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];

            return $objTemplate->parse();
        }

        unset($_SESSION['POSTSALE_TIMEOUT']);
        \System::log('Payment could not be processed.', __METHOD__, TL_ERROR);

        return false;
    }
}
