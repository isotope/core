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

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name']            = array('Name', 'Please enter a name for this status.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['color']           = array('Color', 'Here you can set a custom color for this status. The color will appear on the order list in back end.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid']            = array('Order is paid', 'Assume the order is paid when it has this status. This will for example allow files to be downloaded.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen']   = array('Show on welcome screen', 'Show number of orders with this status on the backend welcome screen.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['notification']    = array('Notification', 'Please select a notification.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status'] = array('Saferpay transaction', 'Please select an option if you want to automatically update the payment gateway. This only applies if the client has used "Saferpay" or "Billpay for Saferpay" payment method.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new']        = array('New order status', 'Create a new order status');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit']       = array('Edit order status', 'Edit order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy']       = array('Duplicate order status', 'Duplicate order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut']        = array('Move order status', 'Move order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete']     = array('Delete order status', 'Delete order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show']       = array('Order status details', 'Show details of order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteafter'] = array('Paste after', 'Paste after order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteinto']  = array('Paste into', 'Paste into order status ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name_legend']    = 'Name';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['email_legend']   = 'E-Mail Notification';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['payment_legend'] = 'Payment Gateways';

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status']['capture'] = 'Capture payment';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['saferpay_status']['cancel']  = 'Cancel transaction';
