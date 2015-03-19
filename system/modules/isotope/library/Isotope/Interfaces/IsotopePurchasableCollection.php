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

use Isotope\Model\ProductCollectionSurcharge;

interface IsotopePurchasableCollection
{

    /**
     * Return true if order has been paid.
     * This is the case if either payment date is set or order status has the paid flag
     *
     * @return bool
     */
    public function isPaid();

    /**
     * Returns true if checkout has been completed
     *
     * @return bool
     */
    public function isCheckoutComplete();

    /**
     * Get label for current order status
     *
     * @return string
     */
    public function getStatusLabel();

    /**
     * Get the alias for current order status
     *
     * @return string
     */
    public function getStatusAlias();

    /**
     * Process the order checkout
     *
     * @return bool
     */
    public function checkout();

    /**
     * Complete order if the checkout has been made. This will cleanup session data
     *
     * @return bool
     */
    public function complete();

    /**
     * Update the status of this order and trigger actions (email & hook)
     *
     * @param int $intNewStatus
     *
     * @return bool
     */
    public function updateOrderStatus($intNewStatus);

    /**
     * Retrieve the array of notification data for parsing simple tokens
     *
     * @param int $intNotification
     *
     * @return array
     */
    public function getNotificationTokens($intNotification);

    /**
     * Return customer email address for the collection
     *
     * @return string
     */
    public function getEmailRecipient();
}
