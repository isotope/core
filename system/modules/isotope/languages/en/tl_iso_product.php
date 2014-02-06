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
$GLOBALS['TL_LANG']['tl_iso_product']['id']                        = array('Product ID');
$GLOBALS['TL_LANG']['tl_iso_product']['pages']                     = array('Categories', 'Select a category (page-based categories take advantage of Contao pages features such as navigation automation, protection, templates, and full integration with content elements.');
$GLOBALS['TL_LANG']['tl_iso_product']['type']                      = array('Product type', 'Select your product type. Product types are defined in the store configuration.');
$GLOBALS['TL_LANG']['tl_iso_product']['alias']                     = array('Alias', 'You can enter a unique alias for this product. It will be automatically generated from the name if empty.');
$GLOBALS['TL_LANG']['tl_iso_product']['name']                      = array('Name', 'Please enter the name of this product.');
$GLOBALS['TL_LANG']['tl_iso_product']['sku']                       = array('SKU', 'Please enter a unique stock keeping unit for this product.');
$GLOBALS['TL_LANG']['tl_iso_product']['shipping_weight']           = array('Shipping weight', 'Please enter the shipping weight for this product. This can be used to calculate shipping cost.');
$GLOBALS['TL_LANG']['tl_iso_product']['teaser']                    = array('Teaser', 'Please enter the teaser.');
$GLOBALS['TL_LANG']['tl_iso_product']['description']               = array('Description', 'Please enter the product description.');
$GLOBALS['TL_LANG']['tl_iso_product']['meta_title']                = array('Meta title', 'Meta title is used as page title in the product detail view. If you leave it empty, the product name will be used.');
$GLOBALS['TL_LANG']['tl_iso_product']['meta_description']          = array('Meta description', 'Meta description will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_product']['meta_keywords']             = array('Meta keywords', 'Meta keywords will be placed in the header on product detail page, for search engine optimization.');
$GLOBALS['TL_LANG']['tl_iso_product']['price']                     = array('Price &amp; tax class', 'Please enter the price(s) for this product and select the tax class.');
$GLOBALS['TL_LANG']['tl_iso_product']['shipping_exempt']           = array('Exempt from shipping', 'Check if item is not a shipped item (such as downloadable products).');
$GLOBALS['TL_LANG']['tl_iso_product']['baseprice']                 = array('Base price amount', 'Enter the amount to calculate the base price (e.g. "1500" if your product is 1500 grams).');
$GLOBALS['TL_LANG']['tl_iso_product']['images']                    = array('Images', 'Upload images to this product. Please save the product after selecting a file.');
$GLOBALS['TL_LANG']['tl_iso_product']['protected']                 = array('Protect product', 'Restrict product access to certain member groups.');
$GLOBALS['TL_LANG']['tl_iso_product']['groups']                    = array('Allowed member groups', 'These groups will be able to access the product.');
$GLOBALS['TL_LANG']['tl_iso_product']['guests']                    = array('Show to guests only', 'Hide the product if there is an authenticated user.');
$GLOBALS['TL_LANG']['tl_iso_product']['cssID']                     = array('CSS ID/class', 'Here you can set an ID and one or more classes.');
$GLOBALS['TL_LANG']['tl_iso_product']['published']                 = array('Publish product', 'Click here to show this product on your website.');
$GLOBALS['TL_LANG']['tl_iso_product']['start']                     = array('Start date', 'Do not show this product before the date specified.');
$GLOBALS['TL_LANG']['tl_iso_product']['stop']                      = array('Stop date', 'Do not show this product after the date specified.');
$GLOBALS['TL_LANG']['tl_iso_product']['inherit']                   = array('Inherited attributes', 'Check the fields you want to inherit from base product.');

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_product']['source']                    = array('Source folder', 'Please choose the folder where product assets are located.');
$GLOBALS['TL_LANG']['tl_iso_product']['mmSrc']                     = 'Preview';
$GLOBALS['TL_LANG']['tl_iso_product']['mmAlt']                     = 'Alternate text';
$GLOBALS['TL_LANG']['tl_iso_product']['mmLink']                    = 'Link target';
$GLOBALS['TL_LANG']['tl_iso_product']['mmDesc']                    = 'Description';
$GLOBALS['TL_LANG']['tl_iso_product']['mmTranslate']               = 'Translate';
$GLOBALS['TL_LANG']['tl_iso_product']['mmTranslateNone']           = array('None', 'Do not translate this image.');
$GLOBALS['TL_LANG']['tl_iso_product']['mmTranslateText']           = array('Text', 'Translate alt text and description for this image.');
$GLOBALS['TL_LANG']['tl_iso_product']['mmTranslateAll']            = array('All', 'Do not include this image in translated version.');
$GLOBALS['TL_LANG']['tl_iso_product']['variantValuesLabel']        = 'Variant';
$GLOBALS['TL_LANG']['tl_iso_product']['showVariants']              = 'Show product variants';
$GLOBALS['TL_LANG']['tl_iso_product']['priceBlankOptionLabel']     = 'Tax-free';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_product']['new']                       = array('New product', 'Create new product');
$GLOBALS['TL_LANG']['tl_iso_product']['new_variant']               = array('Add variant', 'Add new variant to a given product');
$GLOBALS['TL_LANG']['tl_iso_product']['edit']                      = array('Edit product', 'Edit product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['copy']                      = array('Copy product', 'Copy product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['cut']                       = array('Move product', 'Move product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['delete']                    = array('Delete product', 'Delete product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['toggle']                    = array('Publish/unpublish product', 'Publish/unpublish product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['show']                      = array('Product details', 'Show details of product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['filter']                    = 'Advanced filter:';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_noimages']           = 'Without images';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_nocategory']         = 'Unassigned products';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_new']                = 'New products';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_new_today']          = 'Added today';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_new_week']           = 'Added this week';
$GLOBALS['TL_LANG']['tl_iso_product']['filter_new_month']          = 'Added this month';
$GLOBALS['TL_LANG']['tl_iso_product']['sorting']                   = 'Manually sort products in a page';
$GLOBALS['TL_LANG']['tl_iso_product']['product_groups']            = array('Product groups', 'Manage product groups');
$GLOBALS['TL_LANG']['tl_iso_product']['import']                    = array('Import assets', 'Import images and other media from a folder');
$GLOBALS['TL_LANG']['tl_iso_product']['prices']                    = array('Manage prices', 'Click the button to manage advanced prices for this product.');
$GLOBALS['TL_LANG']['tl_iso_product']['prices']['apply_and_close'] = 'Apply and close';
$GLOBALS['TL_LANG']['tl_iso_product']['variants']                  = array('Product variants', 'Show variants for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['generate']                  = array('Generate variants', 'Generate variants for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['related']                   = array('Related products', 'Manage related products for product ID %s');
$GLOBALS['TL_LANG']['tl_iso_product']['downloads']                 = array('Downloads', 'Edit downloads for product ID %s', 'Downloads: %s. ');
$GLOBALS['TL_LANG']['tl_iso_product']['group']                     = array('Move to group', 'Move product ID %s to a group');
$GLOBALS['TL_LANG']['tl_iso_product']['groupSelected']             = 'Group';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_product']['general_legend']            = 'General Settings';
$GLOBALS['TL_LANG']['tl_iso_product']['meta_legend']               = 'Meta data';
$GLOBALS['TL_LANG']['tl_iso_product']['pricing_legend']            = 'Pricing Settings';
$GLOBALS['TL_LANG']['tl_iso_product']['inventory_legend']          = 'Inventory Settings';
$GLOBALS['TL_LANG']['tl_iso_product']['shipping_legend']           = 'Shipping Settings';
$GLOBALS['TL_LANG']['tl_iso_product']['options_legend']            = 'Product Options Settings';
$GLOBALS['TL_LANG']['tl_iso_product']['media_legend']              = 'Media Management';
$GLOBALS['TL_LANG']['tl_iso_product']['expert_legend']             = 'Expert settings';
$GLOBALS['TL_LANG']['tl_iso_product']['publish_legend']            = 'Publishing';

/**
 * Table format
 */
$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min']        = 'Quantity';
$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['min_format'] = 'from %s pcs.';
$GLOBALS['TL_LANG']['tl_iso_product']['price_tiers']['price']      = 'Price';

/**
 * Import assets description
 */
$GLOBALS['TL_LANG']['tl_iso_product']['importAssetsDescr']         = '<p>There are two different ways this feature may be used:</p>
<ol>
    <li>The source folder you select contains files that either match the product\'s SKU or name. Isotope eCommerce is going to match all the files against all of your products and import them into the respective product\'s assets folder.</li>
    <li>The source folder you select contains subfolders that either match the product\'s SKU or name. Isotope eCommerce is going to match all the subfolders against all of your products and import everything within one subfolder into the respective product\'s asset folder.</li>
</ol>
<p>Hint: Any developer can provide you with other matching rules than just the product\'s SKU or name.</p>
';