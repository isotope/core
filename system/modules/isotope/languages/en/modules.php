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
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope Online-Shop';
$GLOBALS['TL_LANG']['FMD']['isoFilterModule']			= array('Isotope Filter Module', 'Define individual filters for Isotope such as category trees and product attribute filters.');
$GLOBALS['TL_LANG']['FMD']['isoProductLister']			= array('Isotope Product Lister', 'General Listing module.  Can be used to list products or values of attributes. May be combined with other modules (i.e. the Filter Module) to provide further drill-down capabilities.');
$GLOBALS['TL_LANG']['FMD']['isoProductReader']			= array('Isotope Product Reader', 'Product reader module.  This is used to display product details.');
$GLOBALS['TL_LANG']['FMD']['isoShoppingCart']			= array('Isotope Shopping Cart', 'A fully-featured shopping cart module.  Box or Full Display can be set by template selection.');
$GLOBALS['TL_LANG']['FMD']['isoCheckout']				= array('Isotope Checkout Module', 'Allow store customers to complete their transactions.');
$GLOBALS['TL_LANG']['FMD']['isoGiftRegistryManager']	= array('Isotope Gift Registry Manager', 'A fully-featured gift registry module.  Allows users to create and manage their own gift registries.');
$GLOBALS['TL_LANG']['FMD']['isoGiftRegistrySearch']		= array('Isotope Gift Registry Search', 'Allow store customers to search for existing Gift Registries');
$GLOBALS['TL_LANG']['FMD']['isoGiftRegistryResults']	= array('Isotope Gift Registry Search Results', 'Allow store customers to search for existing Gift Registries');
$GLOBALS['TL_LANG']['FMD']['isoGiftRegistryReader']		= array('Isotope Gift Registry Reader', 'Allow store customers to view existing Gift Registries');
$GLOBALS['TL_LANG']['FMD']['isoGiftRegistry']			= array('Isotope Gift Registry','Allow customers to set up, manage or shop from already created Gift Registries.');
$GLOBALS['TL_LANG']['FMD']['isoAddressBook']			= array('Isotope Address Book','Allow customers to manage their address book.');
$GLOBALS['TL_LANG']['FMD']['isoOrderHistory']			= array('Order History', 'Order lister that allows customers to view their order history');
$GLOBALS['TL_LANG']['FMD']['isoOrderDetails']			= array('Order Details', 'Order reader that allows customers to view order history details');
$GLOBALS['TL_LANG']['FMD']['isoStoreSwitcher']			= array('Store Switcher', 'Switch between store configuration to change currency and other settings.');
$GLOBALS['TL_LANG']['FMD']['isoDonationsModule']		= array('Donations', 'Adds a special form to allow donations to be made.');


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
$GLOBALS['TL_LANG']['IMD']['iso_mail']					= array('E-Mail manager','Customize Admin and Customer Notification Emails.');
$GLOBALS['TL_LANG']['IMD']['store_configuration']		= array('Store configuration','Configure general settings for this store.');

