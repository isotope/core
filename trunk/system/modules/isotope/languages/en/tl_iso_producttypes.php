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
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name']					= array('Name', 'Please enter a name for this product type.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['class']				= array('Product Class', 'Please select a product class. Different product classes will handle products differently.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['fallback']				= array('Default', 'Check here if this is the default product type.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['list_template']		= array('Listing Template', 'Select a template for product listing.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['reader_template']		= array('Reader Template', 'Select a template for product details.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description']			= array('Description', 'A hint to product managers what this product type is for.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes']			= array('Attributes', 'Select the collection of attributes that should be included for this product type.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variants']				= array('Enable variants', 'Check here if this product type has variants.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['variant_attributes']	= array('Variant attributes', 'Select the collection of variant attributes that should be included for this product type. Those that are not selected will be hidden from view and inherited from the parent product.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['languages']			= array('Additional languages', 'Select the additional languages you want to add product data for. If you remove a language, the product data is not dropped but no longer used.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['downloads']			= array('Enable downloads', 'Check here if this product type has downloads.');


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['new']    				= array('New product type', 'Create new product type.');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['edit']   				= array('Edit product type', 'Edit product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['copy']   				= array('Copy product type definiton', 'Copy definition of product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['delete'] 				= array('Delete product type', 'Delete product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttypes']['show']   				= array('product type details', 'Show details of product type ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_producttypes']['name_legend']			= 'Product type settings';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['description_legend']	= 'Description';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['template_legend']		= 'Templates';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['language_legend']		= 'Multilingual settings';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['attributes_legend']	= 'Product attributes';
$GLOBALS['TL_LANG']['tl_iso_producttypes']['download_legend']		= 'Downloads';

