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
$GLOBALS['TL_LANG']['tl_iso_products']['id']					= array('Product ID');
$GLOBALS['TL_LANG']['tl_iso_products']['pages']					= array('Categories', 'Select a category (page-based categories take advantage of Contao pages features such as navigation automation, protection, templates, and full integration with content elements.');
$GLOBALS['TL_LANG']['tl_iso_products']['type']					= array('Product Type', 'Select your product type. Product types are defined in the store configuration.');
$GLOBALS['TL_LANG']['tl_iso_products']['alias']					= array('Alias', 'You can enter a unique alias for this product. It will be automatically generated from the name if empty.');
$GLOBALS['TL_LANG']['tl_iso_products']['name']					= array('Name', 'Please enter the name of this product.');
$GLOBALS['TL_LANG']['tl_iso_products']['sku']					= array('SKU', 'Please enter a unique stock keeping unit for this product.');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_weight']		= array('Shipping weight', 'Please enter the shipping weight for this product. This can be used to calculate shipping cost.');
$GLOBALS['TL_LANG']['tl_iso_products']['teaser']				= array('Teaser', 'Please enter the teaser.');
$GLOBALS['TL_LANG']['tl_iso_products']['description']			= array('Description', 'Please enter the product description.');
$GLOBALS['TL_LANG']['tl_iso_products']['description_meta']		= array('Meta description', 'Meta description will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_products']['keywords_meta']			= array('Meta keywords', 'Meta keywords will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_products']['price']					= array('Price', 'Please enter the price(s) for this product.');
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_exempt']		= array('Exempt from shipping', 'Check if item is not a shipped item (such as downloadable products).');
$GLOBALS['TL_LANG']['tl_iso_products']['tax_class']				= array('Tax Class', 'Select your appropriate tax class.');
$GLOBALS['TL_LANG']['tl_iso_products']['baseprice']				= array('Base price', 'Please enter your base price information.');
$GLOBALS['TL_LANG']['tl_iso_products']['images']				= array('Images', 'Upload images to this product. Please save the product after selecting a file.');
$GLOBALS['TL_LANG']['tl_iso_products']['protected']				= array('Protect product', 'Restrict product access to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_products']['groups']				= array('Allowed member groups', 'These groups will be able to access the product.');
$GLOBALS['TL_LANG']['tl_iso_products']['guests']				= array('Show to guests only', 'Hide the product if there is an authenticated user.');
$GLOBALS['TL_LANG']['tl_iso_products']['cssID']					= array('CSS ID/class', 'Here you can set an ID and one or more classes.');
$GLOBALS['TL_LANG']['tl_iso_products']['published']				= array('Publish product', 'Click here to show this product on your website.');
$GLOBALS['TL_LANG']['tl_iso_products']['start']					= array('Start date', 'Do not show this product before the date specified.');
$GLOBALS['TL_LANG']['tl_iso_products']['stop']					= array('Stop date', 'Do not show this product after the date specified.');
$GLOBALS['TL_LANG']['tl_iso_products']['variant_attributes']	= array('Variant setup', 'Please select the combination of values for this variant.');
$GLOBALS['TL_LANG']['tl_iso_products']['inherit']				= array('Inherited attributes', 'Check the fields you want to inherit from base product.');


/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_products']['source']				= array('Source folder', 'Please choose the folder where product assets are located.');
$GLOBALS['TL_LANG']['tl_iso_products']['internal']				= array('Internal file', 'Select a media file existing on the web server (flash or mp3 file).');
$GLOBALS['TL_LANG']['tl_iso_products']['external']				= array('External file', 'Specify a video from an external source (such as Youtube).');
$GLOBALS['TL_LANG']['tl_iso_products']['opAttribute']			= 'Product Attribute';
$GLOBALS['TL_LANG']['tl_iso_products']['opValueSets']			= 'Option Values';
$GLOBALS['TL_LANG']['tl_iso_products']['opValue']				= 'Value';
$GLOBALS['TL_LANG']['tl_iso_products']['opLabel']				= 'Label';
$GLOBALS['TL_LANG']['tl_iso_products']['opPrice']				= 'Price (Surcharge)';
$GLOBALS['TL_LANG']['tl_iso_products']['opDisable']				= 'Disable';
$GLOBALS['TL_LANG']['tl_iso_products']['opInherit']				= 'Inherit label';
$GLOBALS['TL_LANG']['tl_iso_products']['mmSrc']					= 'Preview';
$GLOBALS['TL_LANG']['tl_iso_products']['mmAlt']					= 'Alternate text';
$GLOBALS['TL_LANG']['tl_iso_products']['mmLink']				= 'Link target';
$GLOBALS['TL_LANG']['tl_iso_products']['mmDesc']				= 'Description';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslate']			= 'Translate';
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateNone']		= array('None', 'Do not translate this image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateText']		= array('Text', 'Translate alt text and description for this image.');
$GLOBALS['TL_LANG']['tl_iso_products']['mmTranslateAll']		= array('All', 'Do not include this image in translated version.');
$GLOBALS['TL_LANG']['tl_iso_products']['existing_option_set']	= 'Select an existing product option set';
$GLOBALS['TL_LANG']['tl_iso_products']['new_option_set']		= 'Create a new product option set';
$GLOBALS['TL_LANG']['tl_iso_products']['variantValuesLabel']	= 'Variant';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_products']['new_product']		= array('New product', 'Create new product');
$GLOBALS['TL_LANG']['tl_iso_products']['new_variant']		= array('Add variant', 'Add new variant to a given product');
$GLOBALS['TL_LANG']['tl_iso_products']['edit']				= array('Edit product', 'Edit product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['copy']				= array('Copy product', 'Copy product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['cut']				= array('Move product', 'Move product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['delete']			= array('Delete product', 'Delete product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['toggle']			= array('Publish/unpublish product', 'Publish/unpublish product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['show']				= array('Product details', 'Show details of product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['generate']			= array('Generate variants', 'Generate variants for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['related']			= array('Related products', 'Manage related products for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['filter']			= array('Advanced Filters', 'Apply advanced filters');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_remove']		= array('Remove filters', 'Remove active filters');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_noimages']	= array('Products without images', 'Show products with no images assigned');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_nocategory']	= array('Unassigned products', 'Show products that are not assigned to any category');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_today']	= array('Added today', 'Show products added today');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_week']	= array('Added this week', 'Show products added in the last 7 days');
$GLOBALS['TL_LANG']['tl_iso_products']['filter_new_month']	= array('Added this month', 'Show products added in the last 30 days');
$GLOBALS['TL_LANG']['tl_iso_products']['tools']				= array('Tools', 'More options for product management');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleGroups']		= array('Toggle all groups', 'Toggle all groups');
$GLOBALS['TL_LANG']['tl_iso_products']['toggleVariants']	= array('Toggle all variants', 'Toggle all variants');
$GLOBALS['TL_LANG']['tl_iso_products']['import']			= array('Import assets', 'Import images and other media from a folder');
$GLOBALS['TL_LANG']['tl_iso_products']['groups']			= array('Product Groups', 'Manage product groups');
$GLOBALS['TL_LANG']['tl_iso_products']['quick_edit']		= array('Quick-edit variants', 'Quick-edit variants for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['downloads']			= array('Downloads', 'Edit downloads for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_products']['prices']			= array('Manage prices', 'Manage prices for product ID %s');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_products']['general_legend']	= "General Settings";
$GLOBALS['TL_LANG']['tl_iso_products']['meta_legend']		= 'Meta data';
$GLOBALS['TL_LANG']['tl_iso_products']['pricing_legend']	= "Pricing Settings";
$GLOBALS['TL_LANG']['tl_iso_products']['inventory_legend']	= 'Inventory Settings';
$GLOBALS['TL_LANG']['tl_iso_products']['shipping_legend']	= 'Shipping Settings';
$GLOBALS['TL_LANG']['tl_iso_products']['options_legend']	= 'Product Options Settings';
$GLOBALS['TL_LANG']['tl_iso_products']['media_legend']		= 'Media Management';
$GLOBALS['TL_LANG']['tl_iso_products']['expert_legend']		= 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_products']['publish_legend']	= 'Publishing';
$GLOBALS['TL_LANG']['tl_iso_products']['variant_legend']	= 'Product variant setup';


/**
 * Table format
 */
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min']		= 'Quantity';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['min_format']	= 'from %s pcs.';
$GLOBALS['TL_LANG']['tl_iso_products']['price_tiers']['price']		= 'Price';

