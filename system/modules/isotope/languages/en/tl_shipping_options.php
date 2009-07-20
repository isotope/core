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
 * @copyright  Winans Creative/Fred Bliss 2009 
 * @author     Fred Bliss 
 * @package    Isotope 
 * @license    Commercial 
 * @filesource
 */



/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_shipping_options']['name'] = array('Name', 'A brief description of the rate. Used on frontend output.');
$GLOBALS['TL_LANG']['tl_shipping_options']['rate'] = array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_shipping_options']['upper_limit'] = array('Upper Limit', 'The upper limit of the comparison being used (price or weight).');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_zip'] = array('Destination Zip Code', 'Zips used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_country'] = array('Destination Country', 'Country used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_region'] = array('Destination Region', 'Region (county) used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_options']['groups']			= array('Member Groups','Restrict this shipping option to certain member groups.');


/**
 * Reference
 */


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_shipping_options']['new']    = array('New Shipping Rate', 'Create a new Shipping Rate');
$GLOBALS['TL_LANG']['tl_shipping_options']['edit']   = array('Edit Shipping Rate', 'Edit Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['copy']   = array('Copy Shipping Rate', 'Copy Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['delete'] = array('Delete Shipping Rate', 'Delete Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['show']   = array('Shipping Rate Details', 'Show details of Shipping Rate ID %s');

?>