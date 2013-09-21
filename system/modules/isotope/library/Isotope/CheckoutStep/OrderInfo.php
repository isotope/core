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

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;


class OrderInfo extends CheckoutStep implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $objTemplate = new \Isotope\Template('iso_checkout_order_info');
        $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['order_review'];
        $objTemplate->message = $GLOBALS['TL_LANG']['MSC']['order_review_message'];
        $objTemplate->summary = $GLOBALS['TL_LANG']['MSC']['cartSummary'];
        $objTemplate->info = $this->objModule->getCheckoutInfo();
        $objTemplate->edit_info = $GLOBALS['TL_LANG']['MSC']['changeCheckoutInfo'];

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        return '';
    }

    /**
     * Return array of tokens for email templates
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getEmailTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }
}
