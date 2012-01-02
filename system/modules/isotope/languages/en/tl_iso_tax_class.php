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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name']						= array('Name', 'Give this tax class a name that explains what it is used for.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback']					= array('Default', 'Check here if this is the default tax class.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement']	= array('Apply rounding increment', 'Check here if you want to apply the rounding increment of your shop config.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes']					= array('Tax rate included with product price', 'Select if prices of products with this tax class contain a tax rate. This tax rate will be subtracted from product price if it does not match.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['label']					= array('Include label', 'A label for orders to present for subtracted taxes (if included tax does not match). Default tax rate label will be used if this is blank.');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates']					= array('Apply tax rates', 'Add these tax rates to products with this tax class.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['new']    = array('New tax class', 'Create a New tax class');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit']   = array('Edit tax class', 'Edit tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy']   = array('Copy tax class', 'Copy tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'] = array('Delete tax class', 'Delete tax class ID %s');
$GLOBALS['TL_LANG']['tl_iso_tax_class']['show']   = array('Order Details', 'Show details of tax class ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_tax_class']['name_legend']	= 'Name';
$GLOBALS['TL_LANG']['tl_iso_tax_class']['rate_legend']	= 'Tax rates';

