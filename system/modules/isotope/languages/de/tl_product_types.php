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
 * Language file for table tl_product_types (en).
 *
 * PHP version 5
 * @copyright  Winans Creative / Fred Bliss 2009 
 * @author     Fred Bliss
 * @package    Isotope
 * @license    LGPL 
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_product_types']['name'] = array('Product Type Name', '');
$GLOBALS['TL_LANG']['tl_product_types']['alias'] = array('Product Type Key', 'This is used internally by TYPOlight for product editing functionality. It can also be used by developers if there is a need to query or perform logic on specific product types.');
$GLOBALS['TL_LANG']['tl_product_types']['description'] = array('Product Type Description', '');
$GLOBALS['TL_LANG']['tl_product_types']['attributes'] = array('Product Attributes', 'Select the collection of attributes that should be included for this product type.');

/**
 * Reference
 */
//$GLOBALS['TL_LANG']['tl_product_types'][''] = '';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_product_types']['new']    = array('New product type', 'Create new product type.');
$GLOBALS['TL_LANG']['tl_product_types']['edit']   = array('Edit product type', 'Edit product type ID %s');
$GLOBALS['TL_LANG']['tl_product_types']['copy']   = array('Copy product type definiton', 'Copy definition of product type ID %s');
$GLOBALS['TL_LANG']['tl_product_types']['delete'] = array('Delete product type', 'Delete product type ID %s');
$GLOBALS['TL_LANG']['tl_product_types']['show']   = array('product type details', 'Show details of product type ID %s');


/**
 * Legends
 */

$GLOBALS['TL_LANG']['tl_product_types']['name_legend'] = 'Product Type Name & Description';
$GLOBALS['TL_LANG']['tl_product_types']['attributes_legend'] = 'Product Type Attributes';

?>