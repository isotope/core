<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
     * This function can be called from the "isotope_postsale" route when the payment server is requestion/posting a status change.
     * You can see an implementation example in Isotope\Payment\Postsale
     *
     * @param IsotopeProductCollection $objOrder
     */
    public function processPostsale(IsotopeProductCollection $objOrder);

    /**
     * Get the order object in a postsale request
     *
     * @return IsotopeOrderableCollection
     */
    public function getPostsaleOrder();
}
