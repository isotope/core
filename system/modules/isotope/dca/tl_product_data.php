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
 * This is the data container array for table tl_catalog_types.
 *
 * PHP version 5
 * @copyright  Winans Creative 2008 
 * @author     Fred Bliss
 * @package    CatalogModule 
 * @license    GPL 
 * @filesource
 */


/**
 * Table tl_product_data
 */
$GLOBALS['TL_DCA']['tl_product_data'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'DynamicTable',
		'ptable'				 	  => 'tl_product_attribute_sets',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'oncreate_callback'			  => array
			(
				array('ProductCatalog', 'initializeDCA'),
			)
	)
);


class tl_product_data extends Backend
{
	
}

?>