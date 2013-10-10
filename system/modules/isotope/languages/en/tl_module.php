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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Blair Winans <blair@winanscreative.com>
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['iso_list_layout']                 = array('Product list template', 'Please choose a list layout. You can add custom list layouts to folder <em>templates</em>. List template files start with <em>iso_list_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout']               = array('Product reader template', 'Please choose a reader template.  You can add custom reader templates to folder <em>templates</em>. Reader template files start with <em>iso_reader_</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_gallery']                     = array('Gallery', 'Select a gallery to render images (overrides the product type config).');
$GLOBALS['TL_LANG']['tl_module']['iso_collectionTpl']               = array('Product collection template', 'Please choose a template to render the products. You can add custom collection templates to folder <em>templates</em>. Collection template files start with <em>iso_collection_</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl']                   = array('Filter template', 'Please choose a filter template. You can add custom filter templates to folder <em>templates</em>. Filter template files start with <em>iso_filter_</em>.');
$GLOBALS['TL_LANG']['tl_module']['iso_jump_first']                  = array('Redirect to first product', 'Check here if users are redirected to the first product of the list.');
$GLOBALS['TL_LANG']['tl_module']['iso_hide_list']                   = array('Hide in reader mode', 'Hide product list when a product alias is found in the URL.');
$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity']                = array('Enable quantity', 'Allow users to specify 1 or more of a product to be purchased.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method']             = array('Checkout method', 'Choose your checkout method.');
$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo']                = array('Checkout login page', 'Select the page where a user should login to checkout.');
$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo']            = array('Add product jump to page', 'This setting defines to which page a user will be redirected when adding a product to the cart, if other than the current page.');
$GLOBALS['TL_LANG']['tl_module']['iso_cols']                        = array('Columns', 'Enter a number of columns to display width-wise in the listing template.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_id']                   = array('Store configuration', 'Select the store configuration that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['iso_config_ids']                  = array('Store configurations', 'Select the store configurations that this module will be used for.');
$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules']             = array('Payment methods', 'Select one or more payment methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules']            = array('Shipping methods', 'Select one or more shipping methods for this checkout module.');
$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo']             = array('Completed order jump to page', 'Select a page the customer will be referred to after their order is complete.');
$GLOBALS['TL_LANG']['tl_module']['iso_forward_review']              = array('Forward to review page', 'Forward the user to the review page if no data is required on any step.');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer']               = array('Customer notification email', 'Select the Iotope Email used to send to customers when they place an order');
$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin']                  = array('Sales admin notification email', 'Select the Iotope Email used to send to a sales admin when a customers places an order');
$GLOBALS['TL_LANG']['tl_module']['iso_sales_email']                 = array('Sales admin email address', 'Enter an email address other than the default System Admin for store notifications to be sent to.');
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions']            = array('Order conditions form', 'Choose a custom form that is used to display your order terms and conditions (optional).');
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']   = array('Position of order conditions form', 'Define if the order condition form should be shown before or after the products list.');
$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook']            = array('Add to address book', 'Add new addresses to members address book (if logged in).');
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']           = array('Sorting', 'Define in what order the collection items should be listed.');
$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage']                = array('Define empty message', 'Set a custom message when there is nothing to show (empty product list, empty cart, etc.).');
$GLOBALS['TL_LANG']['tl_module']['iso_noProducts']                  = array('Message when empty', 'Enter a custom message if there is nothing to show (empty product list, empty cart, etc.).');
$GLOBALS['TL_LANG']['tl_module']['iso_emptyFilter']                 = array('Define a message if no filter is set', 'Set a custom message when there is no filter set.');
$GLOBALS['TL_LANG']['tl_module']['iso_noFilter']                    = array('Message when no filter is set', 'Enter a custom message if there is no filter set.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope']              = array('Category Scope', 'Specify the scope of a product lister.');
$GLOBALS['TL_LANG']['tl_module']['iso_list_where']                  = array('Condition', 'Here you can enter a SQL condition to filter the products. You must prefix the fields with "p1." (e.g. <em>p1.featured=1</em> or <em>p1.color!=\'red\'</em>)!');
$GLOBALS['TL_LANG']['tl_module']['iso_filterModules']               = array('Filter modules', 'Select the filter modules you want to consider for this product list.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterFields']                = array('Enabled filters', 'Please select filters to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_filterHideSingle']            = array('Hide single options', 'Hide filter field if there is just one option.');
$GLOBALS['TL_LANG']['tl_module']['iso_searchFields']                = array('Enabled search fields', 'Please select search fields to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_searchAutocomplete']          = array('Autocomplete search field', 'If you select a field here, the search will be autocompleted by the values of that field.');
$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields']               = array('Enabled sorting fields', 'Please select orderable fields to enable.');
$GLOBALS['TL_LANG']['tl_module']['iso_newFilter']                   = array('Filtering for new products', 'If you have configured marking products as "new" in your shop configuration, you can either filter for old ones, new ones or just display all products.');
$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit']                 = array('Enable per-page limiting', 'Allow the user to select the number of records to show per page.');
$GLOBALS['TL_LANG']['tl_module']['iso_perPage']                     = array('Per page options', 'Enter a comma separated list for the limit dropdown. The first option will be used as the default value. Values will automatically sort by number.');
$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo']                 = array('Shopping Cart Jump to page', 'This setting defines to which page a user will be redirected when requesting a full shopping cart view.');
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo']             = array('Checkout Jump to page', 'This setting defines to which page a user will be redirected when completing their transaction.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField']            = array('Initial sorting field', 'Select a sorting field to sort the listing by on first page load.');
$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection']        = array('Initial sorting direction', 'Select a initial sorting direction.');
$GLOBALS['TL_LANG']['tl_module']['iso_buttons']                     = array('Buttons', 'Select the buttons you want to show.');
$GLOBALS['TL_LANG']['tl_module']['iso_related_categories']          = array('Related categories', 'Select the categories to show products of.');
$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages']             = array('Include messaging', 'This setting allows the module to include any errors, notifications, or confirmations the visitor should be aware of.');
$GLOBALS['TL_LANG']['tl_module']['iso_continueShopping']            = array('Enable "Continue shopping" button', 'Add a link to the currently added product to the cart.');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['member']                   = 'Login/Registration required';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['guest']                    = 'Guest checkout only';
$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref']['both']                     = 'Both allowed';
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['global']                    = array('All Categories', 'Show all products that are assigned to a page of the active page tree (based on the root page).');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_first_child']   = array('Current Category and First Child Category', 'Show all products that are assigned to the active page or child pages on the first sublevel.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_and_all_children']  = array('Current Category and All Child Categories', 'Show all products that are assigned to the active page or any child page of it.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['current_category']          = array('Current Category', 'Show all products that are assigned to the active page (default).');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['parent']                    = array('Parent Category', 'Show all products that are assigned to the parent page of the active page.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['product']                   = array('Current Product\'s Categories', 'Show all products that are assigned to the same pages the currently active product is assigned to.');
$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref']['article']                   = array('Category of article', 'If you place the module in an article, it will show products assigned to the artilce\'s partent page, even though if you place the article in another page (e.g. using insert tag).');
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['ASC']                             = "ASC";
$GLOBALS['TL_LANG']['tl_module']['sortingDirection']['DESC']                            = "DESC";
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']['top']                = 'On top (before address)';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']['before']             = 'Before products list';
$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions_position']['after']              = 'After products list';
$GLOBALS['TL_LANG']['tl_module']['iso_newFilter']['show_all']                           = 'Show all products';
$GLOBALS['TL_LANG']['tl_module']['iso_newFilter']['show_new']                           = 'Only show new products';
$GLOBALS['TL_LANG']['tl_module']['iso_newFilter']['show_old']                           = 'Only show old products';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['asc_id']                     = 'by date added (ascending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['desc_id']                    = 'by date added (descending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['asc_tstamp']                 = 'by date updated (ascending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['desc_tstamp']                = 'by date updated (descending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['asc_name']                   = 'by product name (ascending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['desc_name']                  = 'by product name (descending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['asc_price']                  = 'by price (ascending)';
$GLOBALS['TL_LANG']['tl_module']['iso_orderCollectionBy']['desc_price']                 = 'by price (descending)';
