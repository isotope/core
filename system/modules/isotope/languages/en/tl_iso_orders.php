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
$GLOBALS['TL_LANG']['tl_iso_orders']['order_subtotal'] = array('Subtotal','');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_tax'] = array('Tax Cost','');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_shipping_cost'] = array('Shipping Cost','');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method'] = array('Shipping Method','');
$GLOBALS['TL_LANG']['tl_iso_orders']['status'] = array('Order Status','');
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_address_id'] = array('Shipping Address','');
$GLOBALS['TL_LANG']['tl_iso_orders']['billing_address_id'] = array('Billing Address','');
$GLOBALS['TL_LANG']['tl_iso_orders']['order_comments'] = array('Comments','');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_message'] = array('Gift Message','');
$GLOBALS['TL_LANG']['tl_iso_orders']['gift_wrap'] = array('Gift Wrap','');
/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['shipping_method_labels']['ups_ground'] = 'UPS Ground';

/**
 * Additional Operations
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['authorize_process_payment'] = array('Authorizet.net Point-of-sale Terminal','Conduct a transaction using the Authorize.net point-of-sale terminal');
$GLOBALS['TL_LANG']['tl_iso_orders']['print_order'] = array('Print this order','Print an invoice for the current order');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_orders']['new']    = array('New Order', 'Create a New order');
$GLOBALS['TL_LANG']['tl_iso_orders']['edit']   = array('Edit Order', 'Edit order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['copy']   = array('Copy Order', 'Copy order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['delete'] = array('Delete Order', 'Delete order ID %s');
$GLOBALS['TL_LANG']['tl_iso_orders']['show']   = array('Order Details', 'Show details of order ID %s');

?>