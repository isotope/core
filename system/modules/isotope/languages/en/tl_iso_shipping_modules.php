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
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['name']				= array('Shipping Method Name', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['type']				= array('Shipping Method Type', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price']				= array('Price', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['note']				= array('Shipping Method Notes', 'These will be displayed on the front end in association with this shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['tax_class']			= array('Tax Class', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['label']				= array('Label', 'This is displayed on the front end in association with the shipping option.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flatCalculation']	= array('Flat calculation', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['countries']			= array('Countries', 'Select the countries this shipping method applies to.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['subdivisions']	= array('State/Regions', 'Select the states or regions this shipping method applies to.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['minimum_total']		= array('Minimum total', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['maximum_total']		= array('Maximum total', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['surcharge_field']	= array('Shipping Surcharge', 'Please specify a surcharge (for example, a fuel surcharge on all orders) to be applied for this shipping method, if any.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['groups']			= array('Member Groups','Restrict this shipping option to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled']			= array('Enabled', 'Is the module available for use in the store?');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['protected']      	= array('Protect module', 'Show the module to certain member groups only.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['guests']         	= array('Show to guests only', 'Hide the module if a member is logged in.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_accessKey']     = array('UPS XML/HTML access key','This is a special alphanumeric key issued by UPS once you sign up for a UPS account and for access to the UPS Online Tools API');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_developersKey']    = array('UPS developer\'s key','This is a special alphanumeric key issued by UPS once you sign up for a UPS account and for access to the UPS Online Tools API');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_userName']     	= array('UPS username','This is the UPS account username that you chose while signing up on the UPS website.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_password']     	= array('UPS password','This is the UPS password that you chose while signing up on the UPS website.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_enabledService'] = array('UPS Service Type','Select a UPS shipping service to offer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_enabledService'] = array('USPS Service Type','Select a USPS shipping service to offer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_userName']     	= array('USPS username','This is the USPS account username that you chose while signing up on the USPS website.');


/** 
 * Legends 
 *
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['title_legend'] = "Title and type";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['configuration_legend'] = "General configuration";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['expert_legend'] = "Expert settings";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['enabled_legend'] = "Enabled settings";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_legend'] = "UPS API settings";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_legend'] = "USPS API settings";
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['price_legend'] = "Pricing threshold and tax class applicability";

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['new']    = array('New shipping method', 'Create a New shipping method');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['edit']   = array('Edit shipping', 'Edit shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['copy']   = array('Copy shipping', 'Copy shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['delete'] = array('Delete shipping', 'Delete shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['show']   = array('shipping Details', 'Show details of shipping method ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['shipping_rates']   = array('Edit Rules', 'Edit the shipping rates');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['flat']					 						= 'Flat';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perProduct']			 						= 'Per Product';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['perItem']				 						= 'Per Item';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['01'] 	 						= 'Next Day Air';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['02'] 	 						= '2nd Day Air'; 
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['03'] 	 						= 'UPS Ground';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['07']		 						= 'Worldwide Express'; 
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['08']		 						= 'Worldwide Expedited';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['11'] 	 						= 'International Standard';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['12'] 	 						= '3 Day Select';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['13'] 	 						= 'Next Day Air Saver';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['14'] 	 						= 'Next Day Air Early AM';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['54'] 	 						= 'Worldwide Express Plus'; 
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['ups_service']['65'] 	 						= 'International Saver'; 
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PARCEL'] 						= 'USPS Parcel Post';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY'] 						= 'USPS Priority Mail (2-3 days average)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS'] 						= 'USPS Express Mail (Overnight Guaranteed)';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['FIRST CLASS']					= 'USPS First Class';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['PRIORITY COMMERCIAL'] 			= 'USPS Priority Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS COMMERCIAL']			= 'USPS Express Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH']					= 'USPS Express Sundays & Holidays';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS SH COMMERCIAL']			= 'USPS Express Sundays & Holidays Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP']					= 'USPS Express Hold For Pickup';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['EXPRESS HFP COMMERCIAL']		= 'USPS Express Hold For Pickup Commercial';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['BPM'] 							= 'USPS Bound Printed Matter';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['MEDIA'] 						= 'USPS Media Mail';
$GLOBALS['TL_LANG']['tl_iso_shipping_modules']['usps_service']['LIBRARY']						= 'USPS Library Mail';  