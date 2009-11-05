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
$GLOBALS['TL_LANG']['tl_store']['store_configuration_name']			= array('Store Configuration Name', '');
$GLOBALS['TL_LANG']['tl_store']['label']							= array('Label', 'The label is used in frontend, e.g. for store switcher.');
$GLOBALS['TL_LANG']['tl_store']['cookie_duration']					= array('Duration (in days) of Shopping Cart Information', 'Items and their related information will be retrievable for this many days for non-registered members.  A value of 0 means after the session, the items are removed. For registered members cart data is always remembered.');
$GLOBALS['TL_LANG']['tl_store']['checkout_login_module']			= array('Checkout Login Module','Select a login module to insert into the checkout module.');
$GLOBALS['TL_LANG']['tl_store']['root_asset_import_path']			= array('Root Asset Import Path','A single directory which contains all "to-be-imported" images and media associated with a given record.  Isotope will look for a corresponding file in this folder instead of in the product\'s standard source folder.');
$GLOBALS['TL_LANG']['tl_module']['productReaderJumpTo']				= array('Product Reader Jump to page', 'This setting defines to which page a user will be redirected when clicking a product for more info.');
$GLOBALS['TL_LANG']['tl_module']['cartJumpTo']						= array('Shopping Cart Jump to page', 'This setting defines to which page a user will be redirected when requesting a full shopping cart view.');
$GLOBALS['TL_LANG']['tl_module']['checkoutJumpTo']					= array('Checkout Jump to page', 'This setting defines to which page a user will be redirected when completing their transaction.');
$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder']		= array('Missing Image Placeholder', 'This image will be used if an image file cannot be found or none are associated with a product.');
$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_width']			= array('Thumbnail Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_height']			= array('Thumbnail Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['medium_image_width']				= array('Medium Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['medium_image_height']				= array('Medium Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['large_image_width']				= array('Large Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['large_image_height']				= array('Large Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['defaultPriceField']				= array('Default Price Field','Select a field that is the default price field for this store.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateFactor']				= array('Price factor', 'Defaults should be 1. You can use this to convert between multiple currencies.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateMode']				= array('Calculation mode', 'Divide or multiply using this factor.');
$GLOBALS['TL_LANG']['tl_store']['currency']							= array('Currency', 'Please select a currency for this store.');
$GLOBALS['TL_LANG']['tl_store']['currencySymbol']					= array('Use currency symbol', 'Use a currency symbol ($, €) if available.');
$GLOBALS['TL_LANG']['tl_store']['currencyPosition']					= array('Position of currency code/symbol', 'Select if you want to show currency on the left or right side of the price.');
$GLOBALS['TL_LANG']['tl_store']['currencyFormat']					= array('Currency formatting', 'Choose a formatting for prices.');
$GLOBALS['TL_LANG']['tl_store']['currencyRoundPrecision']			= array('Rounding precision', 'How much decimal precicion to have. Only applies if price multiplier is not 1. See PHP manual for round().');
$GLOBALS['TL_LANG']['tl_store']['currencyRoundIncrement']			= array('Rounding increment', 'Some currencies (eg. swiss francs) do not support 0.01 precicion.');
$GLOBALS['TL_LANG']['tl_store']['country']							= array('Default Country','What is the default country for this store configuration?');
$GLOBALS['TL_LANG']['tl_store']['countries']						= array('Countries', 'Select the countries you want to allow for checkout.');
$GLOBALS['TL_LANG']['tl_store']['address_fields']					= array('Address fields', 'Select the fields for a new address when checking out.');
$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_width']	= array('Gallery Thumbnail Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_height']	= array('Gallery Thumbnail Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['invoiceLogo']						= array('Invoice Logo','Select a logo to show up on the invoices for this store.');
$GLOBALS['TL_LANG']['tl_store']['isDefaultStore']					= array('Set as default store','Set this as the default store for back-end currency formatting and other locale-specific information.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_store']['left']								= 'To the left of price';
$GLOBALS['TL_LANG']['tl_store']['right']							= 'To the right of price';
$GLOBALS['TL_LANG']['tl_store']['div']								= 'Divide';
$GLOBALS['TL_LANG']['tl_store']['mul']								= 'Multiply';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_store']['new']    							= array('New Store', 'Create a New Store.');
$GLOBALS['TL_LANG']['tl_store']['edit']   							= array('Edit Store', 'Edit Store ID %s.');
$GLOBALS['TL_LANG']['tl_store']['copy']   							= array('Copy Store', 'Copy Store ID %s.');
$GLOBALS['TL_LANG']['tl_store']['delete'] 							= array('Delete Store', 'Delete Store ID %s.  This will not delete related assets but rather the initial store configuration.');
$GLOBALS['TL_LANG']['tl_store']['show']   							= array('Show Store Details', 'Show details for store ID %s.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_store']['name_legend']						= 'Name';
$GLOBALS['TL_LANG']['tl_store']['config_legend']					= 'Configuration';
$GLOBALS['TL_LANG']['tl_store']['module_legend']	    			= 'Modules';
$GLOBALS['TL_LANG']['tl_store']['currency_legend']	    			= 'Pricing & Currency formatting';
$GLOBALS['TL_LANG']['tl_store']['address_legend']	    			= 'Billing and shipping address';
$GLOBALS['TL_LANG']['tl_store']['redirect_legend']	    			= 'Target pages';
$GLOBALS['TL_LANG']['tl_store']['invoice_legend']	    			= 'Invoice';
$GLOBALS['TL_LANG']['tl_store']['images_legend']	    			= 'Images';

