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
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name']			= array('Name', 'Enter a name for this tax rate.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label']			= array('Label', 'This label will be used on the front end in the checkout process.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address']		= array('Address to use for calculation', 'Select to which address this rate should use to apply its calculation.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['countries']		= array('Countries', 'Select the countriees this tax class applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['subdivisions']	= array('States/Regions', 'Select the states or regions this tax class applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postalCodes']	= array('Postal codes', 'Limit the tax rate to postal codes. You can enter a comma separated list and ranges (e.g. 1234,1235,1236-1239,1100-1200).');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount']		= array('Subtotal amount restriction', 'Optional: Restrict this tax rate to specific subtotal amount (such as for a luxury tax.)');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate']			= array('Tax rate', 'A rate in percent this tax is set at.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'] 		= array('Store configuration', 'Select the store configuration that the tax rate applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'] 			= array('Stop calculations on trigger?', 'Stop other calculations if this tax rate is triggered.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['groups']		= array('Member groups', 'Restrict this tax rate to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['protected']		= array('Protect tax rate', 'Apply the tax rate to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['guests']		= array('Apply to guests only', 'Do not apply tax rate if a member is logged in.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new']			= array('New tax rate', 'Create a new tax rate');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit']			= array('Edit tax rate', 'Edit tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy']			= array('Copy tax rate', 'Copy tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete']		= array('Delete tax rate', 'Delete tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show']			= array('Tax rate details', 'Show details of tax rate ID %s');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['billing']		= 'Billing Address';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['shipping']		= 'Shipping Address';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name_legend']		= 'Name';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate_legend']		= 'Rate';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['location_legend']	= 'Location';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['condition_legend']	= 'Conditions';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config_legend']		= 'Configuration';
