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
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'] 				= array('Name', 'A brief description of the rate. Used on frontend output.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'] 				= array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description']		= array('Description', 'A rate description can be used to communicate how the rate is calculated to the customer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total']		= array('Minimum total');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total']		= array('Maximum total');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from']		= array('Weight from', 'If overall weight of all products in cart is more than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to']			= array('Weight to', 'If overall weight of all products in cart is less than this, the rate will match. Make sure you set the correct weight unit in module settings.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['enabled']			= array('Enabled', 'Is the rate available for use in the store?');


/**
 * Reference
 */

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['general_legend']	= 'General information';
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['config_legend']		= 'Configuration';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new']				= array('New Shipping Rate', 'Create a new Shipping Rate');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit']				= array('Edit Shipping Rate', 'Edit Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy']				= array('Copy Shipping Rate', 'Copy Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete']			= array('Delete Shipping Rate', 'Delete Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show']				= array('Shipping Rate Details', 'Show details of Shipping Rate ID %s');

