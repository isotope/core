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

namespace Isotope\Model\Payment;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopePostsale;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;


/**
 * Basic class for postsale handling
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
abstract class Postsale extends Payment implements IsotopePostsale
{

    /**
     * Show message while we are waiting for server-to-server order confirmation
     */
    public function processPayment()
    {
        if (($objOrder = Order::findOneBy('source_collection_id', Isotope::getCart()->id)) === null)
        {
            return false;
        }

        if ($objOrder->date_paid > 0 && $objOrder->date_paid <= time())
        {
            \Isotope\Frontend::clearTimeout();

            return true;
        }

        if (\Isotope\Frontend::setTimeout())
        {
            // Do not index or cache the page
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;

            $objTemplate = new \Isotope\Template('mod_message');
            $objTemplate->type = 'processing';
            $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['payment_processing'];

            return $objTemplate->parse();
        }

        \System::log('Payment could not be processed.', __METHOD__, TL_ERROR);
        \Isotope\Module\Checkout::redirectToStep('failed');
    }
}
