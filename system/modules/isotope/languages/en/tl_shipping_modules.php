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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_shipping_modules']['name']				= array('Shipping Method Name', '');
$GLOBALS['TL_LANG']['tl_shipping_modules']['price']				= array('Price', '');
$GLOBALS['TL_LANG']['tl_shipping_modules']['note']				= array('Shipping Method Notes', 'These will be displayed on the front end in association with this shipping option.');
$GLOBALS['TL_LANG']['tl_shipping_modules']['label']				= array('Label', 'This is displayed on the front end in association with the shipping option.');
$GLOBALS['TL_LANG']['tl_shipping_modules']['flatCalculation']	= array('Flat calculation', '');
$GLOBALS['TL_LANG']['tl_shipping_modules']['countries']			= array('Countries', 'Select the countries this shipping method applies to.');
$GLOBALS['TL_LANG']['tl_shipping_modules']['minimum_total']		= array('Minimum total', '');
$GLOBALS['TL_LANG']['tl_shipping_modules']['maximum_total']		= array('Maximum total', '');
$GLOBALS['TL_LANG']['tl_shipping_modules']['groups']			= array('Member Groups','Restrict this shipping option to certain member groups.');
$GLOBALS['TL_LANG']['tl_shipping_modules']['enabled']			= array('Enabled', 'Is the module available for use in the store?');


/**
 * Reference

$GLOBALS['TL_LANG']['tl_shipping_modules'][''] = '';
 */

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_shipping_modules']['new']    = array('New shipping method', 'Create a New shipping method');
$GLOBALS['TL_LANG']['tl_shipping_modules']['edit']   = array('Edit shipping', 'Edit shipping method ID %s');
$GLOBALS['TL_LANG']['tl_shipping_modules']['copy']   = array('Copy shipping', 'Copy shipping method ID %s');
$GLOBALS['TL_LANG']['tl_shipping_modules']['delete'] = array('Delete shipping', 'Delete shipping method ID %s');
$GLOBALS['TL_LANG']['tl_shipping_modules']['show']   = array('shipping Details', 'Show details of shipping method ID %s');
$GLOBALS['TL_LANG']['tl_shipping_modules']['shipping_rates']   = array('Edit Rules', 'Edit the shipping rates');
$GLOBALS['TL_LANG']['tl_shipping_modules']['tstamp'] = array('Date Modified', '');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_shipping_modules']['flat']				= 'Flat';
$GLOBALS['TL_LANG']['tl_shipping_modules']['perProduct']		= 'Per Product';
$GLOBALS['TL_LANG']['tl_shipping_modules']['perItem']			= 'Per Item';

