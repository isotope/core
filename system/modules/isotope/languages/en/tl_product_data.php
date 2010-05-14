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
$GLOBALS['TL_LANG']['tl_product_data']['pages']					= array('Categories','Select a category (page-based categories take advantage of TYPOlight pages features such as navigation automation, protection, templates, and full integration with content elements.');
$GLOBALS['TL_LANG']['tl_product_data']['type']					= array('Product Type','Product types are defined in the Product Type Manager.');
$GLOBALS['TL_LANG']['tl_product_data']['alias']					= array('Alias', '');
$GLOBALS['TL_LANG']['tl_product_data']['name']					= array('Name', '');
$GLOBALS['TL_LANG']['tl_product_data']['sku']					= array('SKU', '');
$GLOBALS['TL_LANG']['tl_product_data']['weight']				= array('Weight', '');
$GLOBALS['TL_LANG']['tl_product_data']['stock_quantity']		= array('Quantity', '');
$GLOBALS['TL_LANG']['tl_product_data']['teaser']				= array('Teaser', '');
$GLOBALS['TL_LANG']['tl_product_data']['description']			= array('Description', '');
$GLOBALS['TL_LANG']['tl_product_data']['description_meta']		= array('Meta description','');
$GLOBALS['TL_LANG']['tl_product_data']['keywords_meta']			= array('Meta keywords','');
$GLOBALS['TL_LANG']['tl_product_data']['shipping_exempt']		= array('Exempt from shipping', 'Check if item is not a shipped item (such as downloadable products).');
$GLOBALS['TL_LANG']['tl_product_data']['tax_class']				= array('Tax Class', '');
$GLOBALS['TL_LANG']['tl_product_data']['images']				= array('Images', '');
$GLOBALS['TL_LANG']['tl_product_data']['published']				= array('Publish product', 'Click here to show this product on your website.');
$GLOBALS['TL_LANG']['tl_product_data']['source']				= array('Source folder', 'Please choose the folder where product assets are located.');


$GLOBALS['TL_LANG']['tl_product_data']['price']					= array('Price', '');
$GLOBALS['TL_LANG']['tl_product_data']['price_override']		= array('Price override', '');
$GLOBALS['TL_LANG']['tl_product_data']['option_set_source']		= array('Option Set Source','Use an existing option set or create a new one');
$GLOBALS['TL_LANG']['tl_product_data']['option_sets']			= array('Option Sets','Select an existing Option Set');
$GLOBALS['TL_LANG']['tl_product_data']['option_set_title']		= array('Option Set Title','Name your new option set. The combination of values for each attribute will be stored and used to produce a new collection of empty subproducts.');
$GLOBALS['TL_LANG']['tl_product_data']['stock_enabled']			= array('Enabled inventory tracking','Please choose whether or not you would like to track inventory.');
$GLOBALS['TL_LANG']['tl_product_data']['max_order_quantity']	= array('Maximum order quantity','Specify a maximum amount of product that can be purchased in a single order.');
$GLOBALS['TL_LANG']['tl_product_data']['start']					= array('Start date','Do not show this product before the date specified.');
$GLOBALS['TL_LANG']['tl_product_data']['stop']					= array('Stop date','Do not show this product after the date specified.');
$GLOBALS['TL_LANG']['tl_product_data']['variant_attributes']	= array('Variant setup', 'Please select the combination of values for this variant.');
$GLOBALS['TL_LANG']['tl_product_data']['inherit']				= array('Inherited attributes', 'Check the fields you want to inherit from base product.');

$GLOBALS['TL_LANG']['tl_product_data']['enabled'] = 'Enabled';

$GLOBALS['TL_LANG']['tl_product_data']['batch_size']			= array('Batch size','Select the number of records you would like to process at a time.  If the page times out, reduce the number.');
/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_product_data']['internal'] = array('Internal file', 'Select a media file existing on the web server (flash or mp3 file).');
$GLOBALS['TL_LANG']['tl_product_data']['external'] = array('External file', 'Specify a video from an external source (such as Youtube).');

$GLOBALS['TL_LANG']['tl_product_data']['opAttribute'] = 'Product Attribute';
$GLOBALS['TL_LANG']['tl_product_data']['opValueSets'] = 'Option Values';

$GLOBALS['TL_LANG']['tl_product_data']['opValue']		= 'Value';
$GLOBALS['TL_LANG']['tl_product_data']['opLabel']		= 'Label';
$GLOBALS['TL_LANG']['tl_product_data']['opPrice']		= 'Price (Surcharge)';
$GLOBALS['TL_LANG']['tl_product_data']['opDisable']		= 'Disable';
$GLOBALS['TL_LANG']['tl_product_data']['opInherit']		= 'Inherit label';

$GLOBALS['TL_LANG']['tl_product_data']['mmSrc'] = 'Image';
$GLOBALS['TL_LANG']['tl_product_data']['mmAlt'] = 'Alternate text';
$GLOBALS['TL_LANG']['tl_product_data']['mmDesc'] = 'Description';

$GLOBALS['TL_LANG']['tl_product_data']['existing_option_set'] = 'Select an existing product option set';
$GLOBALS['TL_LANG']['tl_product_data']['new_option_set'] = 'Create a new product option set';

$GLOBALS['TL_LANG']['tl_product_data']['variantValuesLabel'] = 'Variant';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_data']['new_product']	= array('New product', 'Create new product.');
$GLOBALS['TL_LANG']['tl_product_data']['new_variant']	= array('New variant', 'Add new variant to a given product.');
$GLOBALS['TL_LANG']['tl_product_data']['edit']			= array('Edit product', 'Edit product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['quick_edit']	= array('Quick-edit variants', 'Quick-edit variants for product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['downloads']		= array('Downloads', 'Edit downloads for product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['copy']			= array('Copy product', 'Copy product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['cut']			= array('Move product', 'Move product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['delete']		= array('Delete product', 'Delete product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['show']			= array('Product details', 'Show details of product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['generate']		= array('Generate variants', 'Generate variants for product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['related']		= array('Related products', 'Manage related products for product ID %s');
$GLOBALS['TL_LANG']['tl_product_data']['tools']			= array('Tools', 'More options for product management.');
$GLOBALS['TL_LANG']['tl_product_data']['import']		= array('Import assets', 'Import images and other media from a folder');
$GLOBALS['TL_LANG']['tl_product_data']['link']			= array('Link Products to Categories', 'Link products to categories');


/** 
 * Legends
 */
$GLOBALS['TL_LANG']['tl_product_data']['general_legend']	= "General Settings";
$GLOBALS['TL_LANG']['tl_product_data']['meta_legend']	= 'Meta data';
$GLOBALS['TL_LANG']['tl_product_data']['pricing_legend']	= "Pricing Settings";
$GLOBALS['TL_LANG']['tl_product_data']['inventory_legend']	= 'Inventory Settings';
$GLOBALS['TL_LANG']['tl_product_data']['shipping_legend']	= 'Shipping Settings';
$GLOBALS['TL_LANG']['tl_product_data']['tax_legend']		= 'Tax Settings';
$GLOBALS['TL_LANG']['tl_product_data']['options_legend']	= 'Product Options Settings';
$GLOBALS['TL_LANG']['tl_product_data']['tax_legend']		= 'Tax Settings';
$GLOBALS['TL_LANG']['tl_product_data']['media_legend']		= 'Media Management';
$GLOBALS['TL_LANG']['tl_product_data']['publish_legend']	= 'Publishing';
$GLOBALS['TL_LANG']['tl_product_data']['variant_legend']	= 'Product variant setup';

