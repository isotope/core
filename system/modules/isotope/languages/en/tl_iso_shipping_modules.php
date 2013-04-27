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
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name']              = array('Shipping Method Name');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type']              = array('Shipping Method Type');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price']             = array('Price');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note']              = array('Shipping Method Notes', 'These will be displayed on the front end in association with this shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class']         = array('Tax Class');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label']             = array('Label', 'This is displayed on the front end in association with the shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation']   = array('Flat calculation');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries']         = array('Countries', 'Select the countries this shipping method applies to. If you don\'t select anything, the shipping method will be applied to all countries.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions']      = array('State/Regions', 'Select the states/regions this shipping method applies to. If you don\'t select anything, the shipping method will be applied to all states/regions.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['postalCodes']       = array('Postal codes', 'Limit this shipping module to postal codes. You can enter a comma separated list and ranges (e.g. 1234,1235,1236-1239,1100-1200).');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total']     = array('Minimum total');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total']     = array('Maximum total');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['product_types']     = array('Product types', 'You can restrict this shipping method to certain product types. If the cart contains a product type you have not selected, the shipping module is not available.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups']            = array('Member Groups','Restrict this shipping option to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected']         = array('Protect module', 'Show the module to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests']            = array('Show to guests only', 'Hide the module if a member is logged in.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled']           = array('Enabled', 'Is the module available for use in the store?');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend']      = 'Title and type';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note_legend']       = 'Shipping note';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['config_legend']     = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend']      = 'Pricing threshold and tax class applicability';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend']     = 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend']    = 'Enabled settings';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new']               = array('New shipping method', 'Create a New shipping method');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit']              = array('Edit shipping', 'Edit shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy']              = array('Copy shipping', 'Copy shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete']            = array('Delete shipping', 'Delete shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['toggle']            = array('Publish/unpublish shipping', 'Publish/unpublish shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show']              = array('Shipping Details', 'Show details of shipping method ID %s');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat']              = 'Flat';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct']        = 'Per Product';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem']           = 'Per Item';
