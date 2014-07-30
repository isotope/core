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
 * Backend Modules
 */
$GLOBALS['TL_LANG']['MOD']['isotope']                   = 'Isotope eCommerce';
$GLOBALS['TL_LANG']['MOD']['iso_products']              = array('Product management', 'Manage products for your Isotope eCommerce shop');
$GLOBALS['TL_LANG']['MOD']['iso_orders']                = array('Orders', 'See and manage orders for your shop');
$GLOBALS['TL_LANG']['MOD']['iso_setup']                 = array('Store configuration', 'Setup and configure Isotope eCommerce to your needs');

/**
 * Frontend modules
 */
$GLOBALS['TL_LANG']['FMD']['isotope']                   = 'Isotope eCommerce';
$GLOBALS['TL_LANG']['FMD']['iso_productfilter']         = array('Product Filter', 'Define individual filters for Isotope such as category trees and product attribute filters.');
$GLOBALS['TL_LANG']['FMD']['iso_cumulativefilter']      = array('Cumulative Filter', 'Provides a cumulative filter so visitors can cut down the product choice by clicking on multiple conditions.');
$GLOBALS['TL_LANG']['FMD']['iso_productlist']           = array('Product List', 'General Listing module. Can be used to list products or values of attributes. May be combined with other modules (i.e. the Filter Module) to provide further drill-down capabilities.');
$GLOBALS['TL_LANG']['FMD']['iso_productvariantlist']    = array('Product Variant List', 'Lists each variant of a product. Make sure you use the iso_list_variants template.');
$GLOBALS['TL_LANG']['FMD']['iso_productreader']         = array('Product Reader', 'Product reader module. This is used to display product details.');
$GLOBALS['TL_LANG']['FMD']['iso_cart']                  = array('Shopping Cart', 'A fully-featured shopping cart module.  Box or Full Display can be set by template selection.');
$GLOBALS['TL_LANG']['FMD']['iso_checkout']              = array('Checkout', 'Allow store customers to complete their transactions.');
$GLOBALS['TL_LANG']['FMD']['iso_addressbook']           = array('Address Book','Allow customers to manage their address book.');
$GLOBALS['TL_LANG']['FMD']['iso_orderhistory']          = array('Order History', 'Order lister that allows customers to view their order history');
$GLOBALS['TL_LANG']['FMD']['iso_orderdetails']          = array('Order Details', 'Order reader that allows customers to view order history details');
$GLOBALS['TL_LANG']['FMD']['iso_configswitcher']        = array('Store Config Switcher', 'Switch between store configuration to change currency and other settings.');
$GLOBALS['TL_LANG']['FMD']['iso_relatedproducts']       = array('Related products', 'List products related to the current one.');
$GLOBALS['TL_LANG']['FMD']['iso_messages']              = array('Messages', 'Displays all Isotope messages if they have not been displayed elsewhere.');

/**
 * Isotope Modules
 */
$GLOBALS['TL_LANG']['IMD']['config_module']             = 'Isotope eCommerce configuration (Version: %s)';
$GLOBALS['TL_LANG']['IMD']['product']                   = 'Products';
$GLOBALS['TL_LANG']['IMD']['attributes']                = array('Attributes', 'Manage and create product attributes such as size, color, etc.');
$GLOBALS['TL_LANG']['IMD']['producttypes']              = array('Product types', 'Manage and create product types from sets of attributes.');
$GLOBALS['TL_LANG']['IMD']['related_categories']        = array('Related categories', 'Define categories for product relations.');
$GLOBALS['TL_LANG']['IMD']['gallery']                   = array('Galleries', 'Define how you want the images in your product to be rendered.');
$GLOBALS['TL_LANG']['IMD']['baseprice']                 = array('Base prices', 'Define base price.');
$GLOBALS['TL_LANG']['IMD']['checkout']                  = 'Checkout Flow';
$GLOBALS['TL_LANG']['IMD']['shipping']                  = array('Shipping methods','Set up shipping methods.');
$GLOBALS['TL_LANG']['IMD']['payment']                   = array('Payment methods', 'Set up payment methods.');
$GLOBALS['TL_LANG']['IMD']['tax_class']                 = array('Tax classes','Set up Tax classes, which contain sets of Tax rates.');
$GLOBALS['TL_LANG']['IMD']['tax_rate']                  = array('Tax rates','Set up tax rates based on things like shipping/billing location and order total.');
$GLOBALS['TL_LANG']['IMD']['config']                    = 'General settings';
$GLOBALS['TL_LANG']['IMD']['configs']                   = array('Store configurations', 'Configure general settings for this store.');
$GLOBALS['TL_LANG']['IMD']['orderstatus']               = array('Order status', 'Define order status.');
$GLOBALS['TL_LANG']['IMD']['notifications']             = array('Notifications', 'Use the Notification Center to manage emails etc. for Isotope eCommerce.');
$GLOBALS['TL_LANG']['IMD']['documents']                 = array('Documents', 'Define documents.');
$GLOBALS['TL_LANG']['IMD']['miscellaneous']             = 'Miscellaneous';
$GLOBALS['TL_LANG']['IMD']['labels']                    = array('Translations', 'Replace given labels for certain languages.');
$GLOBALS['TL_LANG']['IMD']['integrity']                 = array('Integrity Check', 'Validate your shop configuration against common errors.');
