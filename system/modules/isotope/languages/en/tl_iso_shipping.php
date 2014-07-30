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
$GLOBALS['TL_LANG']['tl_iso_shipping']['name']              = array('Shipping Method Name', 'Please enter a name for this shipping method.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['type']              = array('Shipping Method Type', 'Please select the type of this shipping method');
$GLOBALS['TL_LANG']['tl_iso_shipping']['price']             = array('Price', 'Optionally enter a price for this shipping method.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['note']              = array('Shipping Method Notes', 'These will be displayed on the front end in association with this shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['tax_class']         = array('Tax Class', 'Select a tax class that applies to the shipping price.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['label']             = array('Label', 'This is displayed on the front end in association with the shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['flatCalculation']   = array('Flat calculation', 'Select the mode of price calculation.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['countries']         = array('Countries', 'Select the countries this shipping method applies to. If you don\'t select anything, the shipping method will be applied to all countries.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['subdivisions']      = array('State/Regions', 'Select the states/regions this shipping method applies to. If you don\'t select anything, the shipping method will be applied to all states/regions.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['postalCodes']       = array('Postal codes', 'Limit this shipping module to postal codes. You can enter a comma separated list and ranges (e.g. 1234,1235,1236-1239,1100-1200).');
$GLOBALS['TL_LANG']['tl_iso_shipping']['minimum_total']     = array('Minimum subtotal', 'Enter a minimum amount to control availability based on the cart subtotal.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['maximum_total']     = array('Maximum subtotal', 'Enter a maximum amount to control availability based on the cart subtotal.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['minimum_weight']    = array('Minimum weight', 'Enter a minimum weight to control availability based on the products in cart.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['maximum_weight']    = array('Maximum weight', 'Enter a maximum weight to control availability based on the products in cart.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['product_types']     = array('Product types', 'You can restrict this shipping method to certain product types. If the cart contains a product type you have not selected, the shipping module is not available.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['config_ids']        = array('Store configurations', 'You can restrict this shipping method to certain shop configurations.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['group_methods']     = array('Shipping methods', 'Select the shipping methods to group in this method.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['group_calculation'] = array('Group calculation', 'Select how the price of this shipping method should be calculated.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['groups']            = array('Member Groups','Restrict this shipping option to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['protected']         = array('Protect module', 'Show the module to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['guests']            = array('Show to guests only', 'Hide the module if a member is logged in.');
$GLOBALS['TL_LANG']['tl_iso_shipping']['enabled']           = array('Enabled', 'Is the module available for use in the store?');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping']['title_legend']      = 'Title and type';
$GLOBALS['TL_LANG']['tl_iso_shipping']['note_legend']       = 'Shipping note';
$GLOBALS['TL_LANG']['tl_iso_shipping']['config_legend']     = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_shipping']['price_legend']      = 'Pricing threshold and tax class applicability';
$GLOBALS['TL_LANG']['tl_iso_shipping']['expert_legend']     = 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_shipping']['enabled_legend']    = 'Approval';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping']['new']               = array('New shipping method', 'Create a New shipping method');
$GLOBALS['TL_LANG']['tl_iso_shipping']['edit']              = array('Edit shipping method', 'Edit shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping']['copy']              = array('Copy shipping method', 'Copy shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping']['delete']            = array('Delete shipping method', 'Delete shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping']['toggle']            = array('Enable/disable shipping method', 'Enable/disable shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping']['show']              = array('Shipping method details', 'Show details of shipping method ID %s');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping']['flat']              = 'Flat';
$GLOBALS['TL_LANG']['tl_iso_shipping']['perProduct']        = 'Per Product';
$GLOBALS['TL_LANG']['tl_iso_shipping']['perItem']           = 'Per Item';
$GLOBALS['TL_LANG']['tl_iso_shipping']['first']             = 'Price of the first available method';
$GLOBALS['TL_LANG']['tl_iso_shipping']['lowest']            = 'Lowest price of available methods';
$GLOBALS['TL_LANG']['tl_iso_shipping']['highest']           = 'Highest price of available methods';
$GLOBALS['TL_LANG']['tl_iso_shipping']['summarize']         = 'Summed price of available methods';
