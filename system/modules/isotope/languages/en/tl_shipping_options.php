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
$GLOBALS['TL_LANG']['tl_shipping_options']['name'] 				= array('Name', 'A brief description of the rate. Used on frontend output.');
$GLOBALS['TL_LANG']['tl_shipping_options']['rate'] 				= array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_shipping_options']['description']		= array('Description', 'A rate description can be used to communicate how the rate is calculated to the customer.');
$GLOBALS['TL_LANG']['tl_shipping_options']['limit_type']		= array('Limit Type','Lower Limit will only apply to numbers exceeding the value specified., Upper limit will only apply to numbers that do not exceed the value specified.');
$GLOBALS['TL_LANG']['tl_shipping_options']['limit_value']		= array('Limit Value','A positive integer greater than or equal to 0.');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_postalcodes']	= array('Destination Postal Codes', 'Postal/zip codes that apply to this rate. If none, will apply to all based upon allowed countries.');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_countries'] 	= array('Destination Countries', 'Countries that apply to this rate. If none, will apply to all based upon allowed countries.');
$GLOBALS['TL_LANG']['tl_shipping_options']['dest_regions'] 		= array('Destination Regions', 'Regions that apply to this rate. If none, will apply to all based upon allowed countries.');
$GLOBALS['TL_LANG']['tl_shipping_options']['groups']			= array('Member Groups','Restrict this shipping option to certain member groups.');
$GLOBALS['TL_LANG']['tl_shipping_options']['option_type']		= array('Type of shipping option','This determines extra shipping module functionality.');
$GLOBALS['TL_LANG']['tl_shipping_options']['override']			= array('Override Other Price Rules','Should this rule override base price rules?');$GLOBALS['TL_LANG']['tl_shipping_options']['override_rule']		= array('Overrided Rule','Select a rule to be overwritten.');
$GLOBALS['TL_LANG']['tl_shipping_options']['override_message']   = array('Override Message','Communicate a special message in conjunction with this shipping rate.');
$GLOBALS['TL_LANG']['tl_shipping_options']['mandatory']			= array('Required','Is this option mandatory?  If so it will automatically tack on the surcharge value if specific regional/country-based conditions are met.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_shipping_options']['types']['ot_tier'] 		= 'Order Total-Based Tier';
$GLOBALS['TL_LANG']['tl_shipping_options']['types']['surcharge'] 	= 'Surcharge';
$GLOBALS['TL_LANG']['tl_shipping_options']['limit']['lower'] 		= 'Lower Limit';
$GLOBALS['TL_LANG']['tl_shipping_options']['limit']['upper'] 		= 'Upper Limit';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_shipping_options']['new']    = array('New Shipping Rate', 'Create a new Shipping Rate');
$GLOBALS['TL_LANG']['tl_shipping_options']['edit']   = array('Edit Shipping Rate', 'Edit Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['copy']   = array('Copy Shipping Rate', 'Copy Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['delete'] = array('Delete Shipping Rate', 'Delete Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_options']['show']   = array('Shipping Rate Details', 'Show details of Shipping Rate ID %s');

?>