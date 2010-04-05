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
$GLOBALS['TL_LANG']['tl_shipping_rates']['description'] = array('Description', 'A brief description of the rate. Used on frontend output.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['rate'] = array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['upper_limit'] = array('Upper Limit', 'The upper limit of the comparison being used (price or weight).');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_zip'] = array('Destination Zip Code', 'Zips used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_country'] = array('Destination Country', 'Country used in the shipping destination for this rate.');
$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_region'] = array('Destination Region', 'Region (county) used in the shipping destination for this rate.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_shipping_rates']['new']    = array('New Shipping Rate', 'Create a new Shipping Rate');
$GLOBALS['TL_LANG']['tl_shipping_rates']['edit']   = array('Edit Shipping Rate', 'Edit Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_rates']['copy']   = array('Copy Shipping Rate', 'Copy Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_rates']['delete'] = array('Delete Shipping Rate', 'Delete Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_shipping_rates']['show']   = array('Shipping Rate Details', 'Show details of Shipping Rate ID %s');

