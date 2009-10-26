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
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_product_data']['pages'] = array('Categories','Select a category (page-based categories take advantage of TYPOlight pages features such as navigation automation, protection, templates, and full integration with content elements.');
$GLOBALS['TL_LANG']['tl_product_data']['type'] = array('Product Type','Product types are defined in the Product Type Manager.');
$GLOBALS['TL_LANG']['tl_product_data']['option_set_source'] = array('Option Set Source','Use an existing option set or create a new one');
$GLOBALS['TL_LANG']['tl_product_data']['option_sets'] = array('Option Sets','Select an existing Option Set');
$GLOBALS['TL_LANG']['tl_product_data']['option_set_title'] = array('Option Set Title','Name your new option set. The combination of values for each attribute will be stored and used to produce a new collection of empty subproducts.');
$GLOBALS['TL_LANG']['tl_product_data']['variants_wizard'] = array('Product Variants','Configure product variants');


/*
$GLOBALS['TL_LANG']['tl_product_data']['optionSetTitle'] = array('New Option Set','Create a new option set');
$GLOBALS['TL_LANG']['tl_product_data']['optionSetSelect'] = array('Option Set','Select an existing option set');
$GLOBALS['TL_LANG']['tl_product_data']['useOrCreateOptionSet'] = array('Option Set Mode','');
*/
$GLOBALS['TL_LANG']['tl_product_data']['enabled'] = 'Enabled';
$GLOBALS['TL_LANG']['tl_product_data']['values']			= 'Option Values';
$GLOBALS['TL_LANG']['tl_product_data']['sku']				= 'SKU';
$GLOBALS['TL_LANG']['tl_product_data']['price']	= 'Price Change';
$GLOBALS['TL_LANG']['tl_product_data']['weight']	= 'Weight Change';
$GLOBALS['TL_LANG']['tl_product_data']['quantity']		= 'Quantity';

/*
$GLOBALS['TL_LANG']['tl_product_data']['audio_source'] = array('Audio File Source', 'Link to an internal or external media source instead of the default page.');
$GLOBALS['TL_LANG']['tl_product_data']['audio_jumpTo'] = array('Select Internal File', 'Please select the page to which visitors will be redirected.');
$GLOBALS['TL_LANG']['tl_product_data']['audio_url'] = array('Specify External File','Paste a media link here (for example, a Youtube video URL)');
$GLOBALS['TL_LANG']['tl_product_data']['video_source'] = array('Video File Source', 'Link to an internal or external media source instead of the default page.');
$GLOBALS['TL_LANG']['tl_product_data']['video_jumpTo'] = array('Select Internal File', 'Please select the page to which visitors will be redirected.');
$GLOBALS['TL_LANG']['tl_product_data']['video_url'] = array('Specify External File','Paste a media link here (for example, a Youtube video URL)');
$GLOBALS['TL_LANG']['tl_product_data']['option_collection'] = array('Product Option Wizard','Create extra product option combinations.');
$GLOBALS['TL_LANG']['tl_product_data']['add_audio_file'] = array('Add Audio File','Add an audio file to this product');
$GLOBALS['TL_LANG']['tl_product_data']['add_video_file'] = array('Add Video File','Add a video file to this product');
*/


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_product_data']['internal'] = array('Internal file', 'Select a media file existing on the web server (flash or mp3 file).');
$GLOBALS['TL_LANG']['tl_product_data']['external'] = array('External file', 'Specify a video from an external source (such as Youtube).');

$GLOBALS['TL_LANG']['tl_product_data']['opAttribute'] = 'Product Attribute';
$GLOBALS['TL_LANG']['tl_product_data']['opValueSets'] = 'Option Values';

$GLOBALS['TL_LANG']['tl_product_data']['existing_option_set'] = 'Select an existing product option set';
$GLOBALS['TL_LANG']['tl_product_data']['new_option_set'] = 'Create a new product option set';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_data']['new']    = array('New product', 'Create new product.');
$GLOBALS['TL_LANG']['tl_product_data']['edit']   = array('Edit product', 'Edit product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['copy']   = array('Copy product', 'Copy product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['cut']   = array('Move product', 'Move product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['delete'] = array('Delete product', 'Delete product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['show']   = array('product details', 'Show details of product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['editheader'] = array('Edit product type', 'Edit the product type');


/** 
 * Legends
 */
$GLOBALS['TL_LANG']['tl_product_data']['general_legend'] = "General Settings";
$GLOBALS['TL_LANG']['tl_product_data']['pricing_legend'] = "Pricing Settings";
$GLOBALS['TL_LANG']['tl_product_data']['inventory_legend'] = 'Inventory Settings';
$GLOBALS['TL_LANG']['tl_product_data']['shipping_legend'] = 'Shipping Settings';
$GLOBALS['TL_LANG']['tl_product_data']['tax_legend'] = 'Tax Settings';
$GLOBALS['TL_LANG']['tl_product_data']['options_legend'] = 'Product Options Settings';
$GLOBALS['TL_LANG']['tl_product_data']['tax_legend'] = 'Tax Settings';
$GLOBALS['TL_LANG']['tl_product_data']['availability_legend'] = 'Availability Settings';
$GLOBALS['TL_LANG']['tl_product_data']['media_legend'] = 'Media Management';

