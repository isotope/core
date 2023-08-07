<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Template;

/**
 * OrderInfo checkout steps shows a summary of all other checkout steps (e.g. addresses, payment and shipping method).
 */
class OrderInfo extends CheckoutStep implements IsotopeCheckoutStep
{
    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        /** @var Template|\stdClass $objTemplate */
        $objTemplate            = new Template('iso_checkout_order_info');
        $objTemplate->headline  = $GLOBALS['TL_LANG']['MSC']['order_review'];
        $objTemplate->message   = $GLOBALS['TL_LANG']['MSC']['order_review_message'];
        $objTemplate->info      = $this->objModule->getCheckoutInfo();
        $objTemplate->edit_info = $GLOBALS['TL_LANG']['MSC']['changeCheckoutInfo'];

        return $objTemplate->parse();
    }

    /**
     * @inheritdoc
     */
    public function review()
    {
        return '';
    }
}
