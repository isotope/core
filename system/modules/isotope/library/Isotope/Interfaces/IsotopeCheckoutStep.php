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

namespace Isotope\Interfaces;

use Isotope\Interfaces\IsotopeProductCollection;


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
     * Return array of tokens for email templates
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getEmailTokens(IsotopeProductCollection $objCollection);
}
