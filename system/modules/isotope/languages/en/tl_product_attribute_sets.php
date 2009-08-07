<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * Language file for table tl_product_attribute_sets (en).
 *
 * PHP version 5
 * @copyright  Martin Komara 2007 
 * @author     Martin Komara 
 * @package    product attribute setModule 
 * @license    GPL 
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['name'] = array('Product Attribute Set Name', '');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['store_id'] = array('Store Settings', 'Select the global store settings that apply to this attribute set.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['noTable'] = array('Preserve Product Attributes', 'If checked the database table will not be updated when adding or removing product attribute set fields. Useful for editing existing tables.');

$GLOBALS['TL_LANG']['tl_product_attribute_sets']['addImage']     = array('Add an image', 'If you choose this option, an image will be added to the product listing.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['singleSRC']    = array('Image file', 'Please select the image to be displayed in the product attribute set types listing.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['size']        = array('Image width and height', 'Please enter either the image width, the image height or both measures to resize the image. If you leave both fields blank, the original image size will be displayed.');

$GLOBALS['TL_LANG']['tl_product_attribute_sets']['format'] = array('Format title string', 'Enter the format string to use as entry title (optional).<br />Examples: <em>{{image_field::w=100&h=80}} {{title_field}}</em>: <em>{{checkbox_field::src=all.gif}} {{checkbox_field}}</em>');

$GLOBALS['TL_LANG']['tl_product_attribute_sets']['iso_list_layout']      = array('Product List Template Override', 'Please choose a list layout. If left blank, the default as chosen in the module definition record will be used. You can add custom list layouts to folder <em>templates</em>. List template files start with <em>iso_list_</em> and require file extension <em>.tpl</em>.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['iso_reader_layout']  = array('Product Reader Template Override', 'Please choose a reader template. If left blank, the default as chosen in the module definition record will be used. You can add custom reader templates to folder <em>templates</em>. Reader template files start with <em>iso_reader_</em> and require file extension <em>.tpl</em>.');

/**
 * Reference
 */
//$GLOBALS['TL_LANG']['tl_product_attribute_sets'][''] = '';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['new']    = array('New product attribute set', 'Create new product attribute set.');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['edit']   = array('Manage items', 'Manage items of product attribute set ID %s');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['copy']   = array('Copy product attribute set definiton', 'Copy definition of product attribute set ID %s');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['delete'] = array('Delete product attribute set', 'Delete product attribute set ID %s');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['show']   = array('product attribute set details', 'Show details of product attribute set ID %s');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['editheader'] = array('Edit product attribute set', 'Edit the product attribute set');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['attributes'] = array('Define Product Attributes', 'Define product attributes for attribute set ID %s');
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['regenerateDca'] = array('Regenerate DCA', 'Regenerate DCA for all product attribute sets');

/**
 * Misc.
 */
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemFormat'] = ' <span style="color:#b3b3b3;"><em>(%s %s)</em></span>';
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemSingle'] = 'item';
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemPlural'] = 'items';


$GLOBALS['TL_LANG']['tl_product_attribute_sets']['regenerateDcaExplanation'] = 'TYPOlight\'s data container array (DCA) tells TYPOlight how to render back end forms. In order to provide convenient way to edit your product attribute set items in back end, product attribute set module creates DCA for you automatically. However sometimes DCA and your product attribute set may get out of sync, for example after you install new product attribute set version. In this case use the Regenerate DCA button below to synchronize your product attribute sets with TYPOlight.';
$GLOBALS['TL_LANG']['tl_product_attribute_sets']['regenerateDcaSuccess'] = 'TYPOlight\'s data container array has been regenerated successfully.';

?>