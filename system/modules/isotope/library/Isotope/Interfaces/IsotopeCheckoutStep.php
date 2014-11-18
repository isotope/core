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

namespace Isotope\Interfaces;


/**
 * Checkout steps handle individual steps in the Isotope checkout module
 */
interface IsotopeCheckoutStep
{

    /**
     * Return true if the checkout step is available
     * @return  bool
     */
    public function isAvailable();

    /**
     * Return true if the step has an error and forwarding should be cancelled
     * @return  bool
     */
    public function hasError();

    /**
     * Return short name of current class (e.g. for CSS)
     * @return  string
     */
    public function getStepClass();

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate();

    /**
     * Get review information about this step
     * @return  array
     */
    public function review();

    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection);
}
