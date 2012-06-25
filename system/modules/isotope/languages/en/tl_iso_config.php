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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name']							= array('Configuration Name', '');
$GLOBALS['TL_LANG']['tl_iso_config']['label']							= array('Label', 'The label is used in frontend, e.g. for config switcher.');
$GLOBALS['TL_LANG']['tl_iso_config']['fallback']						= array('Set as default store', 'Set this as the default store for back-end currency formatting and other locale-specific information.');
$GLOBALS['TL_LANG']['tl_iso_config']['store_id']						= array('Store ID', 'Use different store IDs to group a set of store configurations. A user\'s cart and addresses will be shared across the same store IDs.');
$GLOBALS['TL_LANG']['tl_iso_config']['missing_image_placeholder']		= array('Missing Image Placeholder', 'This image will be used if an image file cannot be found or none are associated with a product.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor']			= array('Price factor', 'Defaults should be 1. You can use this to convert between multiple currencies.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode']				= array('Calculation mode', 'Divide or multiply using this factor.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision']				= array('Rounding precision', 'How much decimal precicion to have. You should set something between 0 and 2 for all payment gateways to work correctly. See PHP manual for round().');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement']				= array('Rounding increment', 'Some currencies (eg. swiss francs) do not support 0.01 precision.');
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal']					= array('Minimum subtotal', 'The minimum cart subtotal required to order. Enter 0 to disable.');
$GLOBALS['TL_LANG']['tl_iso_config']['currency']						= array('Currency', 'Please select a currency for this store.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol']					= array('Use currency symbol', 'Use a currency symbol ($, €) if available.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace']					= array('Include blank space', 'Add space between price and currency symbol.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition']				= array('Position of currency code/symbol', 'Select if you want to show currency on the left or right side of the price.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat']					= array('Currency formatting', 'Choose a formatting for prices.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator']				= array('Automatic currency conversion', 'Enable automatic update of price calcuation factors by using an online currency conversion provider.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin']					= array('Currency origin', 'Please select the source currency for calculation.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider']				= array('Currency Data Provider', 'Select an available online provider of currency calculation data.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new']					= array('Status for new orders', 'Choose a matching status for new orders. A payment module can override this.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error']				= array('Order status on error', 'Select an order status if something goes wrong (e.g. payment).');
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo']						= array('Invoice Logo', 'Select a logo to show up on the invoices for this store.');
$GLOBALS['TL_LANG']['tl_iso_config']['firstname']   					= array('First name', 'Please enter the first name (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['lastname']    					= array('Last name', 'Please enter the last name (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['company']     					= array('Company', 'You can enter a company name here (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['street_1']      					= array('Street', 'Please enter the street name and the street number.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_2']						= array('Street 2', 'Enter a second street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_3']						= array('Street 3', 'Enter a third street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_iso_config']['postal']      					= array('Postal code', 'Please enter the postal code.');
$GLOBALS['TL_LANG']['tl_iso_config']['city']       						= array('City', 'Plase enter the name of the city.');
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision']       				= array('State', 'Plase enter the name of the state.');
$GLOBALS['TL_LANG']['tl_iso_config']['country']     					= array('Country', 'Please select a country. This is also the default for custom shipping/billing addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['phone']       					= array('Phone number', 'Please enter the phone number.');
$GLOBALS['TL_LANG']['tl_iso_config']['email'] 							= array('Shipping E-mail address', 'Please enter a valid e-mail address.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries']				= array('Shipping countries', 'Select the countries you want to allow for checkout shipping address.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_fields']					= array('Shipping address fields', 'Select the fields for a new shipping address when checking out.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country']				= array('Shipping default country', 'Select a default country for new shipping addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries']				= array('Billing countries', 'Select the countries you want to allow for checkout billing address.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_fields']					= array('Billing address fields', 'Select the fields for a new billing address when checking out.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_country']					= array('Billing default country', 'Select a default country for new billing addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix']						= array('Order number prefix', 'You can add a prefix (eg. fiscal year) to the auto-incrementing order number.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits']						= array('Order number length', 'Select the minimum length of the order number (not including order prefix).');
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup']					= array('Templates folder', 'Here you can select a templates folder to search before every other template folder.');
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries']			= array('Limit member countries', 'Limit member countries (Registration, Personal Data) to the combined list of billing and shipping countries.');
$GLOBALS['TL_LANG']['tl_iso_config']['gallery']							= array('Product image gallery', 'Different image galleries can be developed to present media files in a custom style.');
$GLOBALS['TL_LANG']['tl_iso_config']['imageSizes']						= array('Image sizes', 'You can create custom image sizes for use in your templates. The default sizes are "gallery", "thumbnail", "medium" and "large".');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_config']['left']							= 'To the left of price';
$GLOBALS['TL_LANG']['tl_iso_config']['right']							= 'To the right of price';
$GLOBALS['TL_LANG']['tl_iso_config']['div']								= 'Divide';
$GLOBALS['TL_LANG']['tl_iso_config']['mul']								= 'Multiply';

$GLOBALS['TL_LANG']['tl_iso_config']['tl']								= 'Top Left';
$GLOBALS['TL_LANG']['tl_iso_config']['tc']								= 'Top';
$GLOBALS['TL_LANG']['tl_iso_config']['tr']								= 'Top Right';
$GLOBALS['TL_LANG']['tl_iso_config']['bl']								= 'Bottom Left';
$GLOBALS['TL_LANG']['tl_iso_config']['bc']								= 'Bottom';
$GLOBALS['TL_LANG']['tl_iso_config']['br']								= 'Bottom Right';
$GLOBALS['TL_LANG']['tl_iso_config']['cc']								= 'Center';

$GLOBALS['TL_LANG']['tl_iso_config']['iwName']							= 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWidth']							= 'Width';
$GLOBALS['TL_LANG']['tl_iso_config']['iwHeight']						= 'Height';
$GLOBALS['TL_LANG']['tl_iso_config']['iwMode']							= 'Mode';
$GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark']						= 'Watermark image';
$GLOBALS['TL_LANG']['tl_iso_config']['iwPosition']						= 'Position';

$GLOBALS['TL_LANG']['tl_iso_config']['fwEnabled']						= 'Enable field';
$GLOBALS['TL_LANG']['tl_iso_config']['fwLabel']							= 'Custom label';
$GLOBALS['TL_LANG']['tl_iso_config']['fwMandatory']						= 'Mandatory';

$GLOBALS['TL_LANG']['tl_iso_config']['ecb.int']							= 'European Central Bank';
$GLOBALS['TL_LANG']['tl_iso_config']['admin.ch']						= 'Swiss Federal Department of Finance';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_config']['new']    							= array('New configuration', 'Create a new store configuration.');
$GLOBALS['TL_LANG']['tl_iso_config']['edit']   							= array('Edit configuration', 'Edit store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['copy']   							= array('Copy configuration', 'Copy store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['delete'] 							= array('Delete configuration', 'Delete store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['show']   							= array('Show configuration details', 'Show details for store configuration ID %s.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend']						= 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend']	    			= 'Address configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend']					= 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['price_legend']	    			= 'Pricing';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend']	    			= 'Currency formatting';
$GLOBALS['TL_LANG']['tl_iso_config']['converter_legend']	    		= 'Currency conversion';
$GLOBALS['TL_LANG']['tl_iso_config']['order_legend']	    			= 'Orders';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend']	    			= 'Images';

