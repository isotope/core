<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['store']						= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['product_manager']			= array('Product management','');
$GLOBALS['TL_LANG']['MOD']['orders']					= array('Orders', '');
$GLOBALS['TL_LANG']['MOD']['iso_statistics']			= array('Statistics', '');
$GLOBALS['TL_LANG']['MOD']['isotope']					= array('Store configuration','');
$GLOBALS['TL_LANG']['MOD']['iso_dimensions']			= array('Product Dimensions','');

/**														
 * Frontend modules									
 */
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']			= array('Product Filter', 'Define individual filters for Isotope such as category trees and product attribute filters.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']			= array('Product List', 'General Listing module.  Can be used to list products or values of attributes. May be combined with other modules (i.e. the Filter Module) to provide further drill-down capabilities.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']			= array('Product Reader', 'Product reader module.  This is used to display product details.');
$GLOBALS['TL_LANG']['FMD']['iso_cart']					= array('Shopping Cart', 'A fully-featured shopping cart module.  Box or Full Display can be set by template selection.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']				= array('Checkout', 'Allow store customers to complete their transactions.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']			= array('Address Book','Allow customers to manage their address book.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']			= array('Order History', 'Order lister that allows customers to view their order history');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']			= array('Order Details', 'Order reader that allows customers to view order history details');
$GLOBALS['TL_LANG']['FMD']['iso_storeswitcher']			= array('Store Switcher', 'Switch between store configuration to change currency and other settings.');
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts']		= array('Related products', 'List products related to the current one.');


/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['ISO']['config_module']				= 'Isotope eCommerce configuration';
$GLOBALS['TL_LANG']['IMD']['checkout']					= 'Checkout Flow';
$GLOBALS['TL_LANG']['IMD']['product']					= 'Products';
$GLOBALS['TL_LANG']['IMD']['config']					= 'General settings';
$GLOBALS['TL_LANG']['IMD']['shipping']					= array('Shipping methods','Set up Shipping Methods such as UPS, USPS, DHL, etc.');
$GLOBALS['TL_LANG']['IMD']['payment']					= array('Payment methods','Set up Payment Methods such as Authorize.net, PayPal Pro, and more.');
$GLOBALS['TL_LANG']['IMD']['tax_class']					= array('Tax classes','Set up Tax classes, which contain sets of Tax rates.');
$GLOBALS['TL_LANG']['IMD']['tax_rate']					= array('Tax rates','Set up tax rates based on things like shipping/billing location and order total.');
$GLOBALS['TL_LANG']['IMD']['attribute_manager']			= array('Attribute manager','Manage and create product attributes such as size, color, etc.');
$GLOBALS['TL_LANG']['IMD']['product_type_manager']		= array('Product type manager','Manage and create Product types from sets of Product attributes.');
$GLOBALS['TL_LANG']['IMD']['related_categories']		= array('Related categories', 'Define categories for product relations.');
$GLOBALS['TL_LANG']['IMD']['iso_mail']					= array('E-Mail manager','Customize Admin and Customer Notification Emails.');
$GLOBALS['TL_LANG']['IMD']['store_configuration']		= array('Store configuration','Configure general settings for this store.');

