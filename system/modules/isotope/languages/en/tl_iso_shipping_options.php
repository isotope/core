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
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'] 				= array('Name', 'A brief description of the rate. Used on frontend output.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'] 				= array('Rate', 'The shipping rate in currency format.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description']		= array('Description', 'A rate description can be used to communicate how the rate is calculated to the customer.');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total']		= array('Minimum total', '');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total']		= array('Maximum total', '');


/**
 * Reference
 */

/** 
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['configuration_legend'] = "General configuration";
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['general_legend'] = "General information";


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['new']    = array('New Shipping Rate', 'Create a new Shipping Rate');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit']   = array('Edit Shipping Rate', 'Edit Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy']   = array('Copy Shipping Rate', 'Copy Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete'] = array('Delete Shipping Rate', 'Delete Shipping Rate ID %s');
$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show']   = array('Shipping Rate Details', 'Show details of Shipping Rate ID %s');

