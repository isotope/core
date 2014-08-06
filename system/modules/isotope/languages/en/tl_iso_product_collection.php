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
$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_number']         = array('Order ID');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['member']                  = array('Member (empty for guests)');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['uniqid']                  = array('Unique ID');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['order_status']            = array('Order status', 'Select the status of this order.');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['locked']                  = array('Placed');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['source_collection_id']    = array('Source (Cart ID)');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['date_paid']               = array('Payment date', 'Enter a date when this order has been paid.');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['date_shipped']            = array('Shipped date', 'Enter a date when this order has been shipped.');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['config_id']               = array('Shop configuration');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['payment_id']              = array('Payment method');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_id']             = array('Shipping method');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['billing_address_id']      = array('Billing address');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_address_id']     = array('Shipping address');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['subtotal']                = array('Subtotal');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['tax_free_subtotal']       = array('Subtotal without tax');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['total']                   = array('Total');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['tax_free_total']          = array('Total without tax');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['currency']                = array('Currency');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['language']                = array('Language');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['notes']                   = array('Order notes','If you would like to convey information to other backend users, please do so here.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_product_collection']['edit']                    = array('Edit order', 'Edit order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['copy']                    = array('Copy order', 'Copy order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['delete']                  = array('Delete order', 'Delete order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['show']                    = array('Order details', 'Show details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['payment']                 = array('Payment details', 'Show payment details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping']                = array('Shipping details', 'Show shipping details of order ID %s');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document']          = array('Print a document', 'Print order ID %s with a document of your choice');

/**
 * Document choice
 */
$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_choice']         = array('Document', 'Choose the document you would like to print the data with.');
$GLOBALS['TL_LANG']['tl_iso_product_collection']['print']                   = 'Print';

/**
 * Reference
 */
$GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']               = 'The status of your order has been updated.';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']  = 'Notifications (e.g. email to client) have been sent.';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']    = 'Notifications (e.g. email to client) clould not be sent. Check the system log.';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['noEmailData']                     = 'No additional email data available.';

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_product_collection']['status_legend']           = 'Order status';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['details_legend']          = 'Order details';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['email_legend']            = 'Email data';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['billing_address_legend']  = 'Billing address data';
$GLOBALS['TL_LANG']['tl_iso_product_collection']['shipping_address_legend'] = 'Shipping address data';
