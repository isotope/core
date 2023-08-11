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

use Isotope\Model\ProductCollectionSurcharge;

/**
 * IsotopePurchasableCollection describes a product collection that can be bought.
 */
interface IsotopePurchasableCollection extends IsotopeOrderableCollection
{

    /**
     * Returns true if order has been paid.
     * This is the case if either payment date is set or order status has the paid flag.
     *
     * @return bool
     */
    public function isPaid();

    /**
     * Returns the payment date for this collection.
     *
     * @return int|null
     */
    public function getDatePaid();

    /**
     * Sets payment date as timestamp or null if not paid.
     *
     * @param int|null $timestamp
     *
     * @return mixed
     */
    public function setDatePaid($timestamp = null);

    /**
     * Returns true if order has been shipped.
     * This is the case if a shipping date is set.
     *
     * @return bool
     */
    public function isShipped();

    /**
     * Returns the shipping date or null if not shipped.
     *
     * @return int|null
     */
    public function getDateShipped();

    /**
     * Set shipping date as timestamp or null if not shipped.
     *
     * @param int|null $timestamp
     */
    public function setDateShipped($timestamp = null);

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
     * @param int|array<string,mixed> $intNewStatus
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
