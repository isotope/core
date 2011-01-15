<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tiers']		= array('Price Tiers', 'Setup at least one price tier for "Quantity 1". You can enter discount prices if the user orders more than one product.');
$GLOBALS['TL_LANG']['tl_iso_prices']['tax_class']		= array('Tax Class', 'Please select a tax class for this price.');
$GLOBALS['TL_LANG']['tl_iso_prices']['config_id']		= array('Store configuration', 'Select a store configuration for this price.');
$GLOBALS['TL_LANG']['tl_iso_prices']['member_group']	= array('Price group', 'Select a price group (member group) for this price.');
$GLOBALS['TL_LANG']['tl_iso_prices']['start']			= array('Use from', 'Do not use the price on the website before this day.');
$GLOBALS['TL_LANG']['tl_iso_prices']['stop']			= array('Use until', 'Do not use the price on the website after this day.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['new']				= array('Add price', 'Add a new price to this product');
$GLOBALS['TL_LANG']['tl_iso_prices']['edit']			= array('Edit price', 'Edit price ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['copy']			= array('Duplicate price', 'Duplicate price ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['delete']			= array('Delete price', 'Delete price ID %s');
$GLOBALS['TL_LANG']['tl_iso_prices']['show']			= array('Price details', 'Show details of price ID %s');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['min']	= 'Quantity (min)';
$GLOBALS['TL_LANG']['tl_iso_prices']['price_tier_columns']['price']	= 'Price';


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_prices']['price_legend']	= 'Price';
$GLOBALS['TL_LANG']['tl_iso_prices']['limit_legend']	= 'Limitations';

