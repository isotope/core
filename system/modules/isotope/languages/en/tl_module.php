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
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout']				= array('Product List Template', 'Please choose a list layout. You can add custom list layouts to folder <em>templates</em>. List template files start with <em>iso_list_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout']			= array('Product Reader Template', 'Please choose a reader template.  You can add custom reader templates to folder <em>templates</em>. Reader template files start with <em>iso_reader_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo']			= array('Product Reader Jump to page', 'This setting defines to which page a user will be redirected when clicking a product for more info.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout']				= array('Shopping Cart Template', 'Please choose a shopping cart layout. You can add custom cart layouts to folder <em>templates</em>. Cart template files start with <em>iso_cart_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_registry_layout']			= array('Gift Registry Manager Template', 'Please choose a gift registry layout. You can add custom layouts to folder <em>templates</em>. Registry template files start with <em>iso_registry_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_registry_results']		= array('Gift Registry Search Results Template', 'Please choose a gift registry search results layout. You can add custom layouts to folder <em>templates</em>. Registry template files start with <em>iso_registry_search</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_registry_reader']			= array('Gift Registry Reader Template', 'Please choose a registry reader layout. You can add custom layouts to folder <em>templates</em>. Registry template files start with <em>iso_registry_full</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['new_products_time_window']	= array('New Products Time Window','The number of days in which a recently added product is considered "new" for the New Products Filter');
$GLOBALS['TL_LANG']['tl_module']['listing_filters']				= array('Listing Filters', 'Select one or more filters to be included in the listing module area.');
$GLOBALS['TL_LANG']['tl_module']['columns']						= array('Columns','Enter a number of columns to display width-wise in the listing template.');
$GLOBALS['TL_LANG']['tl_module']['store_id']					= array('Store', 'Select the store that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['store_ids']					= array('Stores', 'Select the stores that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_layout']			= array('Checkout Template','Please choose a reader template.  You can add custom reader templates to folder <em>templates</em>. Reader template files start with <em>iso_mod_checkout_</em> and require file extension <em>.tpl</em>');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_login']			= array('Checkout Login Module','Select a login module to insert into the checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules']			= array('Payment Methods','Select one or more payment methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules']		= array('Shipping Methods','Select one or more shipping methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method']			= array('Checkout method','Choose your checkout method.');
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']		= array('Order Conditions Article','Choose an article that is used to display your order terms and conditions (optional).');
$GLOBALS['TL_LANG']['tl_module']['addressBookTemplate']			= array('Address Book Listing Template','Select a listing template.');
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo']			= array('Completed order jump to page','Select a page the customer will be referred to after their order is complete.');
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first']				= array('Redirect to first product', 'Check here if users are redirected to the first product of the list.');
$GLOBALS['TL_LANG']['tl_module']['iso_forward_cart']			= array('Forward to cart', 'Forward to cart page when adding a product.');
$GLOBALS['TL_LANG']['tl_module']['featured_products']			= array('Display only featured products?', 'Display featured products only.');
$GLOBALS['TL_LANG']['tl_module']['iso_show_teaser']				= array('Show Teaser','Should the teaser be shown in the listing?');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer']			= array('Customer Notification Email','Select the Iotope Email used to send to customers when they place an order');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin']				= array('Sales Admin Notification Email','Select the Iotope Email used to send to a sales admin when a customers places an order');
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email']				= array('Sales Admin Email Address','Enter an email address other than the default System Admin for store notifications to be sent to.');
$GLOBALS['TL_LANG']['tl_module']['iso_list_format']				= array('Listing Format','Please choose whether the products will be listed in grid or list format.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope']				= array('Category Scope','Specify the scope of a product lister.');
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'] 			= array('Enable quantity','Allow users to specify 1 or more of a product to be purchased.');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['member']	= 'Login/Registration required';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['guest']	= 'Guest checkout only';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['both']		= 'Both allowed';

$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['global']				= 'All Categories';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent_and_children']	= 'Current Category and Children';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_category']			= 'Current Category Only';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['featured_legend']  = 'Featured product settings';
$GLOBALS['TL_LANG']['tl_module']['config_legend']    = 'Configuration settings';
$GLOBALS['TL_LANG']['tl_module']['display_legend']    = 'Display settings';

