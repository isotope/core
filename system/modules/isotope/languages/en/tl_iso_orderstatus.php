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
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name']			= array('Name', 'Please enter a name for this status.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid']			= array('Order is paid', 'Assume the order is paid when it has this status. This will for example allow files to be downloaded.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['welcomescreen']	= array('Show on welcome screen', 'Show number of orders with this status on the backend welcome screen.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_customer']	= array('E-Mail to customer', 'Select an email template to notify the customer when this status is assigned to an order.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_admin']		= array('E-Mail to admin', 'Select an email template to notify the admin when this status is assigned to an order.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['sales_email']	= array('Sales admin email address', 'Enter an email address for status notifications to be sent to. If you dont enter anything, the checkout modules sales admin or system admin will be notified.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['new']			= array('New order status', 'Create a new order status');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['edit']			= array('Edit order status', 'Edit order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['copy']			= array('Duplicate order status', 'Duplicate order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['cut']			= array('Move order status', 'Move order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['delete']			= array('Delete order status', 'Delete order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['show']			= array('Order status details', 'Show details of order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteafter']		= array('Paste after', 'Paste after order status ID %s');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['pasteinto']		= array('Paste into', 'Paste into order status ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name_legend']	= 'Name';
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['email_legend']	= 'E-Mail Notification';
