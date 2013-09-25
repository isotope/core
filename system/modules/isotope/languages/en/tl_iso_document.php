<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_iso_document']['name']                      = array('Document name', 'Enter a name for this document. This will only be used in the backend.');
$GLOBALS['TL_LANG']['tl_iso_document']['type']                      = array('Type of document', 'Select a particular document rendering class.');
$GLOBALS['TL_LANG']['tl_iso_document']['logo']                      = array('Logo', 'Select a logo.');
$GLOBALS['TL_LANG']['tl_iso_document']['documentTitle']             = array('Document title', 'You can use simple tokens ("collection_*" whereas * equals the database column of the collection) to render your file title (e.g. "Invoice title ##collection_document_number##").');
$GLOBALS['TL_LANG']['tl_iso_document']['fileTitle']                 = array('File title', 'You can use simple tokens ("collection_*" whereas * equals the database column of the collection) to render your file title (e.g. "invoice_##collection_document_number##").');
$GLOBALS['TL_LANG']['tl_iso_document']['documentTpl']               = array('Document template', 'Choose a template you want to render this document with.');
$GLOBALS['TL_LANG']['tl_iso_document']['collectionTpl']             = array('Collection template', 'Choose a collection template you want to render the products with.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_iso_document']['new']                       = array('New document', 'Create a new document');
$GLOBALS['TL_LANG']['tl_iso_document']['edit']                      = array('Edit document', 'Edit document ID %s');
$GLOBALS['TL_LANG']['tl_iso_document']['copy']                      = array('Copy document', 'Copy document ID %s');
$GLOBALS['TL_LANG']['tl_iso_document']['delete']                    = array('Deletedocument', 'Delete document ID %s');
$GLOBALS['TL_LANG']['tl_iso_document']['show']                      = array('Document details', 'Show details of document ID %s');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_iso_document']['type_legend']               = 'Name & type';
$GLOBALS['TL_LANG']['tl_iso_document']['config_legend']             = 'General configuration';
$GLOBALS['TL_LANG']['tl_iso_document']['template_legend']           = 'Template';