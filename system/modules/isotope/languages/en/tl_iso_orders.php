<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['order_id']                = array('Order ID');
$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid']                  = array('Unique ID');
$GLOBALS['TL_LANG']['tl_iso_orders']['status']                  = array('Order Status', 'Select the status of this order.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date']                    = array('Date');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid']               = array('Payment date', 'Enter a date when this order has been paid.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped']            = array('Shipped date', 'Enter a date when this order has been shipped.');
$GLOBALS['TL_LANG']['tl_iso_orders']['config_id']               = array('Shop configuration');
$GLOBALS['TL_LANG']['tl_iso_orders']['payment_id']              = array('Payment method');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_id']             = array('Shipping method');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address']        = array('Shipping Address');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address']         = array('Billing Address');
$GLOBALS['TL_LANG']['tl_iso_orders']['notes']                   = array('Order notes','If you would like to convey information to other backend users, please do so here.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']                    = array('Edit order', 'Edit order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']                    = array('Copy order', 'Copy order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete']                  = array('Delete order', 'Delete order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']                    = array('Order details', 'Show details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['payment']                 = array('Payment details', 'Show payment details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping']                = array('Shipping details', 'Show shipping details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order']             = array('Print this order', 'Print an invoice for the current order');
$GLOBALS['TL_LANG']['tl_iso_orders']['tools']                   = array('Tools', 'More options for order management.');
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails']           = array('Export Order Emails','Export all emails for those who have ordered.');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices']          = array('Print Invoices','Print one or more invoices into a single document of a certain order status.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['orderStatusEmail']        = 'The status of your order has been updated and the client has been notified by email.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['status_legend']           = 'Order status';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend']          = 'Order details';
$GLOBALS['TL_LANG']['tl_iso_orders']['email_legend']            = 'Email data';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_legend']  = 'Billing address data';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_legend'] = 'Shipping address data';
