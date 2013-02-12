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
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name']                 = array('Name', 'A brief description of the rate.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate']                 = array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description']        = array('Description', 'A rate description can be used to communicate how the rate is calculated to the customer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total']        = array('Minimum total', 'Enter a minimum total for this rate.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total']        = array('Maximum total', 'Enter a maximum total for this rate.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from']        = array('Weight from', 'If overall weight of all products in cart is more than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to']            = array('Weight to', 'If overall weight of all products in cart is less than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['enabled']            = array('Enabled', 'Is the rate available for use in the store?');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new']                = array('New shipping rate', 'Create a new shipping rate');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit']                = array('Edit shipping rate', 'Edit shipping rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy']                = array('Copy shipping rate', 'Copy shipping rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete']            = array('Delete shipping rate', 'Delete shipping rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show']                = array('Shipping rate details', 'Show details of shipping rate ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['general_legend']    = 'General information';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['config_legend']        = 'Configuration';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['publishing_legend'] = 'Publishing';
