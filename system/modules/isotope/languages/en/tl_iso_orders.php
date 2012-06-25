<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['config_id']			= array('Shop configuration');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_id']			= array('Order ID');
$GLOBALS['TL_LANG']['tl_iso_orders']['uniqid']				= array('Unique ID');
$GLOBALS['TL_LANG']['tl_iso_orders']['status']				= array('Order Status', 'Select the status of this order.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_paid']			= array('Payment date', 'Enter a date when this order has been paid.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date_shipped']		= array('Shipped date', 'Enter a date when this order has been shipped.');
$GLOBALS['TL_LANG']['tl_iso_orders']['date']				= array('Date');
$GLOBALS['TL_LANG']['tl_iso_orders']['grandTotal']			= array('Grand total');
$GLOBALS['TL_LANG']['tl_iso_orders']['payment_id']			= array('Payment method');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_id']			= array('Shipping method');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address']	= array('Shipping Address');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address']		= array('Billing Address');

$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'] = array('Subtotal');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'] = array('Tax Cost');
$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'] = array('Shipping Cost');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'] = array('Shipping Method');
$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'] = array('Surcharges');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'] = array('Card Number', 'The credit card number');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'] = array('CCV Number', '3 or 4-digit Credit Card Verification Number');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'] = array('Card Type', 'The type of credit card.');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'] = array('Expiration', 'The expiration date of the credit card');
$GLOBALS['TL_LANG']['tl_iso_orders']['notes'] = array('Order notes','If you would like to convey information to other backend users, please do so here.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['orderStatusEmail']		= 'The status of your order has been updated and the client has been notified by email.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']				= array('Edit order', 'Edit order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']				= array('Copy order', 'Copy order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete']				= array('Delete order', 'Delete order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']				= array('Order details', 'Show details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['payment']				= array('Payment details', 'Show payment details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping']			= array('Shipping details', 'Show shipping details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order']			= array('Print this order', 'Print an invoice for the current order');
$GLOBALS['TL_LANG']['tl_iso_orders']['tools']				= array('Tools', 'More options for order management.');
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails']		= array('Export Order Emails','Export all emails for those who have ordered.');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_invoices']		= array('Print Invoices','Print one or more invoices into a single document of a certain order status.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['status_legend']	= 'Order status';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend']	= 'Order details';
$GLOBALS['TL_LANG']['tl_iso_orders']['email_legend']	= 'Email data';
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_legend']	= 'Billing address data';
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_legend']	= 'Shipping address data';

