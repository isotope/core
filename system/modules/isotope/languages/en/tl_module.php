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
 * @author     Blair Winans <blair@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout']				= array('Product list template', 'Please choose a list layout. You can add custom list layouts to folder <em>templates</em>. List template files start with <em>iso_list_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout']			= array('Product reader template', 'Please choose a reader template.  You can add custom reader templates to folder <em>templates</em>. Reader template files start with <em>iso_reader_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo']			= array('Product reader jump to page', 'This setting defines to which page a user will be redirected when clicking a product for more info.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout']				= array('Shopping cart template', 'Please choose a shopping cart layout. You can add custom cart layouts to folder <em>templates</em>. Cart template files start with <em>iso_cart_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['new_products_time_window']	= array('New products time window','The number of days in which a recently added product is considered "new" for the New Products Filter');
$GLOBALS['TL_LANG']['tl_module']['listing_filters']				= array('Listing filters', 'Select one or more filters to be included in the listing module area.');
$GLOBALS['TL_LANG']['tl_module']['columns']						= array('Columns','Enter a number of columns to display width-wise in the listing template.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_id']				= array('Store configuration', 'Select the store configuration that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_ids']				= array('Store configurations', 'Select the store configurations that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo']			= array('Checkout login page', 'Select the page where a user should login to checkout.');
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules']			= array('Payment methods','Select one or more payment methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules']		= array('Shipping methods','Select one or more shipping methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method']			= array('Checkout method','Choose your checkout method.');
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']		= array('Order conditions form','Choose a custom form that is used to display your order terms and conditions (optional).');
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo']			= array('Completed order jump to page','Select a page the customer will be referred to after their order is complete.');
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first']				= array('Redirect to first product', 'Check here if users are redirected to the first product of the list.');
$GLOBALS['TL_LANG']['tl_module']['iso_forward_cart']			= array('Forward to cart', 'Forward to cart page when adding a product.');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer']			= array('Customer notification email','Select the Iotope Email used to send to customers when they place an order');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin']				= array('Sales admin notification email','Select the Iotope Email used to send to a sales admin when a customers places an order');
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email']				= array('Sales admin email address','Enter an email address other than the default System Admin for store notifications to be sent to.');
$GLOBALS['TL_LANG']['tl_module']['iso_list_format']				= array('Listing format','Please choose whether the products will be listed in grid or list format.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope']			= array('Category Scope','Specify the scope of a product lister.');
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'] 			= array('Enable quantity','Allow users to specify 1 or more of a product to be purchased.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterFields']			= array('Enabled filters','Please select filters to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_orderByFields']			= array('Enabled order by fields','Please select orderable fields to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_searchFields']			= array('Enabled search fields','Please select search fields to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit']				= array('Enable per-page limiting','Allow the user to select the number of records to show per page.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingModule']			= array('Listing module','Please choose a listing module this filter module will work with.');
$GLOBALS['TL_LANG']['tl_module']['iso_enableSearch']			= array('Enable search','Please check this if you would like to enable product search.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo']				= array('Shopping Cart Jump to page', 'This setting defines to which page a user will be redirected when requesting a full shopping cart view.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo']			= array('Checkout Jump to page', 'This setting defines to which page a user will be redirected when completing their transaction.');
$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo']		= array('Add product jump to page', 'This setting defines to which page a user will be redirected when adding a product to the cart, if other than the current page.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'] 		= array('Initial sorting field','Select a sorting field to sort the listing by on first page load.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'] 	= array('Initial sorting direction','Select a initial sorting direction.');
$GLOBALS['TL_LANG']['tl_module']['iso_buttons']					= array('Buttons', 'Select the buttons you want to show.');
$GLOBALS['TL_LANG']['tl_module']['iso_forward_review']			= array('Forward to review page', 'Forward the user to the review page if no data is required on any step.');
$GLOBALS['TL_LANG']['tl_module']['iso_related_categories']		= array('Related categories', 'Select the categories to show products of.');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['member']	= 'Login/Registration required';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['guest']	= 'Guest checkout only';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['both']		= 'Both allowed';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['global']				= 'All Categories';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent_and_first_child'] = 'Current Category and First Child Category';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent_and_all_children']	= 'Current Category and All Child Categories';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_category']			= 'Current Category Only';

$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['ASC'] 	= "ASC";
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['DESC'] 	= "DESC";


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_module']['featured_legend']  = 'Featured product settings';
$GLOBALS['TL_LANG']['tl_module']['config_legend']    = 'Configuration settings';
$GLOBALS['TL_LANG']['tl_module']['display_legend']    = 'Display settings';

