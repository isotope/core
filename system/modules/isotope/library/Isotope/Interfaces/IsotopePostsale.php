<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2008-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Interfaces;


/**
 * IsotopePostsale interface defines if a payment oder shipping method implements postsale handling
 */
interface IsotopePostsale
{

    /**
     * Process post-sale requests.
     *
     * This function can be called from the postsale.php file when the payment server is requestion/posting a status change.
     * You can see an implementation example in Isotope\Payment\Postsale
     */
    public function processPostsale();

}
