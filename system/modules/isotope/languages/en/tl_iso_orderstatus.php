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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['name']			= array('Name', 'Please enter a name for this status.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['paid']			= array('Order is paid', 'Assume the order is paid when it has this status. This will for example allow files to be downloaded.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_customer']	= array('E-Mail to customer', 'Select an email template to notify the customer when this status is assigned to an order.');
$GLOBALS['TL_LANG']['tl_iso_orderstatus']['mail_admin']		= array('E-Mail to admin', 'Select an email template to notify the admin when this status is assigned to an order.');


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

