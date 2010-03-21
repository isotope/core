<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'] = array('Subtotal','');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'] = array('Tax Cost','');
$GLOBALS['TL_LANG']['tl_iso_orders']['shippingTotal'] = array('Shipping Cost','');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'] = array('Shipping Method','');
$GLOBALS['TL_LANG']['tl_iso_orders']['status'] = array('Order Status','');
$GLOBALS['TL_LANG']['tl_iso_orders']['surcharges'] = array('Surcharges','');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_id'] = array('Shipping Address','');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_id'] = array('Billing Address','');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_comments'] = array('Comments','');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_message'] = array('Gift Message','');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_wrap'] = array('Gift Wrap','');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_num'] = array('Card Number','The credit card number');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_cvv'] = array('CCV Number','3 or 4-digit Credit Card Verification Number');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_type'] = array('Card Type','The type of credit card.');
$GLOBALS['TL_LANG']['tl_iso_orders']['cc_exp'] = array('Expiration','The expiration date of the credit card');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels']['ups_ground'] = 'UPS Ground';

$GLOBALS['TL_LANG']['tl_iso_orders']['opLabel']					= 'Name of Surcharge';
$GLOBALS['TL_LANG']['tl_iso_orders']['opPrice']					= 'Price';
$GLOBALS['TL_LANG']['tl_iso_orders']['opTaxClass']				= 'Tax Class';
$GLOBALS['TL_LANG']['tl_iso_orders']['opAddTax']				= 'Add Tax?';


/**
 * Additional Operations
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'] = array('Authorizet.net Point-of-sale Terminal','Conduct a transaction using the Authorize.net point-of-sale terminal');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'] = array('Print this order','Print an invoice for the current order');
$GLOBALS['TL_LANG']['tl_iso_orders']['export_emails'] = 'Export Order Emails';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['new']    = array('New Order', 'Create a New order');
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']   = array('Edit Order', 'Edit order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']   = array('Copy Order', 'Copy order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'] = array('Delete Order', 'Delete order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']   = array('Order Details', 'Show details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['edit_order'] = array('Edit Order', 'Edit order items, add or remove products.');


/** 
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['general_legend'] = 'General Order Information';
$GLOBALS['TL_LANG']['tl_iso_orders']['details_legend'] = 'Order Details';

