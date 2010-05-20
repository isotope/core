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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_store']['name']								= array('Store Configuration Name', '');
$GLOBALS['TL_LANG']['tl_store']['label']							= array('Label', 'The label is used in frontend, e.g. for store switcher.');
$GLOBALS['TL_LANG']['tl_store']['cookie_duration']					= array('Duration (in days) of Shopping Cart Information', 'Items and their related information will be retrievable for this many days for non-registered members.  A value of 0 means after the session, the items are removed. For registered members cart data is always remembered.');
$GLOBALS['TL_LANG']['tl_store']['orderPrefix']						= array('Order number prefix', 'You can add a prefix (eg. fiscal year) to the auto-incrementing order number.');
$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder']		= array('Missing Image Placeholder', 'This image will be used if an image file cannot be found or none are associated with a product.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateFactor']				= array('Price factor', 'Defaults should be 1. You can use this to convert between multiple currencies.');
$GLOBALS['TL_LANG']['tl_store']['priceCalculateMode']				= array('Calculation mode', 'Divide or multiply using this factor.');
$GLOBALS['TL_LANG']['tl_store']['priceRoundPrecision']				= array('Rounding precision', 'How much decimal precicion to have. Only applies if price multiplier is not 1. See PHP manual for round().');
$GLOBALS['TL_LANG']['tl_store']['priceRoundIncrement']				= array('Rounding increment', 'Some currencies (eg. swiss francs) do not support 0.01 precicion.');
$GLOBALS['TL_LANG']['tl_store']['currency']							= array('Currency', 'Please select a currency for this store.');
$GLOBALS['TL_LANG']['tl_store']['currencySymbol']					= array('Use currency symbol', 'Use a currency symbol ($, €) if available.');
$GLOBALS['TL_LANG']['tl_store']['currencyPosition']					= array('Position of currency code/symbol', 'Select if you want to show currency on the left or right side of the price.');
$GLOBALS['TL_LANG']['tl_store']['currencyFormat']					= array('Currency formatting', 'Choose a formatting for prices.');
$GLOBALS['TL_LANG']['tl_store']['invoiceLogo']						= array('Invoice Logo','Select a logo to show up on the invoices for this store.');
$GLOBALS['TL_LANG']['tl_store']['isDefaultStore']					= array('Set as default store','Set this as the default store for back-end currency formatting and other locale-specific information.');
$GLOBALS['TL_LANG']['tl_store']['firstname']   						= array('First name', 'Please enter the first name (if applicable).');
$GLOBALS['TL_LANG']['tl_store']['lastname']    						= array('Last name', 'Please enter the last name (if applicable).');
$GLOBALS['TL_LANG']['tl_store']['company']     						= array('Company', 'You can enter a company name here (if applicable).');
$GLOBALS['TL_LANG']['tl_store']['street_1']      					= array('Street', 'Please enter the street name and the street number.');
$GLOBALS['TL_LANG']['tl_store']['street_2']							= array('Street 2', 'Enter a second street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_store']['street_3']							= array('Street 3', 'Enter a third street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_store']['postal']      						= array('Postal code', 'Please enter the postal code.');
$GLOBALS['TL_LANG']['tl_store']['city']       						= array('City', 'Plase enter the name of the city.');
$GLOBALS['TL_LANG']['tl_store']['subdivision']       				= array('State', 'Plase enter the name of the state.');
$GLOBALS['TL_LANG']['tl_store']['country']     						= array('Country', 'Please select a country. This is also the default for custom shipping/billing addresses.');
$GLOBALS['TL_LANG']['tl_store']['phone']       						= array('Phone number', 'Please enter the phone number.');
$GLOBALS['TL_LANG']['tl_store']['emailShipping'] 					= array('Shipping E-mail address', 'Please enter a valid e-mail address.');
$GLOBALS['TL_LANG']['tl_store']['shipping_countries']				= array('Shipping countries', 'Select the countries you want to allow for checkout shipping address.');
$GLOBALS['TL_LANG']['tl_store']['shipping_fields']					= array('Shipping address fields', 'Select the fields for a new shipping address when checking out.');
$GLOBALS['TL_LANG']['tl_store']['billing_countries']				= array('Billing countries', 'Select the countries you want to allow for checkout billing address.');
$GLOBALS['TL_LANG']['tl_store']['billing_fields']					= array('Billing address fields', 'Select the fields for a new billing address when checking out.');
$GLOBALS['TL_LANG']['tl_store']['weightUnit']						= array('Weight unit of measurement','Specify the unit of measurement for weight (LBS or KGS)');
$GLOBALS['TL_LANG']['tl_store']['enableGoogleAnalytics']			= array('Enable Google Analytics e-commerce tracking','Add Google Analytics e-commerce tracking. Please note you will also have to enable e-commerce tracking in your Google Analytics account.');
$GLOBALS['TL_LANG']['tl_store']['gallery_size']						= array('Gallery image size', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['thumbnail_size']					= array('Thumbnail image size', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['medium_size']						= array('Medium image size', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['large_size']						= array('Large image size', 'In pixels.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_store']['left']								= 'To the left of price';
$GLOBALS['TL_LANG']['tl_store']['right']							= 'To the right of price';
$GLOBALS['TL_LANG']['tl_store']['div']								= 'Divide';
$GLOBALS['TL_LANG']['tl_store']['mul']								= 'Multiply';

$GLOBALS['TL_LANG']['tl_store']['weightUnits']['LBS']				= 'Pounds';
$GLOBALS['TL_LANG']['tl_store']['weightUnits']['KGS']				= 'Kilos';

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
$GLOBALS['TL_LANG']['tl_store']['address_legend']	    			= 'Address configuration';
$GLOBALS['TL_LANG']['tl_store']['config_legend']					= 'Configuration';
$GLOBALS['TL_LANG']['tl_store']['price_legend']	    				= 'Pricing';
$GLOBALS['TL_LANG']['tl_store']['currency_legend']	    			= 'Currency formatting';
$GLOBALS['TL_LANG']['tl_store']['invoice_legend']	    			= 'Invoice';
$GLOBALS['TL_LANG']['tl_store']['images_legend']	    			= 'Images';

