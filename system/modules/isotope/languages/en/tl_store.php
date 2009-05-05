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
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Fields
 */
//$GLOBALS['TL_LANG']['tl_store'][''] = array('', '');

$GLOBALS['TL_LANG']['tl_store']['store_configuration_name']			= array('Store Configuration Name', '');
$GLOBALS['TL_LANG']['tl_store']['cookie_duration']					= array('Duration (in days) of Shopping Cart Information', 'Items and their related information will be retrievable for this many days for non-registered members.  A value of 0 means after the session, the items are removed. For registered members cart data is always remembered.');
$GLOBALS['TL_LANG']['tl_store']['checkout_login_module']			= array('Checkout Login Module','Select a login module to insert into the checkout module.');
$GLOBALS['TL_LANG']['tl_store']['root_asset_import_path']			= array('Root Asset Import Path','A single directory which contains all "to-be-imported" images and media associated with a given record.  Isotope will look for a corresponding file in this folder instead of in the product\'s standard source folder.');
$GLOBALS['TL_LANG']['tl_store']['enabled_modules']					= array('Enabled Isotope Modules','Select certain e-commerce modules to enable on the front end (for the current store configuration).');
$GLOBALS['TL_LANG']['tl_module']['productReaderJumpTo']				= array('Product Reader Jump to page', 'This setting defines to which page a user will be redirected when clicking a product for more info.');
$GLOBALS['TL_LANG']['tl_module']['cartJumpTo']						= array('Shopping Cart Jump to page', 'This setting defines to which page a user will be redirected when requesting a full shopping cart view.');
$GLOBALS['TL_LANG']['tl_module']['checkoutJumpTo']					= array('Checkout Jump to page', 'This setting defines to which page a user will be redirected when completing their transaction.');
																	
$GLOBALS['TL_LANG']['tl_store']['missing_image_placeholder']		= array('Missing Image Placeholder', 'This image will be used if an image file cannot be found or none are associated with a product.');
$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_width']			= array('Thumbnail Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['thumbnail_image_height']			= array('Thumbnail Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['medium_image_width']				= array('Medium Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['medium_image_height']				= array('Medium Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['large_image_width']				= array('Large Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['large_image_height']				= array('Large Standard Height', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['currency']							= array('Currency', 'Please select a currency for this store.');
$GLOBALS['TL_LANG']['tl_store']['currencySymbol']					= array('Use currency icon', 'Use a currency icon ($, €) if available.');

$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_width']	= array('Gallery Thumbnail Standard Width', 'In pixels.');
$GLOBALS['TL_LANG']['tl_store']['gallery_thumbnail_image_height']	= array('Gallery Thumbnail Standard Height', 'In pixels.');

/**
 * Reference
 */


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_store']['new']    = array('New Store', 'Create a New Store.');
$GLOBALS['TL_LANG']['tl_store']['edit']   = array('Edit Store', 'Edit Store ID %s.');
$GLOBALS['TL_LANG']['tl_store']['copy']   = array('Copy Store', 'Copy Store ID %s.');
$GLOBALS['TL_LANG']['tl_store']['delete'] = array('Delete Store', 'Delete Store ID %s.  This will not delete related assets but rather the initial store configuration.');
$GLOBALS['TL_LANG']['tl_store']['show']   = array('Show Store Details', 'Show details for store ID %s.');

?>