<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */


/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['iso_products']				= array('Product management','');
$GLOBALS['TL_LANG']['MOD']['iso_orders']				= array('Orders', '');
$GLOBALS['TL_LANG']['MOD']['iso_setup']					= array('Store configuration','');


/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['isotope']					= 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']			= array('Product Filter', 'Define individual filters for Isotope such as category trees and product attribute filters.');
$GLOBALS['TL_LANG']['FMD']['iso_cumulativefilter']		= array('Cumulative Filter', 'Provides a cumulative filter so visitors can cut down the product choice by clicking on multiple conditions.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']			= array('Product List', 'General Listing module. Can be used to list products or values of attributes. May be combined with other modules (i.e. the Filter Module) to provide further drill-down capabilities.');
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist']	= array('Product Variant List', 'Lists each variant of a product. Make sure you use the iso_list_variants template.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']			= array('Product Reader', 'Product reader module. This is used to display product details.');
$GLOBALS['TL_LANG']['FMD']['iso_cart']					= array('Shopping Cart', 'A fully-featured shopping cart module.  Box or Full Display can be set by template selection.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']				= array('Checkout', 'Allow store customers to complete their transactions.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']			= array('Address Book','Allow customers to manage their address book.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']			= array('Order History', 'Order lister that allows customers to view their order history');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']			= array('Order Details', 'Order reader that allows customers to view order history details');
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher']		= array('Store Config Switcher', 'Switch between store configuration to change currency and other settings.');
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts']		= array('Related products', 'List products related to the current one.');


/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['ISO']['config_module']				= 'Isotope eCommerce configuration (Version: %s)';
$GLOBALS['TL_LANG']['IMD']['product']					= 'Products';
$GLOBALS['TL_LANG']['IMD']['attributes']				= array('Attributes', 'Manage and create product attributes such as size, color, etc.');
$GLOBALS['TL_LANG']['IMD']['producttypes']				= array('Product types', 'Manage and create product types from sets of attributes.');
$GLOBALS['TL_LANG']['IMD']['related_categories']		= array('Related categories', 'Define categories for product relations.');
$GLOBALS['TL_LANG']['IMD']['checkout']					= 'Checkout Flow';
$GLOBALS['TL_LANG']['IMD']['shipping']					= array('Shipping methods','Set up Shipping Methods such as UPS, USPS, DHL, etc.');
$GLOBALS['TL_LANG']['IMD']['payment']					= array('Payment methods','Set up Payment Methods such as Authorize.net, PayPal Pro, and more.');
$GLOBALS['TL_LANG']['IMD']['tax_class']					= array('Tax classes','Set up Tax classes, which contain sets of Tax rates.');
$GLOBALS['TL_LANG']['IMD']['tax_rate']					= array('Tax rates','Set up tax rates based on things like shipping/billing location and order total.');
$GLOBALS['TL_LANG']['IMD']['config']					= 'General settings';
$GLOBALS['TL_LANG']['IMD']['orderstatus']				= array('Order status', 'Define order status.');
$GLOBALS['TL_LANG']['IMD']['baseprice']					= array('Base prices', 'Define base price.');
$GLOBALS['TL_LANG']['IMD']['iso_mail']					= array('E-Mail manager', 'Customize Admin and Customer Notification Emails.');
$GLOBALS['TL_LANG']['IMD']['configs']					= array('Store configurations', 'Configure general settings for this store.');
