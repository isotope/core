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
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name']			= array('Tax Rate Name','Enter a name for your tax rate.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label']			= array('Tax Rate Label','This label will be used on the front-end in the checkout process.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address']		= array('Address to Use for Calculation', 'Select which address this rate should use to apply its calculation.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['country']		= array('Country', 'Select a country this tax class applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['subdivision']	= array('State/Region', 'Select a state or region this tax class applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postal']		= array('Postal Code Range', 'Specify a postal code rage this tax class applies to. (Ex: 10000 - 20000)');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount']		= array('Subtotal Amount Restriction', 'Optional: Restrict this tax rate to specific subtotal amount (such as for a luxury tax.)');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate']			= array('Tax Rate', 'A rate in percent this tax is set at.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['store'] 		= array('Store', 'Select the store that the tax rate applies to.');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'] 		= array('Stop Calculations on Trigger?', 'Stop other calculations if this tax rate is triggered.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new']			= array('New tax rate', 'Create a New tax rate');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit']			= array('Edit tax rate', 'Edit tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy']			= array('Copy tax rate', 'Copy tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete']		= array('Delete tax rate', 'Delete tax rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show']			= array('Order Details', 'Show details of tax rate ID %s');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['billing']		= 'Billing Address';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['shipping']		= 'Shipping Address';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name_legend']		= 'Name Legend';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate_legend']		= 'Rate Legend';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['location_legend']	= 'Location Legend';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['condition_legend']	= 'Conditions Legend';
$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config_legend']		= 'Configuration Legend';
