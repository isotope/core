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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name']                        = array('Configuration Name', 'Please enter a name for your configuration. This is only used for back end reference.');
$GLOBALS['TL_LANG']['tl_iso_config']['label']                       = array('Label', 'The label is used in frontend, e.g. for config switcher.');
$GLOBALS['TL_LANG']['tl_iso_config']['fallback']                    = array('Set as default store', 'Set this as the default store for back-end currency formatting and other locale-specific information.');
$GLOBALS['TL_LANG']['tl_iso_config']['firstname']                   = array('First name', 'Please enter the first name (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['lastname']                    = array('Last name', 'Please enter the last name (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['company']                     = array('Company', 'You can enter a company name here (if applicable).');
$GLOBALS['TL_LANG']['tl_iso_config']['vat_no']                      = array('VAT No.', 'Please enter a VAT number.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_1']                    = array('Street', 'Please enter the street name and the street number.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_2']                    = array('Street 2', 'Enter a second street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_iso_config']['street_3']                    = array('Street 3', 'Enter a third street info if there\'s any.');
$GLOBALS['TL_LANG']['tl_iso_config']['postal']                      = array('Postal code', 'Please enter the postal code.');
$GLOBALS['TL_LANG']['tl_iso_config']['city']                        = array('City', 'Please enter the name of the city.');
$GLOBALS['TL_LANG']['tl_iso_config']['subdivision']                 = array('State', 'Please enter the name of the state.');
$GLOBALS['TL_LANG']['tl_iso_config']['country']                     = array('Country', 'Please select a country. This is also the default for custom shipping/billing addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['phone']                       = array('Phone number', 'Please enter the phone number.');
$GLOBALS['TL_LANG']['tl_iso_config']['email']                       = array('Shipping email address', 'Please enter a valid e-mail address.');
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']              = array('Address fields', 'Select the fields for a billing and shipping address when checking out.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_country']             = array('Default billing country', 'Select a default country for new billing addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_country']            = array('Default shipping country', 'Select a default country for new shipping addresses.');
$GLOBALS['TL_LANG']['tl_iso_config']['billing_countries']           = array('Available billing countries', 'Select the countries you want to allow for checkout billing address. Select none to allow all countries.');
$GLOBALS['TL_LANG']['tl_iso_config']['shipping_countries']          = array('Available shipping countries', 'Select the countries you want to allow for checkout shipping address. Select none to allow all countries.');
$GLOBALS['TL_LANG']['tl_iso_config']['limitMemberCountries']        = array('Limit member countries', 'Limit member countries (Registration, Personal Data) to the combined list of billing and shipping countries.');
$GLOBALS['TL_LANG']['tl_iso_config']['vatNoValidators']             = array('VAT No. validation', 'If you enable one or multiple services, the VAT No. address field will be validated.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceDisplay']                = array('Price display', 'Select if you want to display net or gross prices in your shop. See the help wizard for more information.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyFormat']              = array('Price formatting', 'Choose a formatting for prices.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundPrecision']         = array('Rounding precision', 'How much decimal precision to have. You should set something between 0 and 2 for all payment gateways to work correctly. See PHP manual for round().');
$GLOBALS['TL_LANG']['tl_iso_config']['priceRoundIncrement']         = array('Rounding increment', 'Some currencies (e.g. swiss francs) do not support 0.01 precision.');
$GLOBALS['TL_LANG']['tl_iso_config']['currency']                    = array('Currency', 'Please select a currency for this store.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyPosition']            = array('Position of currency code/symbol', 'Select if you want to show currency on the left or right side of the price.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySymbol']              = array('Use currency symbol', 'Use a currency symbol ($, €) if available.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencySpace']               = array('Include blank space', 'Add space between price and currency symbol.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateFactor']        = array('Price factor', 'Defaults should be 1. You can use this to convert between multiple currencies.');
$GLOBALS['TL_LANG']['tl_iso_config']['priceCalculateMode']          = array('Calculation mode', 'Divide or multiply using this factor.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyAutomator']           = array('Automatic currency conversion', 'Enable automatic update of price calcuation factors by using an online currency conversion provider.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyOrigin']              = array('Currency origin', 'Please select the source currency for calculation.');
$GLOBALS['TL_LANG']['tl_iso_config']['currencyProvider']            = array('Currency Data Provider', 'Select an available online provider of currency calculation data.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderPrefix']                 = array('Order number prefix', 'You can add a prefix (e.g. fiscal year) to the auto-incrementing order number.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderDigits']                 = array('Order number length', 'Select the minimum length of the order number (not including order prefix).');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_new']             = array('Status for new orders', 'Choose a matching status for new orders. A payment module can override this.');
$GLOBALS['TL_LANG']['tl_iso_config']['orderstatus_error']           = array('Order status on error', 'Select an order status if something goes wrong (e.g. payment).');
$GLOBALS['TL_LANG']['tl_iso_config']['invoiceLogo']                 = array('Invoice Logo', 'Select a logo to show up on the invoices for this store.');
$GLOBALS['TL_LANG']['tl_iso_config']['templateGroup']               = array('Templates folder', 'Here you can select a templates folder to search before every other template folder.');
$GLOBALS['TL_LANG']['tl_iso_config']['cartMinSubtotal']             = array('Minimum subtotal', 'The minimum cart subtotal required to order. Enter 0 to disable.');
$GLOBALS['TL_LANG']['tl_iso_config']['newProductPeriod']            = array('Duration to mark a product as "new"', 'If you would like to highlight new products, you can enter a time period here. It will automatically add a CSS class "new" to every product everywhere within Isotope eCommerce.');
$GLOBALS['TL_LANG']['tl_iso_config']['ga_enable']						= array('Enable Google Analytics', 'Enable e-commerce tracking with Google Analytics.');
$GLOBALS['TL_LANG']['tl_iso_config']['ga_account']						= array('Account', 'Please provde the account key (UA-XXXXXXX-X).');
$GLOBALS['TL_LANG']['tl_iso_config']['ga_member']						= array('Track member', 'Enter simple tokens to track a member (e.g. ##firstname## ##lastname##). ATTENTION: make sure you\'re allowed to do this!');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_config']['left']                        = 'To the left of price';
$GLOBALS['TL_LANG']['tl_iso_config']['right']                       = 'To the right of price';
$GLOBALS['TL_LANG']['tl_iso_config']['div']                         = 'Divide';
$GLOBALS['TL_LANG']['tl_iso_config']['mul']                         = 'Multiply';
$GLOBALS['TL_LANG']['tl_iso_config']['iwName']                      = array('Name');
$GLOBALS['TL_LANG']['tl_iso_config']['iwWidth']                     = array('Width');
$GLOBALS['TL_LANG']['tl_iso_config']['iwHeight']                    = array('Height');
$GLOBALS['TL_LANG']['tl_iso_config']['iwMode']                      = array('Mode');
$GLOBALS['TL_LANG']['tl_iso_config']['iwWatermark']                 = array('Watermark image');
$GLOBALS['TL_LANG']['tl_iso_config']['iwPosition']                  = array('Position');
$GLOBALS['TL_LANG']['tl_iso_config']['ecb.int']                     = 'European Central Bank';
$GLOBALS['TL_LANG']['tl_iso_config']['admin.ch']                    = 'Swiss Federal Department of Finance';
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['name']      = array('Address field');
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['billing']   = array('For billing address');
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['shipping']  = array('For shipping address');
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['disabled']  = 'Disabled';
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['enabled']   = 'Enabled (not mandatory)';
$GLOBALS['TL_LANG']['tl_iso_config']['address_fields']['mandatory'] = 'Enabled (mandatory)';
$GLOBALS['TL_LANG']['tl_iso_config']['net']                         = array('Net', 'Show net prices to all customers. Taxes included in the product price (tax class configuration) will be deducted.');
$GLOBALS['TL_LANG']['tl_iso_config']['gross']                       = array('Gross (recommended)', 'Show gross prices to all customers. Applicable taxes will be added to the product price display.');
$GLOBALS['TL_LANG']['tl_iso_config']['fixed']                       = array('Fixed', 'The product price will always be shown as in the product setting. Taxes are always calculated as "included". This means the shop owner\'s profit varies depending on the applicable taxes.');
$GLOBALS['TL_LANG']['tl_iso_config']['legacy']                      = array('Legacy', 'Price display as in Isotope < 2.3. Prices are shown as gross prices if a tax is included and applicable. Additional taxes are only shown as surcharges.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_config']['new']                         = array('New configuration', 'Create a new store configuration.');
$GLOBALS['TL_LANG']['tl_iso_config']['edit']                        = array('Edit configuration', 'Edit store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['copy']                        = array('Copy configuration', 'Copy store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['delete']                      = array('Delete configuration', 'Delete store configuration ID %s.');
$GLOBALS['TL_LANG']['tl_iso_config']['show']                        = array('Show configuration details', 'Show details for store configuration ID %s.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_config']['name_legend']                 = 'Name';
$GLOBALS['TL_LANG']['tl_iso_config']['address_legend']              = 'Address configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['config_legend']               = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_config']['checkout_legend']             = 'Checkout';
$GLOBALS['TL_LANG']['tl_iso_config']['pricing_legend']              = 'Pricing';
$GLOBALS['TL_LANG']['tl_iso_config']['currency_legend']             = 'Currency formatting';
$GLOBALS['TL_LANG']['tl_iso_config']['converter_legend']            = 'Currency conversion';
$GLOBALS['TL_LANG']['tl_iso_config']['order_legend']                = 'Orders';
$GLOBALS['TL_LANG']['tl_iso_config']['images_legend']               = 'Images';
$GLOBALS['TL_LANG']['tl_iso_config']['url_legend']                  = 'URL settings';
$GLOBALS['TL_LANG']['tl_iso_config']['products_legend']             = 'Products';
$GLOBALS['TL_LANG']['tl_iso_config']['analytics_legend']            = 'Analytics';
