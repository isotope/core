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
 * Language file for table tl_content (en).
 *
 * PHP version 5
 * @copyright  Winans Creative / Fred Bliss 2008
 * @author     Fred Bliss 
 * @package    Isotope
 * @license    GPL 
 * @filesource
 */


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_tax_rate']['country_id'] = array('Country','Select a country this tax class applies to.');
$GLOBALS['TL_LANG']['tl_tax_rate']['region_id'] = array('State/Region','Select a state or region this tax class applies to.');
$GLOBALS['TL_LANG']['tl_tax_rate']['postcode'] = array('Postal Code','Specify a postal code this tax class applies to.');
$GLOBALS['TL_LANG']['tl_tax_rate']['code'] = array('Code','A human-readable code for this tax class.');
$GLOBALS['TL_LANG']['tl_tax_rate']['rate'] = array('Tax Rate','A rate in percent this tax is set at.');


/*
`country_id` int(10) unsigned NOT NULL default '0',
  `region_id` int(10) unsigned NOT NULL default '0',
  `postcode` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `rate` double default '0.00',
*/
/**
 * Reference
 */
//$GLOBALS['TL_LANG']['tl_tax_rate']['shipping_method_labels']['ups_ground'] = 'UPS Ground';

//$GLOBALS['TL_LANG']['tl_tax_rate']['']['pending'] = 'Pending';
//$GLOBALS['TL_LANG']['tl_tax_rate']['order_status_labels']['processing'] = 'Processing';
//$GLOBALS['TL_LANG']['tl_tax_rate']['order_status_labels']['shipped'] = 'Shipped';
//$GLOBALS['TL_LANG']['tl_tax_rate']['order_status_labels']['complete'] = 'Complete';
//$GLOBALS['TL_LANG']['tl_tax_rate']['order_status_labels']['on_hold'] = 'On Hold';

/**
 * Additional Operations
 */
//$GLOBALS['TL_LANG']['tl_tax_rate']['authorize_process_payment'] = array('Authorizet.net Point-of-sale Terminal','Conduct a transaction using the Authorize.net point-of-sale terminal');
//$GLOBALS['TL_LANG']['tl_tax_rate']['print_order'] = array('Print this tax rate','Print an invoice for the current tax rate');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_tax_rate']['new']    = array('New tax rate', 'Create a New tax rate');
$GLOBALS['TL_LANG']['tl_tax_rate']['edit']   = array('Edit tax rate', 'Edit tax rate ID %s');
$GLOBALS['TL_LANG']['tl_tax_rate']['copy']   = array('Copy tax rate', 'Copy tax rate ID %s');
$GLOBALS['TL_LANG']['tl_tax_rate']['delete'] = array('Delete tax rate', 'Delete tax rate ID %s');
$GLOBALS['TL_LANG']['tl_tax_rate']['show']   = array('Order Details', 'Show details of tax rate ID %s');

?>