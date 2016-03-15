<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_producttype']['name']                  = array('Name', 'Please enter a name for this product type.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['class']                 = array('Product Class', 'Please select a product class. Different product classes will handle products differently.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['fallback']              = array('Default', 'Check here if this is the default product type.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['description']           = array('Description', 'A hint to product managers what this product type is for.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['prices']                = array('Advanced pricing', 'Allow to define multiple prices per product, e.g. for different store configs, member groups or dates.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['show_price_tiers']      = array('Show price tiers', 'Show highest tier as lowest product price.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['list_template']         = array('List template', 'Select a template for product listing.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['reader_template']       = array('Reader template', 'Select a template for product details.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['list_gallery']          = array('List gallery', 'Select a gallery for product listing.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['reader_gallery']        = array('Reader gallery', 'Select a gallery for product details.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']            = array('Attributes', 'Select the collection of attributes that should be included for this product type.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['variants']              = array('Enable variants', 'Check here if this product type has variants.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['variant_attributes']    = array('Variant attributes', 'Select the collection of variant attributes that should be included for this product type. Those that are not selected will be hidden from view and inherited from the parent product.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['force_variant_options'] = array('Always show variant attributes', 'Show a variant attribute (select, radio) even if there is only one choice.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['shipping_exempt']       = array('Exempt from shipping', 'Check if items of this product type are not a shipped item (such as downloadable products).');
$GLOBALS['TL_LANG']['tl_iso_producttype']['downloads']             = array('Enable downloads', 'Check here if this product type has downloads.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_producttype']['new']                   = array('New product type', 'Create new product type.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['edit']                  = array('Edit product type', 'Edit product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttype']['copy']                  = array('Copy product type definiton', 'Copy definition of product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttype']['delete']                = array('Delete product type', 'Delete product type ID %s');
$GLOBALS['TL_LANG']['tl_iso_producttype']['show']                  = array('product type details', 'Show details of product type ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_producttype']['name_legend']           = 'Product type settings';
$GLOBALS['TL_LANG']['tl_iso_producttype']['description_legend']    = 'Description';
$GLOBALS['TL_LANG']['tl_iso_producttype']['prices_legend']         = 'Prices';
$GLOBALS['TL_LANG']['tl_iso_producttype']['template_legend']       = 'Templates';
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes_legend']     = 'Product attributes';
$GLOBALS['TL_LANG']['tl_iso_producttype']['variants_legend']       = 'Variant attributes';
$GLOBALS['TL_LANG']['tl_iso_producttype']['expert_legend']         = 'Expert settings';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['legend']      = array('Grouping', 'Group fields by topic (legends)');
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['tl_class']    = array('Alignment', 'Enter a tl_class to override alignment for this attribute.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['mandatory']   = array('Mandatory', 'You can override the default mandatory-ness here.');
$GLOBALS['TL_LANG']['tl_iso_producttype']['attributes']['default']     = 'Default';
$GLOBALS['TL_LANG']['tl_iso_producttype']['duplicatePriceAttribute']   = 'These attributes should be defined only for attributes or only for variant attributes: %s';
$GLOBALS['TL_LANG']['tl_iso_producttype']['noVariantAttributes']       = 'You must select at least one variant option (one of those: %s).';
